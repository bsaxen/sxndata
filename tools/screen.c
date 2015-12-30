#include <stdlib.h>
#include <sys/ioctl.h>
#include <stdio.h>
#include <string.h>
#include <time.h>

#define KNRM  "\x1B[0m"
#define KRED  "\x1B[31m"
#define KGRN  "\x1B[32m"
#define KYEL  "\x1B[33m"
#define KBLU  "\x1B[34m"
#define KMAG  "\x1B[35m"
#define KCYN  "\x1B[36m"
#define KWHT  "\x1B[37m"

#define C_NORMAL  0
#define C_RED     1
#define C_GREEN   2
#define C_YELLOW  3
#define C_BLUE    4
#define C_MAGENTA 5
#define C_CYAN    6
#define C_WHITE   7

struct winsize w;
int g_rows;
int g_cols;
char dmx[200][200];
int  cmx[200][200];

void writeString(char text[],int x, int y, int color);


char *trim(char *s) {
    char *ptr;
    if (!s)
        return NULL;   // handle NULL string
    if (!*s)
        return s;      // handle empty string
    for (ptr = s + strlen(s) - 1; (ptr >= s) && isspace(*ptr); --ptr);
    ptr[1] = '\0';
    return s;
}

//==========================================
void readFile(char filename[])
//==========================================
{
       FILE * fp;
       char * line = NULL;
       size_t len = 0;
       ssize_t read;
       int x=g_rows;

       fp = fopen(filename, "r");
       if (fp == NULL)
           return;
     
       while ((read = getline(&line, &len, fp)) != -1) {
           //printf("Retrieved line of length %zu :\n", read);
           //printf("%s", line);
           line = trim(line);
           x--;
           //if(read > g_cols) line[g_cols] = '\0';
           writeString(line,3,x,C_BLUE);
       }

       fclose(fp);
       if (line)
           free(line);
       return;
}
//==========================================
void display()
//==========================================
{
  int i,j,color;
  for(j=g_rows;j>0;j--)
  {
    printf("%s%2d",KYEL,j);
    for(i=1;i<=g_cols-2;i++)
    {
          color = cmx[i][j];
          if(color == C_NORMAL) printf("%s%c",KNRM,dmx[i][j]);
          if(color == C_RED)    printf("%s%c",KRED,dmx[i][j]);
          if(color == C_GREEN)  printf("%s%c",KGRN,dmx[i][j]);
          if(color == C_YELLOW) printf("%s%c",KYEL,dmx[i][j]);
          if(color == C_BLUE)   printf("%s%c",KBLU,dmx[i][j]);
          if(color == C_MAGENTA)printf("%s%c",KMAG,dmx[i][j]);
          if(color == C_CYAN)   printf("%s%c",KCYN,dmx[i][j]);
          if(color == C_WHITE)  printf("%s%c",KWHT,dmx[i][j]);
          printf("%s",KNRM);
    }
    printf("\n");
  }
}
//==========================================
void writeString(char text[],int x, int y, int color)
//==========================================
{
    int i,k=0;
    int len = strlen(text);
    if(len > g_cols - x) len = g_cols - x;
    for(i=x;i<x+len;i++)
    {
        dmx[i][y] = text[k];
        cmx[i][y] = color;
        k++;
    }
}
//==========================================
int main()
//==========================================
{
    int i,j,x=0,y=0,sid=5;
    char ch,sys[240];
    
    // Init
    ioctl(0, TIOCGWINSZ, &w);
    g_rows = w.ws_row;
    g_cols = w.ws_col;
    for(j=1;j<=g_rows-1;j++)
    {
       for(i=1;i<=g_cols-2;i++)
       {
          dmx[i][j] = ' ';
          cmx[i][j] = 0;
       }
    }


    //printf("lines %d\n", g_rows);
    //printf("columns %d\n", g_cols);
    while (1)
    {
      sprintf(sys,"wget -q -O server_response.txt \"http://78.67.160.17/sxndata/index.php?mid=2&nsid=1&sid1=%d\"",sid);
      //sprintf(sys,"wget -q -O server_response.txt \"http://127.0.0.1/sxndata/index.php?mid=2&nsid=1&sid1=%d&dat1=33.33\"",sid);
      //printf("%s\n",sys);
      system(sys);  
      writeString("Client:",30,20,C_GREEN); 
      readFile("server_response.txt");
      display();
   
      printf("%s>",KNRM);
      sleep(5);
      //ch = getchar();
      // Cleanup
      system("rm -f server_response.txt");
      system("clear");
 

        
      if (ch=='q')
	  {
	    exit(0);
	  }
    }
    /*printf("%sred\n",     KRED);
    printf("%sgreen\n",   KGRN);
    printf("%syellow\n",  KYEL);
    printf("%sblue\n",    KBLU);
    printf("%smagenta\n", KMAG);
    printf("%scyan\n",    KCYN);
    printf("%swhite\n",   KWHT);
    printf("%snormal\n",  KNRM);*/
    return 0;

   
}
