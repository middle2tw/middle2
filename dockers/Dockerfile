FROM debian:jessie
RUN apt-get update
RUN apt-get install -y git ca-certificates locales curl debian-archive-keyring multiarch-support g++ gcc make dpkg-dev libcurl3
RUN echo 'zh_TW.UTF-8 UTF-8' >> /etc/locale.gen
RUN echo 'en_US.UTF-8 UTF-8' >> /etc/locale.gen
RUN locale-gen
RUN apt-get install -y php5 php5-mysqlnd php5-pgsql libapache2-mod-rpaf php5-fpm apache2 php5-curl php5-gd
RUN curl -sL https://deb.nodesource.com/setup_6.x | bash -
RUN apt-get install -y nodejs
RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add -
RUN echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list
RUN apt-get update
RUN apt-get install yarn
RUN apt-get install -y ruby ruby-dev
RUN apt-get install -y python python-pip libmysqlclient-dev mysql-client python-dev libpq-dev gunicorn
COPY config/ /
