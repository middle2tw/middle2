# Middle2 (Hisoku) PaaS — 系統說明文件

> 給 AI Agent 快速了解本專案用。本文件涵蓋系統架構、檔案結構、資料庫結構、服務元件，以及重大開發 milestone。

---

## 一、專案概述

Middle2（舊稱 Hisoku）是一個自行開發的 **PaaS (Platform as a Service)** 平台，類似簡化版的 Heroku。使用者可以把程式碼 `git push` 上來，平台自動分配 Docker container 執行應用程式，並透過 Load Balancer 對外提供服務。

**核心域名：**
- 管理後台：`middle2.com`（主頁）
- 應用程式：`{project-name}.hisokuapp.ronny.tw`（或其他 APP_SUFFIX 設定）
- Git Server：`git.middle2.com`
- Elasticsearch：`elastic-2.middle2.com:9200`

**技術棧：**
- 控制面板：PHP + [PixFramework](https://github.com/pixnet/pixframework)（自製 MVC framework）
- Load Balancer：Node.js（`loadbalancer.js`）
- 應用容器：Docker（Debian Bullseye based）
- 資料庫：MySQL
- 快取：Memcached
- 日誌：Scribed（Facebook log system）
- 搜尋附加服務：Elasticsearch
- 監控：AWS SNS 警報

---

## 二、系統架構

```
使用者瀏覽器
     │
     ▼
Load Balancer (loadbalancer.js)
  ├── HTTP :80 / HTTPS :443
  ├── SNI SSL 多憑證支援
  ├── 查詢 MySQL 取得 project → webnode 對應
  ├── 若無可用 node → SSH 到 Node Server 做 clone + start
  └── Reverse Proxy → WebNode (ip:port)
     
Git Server (git.middle2.com)
  ├── SSH 認證 → scripts/ssh-serve
  ├── git push → pre-receive hook → 更新 project.commit
  └── 支援 run/log/tunnel/sftp 指令

管理後台 (webdata/ PHP App)
  ├── 使用者管理、專案管理、Addon 管理
  ├── 透過 SSH 控制 Node Server
  └── 管理員介面 (Admin panel)

Node Servers (多台實體機/VM)
  ├── 每台有 1~100 個 slot (port 20001~20100)
  ├── 每個 slot = 一個 Docker container
  ├── SSH 指令：init / clone / restart-web / shutdown / run / check_alive
  └── Docker image: Debian Bullseye + PHP + Node.js + Python3 + Ruby

Cron Worker (scripts/cron-worker)
  ├── 每秒檢查 cron_job 表
  ├── fork 子程序執行 job
  └── Worker job (period=99) 長期執行保持存活

Background Daemons
  ├── cron/1min/health-check — 節點健康檢查
  ├── cron/1min/updatenodes.php — 更新 node 資訊、回收異常節點
  ├── cron/1min/run-worker — Worker job 監控
  └── cron/1day/analytics — 流量統計
```

### 請求流程（web request lifecycle）

```
1. 瀏覽器請求 {project}.hisokuapp.ronny.tw
2. Load Balancer 從 mapping_cache 找 project（cache miss 就查 MySQL）
3. 查詢 webnode 表找 STATUS_WEBNODE (status=10) 且 commit 相符的 node
4. 若無可用 node → 找 STATUS_UNUSED (status=0) 的 slot
   a. UPDATE webnode SET status=1 (WEBPROCESSING)
   b. SSH 到 node: `clone {project} {node_id}`
   c. SSH 到 node: `restart-web {project} {node_id}`
   d. UPDATE webnode SET status=10 (WEBNODE)
5. 反向代理到 node_ip:port
6. 記錄 access_at 到 Memcached（供 idle timeout 判斷用）
7. 超過 1 小時無存取 → 標為 STATUS_OVER → reset 回 STATUS_UNUSED
```

### Git push 部署流程

```
1. git push middle2 master
2. SSH 到 git.middle2.com → scripts/ssh-serve → gitCommand()
3. pre-receive hook 執行 → 更新 project.commit（新 commit hash）
4. Load Balancer 收到請求時發現 commit 不符 → 舊 node markAsUnused
5. 分配新 node，clone 最新 commit，啟動
```

---

## 三、檔案結構

```
middle2/
├── loadbalancer.js          # Load Balancer 主程式（Node.js）
├── Makefile                 # 部署指令
├── package.json             # LB 用 npm dependencies
├── README.md
│
├── webdata/                 # 控制面板 PHP App
│   ├── init.inc.php         # 全域初始化（DB, framework, 環境變數）
│   ├── config.sample.php    # 設定檔範本
│   ├── prompt.php           # CLI 工具（手動執行管理指令）
│   ├── controllers/
│   │   ├── IndexController.php
│   │   ├── UserController.php      # 使用者登入/SSH key 管理
│   │   ├── ProjectController.php   # 專案管理、domain、variable、addon
│   │   ├── DeployController.php    # 從 GitHub URL 一鍵部署（app.json 支援）
│   │   ├── AdminController.php     # 管理員：Node/DB/SSL/機器管理
│   │   ├── MysqldbController.php   # MySQL addon 管理
│   │   ├── PgsqldbController.php   # PostgreSQL addon 管理
│   │   ├── ApiController.php       # API 端點
│   │   └── ErrorController.php
│   ├── models/
│   │   ├── User.php              # 使用者帳號
│   │   ├── UserKey.php           # SSH public key
│   │   ├── Project.php           # 專案（含 getWebNodes、getCronNode）
│   │   ├── WebNode.php           # Node 槽位管理（核心）
│   │   ├── WebNodeEAV.php        # WebNode EAV 擴充屬性
│   │   ├── Machine.php           # 實體機器清單
│   │   ├── MachineStatus.php     # 機器健康狀態
│   │   ├── CronJob.php           # Cron/Worker job（含 loopCronWorker）
│   │   ├── ProjectMember.php     # 專案成員
│   │   ├── ProjectVariable.php   # 環境變數
│   │   ├── CustomDomain.php      # 自訂域名
│   │   ├── SSLKey.php            # SSL 憑證（by domain）
│   │   ├── EAV.php               # Entity-Attribute-Value 通用表
│   │   ├── Elastic.php           # Elasticsearch API client
│   │   ├── GitHelper.php         # Git commit log 讀取
│   │   ├── Hisoku.php            # 全域工具（getLoginUser, getLoadBalancers, etc.）
│   │   ├── Logger.php            # Scribed log 介面
│   │   ├── MemcacheSASL.php      # Memcached SASL 認證
│   │   ├── NotifyLib.php         # 通知工具
│   │   ├── SFTPServer.php        # SFTP server 實作
│   │   ├── SignupConfirm.php     # 註冊確認
│   │   ├── AWS.php               # AWS SDK 封裝
│   │   └── Addon/
│   │       ├── MySQLDB.php       # MySQL 附加資料庫
│   │       ├── MySQLDBMember.php # MySQL DB 與 project 對應
│   │       ├── PgSQLDB.php       # PostgreSQL 附加資料庫
│   │       ├── PgSQLDBMember.php
│   │       ├── Memcached.php     # Memcached 服務
│   │       ├── Elastic.php       # Elasticsearch addon（舊）
│   │       └── Elastic2.php      # Elasticsearch addon（elastic-2）
│   └── stdlibs/
│       └── pixframework/        # 自製 PHP MVC framework
│
├── scripts/
│   ├── ssh-serve               # Git server SSH 指令處理（git/run/log/tunnel/sftp）
│   ├── root@nodes-ssh_serve    # Node server SSH 指令處理（init/clone/restart-web/shutdown/run）
│   ├── cron-worker             # Cron 工作排程主程式
│   ├── cron-worker-loop        # Cron worker 保持存活的 loop script
│   ├── pre-receive             # git pre-receive hook（push 後更新 project.commit）
│   ├── update-keys             # 更新 git server authorized_keys
│   ├── sftp-server             # SFTP server wrapper
│   ├── initdb                  # 初始化資料庫 schema
│   ├── init.sh                 # 伺服器初始化腳本
│   ├── update-ssl-keys.php     # SSL 憑證更新工具
│   ├── update-docker-registry-ssl.php  # Docker registry SSL 更新
│   └── transfer/               # 資料遷移腳本（elastic、pgsql）
│
├── cron/
│   ├── cron.php                # Cron 主排程（含 WebNode::updateNodeInfo）
│   ├── 1min/
│   │   ├── health-check        # 節點健康檢查
│   │   ├── cron-health-check   # Cron worker 健康檢查
│   │   ├── updatenodes.php     # 更新 node 狀態、回收異常 node
│   │   ├── run-worker          # Worker job 監控
│   │   └── reset-init-base     # 重置 init node
│   └── 1day/
│       ├── analytics           # 流量統計
│       ├── clean-machine-status.php  # 清理機器狀態記錄
│       ├── nodes-count.php     # 節點計數統計
│       └── rotate-log          # 日誌輪替
│
├── dockers/
│   ├── Dockerfile              # Node server 容器 image（Debian Bullseye）
│   ├── gen.php                 # 產生 Dockerfile 的工具
│   └── config/
│       ├── post-clone.sh       # clone 後執行（安裝依賴）
│       ├── start-web.sh        # 啟動 web server
│       └── shutdown.sh         # 關閉容器
│
├── config/
│   ├── middle2.conf            # Apache/Nginx 設定（管理後台）
│   ├── elastic-1.middle2.com.conf  # Nginx for Elasticsearch
│   ├── elastic-2.middle2.com.conf
│   ├── m2-lb.service           # systemd service for Load Balancer
│   ├── m2-cron-worker-loop.service  # systemd service for Cron Worker
│   └── scribed.conf            # Scribe log server 設定
│
├── firewall/
│   ├── gen.php                 # 產生防火牆規則
│   ├── m2-fw.sh                # 防火牆規則腳本
│   └── outputs/                # 各機器 IP 對應的防火牆腳本
│
├── docs/
│   ├── servers/                # 各類型伺服器設定筆記
│   ├── elastic/                # Elasticsearch 設定筆記
│   └── ...
│
├── webroot/
│   ├── index.php               # 前端入口（路由）
│   ├── .htaccess
│   ├── phpMyAdmin/             # MySQL 管理介面
│   └── phpPgAdmin/             # PostgreSQL 管理介面
│
└── test-repo/                  # 測試用 repository 範本
```

---

## 四、資料庫結構

主資料庫：MySQL（連線設定由環境變數 `MYSQL_HOST/USER/PASS/DATABASE` 提供）

### 核心資料表

| 資料表 | 說明 |
|--------|------|
| `user` | 使用者帳號（id, name, password_type, password, status） |
| `user_key` | 使用者 SSH 公鑰（user_id, key_fingerprint, key_body） |
| `project` | 專案（id, name, commit, status, config JSON, created_at, created_by） |
| `project_member` | 專案成員（project_id, user_id, is_admin） |
| `project_variable` | 環境變數（project_id, key, value, is_magic_value） |
| `custom_domain` | 自訂域名（project_id, domain） |
| `webnode` | **核心**：Node 槽位（ip, port, project_id, commit, status, config JSON, start_at, access_at, cron_id） |
| `webnode_eav` | WebNode EAV 擴充屬性（ip, port, key, value） |
| `machine` | 實體/雲端機器（machine_id, name, ip, groups） |
| `machine_status` | 機器健康狀態快照（machine_id, time, data JSON） |
| `cron_job` | Cron/Worker job（id, project_id, period, start_at, last_run_at, job） |
| `ssl_keys` | SSL 憑證（domain, config JSON: ca/key/cert） |
| `eav` | 通用 EAV（table, id, key, value），用於 Project/User/CronJob 擴充屬性 |
| `admin` | 管理員清單（user_id） |

### Addon 資料表

| 資料表 | 說明 |
|--------|------|
| `addon_mysqldb` | MySQL 附加資料庫（id, project_id, host, db, user, password, env_key） |
| `addon_mysqldb_member` | MySQL DB 分配記錄 |
| `addon_pgsqldb` | PostgreSQL 附加資料庫 |
| `addon_pgsqldb_member` | PostgreSQL DB 分配記錄 |
| `addon_memcached` | Memcached 服務（host, port, project_id） |
| `addon_elastic` / `addon_elastic2` | Elasticsearch 服務（project_id, url, user, password, prefix） |

### WebNode 狀態機

```
STATUS_UNUSED (0)
   ↓ 分配給 project
STATUS_WEBPROCESSING (1) 或 STATUS_CRONPROCESSING (2)
   ↓ clone + start 完成
STATUS_WEBNODE (10) 或 STATUS_CRONNODE (11)
   ↓ 1小時無存取 / commit 變更 / 手動釋放
STATUS_OVER (101)  → reset → STATUS_UNUSED (0)
STATUS_WAIT (102)  ← cron job 完成後暫存（保有 repo 2小時，可快速再用）
STATUS_STOP (100)  ← 管理員手動停止
STATUS_SERVICE (103) ← 固定使用（不會被回收）
```

### CronJob 週期設定（period 欄位）

| period | 說明 |
|--------|------|
| 0 | 停用 |
| 1 | 每 10 分鐘 |
| 2 | 每小時 |
| 3 | 每天 |
| 4 | 每分鐘 |
| 99 | Worker（長駐程序，需保持存活） |

---

## 五、服務架構（機器群組）

Machine 資料表用 `groups` 欄位區分機器類型，Hisoku class 提供對應查詢：

| 群組名稱 | 用途 | 對應方法 |
|----------|------|----------|
| `loadbalancer` | Load Balancer 機器 | `Hisoku::getLoadBalancers()` |
| `nodes` | Node Server 機器 | `Hisoku::getNodeServers()` |
| `mysql` | MySQL 資料庫伺服器 | `Hisoku::getMySQLServers()` |
| `pgsql` | PostgreSQL 伺服器 | `Hisoku::getPgSQLServers()` |
| `elastic` | Elasticsearch 伺服器 | — |
| `dev` | 開發/管理伺服器 | `Hisoku::getDevServers()` |

### 重要 Port 對應

| Port | 服務 |
|------|------|
| 80 | HTTP（Load Balancer + Let's Encrypt） |
| 443 | HTTPS（Load Balancer） |
| 3128 | HTTP Proxy |
| 3306 | MySQL |
| 11211 | Memcached |
| 20001–20100 | Node Server slots（每台最多 100 個 Docker container） |
| 9200 | Elasticsearch |

### 環境變數（config.php 或 docker env）

| 變數 | 說明 |
|------|------|
| `MYSQL_HOST/USER/PASS/DATABASE` | 資料庫連線 |
| `MEMCACHE_PRIVATE_HOST/PORT` | Memcached |
| `MAINPAGE_HOST/PORT` | 管理後台後端 |
| `MAINPAGE_DOMAIN` | 管理後台域名 |
| `APP_SUFFIX` | 應用程式域名後綴（如 `.hisokuapp.ronny.tw`） |
| `SCRIBE_HOST/PORT` | Scribed 日誌服務 |
| `GIT_SERVER` | Git server 主機（預設 `git.middle2.com`） |
| `ELASTIC_ADMIN_USER/PASSWORD` | Elasticsearch admin 帳密 |
| `TRY_MODE` | 試用模式（限制 3 個 project） |

---

## 六、Node Server SSH 指令

管理後台透過 SSH 公鑰認證連到 Node Server root 帳號，執行以下指令（由 `scripts/root@nodes-ssh_serve` 處理）：

| 指令 | 說明 |
|------|------|
| `init {node_id}` | 初始化 Docker container slot |
| `clone {project} {node_id}` | 從 git server clone 專案到 container |
| `restart-web {project} {node_id}` | 啟動 web server（執行 `start-web.sh`） |
| `shutdown {node_id}` | 停止並清理 container |
| `run {project} {node_id} {command} {without_status}` | 執行指令（cron/ssh run）|
| `check_alive {node_id}` | 回傳 container 內的 process 清單（JSON）|

SSH 金鑰：`/srv/config/web-key`（private）和 `/srv/config/web-key.pub`（public）

---

## 七、Docker Container（應用環境）

`dockers/Dockerfile` 定義的 Node server image，包含：
- PHP（含 Apache mod_rpaf, php-fpm, composer）
- Node.js 14.x + Yarn
- Ruby
- Python3 + pip + gunicorn
- 透過 `post-clone.sh` 自動安裝依賴（composer install, npm install, bundle install, pip install）
- 支援框架：PHP、Laravel、Node.js、Python（Flask/Django + gunicorn）、Ruby

---

## 八、重大開發 Milestone

| 時間 | Milestone |
|------|-----------|
| **2012-07** | 專案初建（Hisoku），基本 User、Project、SSH key 管理 |
| **2012-07** | SSH serve 實作，git push 部署流程，WebNode 雛型 |
| **2012-08** | Node server chroot 環境，Apache virtual host，pre-receive hook |
| **2012-09 ~ 2013-08** | 核心功能開發：EAV 系統、WebNode 狀態機、Load Balancer（perlbal → 自製）|
| **2013** | Cron job 系統、Worker job、自訂域名、MySQL addon |
| **2014-2015** | MySQL/PostgreSQL addon 穩定化，Custom domain |
| **2016-2017** | Docker 化（取代 chroot），Load Balancer 改寫為 Node.js |
| **2017** | Node group 功能（區隔不同用途的 node pool） |
| **2018** | 多機房支援（mysql_new, pgsql_new），Docker image 升級（Jessie） |
| **2019-2020** | Docker 升級（Debian Buster），PostgreSQL 完整支援，機器健康監控 |
| **2020** | 維護模式（maintaince flag），管理員可查看 cron log |
| **2021** | 更新 SSL 自動化，SSH ed25519 key 支援，cron transaction 修復 |
| **2022** | Debian Bullseye，PHP 7.3+，Composer，Laravel 框架支援 |
| **2023-07** | Elasticsearch 2 addon（elastic-2.middle2.com），管理員 ES admin 頁面 |
| **2024** | elastic-1 nginx config，Let's Encrypt 整合 |
| **2025** | 遷移至 elastic-2，移除 getSearchServers 相關檢查 |
| **2026** | Elasticsearch 進階設定，MAINPAGE_DOMAIN 預設值 |

---

## 九、重要設計決策與注意事項

1. **WebNode port 計算**：`port = node_id + 20000`，即 node_id=1 → port 20001
2. **IP 儲存方式**：webnode/machine 的 ip 欄位存的是 `ip2long()` 後的整數，使用時需 `long2ip()`
3. **Race condition 防護**：getCronNode 使用 `affected_rows` 判斷是否搶到 node，失敗則遞迴重試
4. **Load Balancer cache**：mapping_cache 在 PHP 端更新 webnode 狀態後要呼叫 `WebNode::cleanLoadBalancerCache()` 讓 LB 清快取
5. **Worker job (period=99)**：每分鐘檢查是否存活，commit 變更時自動重啟
6. **Node group**：project 和 webnode 都有 `node-group` 設定，同 group 的 project 優先使用同 group 的 node
7. **Try mode**：`TRY_MODE` 環境變數啟用試用模式，限制 3 個 project，禁用某些功能
8. **EAV 系統**：Project、User、CronJob、WebNode 都透過 EAV 儲存彈性屬性（如 `always-alive`、`try-user`、`note` 等）
