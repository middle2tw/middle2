all:
	@git pull -v

deploy:
	@rsync -avz --exclude .git --exclude .gitignore --exclude '.*.swp' --exclude 'webdata/config.php' --delete --delete-excluded -e ssh . code@10.0.0.26:~/hisoku #dev
	@rsync -avz --exclude .git --exclude .gitignore --exclude '.*.swp' --exclude 'webdata/config.php' --delete --delete-excluded -e ssh . code@10.0.0.74:~/hisoku #loadbalancers
	@rsync -avz --exclude .git --exclude .gitignore --exclude '.*.swp' --exclude 'webdata/config.php' --delete --delete-excluded -e ssh . code@10.0.0.98:~/hisoku #nodes
