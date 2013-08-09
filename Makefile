DEV_HOSTS= 210.65.10.110
LB_HOSTS= 
NODES_HOSTS= 210.61.2.239 210.61.2.65 203.66.14.47
DB_HOSTS= 210.65.11.197

HOSTS?= ${DEV_HOSTS} ${LB_HOSTS} ${NODES_HOSTS} ${DB_HOSTS}


all:
	@git pull -v

deploy:
	@for HOST in ${HOSTS} ; do \
	rsync -avz --exclude .git --exclude .gitignore --exclude '.*.swp' --exclude 'webdata/config.php' --delete --delete-excluded -e ssh . code@$${HOST}:~/hisoku ; \
	done
