#!/bin/sh

echo "Bringing down eth0 if up (10.1.10.6)"

/etc/sysconfig/network-scripts/ifdown ifcfg-eth0


echo "Shutdown Apache"

service httpd stop


echo "Link up live apache config"

rm /etc/httpd/conf/httpd.conf
ln -s /etc/httpd/conf/intranet.live.conf /etc/httpd/conf/httpd.conf


echo "Bringing up eth0 (10.1.10.6)"

/etc/sysconfig/network-scripts/ifup ifcfg-eth0


echo "Restart Apache"

service httpd start

echo "Done"