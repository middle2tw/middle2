# middle2 base image 更新 SOP

PHP 等套件出現資安問題時，更新 `middle2` base image，並讓所有專案換上新版的流程。
整理自 2026-10-22 CVE-2026-109618/109619（PHP RCE）的處理過程。

## 0. 先了解架構（會影響每一步）

- 所有專案共用 `docker-registry-linode-1.middle2.com:5000/middle2` 這個 base image（`dockers/Dockerfile`），
  專案 image `image-<project>` 是在 base image 上 clone 專案、跑 build 步驟產生的。
- **只更新 base image 不會改變任何線上服務**，要每個專案重新 build，再 release 正在跑的 node，新版才會生效。
- `build-docker-project-image` 在 **load balancer 主機**上執行（同一台承受所有流量），build 會吃它的 CPU。
- `loadbalancer.js` 一次只啟動一個專案的 node（刻意排隊，平行啟動曾讓 load balancer 主機的 load 爆增），
  某個專案啟動很慢或卡住，其他所有需要新 node 的專案都會跟著等。
- release 的 node 會變成 OVER，由 `cron/1min/updatenodes.php` 每分鐘 reset 回 unused。
- Wait node 是保留給 cron 用的，不接 web 流量；被 cron 重複使用時 container 不會重建，一定要 release 才會換新 image。
- PHP-FPM 讀的是 `/etc/php/7.4/fpm/php.ini`（Debian 預設值），`dockers/config/etc/php.ini` 是 CentOS 時代的遺留檔，**沒有作用**。

## 1. 確認影響範圍

1. 讀上游公告，確認受影響版本、修正版本，以及官方的緩解措施。
2. 確認 image 裡實際的版本：
   ```
   docker run --rm middle2:latest sh -c 'dpkg -l | grep php7.4; php -v'
   ```
3. 查 Debian security tracker（`https://security-tracker.debian.org/tracker/<CVE>`）有沒有對應發行版的修補。
   - bullseye 的 LTS 已經結束，tracker 上通常不會有 bullseye 的修補，要自己 patch（見步驟 2）。

## 2. 修改 Dockerfile 並在本機 build

在 `dockers/` 底下：

- **自己 patch Debian 套件**：參考 Dockerfile 的 `php-build` stage。
  把補丁放進 `dockers/php-patches/`，build 時會加進 Debian 原始碼包的 `debian/patches/series`，
  版號加上 `+middle2uN` 後重新打包。
  - 上游的補丁是對官方原始碼寫的，Debian LTS 可能已經 backport 了其中一部分。
    要先在 bullseye container 裡 `apt-get source` 後用 `patch --dry-run` 試套，
    特別注意「看似套用成功、其實重複插入程式碼」的 hunk（fuzz/offset 套用的那種要逐一檢查）。
  - 調整過的補丁要在檔頭註明來源和調整了什麼（DEP-3 格式）。
- 每次修改後，補丁的 `+middle2uN` 版號要遞增。
- 完整 build 一次大約 30–60 分鐘（php7.4 要編譯好幾個 SAPI）：
  ```
  docker build . -t middle2-new
  ```

**已知的 build 問題**（2026-10 時的狀態）：
- bullseye-security 已經搬到 `archive.debian.org`，Dockerfile 已改用這個來源。
  如果 `deb.debian.org` 上的 bullseye 本體也搬走了，同樣要改。
- NodeSource 的 `setup_14.x` 會匯入不對的金鑰，所以改用 repo 裡的 `apt-keys/nodesource-legacy.gpg`。

## 3. 在本機驗證 image

```
docker run --rm middle2-new sh -c 'dpkg -l | grep php7.4; php -v; node -v; ruby -v; python3 -V'
diff <(docker run --rm middle2:latest php -m) <(docker run --rm middle2-new php -m)
```

再起一個 container，啟動 php-fpm 和 apache，用 curl 打一支測試 PHP：
- 確認實際生效的 ini 值（`ini_get`）。
- 測 multipart 上傳、fatal error、記憶體用光之後，後續的 request 是否還正常。

注意：node 的 container 沒有設 `--memory` 限制（`scripts/root@nodes-ssh_serve`），
**不要把 `memory_limit` 設成 -1**，否則單一 request 就能吃光整台 node 的記憶體。

## 4. 用單一專案測試

```
docker tag middle2-new docker-registry-linode-1.middle2.com:5000/middle2-new
docker --config /srv/config/docker push docker-registry-linode-1.middle2.com:5000/middle2-new
sudo ssh git@middle2.com build-docker-project-image <project>.git clean-build base=middle2-new
```

- 一般使用者的 ssh key 是 management key，不能 build，**一定要 `sudo ssh`**（用的是 root 的 deploy key）。
- build 只會推 image，要到 https://middle2.com/admin/nodeservers release 該專案的 node 才會生效。
- **至少測兩種執行方式**：
  - Apache + PHP-FPM 的一般 PHP 專案（沒有 Procfile、也不是 Laravel）。
  - Laravel `artisan serve` 的專案（有 `artisan`）。
- 比對 release 前後頁面的內容，以及 `server` / `x-powered-by` header。
- 確認新 node 在後台會從 `WebNode processing` 變成 `WebNode`（不會一直卡在 processing）。

## 5. 替換正式的 base image

先備份舊的，再推新的：
```
R=docker-registry-linode-1.middle2.com:5000
docker tag $R/middle2:latest $R/middle2:<舊 image 的日期>
docker --config /srv/config/docker push $R/middle2:<舊 image 的日期>
docker tag middle2-new $R/middle2:latest
docker tag middle2-new middle2:latest
docker --config /srv/config/docker push $R/middle2:latest
```

要退回時，把 `latest` 指回備份的 tag 再 push。

## 6. 重新 build 所有專案

在 load balancer 主機上，用 `dockers/tools/` 底下的 script：

```
sh dockers/tools/list-projects.sh > projects.tsv
# 挑出要先處理的專案，例如會跑 PHP 的那些
awk -F'\t' '$2=="php-fpm" || $2=="laravel" {print $1}' projects.tsv > batch1.txt
sh dockers/tools/rebuild-projects.sh batch1.txt results.tsv builds/
```

- 一次只 build 一個專案，1 分鐘 load average 到 4 以上就暫停。2026-10 那次 87 個專案大約跑了 40 分鐘。
- 中途停掉的話，重跑會自動跳過 `results.tsv` 裡已經有結果的專案。
- 失敗時看 `builds/<project>.log`。常見的原因是專案自己的相依套件
  （例如用了 `git://github.com/...`、Python 套件裝不起來），跟 base image 無關。
  build 失敗不會推新 image，所以那些網站會繼續跑舊 image。
- 跑完後確認每個成功的 image 都是新版：
  ```
  docker run --rm --entrypoint dpkg docker-registry-linode-1.middle2.com:5000/image-<project> -s php7.4-common
  ```
- 磁碟大約用到 79%。空間不夠時可以 `docker system prune`，有 tag 的 base image 不會被刪。

## 7. 依序 release 舊的 node

```
sh dockers/tools/image-times.sh results.tsv > image-times.tsv
# 步驟 4 測試過的專案也要加進來，格式是 "<project>\t<image 建立時間 unix time>"
php dockers/tools/release-old-nodes.php image-times.tsv          # dry-run，只列出計畫
php dockers/tools/release-old-nodes.php image-times.tsv --run    # 實際執行
```

`release-old-nodes.php` 的規則：
- 只 release `start_at` 早於該專案新 image 建立時間的 node（也就是還在跑舊 image 的），
  CronNode 和 processing 狀態的 node 不動。
- **Wait node 先 release**（不接 web 流量，不需要等），接著依 `start_at` 從早到晚處理 WebNode。
- 每次 release WebNode 之前：
  - load 太高就等。
  - 同一個 node group 的 unused node 至少要 8 個、OVER 不能超過 3 個，否則就等 cron 回收。
  - 只要有 node 在 `WebNode processing`（load balancer 的啟動佇列在忙），就先等。
- release 後等 15 秒。如果有 request 進來讓專案重啟，就等新 node 變成 `WebNode` 才繼續，
  所以持續被存取的服務會一個一個處理。
- 有 node 卡在 processing 超過 330 秒，script 會直接停下來，要人工檢查。
- 中途停掉的話，重跑會重新從 DB 讀取，已經換成新版的 node 會被略過。
  停 script 時用 PID 來 kill，不要用 `pkill -f`，以免連自己的 shell 一起砍掉。

## 8. 完成後檢查

- 線上網站的狀態碼、`server` / `x-powered-by` header 有沒有換成新版。
- 後台沒有 node 一直卡在 `WebNode processing`，OVER 都被回收了，unused node 的數量也恢復。
- 列出 build 失敗、還在跑舊 image 的專案，另外通知專案負責人。
