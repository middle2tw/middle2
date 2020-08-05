<?php

putenv('DOCKER_REGISTRY=docker-registry-linode-1.middle2.com:5000');

putenv('MYSQL_USER=hisoku');
putenv('MYSQL_PASS=___');
putenv('MYSQL_HOST=___');
putenv('MYSQL_DATABASE=hisoku');

putenv('MYSQL_USERDB_USER=hisoku_userdb');
putenv('MYSQL_USERDB_PASS=___');

putenv('PGSQL_USERDB_HOST=');
putenv('PGSQL_USERDB_PORT=5432');
putenv('PGSQL_USERDB_USER=middle2');
putenv('PGSQL_USERDB_PASS=___');

putenv('MEMCACHE_PRIVATE_HOST=127.0.0.1');
putenv('MEMCACHE_PRIVATE_PORT=11211');

putenv('MAINPAGE_HOST=127.0.0.1');
putenv('MAINPAGE_PORT=9999');
putenv('MAINPAGE_DOMAIN=middle2.com');
putenv('APP_SUFFIX=.middle2.me');

putenv('SCRIBE_HOST=127.0.0.1');
putenv('SCRIBE_PORT=1463');

putenv('SESSION_SECRET=____'); // random

putenv('GIT_PUBLIC_SERVER=git.middle2.com');
putenv('GIT_PRIVATE_SERVER=git.middle2.com');
putenv('GIT_SERVER=git.middle2.com');

putenv('HEALTHCHECK_KEY=____');
putenv('HEALTHCHECK_SECRET=____');
putenv('ELASTIC_SECRET=_____');

putenv('SES_MAIL=non-reply@middle2.com');
putenv('SES_SECRET=______');
putenv('SES_KEY=_______');

putenv('LE_ROOT=/srv/certs/acme-challenges');
