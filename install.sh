#!/bin/bash

#some definitions
conffile=/usr/local/etc/smw-logger.conf
tabelle=log10mt2  ## The Mysql DB tab-prefix
db=solarmax       ## The Mysql DB name
newuser=solaruser ## The Mysql DB user
realpath=$0
instpath="${realpath%/*}"
if [ "$instpath" = "." ]; then
	instpath=`pwd`
fi
sitehead=$instpath/web-custom/sitehead.php
installerversion=`10MT2`

######################################
### definition for custom commands ###
######################################


#Ask for Mysql Host
ask_mysql_host(){
echo -n -e "\n\n IP-address or hostname of your Mysql server [localhost]: "
read dbhost
   if [ -z $dbhost ]; then
     dbhost=localhost
   fi
   if [ $dbhost != localhost ]; then
     echo -e "\n Please secure, that the user 'root' may access the Mysql-server $dbhost from "
     echo -e " external and that the regarding firewall rules and port forwardings are adjusted. \n"
   fi
   echo -n -e "\n Is the DB-host '$dbhost' correct? [y/n] "
   korrekt6(){
     read RICHTIG6
     case "$RICHTIG6" in
       y)
         return 0
         ;;
       n)
         ask_mysql_host
         ;;
       *)
         echo -n -e "\n Input error, is the given hostname correct? [y/n] "
         korrekt6
         ;;
     esac
   }
   korrekt6
}

#Ask for Mysql-root-PW
ask_mysqlroot_pw(){
  mysql -u root -h $dbhost -e "show databases" >/dev/null 2>&1
  if [ $? -ne 0 ]; then
    korrekt2(){
      echo -n -e "\n\n Allready existing Mysql password for user 'root': "
      read -s rootpw
      echo -n -e "\n Repeat password input                           : "
      read -s rootpw2
      if [ "$rootpw" == "$rootpw2" ]; then
        echo -e "\n"
        return 0
      else
        echo -e "\n\n The passwords don't match, try again ... "
        korrekt2
      fi
    }
    korrekt2
  else
    korrekt3(){
      echo -e "\n Until now, no password is set for Mysql-user 'root'. We'll set it now ... "
      echo -n -e "\n Mysql password for user 'root': "
      read -s rootpw
      echo -n -e "\n Repeat password input         : "
      read -s rootpw2
      if [ "$rootpw" == "$rootpw2" ]; then
        echo -e "\n"
        return 0
      else
        echo -e "\n\n The passwords don't match, try again please... "
        korrekt3
      fi
    }
    korrekt3
    mysqladmin -u root -h $dbhost password @rootpw
  fi
}

#Ask for Mysql-user-PW
ask_mysqluser_pw(){
  echo -e "\n The DB-user for the Solarmax-DB needs a password, which is asked here ... "
    korrekt4(){
      echo -n -e "\n Mysql password for DB-User '$newuser': "
      read -s userpw
      echo -n -e "\n Repeat password input                 : "
      read -s userpw2
      if [ "$userpw" == "$userpw2" ]; then
        echo -e "\n"
        return 0
      else
        echo -e "\n\n The passwords don't match, try again please... "
        korrekt4
      fi
    }
    korrekt4
}

##Proof, if DB exists, otherwise create it
create_db(){
  if [ `mysql -u root -h $dbhost -p$rootpw -e "show databases"|grep -c $db` = 1 ]; then
    echo -e "\n The 'solarmax' database is allready existing. Nothing to do here ... "
    else
    echo -e "\n A new 'solarmax' database will be created now ... "

    ## create Database
    command1="create database if not exists $db;
    GRANT ALL PRIVILEGES ON $db.* to $newuser@'localhost' IDENTIFIED BY '$userpw';
    GRANT ALL PRIVILEGES ON $db.* to $newuser IDENTIFIED BY '$userpw';
    flush privileges;"

    mysql -uroot -h $dbhost -p"$rootpw" -e "$command1"

    ## create tables
    command2="use $db;CREATE TABLE IF NOT EXISTS $tabelle$i (
    created timestamp NOT NULL default CURRENT_TIMESTAMP,
    kdy  int(11) unsigned default NULL,
    kmt  int(11) unsigned default NULL,
    kyr  int(11) unsigned default NULL,
    kt0  int(11) unsigned default NULL,
    tkk  int(11) unsigned default NULL,
    pac  int(11) unsigned default NULL,
    udc1 int(11) unsigned default NULL,
    udc2 int(11) unsigned default NULL,
    idc1 int(11) unsigned default NULL,
    idc2 int(11) unsigned default NULL,
    sys  int(11) unsigned default NULL,
    PRIMARY KEY  (created)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"

    mysql -u$newuser -h $dbhost -p"$userpw" -e "$command2"

    ## show tables and status
    command3="show databases;
    use $db;
    show tables;
    status;"

    mysql -u$newuser -h $dbhost -p"$userpw" -e "$command3"

  fi
}

## ask for debugging
ask_debug() {
	echo -n -e "\n\n Shall debugging be enabled? (y/n) [N] "
	read debug
	if [ -z $debug ]; then
		DEBUGENABLE=0
	else
		case "$debug" in
		y|n)
			if [ $debug == "y" ]; then
				DEBUGENABLE=1
			else
				DEBUGENABLE=0
			fi
			return 0
			;;
		*)
			echo -e "\nEither use y or n for your choice, please."
			ask_debug
			;;
		esac
	fi
}

## compile logger and move into filesystem
compile_logger(){
	mkdir -p $instpath/logger-bin
	gcc -W -Wall -Wextra -Wshadow -Wlong-long -Wformat -Wpointer-arith -rdynamic -pedantic-errors -std=c99 -o $instpath/logger-bin/smw-logger $instpath/logger-src/smw-logger.c -lmysqlclient
	cp -f $instpath/logger-bin/smw-logger /usr/local/bin/
}

#Creation of config-file
config_file_logger(){
	echo -e "\n\n Define a logging interval in seconds here (120 might be a good choice) ... \n"
	echo -n " Logging interval: "
	read loginterval

	echo -e "\n\n LAN-Settings for 1st inverter"
	echo -e " -------------------\n"
	echo -n " Hostname or IP: "
	read invhost
	echo -n " Port          : "
	read invport

	mkdir -p /usr/local/etc
	cp $instpath/example-config/smw-logger.conf $conffile
	chmod 0600 $conffile
	chown root.root $conffile
	sed -e "s/Debug=0/Debug=$DEBUGENABLE/" \
	-e "s/Loginterval=60/Loginterval=$loginterval/" \
	-e "s/DBhost=localhost/DBhost=$dbhost/" \
	-e "s/DBtable=log10mt2/DBtable=$tabelle/" \
	-e "s/DBname=solarmax/DBname=$db/" \
	-e "s/DBuser=solaruser/DBuser=$newuser/" \
	-e "s/DBpass=userpassword/DBpass=$userpw/" \
	-e "s/Hostname=192.168.178.35/Hostname=$invhost/" \
	-e "s/Hostport=12345/Hostport=$invport/" \
	$conffile > atempfile
	mv atempfile $conffile

	echo -e "\n\n If any of the settings above was incorrect or should change in the future, "
	echo -e " please edit the file '$conffile' to change these settings.\n"
}

#activation and start of the smw-logger
activation(){
	cp -f $instpath/init.d/solarmax-logger /etc/init.d/
	/etc/init.d/solarmax-logger start
	insserv solarmax-logger >/dev/null 2>&1
	chkconfig solarmax-logger >/dev/null 2>&1
	update-rc.d solarmax-logger defaults >/dev/null 2>&1

	if [ `cat /etc/crontab| grep -c 'solarmax-logger'` = 0 ]; then
		echo "00 4 * * *  root  /etc/init.d/solarmax-logger start" >> /etc/crontab
		echo "00 23 * * *  root  /etc/init.d/solarmax-logger stop" >> /etc/crontab
	fi
}

#Creation of Web
create_web(){
echo -e "\n\n Please enter the root of your web folder or choose one of the following ... "
echo -e "\n   1. /srv/www/htdocs"
echo "   2. /var/www"
echo "   3. other choice"
echo "   4. no web-folder now"
echo -e -n "\n   Your choice: "
read web_alt
no_web=0
if [ $web_alt -eq "3" ]; then
  echo -e -n "\n  Your web root (without ending slash '/' ) : "
  read web_path
  elif [ $web_alt -eq "1" ]; then
  web_path=/srv/www/htdocs
  elif [ $web_alt -eq "2" ]; then
  web_path=/var/www
else
  no_web=1
  echo -e "\n No web root was choosen. The web folder will stay undone in the subfolder"
  echo " 'web-custom' of the src folder of this software ..."
fi
if [ $no_web = "0" ]; then
  mkdir -p $web_path/solarmax
  cp -pfR $instpath/web/* $web_path/solarmax
  chown -R wwwrun.www $web_path/solarmax >/dev/null 2>&1
  chown -R www-data.www-data $web_path/solarmax >/dev/null 2>&1
else
  web_path=no__web
fi
}

conf_uinst(){
echo -e "\n\n Configuring the uninstall script, residing in the root-folder"
echo -e " of this archive ... \n"
sed -e "s,someweb,$web_path," -e "s,somehost,$dbhost," -e "s,somedb,$db," $instpath/bin/SolarmaxWatcher-uninstaller-blank.sh > $instpath/Solarmax_uninstaller.sh
chmod 0777 $instpath/Solarmax_uninstaller.sh
}

function pause(){
  read -p "$*"
}

clear
echo -e "\n --------------------------------------------------------------\n"
echo "                     Solarmax Watcher $installerversion"
echo -e "                     ----------------------\n"
echo "                Installer for the Solarmax Logger "
echo "               and the Solarmax Watcher php-scripts"
echo -e "\n --------------------------------------------------------------\n"

if [ `whoami` != "root" ]; then
  echo -e "\n To execute this script, root privileges are required. So please "
  echo -e " login as root or use the 'sudo' command to start this installer; "
  echo -e " exiting. \n"
  exit 1
fi

echo -e "\n\n To run the logger and the php-watcher some requirements have to "
echo -e " be fulfilled. \n"
echo -e " Needed packages: \n"
echo "   - GNU C compiler (gcc)"
echo "   - libmysqlclient-devel (containing /usr/include/mysql/mysql.h) "
echo "     (path may differ)"
echo "   - a running Mysql server (may reside on another machine)"
echo "   - a running webserver, e. g. Apache with installed and activated "
echo "     php extension"
echo -e "   - php-modules 'gd' and 'mysql' \n"
echo " Press 'q' to quit here and improve your installation before "
echo " installing this software or press any other key to proceed. "
read -s -n 1 go_on
case $go_on in
  q)
    exit 0
    ;;
  *)
    echo -e "\n OK, let's go on then ... \n"
#    return 0
    ;;
esac

ask_mysql_host
ask_mysqlroot_pw
ask_mysqluser_pw
create_db
ask_debug
compile_logger
config_file_logger
activation
create_web
conf_uinst

echo ""
pause ' Press Enter key to proceed ...'
