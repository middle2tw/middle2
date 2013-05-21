DEV_HOSTS= 10.0.0.26
LB_HOSTS= 10.0.0.74
NODES_HOSTS= 10.0.0.10

HOSTS?= ${DEV_HOSTS} ${LB_HOSTS} ${NODES_HOSTS}


all:
	@git pull -v

deploy:
	@for HOST in ${HOSTS} ; do \
	rsync -avz --exclude .git --exclude .gitignore --exclude '.*.swp' --exclude 'webdata/config.php' --delete --delete-excluded -e ssh . code@$${HOST}:~/hisoku ; \
	done
