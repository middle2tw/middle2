FROM debian
RUN apt-get update
RUN apt-get install -y ca-certificates locales curl debian-archive-keyring multiarch-support g++ gcc make dpkg-dev libcurl3
RUN echo 'zh_TW.UTF-8 UTF-8' >> /etc/locale.gen
RUN echo 'en_US.UTF-8 UTF-8' >> /etc/locale.gen
RUN locale-gen
RUN apt-get install -y php5 php5-mysqlnd php5-pgsql
RUN apt-get install -y nodejs
RUN apt-get install -y ruby
RUN apt-get install -y python
