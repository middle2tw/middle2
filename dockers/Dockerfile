# PHP 7.4 is EOL and bullseye LTS has ended, so rebuild Debian's php7.4 with
# extra security patches from php-patches/ (CVE-2026-109618, CVE-2026-109619)
FROM debian:bullseye AS php-build
# bullseye-security has been moved to archive.debian.org
RUN sed -i 's#deb.debian.org/debian-security#archive.debian.org/debian-security#' /etc/apt/sources.list
RUN sed -n 's/^deb /deb-src /p' /etc/apt/sources.list > /etc/apt/sources.list.d/src.list
RUN apt-get update
RUN apt-get install -y dpkg-dev devscripts quilt
RUN apt-get build-dep -y php7.4
WORKDIR /build
RUN apt-get source php7.4=7.4.33-1+deb11u12
COPY php-patches/ /build/php-patches/
RUN cd php7.4-7.4.33 \
    && cp ../php-patches/*.patch debian/patches/ \
    && (cd ../php-patches && ls *.patch) >> debian/patches/series \
    && QUILT_PATCHES=debian/patches quilt push -a \
    && DEBFULLNAME=middle2 DEBEMAIL=root@middle2.com dch -v 7.4.33-1+deb11u12+middle2u1 -D bullseye-security "Apply patches for CVE-2026-109618, CVE-2026-109619." \
    && DEB_BUILD_OPTIONS="nocheck noddebs parallel=$(nproc)" dpkg-buildpackage -b -uc -us

FROM debian:bullseye
RUN sed -i 's#deb.debian.org/debian-security#archive.debian.org/debian-security#' /etc/apt/sources.list
RUN apt-get update
RUN apt-get install -y git ca-certificates locales curl debian-archive-keyring g++ gcc make dpkg-dev
COPY --from=php-build /build/*.deb /opt/php-debs/
RUN cd /opt/php-debs && dpkg-scanpackages . > Packages && echo 'deb [trusted=yes] file:/opt/php-debs ./' > /etc/apt/sources.list.d/php-patched.list && apt-get update
RUN echo 'zh_TW.UTF-8 UTF-8' >> /etc/locale.gen
RUN echo 'en_US.UTF-8 UTF-8' >> /etc/locale.gen
RUN locale-gen
RUN apt-get install -y php php-mysql php-pgsql libapache2-mod-rpaf php-fpm apache2 php-curl php-gd php-mbstring php-xml composer
# setup_14.x now imports a different key than the one node_14.x is signed with,
# so use the legacy NodeSource key (9FD3B784BC1C6FC31A8A0A1C1655A0AB68576280) directly
COPY apt-keys/nodesource-legacy.gpg /usr/share/keyrings/
RUN echo "deb [signed-by=/usr/share/keyrings/nodesource-legacy.gpg] https://deb.nodesource.com/node_14.x bullseye main" > /etc/apt/sources.list.d/nodesource.list
RUN apt-get install -y gnupg
RUN apt-get update
RUN apt-get install -y nodejs
RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add -
RUN echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list
RUN apt-get update
RUN apt-get install yarn
RUN apt-get install -y ruby ruby-dev
RUN apt-get install -y python3 python3-pip python3-dev libpq-dev gunicorn
RUN apt-get install -y sysvinit-core
RUN update-rc.d -f apache2 remove
RUN update-rc.d -f php7.4-fpm remove
RUN mkdir /run/php/
COPY config/ /
