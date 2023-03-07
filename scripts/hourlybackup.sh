rsync -av --delete /home/2hour/live	/home/backups/3hour/
rsync -av --delete /home/2hour/dev	/home/backups/3hour/

rsync -av --delete /home/1hour/live	/home/backups/2hour/
rsync -av --delete /home/1hour/dev	/home/backups/2hour/

rsync -av --delete /home/live	/home/backups/1hour/
rsync -av --delete /home/dev	/home/backups/1hour/