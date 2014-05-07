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
    # MYSQL Passwort fuer den neuen Solarwatch Nutzer
    userpw=<password>
    # Anzahl der Wechselrichter = Anzahl der Tabellen
    anz_wr=1
    # Name der Tabelle
    tabelle=log13mt3

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
    command2="use $db;CREATE TABLE IF NOT EXISTS $tabelle (
    created timestamp NOT NULL default CURRENT_TIMESTAMP,
    kdy  int(11) unsigned default NULL,
    kmt  int(11) unsigned default NULL,
    kyr  int(11) unsigned default NULL,
    kt0  int(11) unsigned default NULL,
    tkk  int(11) unsigned default NULL,
    pac  int(11) unsigned default NULL,
    udc1 int(11) unsigned default NULL,
    udc2 int(11) unsigned default NULL,
    udc3 int(11) unsigned default NULL,
    idc1 int(11) unsigned default NULL,
    idc2 int(11) unsigned default NULL,
    idc3 int(11) unsigned default NULL,
    sys  int(11) unsigned default NULL,
    PRIMARY KEY  (created)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"

    mysql -u$newuser -p"$userpw" -e "$command2"

    ## Tabellen und Status anzeigen
    command3="show databases;
    use $db;
    show tables;
    status;"

    mysql -u$newuser -p"$userpw" -e "$command3"

