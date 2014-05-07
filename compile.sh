#!/bin/bash

#some definitions
realpath=$0
instpath="${realpath%/*}"
if [ "$instpath" = "." ]; then
	instpath=`pwd`
fi

######################################
### definition for custom commands ###
######################################

## compile logger and move into filesystem
compile_logger(){
	mkdir -p $instpath/logger-bin
	gcc -W -Wall -Wextra -Wshadow -Wlong-long -Wformat -Wpointer-arith -rdynamic -pedantic-errors -std=c99 -o $instpath/logger-bin/smw-logger $instpath/logger-src/smw-logger.c -lmysqlclient
	cp -f $instpath/logger-bin/smw-logger /usr/local/bin/
}


function pause(){
  read -p "$*"
}

clear
echo -e "\n --------------------------------------------------------------\n"
echo "                Compile and copy the Solarmax Logger "
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

compile_logger

echo ""
pause ' Press Enter key to proceed ...'
