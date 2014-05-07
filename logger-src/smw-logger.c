/*
    Simple solarmax logger c program written by zagibu@gmx.ch in July 2010
    This program was originally licensed under WTFPL 2 http://sam.zoy.org/wtfpl/
    It is now licensed under GPLv2 or later http://www.gnu.org/licenses/gpl2.html

    You need the mysql client library files installed to be able to compile it.

    Compile with: gcc -W -Wall -Wextra -Wshadow -Wlong-long -Wformat -Wpointer-arith -rdynamic -pedantic-errors -std=c99 -o smw-logger smw-logger.c -lmysqlclient

    Run with: ./smw-logger /path/to/config-file

    Structure of the config-file:

    Debug=0
    Loginterval=60
    Waitinterval=200
    DBhost=localhost
    DBname=solarmax
    DBtable=log10mt2
    DBuser=solaruser
    DBpass=solar5647
    Hostname=192.168.178.35
    Hostport=12345

   You can set DEBUG to 1 to get detailed output in a separate logfile.

   It is recommended to schedule the smw-logger to be started between 5:00 - 6:00 in the
   morning and stopped between 22:00 and 23:00 in the evening (compare with sunshine
   duration). The smw-logger has no built-in facility for logging, so use CRON or similar.

   Example CRON entries:
   00 05 * * * /usr/local/bin/smw-logger /usr/local/etc/smw-logger.conf
   00 23 * * * killall smw-logger

   Sources:
  - http://www.linuxhowtos.org/C_C++/socket.htm
  - http://wwwuser.gwdg.de/~kboehm/ebook/21_kap15_w6.html#49329
  - http://man.cx/setbuf%283%29
  - http://allfaq.org/forums/t/169895.aspx
  - http://dev.mysql.com/tech-resources/articles/mysql-capi-tutorial.html
*/

#define _GNU_SOURCE

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <strings.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <netdb.h>
#include <mysql/mysql.h>
#include <regex.h>
#include <time.h>
#include <unistd.h>
#include <fcntl.h>
#include <errno.h>
#include <pthread.h>

static  FILE*   error_file = NULL;
static  char    error_file_name[512];

static  FILE*   debug_file = NULL;
static  char    debug_file_name[512];

static  FILE*   config_file = NULL;
static  char*   config_file_name;

static  int     sockfd, portno, n, log_interval, logavg_interval;
static  int     result, wait_interval, DEBUG;

static  int     failure_flag;
static  struct  sockaddr_in serv_addr;
static  struct  hostent* server;
static  char    dbhost[512];
static  char    dbname[512];
static  char    dbtable[512];
static  char    dbuser[512];
static  char    dbpass[512];
static  char    hostaddr[512];
static  char    line[512];
static  char*   message;

static  int     kdy;    // Energy today [Wh]  (KDY)
static  int     kmt;    // Energy this month [kWh] (KMT)
static  int     kyr;    // Energy this year [kWh] (KYR)
static  int     kt0;    // Energy total [kWh] (KT0)
static  int     tkk;    // Temperature power unit 1 TKK
static  int     pac;    // AC power [mW] (PAC)
static  int     ud01;   // DC voltage [mV] string 1
static  int     ud02;   // DC voltage [mV] string 2
static  int     ud03;   // DC voltage [mV] string 3
static  int     id01;   // DC current [mA] string 1
static  int     id02;   // DC current [mA] string 2
static  int     id03;   // DC current [mA] string 3
static  int     sys;    // Operating state

static  char*   expression = "...=([0-9A-F]*);...=([0-9A-F]*);...=([0-9A-F]*);...=([0-9A-F]*);...=([0-9A-F]*);...=([0-9A-F]*);....=([0-9A-F]*);....=([0-9A-F]*);....=([0-9A-F]*);....=([0-9A-F]*);....=([0-9A-F]*);....=([0-9A-F]*);...=([0-9A-F]*)";
static  char    buffer[512], buffer2[512];
static  char    query[512];
static  regex_t rx;
static  regmatch_t* matches;
static  MYSQL*  connection = NULL;

static void error_exit(const char* msg) {
    perror(msg);
    if (error_file != NULL) fclose(error_file);
    if (debug_file != NULL) fclose(debug_file);
    exit(0);
}

static void error_retry(char* msg) {
    time_t timestamp = time(NULL);
    char error_msg[512];
    char *time_now = ctime(&timestamp);
    time_now[strlen(time_now)-1]=0;
    if (error_file == NULL) error_exit("ERROR writing to error.log file");
    sprintf(error_msg, "%s %s", time_now, msg);
    fprintf(error_file, "%s\n", error_msg);
}

static void debug_entry(char* msg) {
    time_t timestamp = time(NULL);
    char debug_msg[512];
    char *time_now = ctime(&timestamp);
    if (debug_file != NULL) {;
      time_now[strlen(time_now)-1]=0;
      sprintf(debug_msg, "%s %s", time_now, msg);
      fprintf(debug_file, "%s\n", debug_msg);
    } // if
    else {
      error_retry("ERROR writing to debug.log file");
    } // else
}

static void set_nonblock(int sock) {
    int flags;
    flags = fcntl(sock,F_GETFL,0);
    if (flags == -1) error_exit("ERROR no valid flags on socket");
    fcntl(sock, F_SETFL, flags | O_NONBLOCK);
}

int main(int argc, char *argv[]) {
    int     avgCount;

    // Check commandline arguments
    if (argc < 2) error_exit("ERROR program needs config-file as parameter");

    //Read Config File
    config_file_name = argv[1];
    config_file = fopen(config_file_name, "r");

    // Read variables
    if (config_file) {
        while (fgets(line, sizeof(line), config_file)) {
            sscanf(line, "Debug=%d[^\n]", &DEBUG);
            sscanf(line, "Errorfile=%[^\n]", error_file_name);
            sscanf(line, "Debugfile=%[^\n]", debug_file_name);
            sscanf(line, "Loginterval=%d[^\n]", &log_interval);
            sscanf(line, "Logavginterval=%d[^\n]", &logavg_interval);
            sscanf(line, "Waitinterval=%d[^\n]", &wait_interval);
            sscanf(line, "DBhost=%[^\n]", dbhost);
            sscanf(line, "DBname=%[^\n]", dbname);
            sscanf(line, "DBtable=%[^\n]", dbtable);
            sscanf(line, "DBuser=%[^\n]", dbuser);
            sscanf(line, "DBpass=%[^\n]", dbpass);
            sscanf(line, "Hostname=%[^\n]", hostaddr);
            sscanf(line, "Hostport=%d[^\n]", &portno);
        } // while
    } // if
    fclose(config_file);

    // Try to open error log file
    if ((error_file = fopen(error_file_name, "w")) == NULL) {
        error_exit("ERROR opening error.log file");
    } // if

    // Make file unbuffered
    setbuf(error_file, NULL);

    // calculate the requests per log_interval
    int logavg_pertick = log_interval /logavg_interval;

    // Try to open debug log file, if necessary
    if(DEBUG) {
        if((debug_file = fopen(debug_file_name, "w")) == NULL) {
            error_exit("ERROR opening debug.log file");
        } // if

        // Make file unbuffered
        setbuf(debug_file, NULL);
    } // if (DEBUG)

    // Try to compile regular expression
    result = regcomp(&rx, expression, REG_EXTENDED);
    if (result != 0) {
        regerror(result, &rx, expression, sizeof(expression));
        regfree(&rx);
        sprintf(buffer, "ERROR invalid regular expression: %s", expression);
        error_exit(buffer);
    } // if

    // Try to reserve memory for matches
    matches = (regmatch_t *) malloc((rx.re_nsub + 1) * sizeof(regmatch_t));
    if (!matches) error_exit("Out of memory");

    // Connect to database
    connection = mysql_init(NULL);
    if (!mysql_real_connect(connection, dbhost, dbuser, dbpass, dbname, 0, NULL, 0)) {
        error_exit(mysql_error(connection));
    } // if

    if (DEBUG) {
        sprintf(buffer, "Connected to database %s on host %s", dbname, dbhost);
        debug_entry(buffer);
    } // if (DEBUG)

    while (1) {

        // Check if connection to db-server must be re-established
        if (mysql_ping(connection)) {

            //TODO Maybe a reconnect (if needed) here ?
            // Connect to database
            if (!mysql_real_connect(connection, dbhost, dbuser, dbpass, dbname, 0, NULL, 0))
                error_exit(mysql_error(connection));

            if (DEBUG) {
                sprintf(buffer, "Connected to database %s on host %s", dbname, dbhost);
                debug_entry(buffer);
            }
        }

        // Try to resolve solarmax address/hostname
        server = gethostbyname(hostaddr);
        if (server == NULL) {
            sprintf(buffer, "Can't resolve \"%s\"", hostaddr);
            error_retry(buffer);
            sleep(60);
            continue;
        }

        // prepare network parameters
        bzero((char *) &serv_addr, sizeof(serv_addr));
        serv_addr.sin_family = AF_INET;
        bcopy((char *) server->h_addr, (char *) &serv_addr.sin_addr.s_addr, server->h_length);
        serv_addr.sin_port = htons(portno);

        // Start sending the data requests and logging the answers
        time_t start_time = time(NULL);
        while (1) {
            failure_flag = 0;
            kdy = kmt = kyr = kt0 = tkk = pac = ud01 = ud02 = ud03 = id01 = id02 = id03 = sys = 0;

            // sample the data
            avgCount = logavg_pertick;
            while (avgCount--) {
                time_t single_start_time = time(NULL);

                // Generate message according to device address of solarmax:
                // Could be something like this:
                // sprintf(message, "{FB;0%d;46|64:KDY;KMT;KYR;KT0;TNF;TKK;PAC;PRL;IL1;IDC;UL1;UDC;SYS|%s}", active_max, 16_bit_checksum
                // For further information on the protocol refer to: http://blog.dest-unreach.be/2009/04/15/solarmax-maxtalk-protocol-reverse-engineered

                message = "{FB;01;4C|64:KDY;KMT;KYR;KT0;TKK;PAC;UD01;UD02;UD03;ID01;ID02;ID03;SYS|124A}";

                if (DEBUG) {
                    sprintf(buffer, "Sending message: %s", message);
                    debug_entry(buffer);
                } // if (DEBUG)

                // Try to open socket for communication with solarmax
                sockfd = socket(AF_INET, SOCK_STREAM, 0);
                if (sockfd < 0) {
                    error_retry("Can't open any socket");
                    sleep(60);
                    continue;
                } // if

                // Establish a connection with solarmax
                if (connect(sockfd, (struct sockaddr*) &serv_addr, sizeof(serv_addr)) < 0) {
                    sprintf(buffer, "%s: Can't connect to solarmax (%s) on port %d", strerror(errno), hostaddr, portno);
                    error_retry(buffer);
                    close(sockfd);
                    sleep(600);
                    failure_flag = 1;
                    break;
                } // if

                // Make socket non-blocking
                set_nonblock(sockfd);

                // Send message
                n = write(sockfd,message,strlen(message));
                if (n < 0) {
                    close(sockfd);
                    error_retry("ERROR sending TCP packet");
                    failure_flag = 1;
                    break;
                }

                // Read answer
                bzero(buffer, 256);
                usleep(10000);
                n = read(sockfd, buffer, 255);
                // try a second time
                if (n < 0) {
                    if (DEBUG) debug_entry("Socket contains no data, trying to read again later");
                    usleep(50000);
                    n = read(sockfd, buffer, 255);
                } // if (n < 0)

                // close connection
                close(sockfd);

                if (n < 0) {
                    error_retry("ERROR receiving TCP packet");
                    failure_flag = 1;
                    break;
                } // if

                if (DEBUG) {
                    sprintf(buffer2, "Received answer: %s", buffer);
                    debug_entry(buffer2);
                } // if (DEBUG)

                // Extract the data fields from answer
                result = regexec(&rx, buffer, rx.re_nsub + 1, matches, 0);
                if (result) {
                    regerror(result, &rx, buffer, sizeof(buffer));
                    error_retry("ERROR no regexp match");
                    failure_flag = 2;
                    break;
                }

                // Convert the extracted data fields to integer values
                kdy   = strtol(buffer + matches[1].rm_so, NULL, 16);
                kmt   = strtol(buffer + matches[2].rm_so, NULL, 16);
                kyr   = strtol(buffer + matches[3].rm_so, NULL, 16);
                kt0   = strtol(buffer + matches[4].rm_so, NULL, 16);
                tkk  += strtol(buffer + matches[5].rm_so, NULL, 16);
                pac  += strtol(buffer + matches[6].rm_so, NULL, 16) / 2;
                ud01 += strtol(buffer + matches[7].rm_so, NULL, 16);
                ud02 += strtol(buffer + matches[8].rm_so, NULL, 16);
                ud03 += strtol(buffer + matches[9].rm_so, NULL, 16);
                id01 += strtol(buffer + matches[10].rm_so, NULL, 16);
                id02 += strtol(buffer + matches[11].rm_so, NULL, 16);
                id03 += strtol(buffer + matches[12].rm_so, NULL, 16);
                sys   = strtol(buffer + matches[13].rm_so, NULL, 16);

                //TODO check if the task need more time than logavg_interval
                if (avgCount) sleep(logavg_interval - (time(NULL)-single_start_time));

            } // while (avgCount--)

            // Calculate the average values and insert into db
            if (failure_flag == 0) {
                    tkk  = tkk  / logavg_pertick;
                    pac  = pac  / logavg_pertick;
                    ud01 = ud01 / logavg_pertick;
                    ud02 = ud02 / logavg_pertick;
                    ud03 = ud03 / logavg_pertick;
                    id01 = id01 / logavg_pertick;
                    id02 = id02 / logavg_pertick;
                    id03 = id03 / logavg_pertick;

                    // Construct the query according to active solarmax
                    sprintf(query, "INSERT INTO %s (kdy, kmt, kyr, kt0, tkk, pac, udc1, udc2, udc3, idc1, idc2, idc3, sys) VALUES (%d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d);",
                                           dbtable, kdy, kmt, kyr, kt0, tkk, pac, ud01, ud02, ud03, id01, id02, id03, sys);
                    if (DEBUG) {
                        sprintf(buffer, "Executing query: %s", query);
                        debug_entry(buffer);
                    } // if (DEBUG)

                    // Execute the query to write the data into db
                    mysql_query(connection, query);
                    if (mysql_errno(connection)) error_exit(mysql_error(connection));

            } // if (failure_flag == 0)

            // Wait for the specified number of seconds - calc duration - 1
            if (DEBUG) debug_entry("Waiting for next sampling ...");

            // Get the current time
            time_t stop_time = time(NULL);

            // TODO check if time needed is > log_interval
            int sleepTime = log_interval + start_time - stop_time;
            if(sleepTime > 0) {
                sleep(sleepTime);
                start_time += log_interval;
            } // if
            else {
                // you get here after the 10 min delay after a connection error
                if (DEBUG) {
                    sprintf(buffer, "!!! sleepTime error. Assuming desync: %i seconds. !!!\n", sleepTime);
                    debug_entry(buffer);
                } // if (DEBUG)
                start_time = time(NULL);
            } // else

        } // while
    } // while

    return 0;
} // main
