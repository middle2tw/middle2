HOSTS?= `./scripts/getip dev loadbalancer nodes mysql pgsql search`

all:
	@git pull -v

npm: package.json
	npm install

deploy: npm
	php firewall/gen.php
	@for HOST in ${HOSTS} ; do \
	rsync -avz --exclude .git --exclude .gitignore --exclude '.*.swp' --exclude 'webdata/config.php' --delete --delete-excluded -e ssh . code@$${HOST}:~/hisoku ; \
	done
