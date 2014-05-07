#!/bin/bash

#Variables
day=$(date +"%Y%m%d_%H%M")
basedir=/tmp/Solarmax-Archive-$day
web_path=someweb
dbhost=somehost
dbname=somedb

#make archive-directory
mkbasedir(){
	mkdir -p $basedir
}

#uninstall logger
uinst_logger(){
	echo -e "\n uninstalling the smw-logger ... "
	killall smw-logger
	chkconfig -d solarmax-logger >/dev/null 2>&1
	insserv -r solarmax-logger >/dev/null 2>&1
	update-rc.d -f solarmax-logger remove >/dev/null 2>&1
	tar -cPf $basedir/logger.tar.gz /usr/local/bin/smw-logger /etc/init.d/solarmax-logger /var/log/solarmax*.log
	rm -f /usr/local/bin/smw-logger /etc/init.d/solarmax-logger /var/log/solarmax*.log
}

#uninstall logger.conf
	uinst_logconf(){
	echo -e "\n deleting of the loggers config-file ... "
	tar -cPf $basedir/logger.conf.tar.gz /usr/local/etc/smw-logger.conf
	rm -f /usr/local/etc/smw-logger.conf
}

#uninstall watcher-web
	uinst_web(){
	if [ $web_path == "no__web" ]; then
		echo -e "\n During the installation of Solarmax Watcher no web-root for your web-server was given. "
		echo -e " Please archive your Solarmax web (php-scripts) manually. \n"
		else
		echo -e "\n uninstalling of the Solarmax Watcher's web ... "
		tar -cPf $basedir/web.tar.gz $web_path/solarmax/*
		rm -fR $web_path/solarmax
	fi
}

#drop DB
drop_db(){
	mysql -u root -h $dbhost -e "show databases" >/dev/null 2>&1
	if [ $? -ne 0 ]; then
		korrekt1(){
			echo -n -e "\n Allready existing Mysql password for user 'root': "
			read -s rootpw
			echo -n -e "\n Repeat password input                           : "
			read -s rootpw2
			if [ "$rootpw" == "$rootpw2" ]; then
				echo -e "\n"
			return 0
			else
				echo -e "\n\n The passwords don't match, try again ... "
				korrekt1
			fi
		}
		korrekt1
		mysqldump -u root -p"$rootpw" -h $dbhost --lock-all-tables $dbname >$basedir/solarmax_db_$day
		mysql -u root -p"$rootpw" -h $dbhost -e "drop database $dbname"
	else
		mysqldump -u root -h $dbhost --lock-all-tables $dbname >$basedir/solarmax_db_$day
		mysql -u root -h $dbhost -e "drop database $dbname"
	fi
}

#Archives should only be readable for root because PW's are saved inside
archive_rights(){
	chown -R root.root $basedir
	chmod 0700 -R $basedir
}

#Ask for uninstall procedure
clear
echo -e "\n\n Uninstaller for the Solarmax Watcher"

if [ `whoami` != "root" ]; then
	echo -e "\n Root privileges are required to execute this script. So please "
	echo -e " login as root or use the 'sudo' command to start this uninstaller; "
	echo -e " exiting. \n"
	exit 1
fi

echo -e "\n\n Please select: \n"
echo " 1 - complete uninstall including drop of DB "
echo " 2 - selective uninstall "
echo " 3 - quit "
echo -e "\n Note: Every deleted component will be archived in '$basedir'. "
echo -n -e "\n Your choice? [1,2,3] "
read -n 1 -s uopt
case $uopt in
	1)
		echo -e "\n\n performing a complete uninstall ... \n\n"
		mkbasedir
		uinst_logger
		uinst_logconf
		uinst_web
		drop_db
		archive_rights
		echo -e "\n It's done now, bye ... \n"
		;;
	2)
		echo -e "\n\n selective uninstall, we will ask before every step ... \n\n"
		mkbasedir
		echo -n -e "\n Should the smw-logger be uninstalled ? [y/n] "
		read s_logger
		case "$s_logger" in
			y)
				uinst_logger
				;;
			*)
				echo " ... skipping this point "
				;;
		esac
		echo -n -e "\n Should we archive and delete the 'smw-logger.conf' ? [y/n] "
		read s_logconf
		case "$s_logconf" in
			y)
				uinst_logconf
				;;
			*)
				echo " ... skipping this point "
				;;
		esac
		echo -n -e "\n Should the web-folder of the Solarmax Watcher be uninstalled ? [y/n] "
		read s_web
		case "$s_web" in
			y)
				uinst_web
				;;
			*)
				echo " ... skipping this point "
				;;
		esac
		echo -n -e "\n Should we archive and drop the db of the Solarmax logger ? [y/n] "
		read s_db
		case "$s_db" in
			y)
				drop_db
				;;
			*)
				echo " ... skipping this point "
				;;
		esac
		echo -e "\n\n It's done now, bye ... \n"
		;;
	*)
		echo -e "\n\n ok, lets stop here ... \n"
		exit 0
		;;
esac
