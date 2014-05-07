#!/bin/sh

    ##############################################
    ######### Variablen setzen ###################
    ##############################################

    # IP Adresse der MYSQL DB oder localhost falls es auf dem gleichen System ist
    dbhost=localhost
    # MYSQL Datenbank Name Solarwatch
    db=solarmax
    # MYSQL neuer Solarwatch Nutzer
    newuser=solaruser
    # MYSQL Passwort für den neuen Solarwatch Nutzer
    userpw="userpassword"
    # Anzahl der Wechselrichter = Anzahl der Tabellen
    anz_wr=1
    # Name der Tabelle
    tabelle=log
    # root Password
    rootpw="rootpassword"

#    ##############################################
#    ######### MYSQL Root PW ######################
#    ##############################################
#
#    #rootpw="rootpassword"     ## ROOT Passwort für die MYSQL DB
#    # Falls  das rootpw direkt gesetzt werden soll auskommentieren und vor folgender zeile die Raute  # einfügen!!
     echo -n "Bitte MYSQL 'root' Passwort eingeben: " ; stty -echo ; read rootpw ; stty echo ; echo ""

    ###############################################
    ###### ab hier nichts mehr aendern ############
    ###############################################

    ## Datenbank anlegen
    command1="create database if not exists $db;
    GRANT ALL PRIVILEGES ON $db.* to $newuser IDENTIFIED BY '$userpw';
    flush privileges;"

    mysql -uroot -p"$rootpw" -e "$command1"

    ## Tabellen fuer jeden WR anlegen
    i=1
    while [ $i -le $anz_wr ]
    do
    command2="use $db;CREATE TABLE IF NOT EXISTS $tabelle$i (
    created timestamp NOT NULL default CURRENT_TIMESTAMP,
    kdy int(11) unsigned default NULL,
    kmt int(11) unsigned default NULL,
    kyr int(11) unsigned default NULL,
    kt0 int(11) unsigned default NULL,
    tnf int(11) unsigned default NULL,
    tkk int(11) unsigned default NULL,
    pac int(11) unsigned default NULL,
    prl int(11) unsigned default NULL,
    il1 int(11) unsigned default NULL,
    idc int(11) unsigned default NULL,
    ul1 int(11) unsigned default NULL,
    udc int(11) unsigned default NULL,
    sys int(11) unsigned default NULL,
    PRIMARY KEY  (created)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"

    mysql -u$newuser -p"$userpw" -e "$command2"
    i=`expr $i + 1`
    done

    ## Tabellen und Status anzeigen
    command3="show databases;
    use $db;
    show tables;
    status;"

    mysql -u$newuser -p"$userpw" -e "$command3"

