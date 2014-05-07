#include <stddef.h>
#include <stdio.h>
#include <stdlib.h>
#include <ctype.h>
#include <string.h>

/****************************************************************************
 ****************************************************************************
 Builds the command message to send to the Solarmax
 msg : a buffer that can hold the complete message
 cmd : the command section between "...|64:" and "|checksum}"
 addr: the destiantion address
 Example:
 BuildCommandMessage(msg, "KDY;KMT;KYR;KT0;TNF;TKK;PAC;PRL;IL1;IDC;UL1;UDC;SYS", 1);

 ****************************************************************************/

static void BuildCommandMessage(char* msg, const char* cmd, int addr)
{
  int   sum;
  char* pChar;
  char  s[8];

  sprintf(msg, "{FB;%02X;%02X|64:%s|", addr, strlen(cmd) + 19, cmd);
  sum = 0;
  pChar = msg + 1; // sum starts after the opening {
  while (*pChar != 0) {
    sum += (int)*pChar++;
  } /* while */
  sprintf(s, "%04X}", sum);
  strcat(msg, s);
} /* BuildCommandMessage */


/*************************************************************************
| MAIN
| Build:
|   gcc smw-message.c -o smw-message
| Usage:
|   ./smw-message ["commands" [address]]
| Example:
|   ./smw-message "UDC1;UDC2;PAC" 3
|   {FB;03;20|64:UDC2;UDC3;PAC|06C6}
|*************************************************************************/

int main(int argc, char *argv[])
{
  char  msg[256];
  int   address;

  if (argc == 3) {
    sscanf(argv[2], "%i", &address);
    BuildCommandMessage(msg, argv[1], address);
  } /* if */
  else if (argc == 2) {
    BuildCommandMessage(msg, argv[1], 1);
  } /* if */
  else {
    BuildCommandMessage(msg, "KDY;KMT;KYR;KT0;TKK;PAC;UD01;UD02;ID01;ID02;SYS", 1);
  } /* else */
  printf("%s\n", msg);
  return 0;
} /* main */

