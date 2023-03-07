#!/bin/sh

if test -d /home/live-backup
then
	echo "Removing old backup..."
	rm -rf /home/live-backup
fi

echo "Making backup..."
mv /home/live /home/live-backup

echo "Copying dev to live..."
cp -Rp /home/dev /home/live


#
# move stats database back
#

echo "Removing dev stats database from live..."
rm -rf /home/live/apps/stats/var


echo "Re-installing live stats database..."
mv /home/live-backup/apps/stats/var /home/live/apps/stats/var


echo "Removing dev stats images from live..."
rm -rf /home/live/apps/stats/images

echo "Re-installing live images..."
mv /home/live-backup/apps/stats/images /home/live/apps/stats/images


#
# move CCR attachments back
#

echo "Removing dev CCR attachment data from live..."
rm -rf /home/live/apps/ccr/attachments

echo "Re-installing live CCR attachment data..."
mv /home/live-backup/apps/ccr/attachments /home/live/apps/ccr/attachments

echo "Done"


#
# move Technical services attachments back
#

echo "Removing dev technical attachment data from live..."
rm -rf /home/live/apps/technical/attachments

echo "Re-installing live technical attachment data..."
mv /home/live-backup/apps/technical/attachments /home/live/apps/technical/attachments

echo "Done"
