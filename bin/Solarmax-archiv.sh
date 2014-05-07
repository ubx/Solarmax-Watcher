#!/bin/bash

cd..

clear
echo ""
echo " ----------------------------------------------------"
echo " |                                                  |"
echo " |       Solarmax Install-Repository wird           |"
echo " |      in ein *.tar.gr - File archiviert           |"
echo " |                                                  |"
echo " ----------------------------------------------------"
echo ""

if [ -d web-custom ]; then
  rm -fR web-custom
fi

if [ -d logger-bin ]; then
  rm -fR logger-bin
fi

echo -e "\n Archivierung wird durchgeführt in Datei "
echo -n " Solarmax-Watcher-10MT.tar.gz ... "
cd $HOME
tar -czvPf Solarmax-Watcher-10MT.tar.gz SolarmaxWatcher/
echo -e "fertig!\n"
