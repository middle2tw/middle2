all:
	@git pull -v

deploy:
	@rsync -avz --exclude .git --exclude .gitignore --exclude .*.swp --delete --delete-excluded -e ssh . code@10.0.0.98:~/hisoku
