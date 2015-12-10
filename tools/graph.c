//======================================
// graph.c
// apt-get install ncurses-dev
// gcc -o graph graph.c -lncurses -lm
// Version: 2015-12-10
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <time.h> 
#include <math.h> 
#include <ctype.h>
#include <termios.h>
#include <unistd.h>
#include <ncurses.h>
#include <sys/stat.h>
#include <form.h>

#include <sys/ioctl.h>

#define KNRM  "\x1B[0m"
#define KRED  "\x1B[31m"
#define KGRN  "\x1B[32m"
#define KYEL  "\x1B[33m"
#define KBLU  "\x1B[34m"
#define KMAG  "\x1B[35m"
#define KCYN  "\x1B[36m"
#define KWHT  "\x1B[37m"


WINDOW *graph,*data,*misc;
static struct termios orig, nnew;

#define GRAPH_H  20
#define GRAPH_W  61
#define GRAPH_COLOR 6
#define DATA_COLOR 7
#define MISC_COLOR 5
#define WORLD 1000

#define RF 1 
#define ER 1
#define SR 20

int graph_h=0, graph_w=0, graph_x=0, graph_y=0;
int data_h=0, data_w=0, data_x=0, data_y=0;
int misc_h=0, misc_w=0, misc_x=0, misc_y=0;
//int board_w=0,board_h=0,board_x=0,board_y=0;
int ap,dp,s_row,s_col,pos=0;

#define DATA_MAX 86400
//#define X_MAX 500
//#define Y_MAX 500

int g_dataSource = 1;
int g_mode = 1;
int g_display,g_nData,g_selectedFile,g_zn;
int g_hour,g_minute,g_second;
float g_xmin=9999.,g_xmax=0.,g_ymin=9999.,g_ymax=0.;
float g_wxmax=0., g_wxmin=999999.,g_wymax=0., g_wymin=999999.;
//char str[40],sstr[20];
//char command[40][40];
char g_data_file[120];
int g_selected_file = 1,g_chan = 1;
//char g_area[X_MAX][Y_MAX];
float g_xdata[DATA_MAX],g_ydata[DATA_MAX],g_der[DATA_MAX];
float g_xvalue[DATA_MAX];
int g_NX,g_NY,g_X,g_Y,g_freq[DATA_MAX];
float r_ymax=0.0,r_ymin=0.0,r_xmin=0.0,r_xmax=0.0;

float g_pi=3.1415926;

int generatedData(int mdl);

//========================================
int valToHourMinSec(float val)
//========================================
{
    g_hour = floor(val/3600);
    val = val-g_hour*3600;
    g_minute = floor(val/60);
    val = val-g_minute*60;
    g_second = val;
    return;
}
//========================================
void list_file_content()
//========================================
{
  FILE *in;
  int i,j,lines;
  char line[80];

  system("ls *.nbc > nbc_data_file_list.txt");
  wclear(data);    
    
  lines = data_h-2;
  //wmove(misc,1,2);wprintw(misc,"%d",start);wrefresh(misc); 

  i = 0;
  j = 0;
  in = fopen("nbc_data_file_list.txt","r");
  if(in == NULL)
  {
    //printf("Error: Noh file: %s\n",file);   
  }
  else
  {
    while (fgets(line,sizeof line,in) != NULL)
    {
        i++;
        if(i >= g_selected_file)
        {
          j++;
          wmove(data,j,1);
          if(i == g_selected_file)
          {
              wprintw(data,">%3d %s",i,line);
              sscanf(line,"%s",g_data_file); 
          }
          else
              wprintw(data," %3d %s",i,line);
            
          if(j == lines)return;
        }
    }
    return;
  }
}
//========================================
int derivata()
//========================================
{
    int i,n;
    float v;
    
    n = g_xdata[0];
    g_ymin=9999.;
    g_ymax=0.;
    
    //printf("n=%d\n",n);exit(0);
    for(i=2;i<=n;i++)
    {
        v = g_ydata[i] - g_ydata[i-1];
        g_der[i] = v;
        if(g_ymax < v)g_ymax = v;
        if(g_ymin > v)g_ymin = v;
    }
    for(i=1;i<n;i++)
    {
        g_ydata[i] = g_der[i+1];
    }
    n = n-1;
    return(n);
}
//========================================
int frequency()
//========================================
{
    int i,n,itemp;
    float t,v,DIV=1;
    
    n = g_xdata[0];
    g_ymin=9999.;
    g_ymax=0.;
    g_xmin=9999.;
    g_xmax=0.;

    DIV = g_ymax/DATA_MAX;
    if(DIV < 1)DIV = 1;
    mvwprintw(misc,misc_h-5,1,"DIV = %.3f >",DIV);    
    for(i=0;i<DATA_MAX;i++)
    {
          g_freq[i] = 0.0;
    }
    for(i=1;i<=n;i++)
    {
        itemp = floor(g_ydata[i]/DIV);
        if(itemp < 0)itemp = -itemp;
        g_freq[itemp]++;
        v = g_freq[itemp];
        t = itemp;
        if(g_ymax < v)g_ymax = v;
        if(g_ymin > v)g_ymin = v;
        if(g_xmax < t)g_xmax = t;
        if(g_xmin > t)g_xmin = t;
    }
    n=g_xmax;
    mvwprintw(misc,misc_h-4,1,"g_xmax = %.3f >",g_xmax);
    for(i=1;i<=n;i++)
    {
        g_ydata[i] = g_freq[i];
        g_xdata[i] = i;
    }
    r_xmin = 0; r_xmax = n;
    r_ymin = 0; r_ymax = g_ymax;
    return(n);
}
//========================================
int readFile(int mode,char *selectedFile)
//========================================
{
    FILE *in;
    int i,n,skip = 0,hour,minute,sec;
    float t,t_prev,old_t;
    float x,x1,x2,old_x,v;    
    char line[80],ttime[80];
    
    if(mode==1)
    {


       for(i=0;i<DATA_MAX;i++)
       {
          g_xdata[i] = 0.0;
          g_ydata[i] = 0.0;
          g_xvalue[i] = 0.0;
       }
       g_xmin=9999.;
       g_xmax=0.;
       g_ymin=9999.;
       g_ymax=0.;
    

       in = fopen(selectedFile,"r");
       if(in == NULL)
       {
         n = 0;
    //printf("Error: No data file\n");  
         g_display = 0; 
       }
       else
       {
          n = 0;
          while (fgets(line,sizeof line,in) != NULL)
          {
             n++;
             if(n > DATA_MAX)
             {
              //printf("**** Warning: Max data range reached ****");
              return(n);
             }
             sscanf(line,"%s %f %f",ttime,&x1,&x2);
             sscanf(ttime,"%d:%d:%d",&hour,&minute,&sec);

             //if(g_chan == 1) 
             //{
                t = hour*3600+minute*60+sec;
                v = x1;
             //}
             g_xvalue[(int)t] = v;
             g_xdata[n] = t*1.0;
             g_ydata[n] = v;
               
            if(g_ymax < v)g_ymax = v;
            if(g_ymin > v)g_ymin = v;
            if(g_xmax < t)g_xmax = t;
            if(g_xmin > t)g_xmin = t;
         }
    
        fclose(in);
        for(i=1;i<DATA_MAX;i++)
        {
            if(g_xvalue[i] == 0.0) g_xvalue[i] = g_xvalue[i-1];
        }
      } 
    }
    if(mode==2)
    {
      n = generatedData(1);
    }
    
    g_xdata[0] = n;
    
    //++++++++++++++++++++++++++
    //n = derivata();
    g_xdata[0] = n;
    //n = frequency();
    g_xdata[0] = n;
    //++++++++++++++++++++++++++
    
return(n);
}
//========================================
float stepFunction(float s,float h,float x)
//========================================
{
    //              
    //        -------------------------
    //        |           
    //       h|            
    //        |           
    //---------            
    //        s
    //
    //printf("c1 %f\n",x);
    float y;
    if(x >= s) y = h;
    if(x < s)  y = 0.0;
    //printf("c2\n");
    return(y);    
}
//========================================
float squareFunction(float s,float h,float d,float x)
//========================================
{
    //            d  
    //        ----------
    //        |        |  
    //       h|        |    
    //        |        |   
    //---------        ----------    
    //        s
    //
    //printf("b1 %f\n",x);
    float y,temp1,temp2;
    temp1 = s+d;
    temp2 = -h;
    y = stepFunction(s,h,x) + stepFunction(temp1,temp2,x);
    //printf("b2\n");
    return(y);    
}
//========================================
float squareWave(float s,float h,float T,float p, float x)
//========================================
{
    //              d
    //        --------------         -----------
    //        |            |         |
    //       h|            |         |
    //        |            |   p     |
    //---------            -----------
    //        s
    //
    // Period T = d+p
    //
    
    float y,temp;
 
    float z = floor(x/T); x = x-z*T;
    temp = T-p;
    y = squareFunction(s,h,temp,x);
    //printf("z=%f y=%f s=%f h=%f T=%f p=%f x=%f temp=%f\n",z,y,s,h,T,p,x,temp);
    //printf("a2\n");   
    return(y);    
}
    //========================================
int generatedData(int mdl)
//========================================
{
        FILE *out;
int i,n,skip = 0,hour,minute,sec;
float x,x1,x2,old_x,v,t;    
char line[80],ttime[80];

for(i=0;i<DATA_MAX;i++)
{
        g_xdata[i] = 0.0;
        g_ydata[i] = 0.0;
}
g_xmin=9999.;
g_xmax=0.;
g_ymin=9999.;
g_ymax=0.;
    
out = fopen("tempData.tg","w");
n = 0;
for(i=-1000;i<1000;i++)
{
  n++;
  t = i;
  //t = g_pi*i/200;
  //v = sin(g_pi*i/200)*t*t;
  v = 
      squareWave(0.0 ,10.0,100.0,50.0, t)+
      squareWave(20.0,50.0, 70.0,50.0, t)+
      squareWave(10.0,100.0,200.0,70.0, t);
      
  g_xdata[n] = t;
  g_ydata[n] = v;
  if(g_ymax < v)g_ymax = v;
  if(g_ymin > v)g_ymin = v;
  if(g_xmax < t)g_xmax = t;
  if(g_xmin > t)g_xmin = t;
  fprintf(out,"%d %f %f\n",n,t,v);
}
fclose(out);
    //exit(0);
return(n);
}
//========================================
void prep_area(int ndata,float xdata[],float ydata[], float left, float right, float down, float top)
//========================================
{
    
      wclear(graph); 

      int i,j,k,ix,iy,zeroline,plot[WORLD][WORLD];
      float dx,dy,tx,ty;
 
      dx = (right - left)/graph_w;
      dy = (top - down)/graph_h;
    
      float midX = (right + left)/2;
      float midY = (top + down)/2;
    
      int origo = WORLD/2;

      for(i=0;i<WORLD;i++)
      {
        for(j=0;j<WORLD;j++)
        {
           plot[i][j] = 0;
        }
      }
 
      g_wxmin = 999999.; g_wxmax = 0.;
      g_wymin = 999999.; g_wymax = 0.;
    
      int ix0 = origo -midX/dx + 0.5;
      if(ix0 < 0)ix0=0;
      int iy0 = origo -midY/dy + 0.5;
      if(iy0 < 0)iy0=0;
      int ix1 = origo +(left-midX)/dx + 0.5;
      if(ix1 < 0)ix1=0;
      int ix2 = origo +(right-midX)/dx + 0.5;
      if(ix2 < 0)ix2=0;
      int iy1 = origo +(down-midY)/dy + 0.5;
      if(iy1 < 0)iy1=0;
      int iy2 = origo +(top-midY)/dy + 0.5;
      if(iy2 < 0)iy2=0;
      //wmove(misc,1,2);wprintw(misc,"ndata=%d ix0=%d iy0=%d ix1=%d ix2=%d iy1=%d iy2=%d\n",ndata,ix0,iy0,ix1,ix2,iy1,iy2);
      wmove(misc,2,2);wprintw(misc,"ndata=%d",ndata);
      valToHourMinSec(left);
      wmove(misc,1,2);wprintw(misc,"%2d:%2d:%2d",g_hour,g_minute,g_second); 
      int mid = (right + left)/2;
      valToHourMinSec(mid);
      wmove(misc,1,11);wprintw(misc,"<%2d:%2d:%2d>",g_hour,g_minute,g_second);    
      valToHourMinSec(right);
      wmove(misc,1,22);wprintw(misc,"%2d:%2d:%2d",g_hour,g_minute,g_second);    
      valToHourMinSec(dx);
      wmove(misc,1,32);wprintw(misc,"d=%2d:%2d:%2d",g_hour,g_minute,g_second);    

      wmove(misc,2,22);wprintw(misc,"value=%.2f     ",g_xvalue[mid]);    
    
      for(i=iy1;i<=iy2;i++)plot[ix0][i] = 4;
      for(i=ix1;i<=ix2;i++)plot[i][iy0] = 3;
      plot[ix0][iy0] = 2;
      
      for(i=1;i<=ndata;i++)
      {       
        tx = xdata[i]; ty = ydata[i];
        if((tx >= left && tx <= right))
        {
              ix = origo + (tx-midX)/dx + 0.5;
              iy = origo + (ty-midY)/dy + 0.5;
              if(iy > iy0)for(j=iy;j>iy0;j--)plot[ix][j] = 1;
              if(iy < iy0)for(j=iy;j<iy0;j++)plot[ix][j] = 1;
              plot[ix][iy] = 5;
        }
        if(g_wxmax < tx)g_wxmax = tx;
        if(g_wxmin > tx)g_wxmin = tx;
         
        if(g_wymax < ty)g_wymax = ty;
        if(g_wymin > ty)g_wymin = ty;
      }

          
      for(i=1;i<graph_w;i++)
      {
        for(j=1;j<graph_h;j++)
        {
            ix = origo+i-graph_w/2;
            iy = origo+j-graph_h/2;
            wmove(graph,graph_h-j,i);
            if(plot[ix][iy] == 1)wprintw(graph,"."); // fill 
            if(plot[ix][iy] == 2)wprintw(graph,"o"); // origi
            if(plot[ix][iy] == 3)wprintw(graph,"-"); // x-axis
            if(plot[ix][iy] == 4)wprintw(graph,"|"); // y-axis
            if(plot[ix][iy] == 5)wprintw(graph,"*"); // top value
            if(plot[ix][iy] == 6)wprintw(graph,"="); // left corner
            if(plot[ix][iy] == 7)wprintw(graph,"+"); // right corner
        }
      }

}
//====================================
void show(WINDOW *win)
//====================================
{
  int next;
  box(win,0,0);
  if(win == graph)
  {
     wmove(win,0,2);
     wprintw(win," Graph ");
  }
  if(win == data)
  {
     wmove(win,0,2);
     wprintw(win," Data ");
  }
  if(win == misc)
  {
     wmove(win,0,2);
     wprintw(win," Misc ");
  }
  wrefresh(win);
}
//====================================
int tokCommand(char res[40][40],char *inp)
//====================================
{
  char *pch;
  int count = 0;

  pch = strtok(inp," ");
  while (pch != NULL)
    {
      strcpy(res[count],pch);
      count++;
      pch = strtok(NULL, " ");
    }
  return(count);
}

//====================================
void runMode()
//====================================
{
  int ch,x,step,tmp,res=0,a=0,b=0,ir,ok=0,n=0;
  char tempName[80],syscom[120],temp[80];
  char command[40][40];
  float xdelta = 0.0,ydelta=0.0;

  while(1)  
    {
      xdelta = (r_xmax-r_xmin)/graph_w;
      ydelta = (r_ymax-r_ymin)/graph_h;
      show(graph); show(data); show(misc);         
      wmove(misc,misc_h-2,1);
      wprintw(misc,"                                    ");
      mvwprintw(misc,misc_h-2,1,"%d>%s",g_mode,temp);
       
      wrefresh(misc);
      ch = getchar();
      strcpy(temp," ");
      if (ch=='q')
	  {
	    return;
	  }
      else if (ch=='h')
      {
 
      }
      else if (ch=='1')
      {
            g_chan = 1;
            g_nData = readFile(g_mode,g_data_file);
      }
      else if (ch=='2')
      {
            g_chan = 2;
            g_nData = readFile(g_mode,g_data_file);
      }
      else if (ch=='3')
      {
            g_chan = 3;
            g_nData = readFile(g_mode,g_data_file);
      }
      else if (ch=='4')
      {
            g_chan = 4;
            g_nData = readFile(g_mode,g_data_file);
      }
      else if (ch=='n')
      {
          g_selected_file++;
          list_file_content();
          g_nData = readFile(g_mode,g_data_file);
          r_xmin = g_xmin;r_xmax = g_xmax;
          r_ymin = g_ymin;r_ymax = g_ymax;
 
      }
      else if (ch=='p')
      {
          g_selected_file--;
          list_file_content();
          g_nData = readFile(g_mode,g_data_file);
          r_xmin = g_xmin;r_xmax = g_xmax;
          r_ymin = g_ymin;r_ymax = g_ymax;
      }
      else if (ch=='b')
	  {
          r_xmin = g_wxmin;
          r_xmax = g_wxmax;
          r_ymin = g_wymin;
          r_ymax = g_wymax;
	  }
      else if (ch=='x')
	  {
          r_xmin = r_xmin+xdelta;
          r_xmax = r_xmax-xdelta;
          r_ymin = r_ymin+ydelta;
          r_ymax = r_ymax-ydelta;
	  }
      else if (ch=='X')
	  {
          r_xmin = r_xmin-xdelta;
          r_xmax = r_xmax+xdelta;
          r_ymin = r_ymin-ydelta;
          r_ymax = r_ymax+ydelta;
	  }
      else if (ch=='z')
	  {
          r_xmin = r_xmin+xdelta;
          r_xmax = r_xmax-xdelta;
	  }
      else if (ch=='Z')
	  {
          r_xmin = r_xmin-xdelta;
          r_xmax = r_xmax+xdelta;
	  }
      else if (ch=='l')
	  {
          r_xmin = r_xmin-xdelta;
          r_xmax = r_xmax-xdelta;
	  }
      else if (ch=='r')
	  {
          r_xmin = r_xmin+xdelta;
          r_xmax = r_xmax+xdelta;
	  }
      
      else if (ch=='s')  // zoom out Y
	  {
          r_ymin = r_ymin+ydelta;
          r_ymax = r_ymax-ydelta;
	  }
      else if (ch=='S')  //zoom in Y
	  {
          r_ymin = r_ymin-ydelta;
          r_ymax = r_ymax+ydelta;
	  }
      else if (ch=='u')
	  {
          r_ymin = r_ymin-ydelta;
          r_ymax = r_ymax-ydelta;
	  }
      else if (ch=='d')
	  {
          r_ymin = r_ymin+ydelta;
          r_ymax = r_ymax+ydelta;
	  }
      else if (ch=='m')
	  {
          g_mode++;
          if (g_mode>2)g_mode=1;
	  }     
      else
	  {
	    sprintf(temp,"Unknown command: %c",ch);
	  }
      
      prep_area(g_nData,g_xdata,g_ydata, r_xmin, r_xmax, r_ymin, r_ymax);           
    }
    return;
}
//====================================
void openCommand()
//====================================
{
  struct stat st;
  int ch,nsteps=1000,x,i,n,stop=0,loop,projNo = 0,ok=0,tmp;
  char *p,str[120],sstr[20],fileName[120],temp[120],syscom[120];
  char command[40][40];

  while(strstr(str,"ex") == NULL)
  {
      show(graph); show(data);show(misc);
      wmove(misc,misc_h-2,1);
      wprintw(misc,"                                                  ");
      mvwprintw(misc,misc_h-2,1,"tg3>");
      wrefresh(misc);
      strcpy(command[0],"");
      wgetstr(misc,str);
      n = tokCommand(command,str);
      strcpy(sstr,command[0]);

      if(strstr(sstr,"run"))
        {
          runMode();
        }
      else if(strstr(sstr,"fil"))
        {
          list_file_content();       
        }
  }
}
//====================================
int main(int argc, char *argv[])
//====================================
{
  char syscom[120];
  int ch,i,x;

initscr();
clear();
cbreak();

getmaxyx(stdscr,s_row,s_col);
    
start_color();
init_pair(1,COLOR_BLACK,COLOR_BLUE);
init_pair(2,COLOR_BLACK,COLOR_GREEN);
init_pair(3,COLOR_BLUE,COLOR_WHITE); 
init_pair(4,COLOR_RED,COLOR_WHITE); 
init_pair(5,COLOR_MAGENTA,COLOR_WHITE); 
init_pair(6,COLOR_WHITE,COLOR_BLACK); 
init_pair(7,COLOR_WHITE,COLOR_BLUE);
  
  /*     COLOR_BLACK   0 */
  /*     COLOR_RED     1 */
  /*     COLOR_GREEN   2 */
  /*     COLOR_YELLOW  3 */
  /*     COLOR_BLUE    4 */
  /*     COLOR_MAGENTA 5 */
  /*     COLOR_CYAN    6 */
  /*     COLOR_WHITE   7 */
    
  // Graph Window   
  graph_w = s_col;
  graph_h = s_row-10;
  graph_x = 0;
  graph_y = 0;
  graph=newwin(graph_h,graph_w,graph_x,graph_y);
  wbkgd(graph,COLOR_PAIR(GRAPH_COLOR));
    
  // Data Window   
  data_w = s_col/2;
  data_h = 10;
  data_x = s_row-10;
  data_y = 0;
  data=newwin(data_h,data_w,data_x,data_y);
  wbkgd(data,COLOR_PAIR(DATA_COLOR));
    
  // Misc Window   
  misc_w = s_col/2;
  misc_h = 10;
  misc_x = s_row-10;
  misc_y = s_col/2;
  misc=newwin(misc_h,misc_w,misc_x,misc_y);
  wbkgd(misc,COLOR_PAIR(MISC_COLOR));
 
  openCommand();
 
  delwin(graph);
  endwin();

}
//====================================
// End of file
//====================================
