// g++ -Wall -pthread -o camerapio camera.c -lpigpio -lrt -lwiringPi ` pkg-config --cflags --libs opencv gtk+-2.0` `mysql_config --cflags --libs` 


#include "opencv2/core/core.hpp" //bibliotheque générale d'opencv
#include "opencv2/highgui/highgui.hpp" //bibilotheque auxilliaire(traitement d'image)
#include "opencv2/imgproc/imgproc.hpp" //bibliotheque auxilliaire(affochage des images)
#include "opencv2/opencv.hpp" // Root des bibilotheques
//#include "opencv2/gpu/gpu.hpp"
#include <stdlib.h>
#include <stdio.h>
#include <iostream> //bibliotheque de gestion des entrées video
#include <string.h>
#include <sys/time.h> //bibliotheque interne a la raspberry (permet de recupere la date et l'heure de la raspberry
#include <gtk/gtk.h> //bibliotheque GTK permettant l'affichage et la gestion des fenetres.
#include <mysql/mysql.h>
#include <sys/fcntl.h>
#include <sys/types.h>
#include <pigpio.h> //bibliotheque pour la gestion des GPIO de la rpi 
#include <sys/io.h>
#include <pthread.h> //bibliotheque de gestion des thread processeurs (au nombre de 4)
#include <unistd.h>

using namespace std;
using namespace cv;


double cpt_clock=1;
double total_elapsed=0;
	int pass=0;
	float tot=0;

// -------PROTOTYPAGE ----------

void Initialisation (void);

void sauvegarde_graphique();// Fonction de sauvegarde du graphique en temps réel

void calib_auto();//foncion de calibration automatique

void sauvegarde_calib(); //fonction de sauvegarde de la calibration

void calibration(Mat CSource); // fonction permettant de modifier la position des portes pendant l'execution du programme (visuel uniquement)

void get_time(); //fonction de récupération de l'heure et de la date (gere aussi l'initialisation des variables correspondantes (Heure,Minute,Day ... etc)

void window_create(); //fonctiond d'initialisation des fenetres (aps obligatoire mais permet de travailler dans une memoire que nous connaissons et qui est sure)

void passage(int i,int *deplacement,int *bisY, int *LastY,int *flag, int *entree, int *sortie); //fonction permettant de compter le passage des abeilles (le i correspond a l'indice de l'image que nous passons)

void dessinligne(int i,Mat *image,int *posX,int *posY,int *LastX,int *LastY, int couleur); // fonction de déterminantion et affichage du vecteur pour le comptage

void sauvegarde_automatique(); //fonction de sauvegarde (tout les x minutes (choisi apr l'utilisateur)) uniquement les ficher .csv

void suppressbruit(Mat Pic); //fonction générique qui permet de supprimer le bruit dans une image

void recall(); //rappel des données de calibration (obslolete depuis le calibrage automatique)

void GTK (void); //fonctionne d'affichage et gestion des fenetres.

void OnDestroy(GtkWidget *pWidget, gpointer pData);//fonction fesant fonctionner GTK <- bouton "quitter"

void OnValid(GtkWidget *pWidget, gpointer pData);
	
void OnInit(GtkWidget *pWidget, gpointer pData);
	
void Minutep(GtkWidget *pWidget, GtkLabel *pLabel);//fonction pour minute "+"
void Minutem(GtkWidget *pWidget, GtkLabel *pLabel);//fonction pour minute "-"

void FlagModifier( GtkWidget *pWidget, GtkButton *pButton); //fonction de récupération de la case sur laquelle nous avons cliqué.

void OnScrollChange( GtkWidget *pWidget, gpointer pData);

void on_activate_entry(GtkWidget *pEntry, gpointer data); // Ecriture dans la fenetre de saisie(initialisation)

void on_copier_button(GtkWidget *pButton, gpointer data); // Récupération des données dans la fenetre de saisie (initialisation)

void sauvegarde_usb(void); //Nous permettra d'effectuer une sauvegarde des données dans une clef usb ajoutée.

void sauvegarde_sql(void); // fonction permettant d'ecrire dans la BDD




//--------DECLARATION DES VARIABLES -------------------------------------------------------

//initialisation par defaut(obsolete mais présent au cas ou un sous nombre de porte serait utilisé
int X[40]={450,460,470,480,490,500,510,520,530,540,550,560,570,580,590,600,610,620,630,640};
int Y[2]={300,330};

int Quitter=0; //varibales flag permettant de quitter le programme sur demande (debug only)
int C=0; //variable flag permettant d'entrer dans la calibration des portes (debugo only)
int video=0;// variable permettant de determiner l'affichage que nous voulons( 0=rien, 1=video, 2=image fixe, 3=graphique)
int quitterC=0;//variable flag permettant de quitter la calibration automatique
int T_variable[100]={0}; //(dans fonction recall) permet de recupere dans un tableau les valeurs de fichier "Sauvegarde.txt" qui est le fichier de sauvegarde de la calibration
int T_raw[24000]={0}; //(dans fonction sauvegare_graphique) tableau tres lourd, permettant de mettre en memoire la totalitée de la sauvegarde journaliere pour créer le graphique au format une case pour un chiffre
int T_data[7500]={0}; //(dans sauvegarde_graphique) tableau plus leger contenant les données des mesures de la journée au format une case pour un valeur (
int nombreporte=0; //variable qui porte le nombre de porte cette variable est initialisée dans calib_auto

//---------Creation de toutes les images--------------------

Mat hsvcrop[20]; //tableau contenant toutes les petites images apres leur traitement HSV
Mat hsvcropR[20]; 
Mat hsvcropB[20]; 
Mat hsvcropJ[20]; 
Mat hsvcropV[20]; 
Mat source,hsvsource,masquesource,src,masquesourcebleu,masquesourcerouge,masquesourcerouge1,masquesourcerouge2; //declaration des matrices d'images principales (pour le debug)
Mat masquecropTotal[20]; //tableau contenant toutes les petites images apres le traitement du filtre de couleur
Mat masquecropRouge[20]; //HSV 42  153 115

Mat masquecropJaune[20]; //HSV 5   153 115
Mat masquecropVert[20];	 //HSV 81  153 115
Mat masquecropBleu[20];  //HSV 170 153 115
Mat masquecropViolet[20];//HSV 210 153 115
Mat cropedImage[20]; //tableau contenant toutes les originaux des petites images
Mat im_menu; //declaration d'une matrice pour l'image principale
Mat Graph; // matrice de sauvegarde de l'image du graphique (permet aussi d'afficher ce dernier


VideoCapture capture(0); //initialisation du flux(on le met ici car toutes les fonctions profiterons du flux video sans redéclaration


//////////////////////////////////////////////////////////////

//------------Creation des variables de traitement d'image----------------


Mat Lignes = Mat::zeros( source.size(), CV_8UC3 ); //permet le dessin de lignes (pr le debug)

//////////////////////////////////////////////////////////////

// ---------- declaration de toutes les variables de chaque image crées nous permet de creer les vecteurs de déplacement des abeilles --------

int LastXTotal[40]={-1}, LastYTotal[40]={-1}, posXTotal[40],posYTotal[40]; //tableau de variables (1case /image) permettant de tracer le mouvement d'une abeille dans un canal en temps réel sur un ecran <=> position actuelle de l'abeille
int LastXBleu[40]={-1}, LastYBleu[40]={-1}, posXBleu[40],posYBleu[40];
int LastXVert[40]={-1}, LastYVert[40]={-1}, posXVert[40],posYVert[40];
int LastXJaune[40]={-1}, LastYJaune[40]={-1}, posXJaune[40],posYJaune[40];
int LastXViolet[40]={-1}, LastYViolet[40]={-1}, posXViolet[40],posYViolet[40];
int LastXRouge[40]={-1}, LastYRouge[40]={-1}, posXRouge[40],posYRouge[40];

int flagTotal[40]={0}; //flag de passage evite de compter 1000000 sorties alors que c'etait juste une abeille qui attendais dans la mauvaise zone .... sa****
int flagBleu[40]={0}; 
int flagVert[40]={0}; 
int flagJaune[40]={0}; 
int flagViolet[40]={0}; 
int flagRouge[40]={0}; 

//////////////////////////////////////////////////////////////

int bisYTotal[40]={0}; //sauvegarde différée de la position de l'abeille. Pour detecter correctement le mouvement position a T+1
int bisYBleu[40]={0};
int bisYVert[40]={0};
int bisYJaune[40]={0};
int bisYViolet[40]={0};
int bisYRouge[40]={0};

int deplacementTotal[40]={0}; //variable etant plus simplement le sens du vecteur (son signe etant la seule chose qui nous importe)
int deplacementBleu[40]={0};
int deplacementVert[40]={0};
int deplacementJaune[40]={0};
int deplacementViolet[40]={0};
int deplacementRouge[40]={0};

//-------------------------variables communes au programme-----------------------

int initialisation=0; // variable permettant de detecter si l'initialisation a déja été effectuée avant
int entreeTotal=0,sortieTotal=0; //variables comptant les entrée sorties des abeilles
int entreeRouge=0,sortieRouge=0;
int entreeVert=0,sortieVert=0;
int entreeBleu=0,sortieBleu=0;
int entreeJaune=0,sortieJaune=0;
int entreeViolet=0,sortieViolet=0;
int totalentree=0,totalsortie=0; //variables comptant les entrée sorties des abeilles sur une journée complète.
char sentreeTotal[4]={0},ssortieTotal[4]={0};
char sentreeRouge[4]={0},ssortieRouge[4]={0};
char sentreeBleu[4]={0}, ssortieBleu[4] ={0};
char sentreeVert[4]={0}, ssortieVert[4] ={0};
char sentreeJaune[4]={0},ssortieJaune[4]={0};
char name[30]; //variable permettant de créer les affichages des entrées sorties
int flagdetectcouleur=1; // Variable permettant de choisir si l'on souhaite utiliser la multiple détection de couleur ou non
//////////////////////////////////////////////////////////////

// --------- variables pour recupere l'heure permettant la sauvegarde -----------

static int seconds_last = 99; //variable permettant de determiner le changement de seconde(chargé avec 99 aléatoirement pour entrer une premeire fois dans la boucle)
char DateString[20],Jour[20],Minute[20],HeureMinute[20],Time[20],sDate[30]; //variables dont nous allons nous servir dans tout le programme et nous permettant de mettre l'heure et la date dans des variables lisibles
string oldday="\0",oldminute="\0"; //variables de flag permettant de determiner si nous changeons de jour ou non.

//////////////////////////////////////////////////////////////

///// Variables utiles a la sauvegarde .//////////


FILE *file; //fichier de sortie des detections
FILE *file2; //fichier de sauvegarde de secours
FILE *variables; //fichier de sauvegarde des données de calibration
FILE *graph; //fichier pour creer le graphique
FILE *Finitialisation; //Fichier pour recall et sauvegarde des données d'initialisation
FILE *sauvegardeusb;
char nom[100]; //tableau sauvegardant le nom du ficher de facon dynamique(le nom est changant a hauteur d'une fois par jour)[sauvegarde serveur]
char nom2[100];//tableau sauvegardant le nom du ficher de facon dynamique(le nom est changant a hauteur d'une fois par jour)[sauvegarde interne]
char image[100];//tableau sauvegardant le nom du graphique de facon dynamique(le nom est changant a hauteur d'une fois par jour)[sauvegarde interne]
char image2[100];//tableau sauvegardant le nom du grahpique de facon dynamique(le nom est changant a hauteur d'une fois par jour)[sauvegarde serveur]
char image3[100];

int minuteS=1,compteurS=0;//variables permettant de faire variere le temps de sauvegarde des sauvegardes
float loop=0,loop1=0; //essai de fps
float fps=0;

char NumeroRuche[20] ={0};
char NomLieu[20] ={0};
char NomProprio[20] ={0};

// Création de la structure pour envoi de donnée sur les threads //
struct thread_parametre
{
   /* Premiere porte a analyser*/
   int debut;
   /* Dernière porte a analyser */
   int fin;
};
struct thread_parametre thread_param[4];


///////////////////////////////////////////////////////////////
//---------------déclaration des widgets----&---- GTK--------------------------------
//Déclaration des widgets pour la fenetre principale
	GtkWidget *pWindow1; //fenetre
	GtkWidget *pVBox1;//box verticale.
	GtkWidget *pHBox1;//box horizontale
	GtkWidget *pButton[10];//tout les boutons
	GtkWidget *pLabel;

	//Déclaration des widgets pour la fenetre de présentation
	GtkWidget *pWindow2; //fenetre
	GtkWidget *pVBox2;//box verticale.
	GtkWidget *pButton1;//bouton pour la fenetre 1
	GtkWidget *pLabel1;
	GtkWidget *pLabel2;
	GtkWidget *pLabel3;
	GtkWidget *pLabel4;
	GtkWidget *pImage;	
	GtkWidget *pImage1;

	//Déclaration des widgets pour les trackbars
	GtkWidget *pScroll[40];
	GtkWidget *pLabelT[40];
	GtkWidget *pWindow3[3];
	GtkWidget *pLabelT1;
	GtkWidget *pLabelT2;
	GtkWidget *pVBox3[3];
	GtkObject *Adjust[40];

	//Décalaration des widgets pour la fenetre d'initialisaiton 
	 
	GtkWidget *pWindow4;
	GtkWidget *pVBox4;
	GtkWidget *pEntryI1;
	GtkWidget *pEntryI2;
	GtkWidget *pEntryI3;
	GtkWidget *pButtonI;
	GtkWidget *pLabelI1;
	GtkWidget *pLabelI2;
	GtkWidget *pLabelI3;

	int indiceGtk=0;
///////////////////////////FIN des Widgets////////////////////

//-----------------FIN DE LA DECLARATION DES VARIABLES ----------------------------------------------------//
void sauvegarde_usb()
{
	int mois=0;
	int jour=0;
	char c=0;
	FILE *sortieusb;
	char nomfichier[100]={0};
	
	for(mois=1;mois<13;mois++)
	{
		if(mois<10)
		{
			for(jour=1;jour<32;jour++)
			{
				if(jour<10)
				{
					
					sprintf(nomfichier,"/home/pi/Documents/onebee/Sauvegarde/Fichier_csv/0%d-0%d-2017.csv",jour,mois);
								
					sauvegardeusb = fopen(nomfichier,"r");
		
					if(sauvegardeusb != NULL)
					{
						
						sprintf(nomfichier,"/media/pi/ONEBEE/0%d-0%d-2017.csv",jour,mois);
						sortieusb = fopen(nomfichier,"w");
						do
						{
							c=getc(sauvegardeusb);
							fprintf(sortieusb,"%c",c);
						}while (c!=255);
					}
				}
				else
				{
					
					sprintf(nomfichier,"/home/pi/Documents/onebee/Sauvegarde/Fichier_csv/%d-0%d-2017.csv",jour,mois);
								
					sauvegardeusb = fopen(nomfichier,"r");
		
					if(sauvegardeusb != NULL)
					{
						
						sprintf(nomfichier,"/media/pi/ONEBEE/%d-0%d-2017.csv",jour,mois);
						sortieusb = fopen(nomfichier,"w");
						do
						{
							c=getc(sauvegardeusb);
							fprintf(sortieusb,"%c",c);
						}while (c!=255);
					}
				}
			}	
		}
		else
		{
			for(jour=1;jour<32;jour++)
			{
				if(jour<10)
				{
					
					sprintf(nomfichier,"/home/pi/Documents/onebee/Sauvegarde/Fichier_csv/0%d-%d-2017.csv",jour,mois);
								
					sauvegardeusb = fopen(nomfichier,"r");
		
					if(sauvegardeusb != NULL)
					{
						
						sprintf(nomfichier,"/media/pi/ONEBEE/0%d-%d-2017.csv",jour,mois);
						sortieusb = fopen(nomfichier,"w");
						do
						{
							c=getc(sauvegardeusb);
							fprintf(sortieusb,"%c",c);
						}while (c!=255);
					}
				}
				else
				{
					
					sprintf(nomfichier,"/home/pi/Documents/onebee/Sauvegarde/Fichier_csv/%d-%d-2017.csv",jour,mois);
								
					sauvegardeusb = fopen(nomfichier,"r");
		
					if(sauvegardeusb != NULL)
					{
						
						sprintf(nomfichier,"/media/pi/ONEBEE/%d-%d-2017.csv",jour,mois);
						sortieusb = fopen(nomfichier,"w");
						do
						{
							c=getc(sauvegardeusb);
							fprintf(sortieusb,"%c",c);
						}while (c!=255);
					}
				}
			}	
		}
	}
	printf("Sauvegarde sur clef usb effectuée\n");
}
void Initialisation ()
{
	int remplissage=0;
	int indice=0;
	int c=0;

	Finitialisation=fopen("Initialisation.txt","r");
	if(Finitialisation == NULL)
	{
		initialisation=1;
	}
	else
	{
		do{
		c=(int)getc(Finitialisation);
		if(c==10)
			{
				indice=0;
				remplissage++;
			}
		else
			{
			switch(remplissage)
				{
					case(0):
					NumeroRuche[indice]=c;
					indice++;
					break;
					
					case(1):
					NomLieu[indice]=c;
					indice++;
					break;
					
					case(2):
					NomProprio[indice]=c;
					indice++;
					break;
					
					default:
					break;
			
				}
			}
		}while (c!=EOF);
	fclose(Finitialisation);
	}
	



}

void calib_auto()
/*
	Présentation :
	Cette fonction ne prenant aucun de parametres et ne retournant rien nous permet d effectuer une calibration automatique
	des portes d'entrées sortie. 
	Explication : 
	1-Nous prennons l'image sortant du flux video(qui est normalement l'entrée de la ruche vue du dessus)
	2-Nous traitons cette image pour ne garder que la couleur "rouge"
	3-Nous prenons une ligne de l'image et nous sucrutons la totalitée de cette ligne
	4-Nous scrutons les données au fur et a mesure qu'elle arrivent et les rangeons dans un tableau.
	Précisions:
	etape 4: -> en toute logique, nous avons dans le tableau tout les moments où la ligne de pixel change de couleur
	cad que lorsque que l'on a decouvert le pixel 0 nosu enregistrons l'endroit ou nous sommes dans la ligne
	et ensuite nous ne fesons rien mais des que nous trouvons un pixel a 255 nous reenregistrons cette position qui
	marquera la fin de la detection de la porte "1".
*/

{
	int flag0=0,flag255=0,ecart=0,matj=0,nbporte=0,i=0,tmp=0;
	int calibauto[80]={0};
	int flagcalib=1;
	
	gpioWrite(25,0);
	time_sleep(1);
	waitKey(1);
	capture >> source;
	waitKey(1);
	capture >> source;
	gpioWrite(25,1);
	
	//imshow("video",source);
	cvtColor(source,hsvsource,CV_BGR2HSV);
	inRange(hsvsource,Scalar(90,100,50,0),Scalar(130,255,255,0),masquesource);
	suppressbruit(masquesource);
	//imshow("masksourceB",masquesource);
	//printf("%d %d",masquesource.cols,masquesource.rows);
	
		for(matj=0;matj<masquesource.rows;matj++)
		{	
			switch(masquesource.at<uchar>(matj,320))
			{
				case 0:
				if(flag0==0 && ecart >10)
				{
					//printf("%d %d \n",matj,nbporte);
					flag0=1;flag255=0;ecart=0;
					calibauto[nbporte]=matj;nbporte++;
				}break;
				
				case 255:
				if(flag255==0 && ecart >10)
				{
					//printf("%d %d \n",matj,nbporte);
					flag0=0;flag255=1;ecart=0;
					calibauto[nbporte]=matj;nbporte++;
				}break;
				
			}
		ecart++;
			
		}
	Y[0]=calibauto[2]+3;
	Y[1]=calibauto[3]-20;
	
	
	ecart=0;flag0=0;flag255=0;nbporte=0,tmp=calibauto[2]+20;
	//printf("%d \n",tmp);
	
	cvtColor(source,hsvsource,CV_BGR2HSV);
	inRange(hsvsource,Scalar(0,50,50,0),Scalar(20,255,255,0),masquesource);
	suppressbruit(masquesource);
	//imshow("masksourceR",masquesource);
	
		for(matj=0;matj<masquesource.cols;matj++)
		{	
			switch(masquesource.at<uchar>(tmp,matj))
			{
				case 0:
				if(flag0==0 && ecart >7)
				{
					//printf("%d %d \n",matj,nbporte);
					flag0=1;flag255=0;ecart=0;
					calibauto[nbporte]=matj;nbporte++;
				}break;
				
				case 255:
				if(flag255==0 && ecart >7)
				{
					//printf("%d %d \n",matj,nbporte);
					flag0=0;flag255=1;ecart=0;
					calibauto[nbporte]=matj;nbporte++;
				}break;
				
			}
		ecart++;
			
		}
		printf("%d %d",calibauto[2],calibauto[3]);
		nombreporte=(nbporte-3)/2;
		printf("%d",nombreporte);
		for(i=0;i<nombreporte*2;i++)
		{
			if(flagcalib==1)
			{
				X[i]=calibauto[i+2]+5;
				flagcalib=0;
			}
			else
			{

				X[i]=calibauto[i+2]-5;
				flagcalib=1;
			}
		}
		for(i=nombreporte*2;i<40;i++)
		{
			X[i]=0;
		}
		X[0]=X[0]+5;
		if(nombreporte<=0)
		{
			recall();
			nombreporte=10;
			
		}	
	printf("nbporte : %d \n",nombreporte);
	switch (nombreporte)
	{
		case(1):
		
			thread_param[0].debut = 0;
			thread_param[0].fin   = 1;
			thread_param[1].debut = 0;
			thread_param[1].fin   = 0;
			thread_param[2].debut = 0;
			thread_param[2].fin   = 0;
			thread_param[3].debut = 0;
			thread_param[3].fin   = 0;
		break;
		case(2):
		
			thread_param[0].debut = 0;
			thread_param[0].fin   = 1;
			thread_param[1].debut = 1;
			thread_param[1].fin   = 2;
			thread_param[2].debut = 0;
			thread_param[2].fin   = 0;
			thread_param[3].debut = 0;
			thread_param[3].fin   = 0;
		break;
		case(3):
		
			thread_param[0].debut = 0;
			thread_param[0].fin   = 1;
			thread_param[1].debut = 1;
			thread_param[1].fin   = 2;
			thread_param[2].debut = 2;
			thread_param[2].fin   = 3;
			thread_param[3].debut = 0;
			thread_param[3].fin   = 0;
		break;
		case(4):
		
			thread_param[0].debut = 0;
			thread_param[0].fin   = 1;
			thread_param[1].debut = 1;
			thread_param[1].fin   = 2;
			thread_param[2].debut = 2;
			thread_param[2].fin   = 3;
			thread_param[3].debut = 3;
			thread_param[3].fin   = 4;
		break;
		case(5):
		
			thread_param[0].debut = 0;
			thread_param[0].fin   = 2;
			thread_param[1].debut = 2;
			thread_param[1].fin   = 3;
			thread_param[2].debut = 3;
			thread_param[2].fin   = 4;
			thread_param[3].debut = 4;
			thread_param[3].fin   = 5;
		break;
		case(6):
		
			thread_param[0].debut = 0;
			thread_param[0].fin   = 2;
			thread_param[1].debut = 2;
			thread_param[1].fin   = 4;
			thread_param[2].debut = 4;
			thread_param[2].fin   = 5;
			thread_param[3].debut = 5;
			thread_param[3].fin   = 6;
		break;
		case(7):
		
			thread_param[0].debut = 0;
			thread_param[0].fin   = 2;
			thread_param[1].debut = 2;
			thread_param[1].fin   = 4;
			thread_param[2].debut = 4;
			thread_param[2].fin   = 6;
			thread_param[3].debut = 6;
			thread_param[3].fin   = 7;
		break;
		case(8):
		
			thread_param[0].debut = 0;
			thread_param[0].fin   = 2;
			thread_param[1].debut = 2;
			thread_param[1].fin   = 4;
			thread_param[2].debut = 4;
			thread_param[2].fin   = 6;
			thread_param[3].debut = 6;
			thread_param[3].fin   = 8;
		break;
		case(9):
		
			thread_param[0].debut = 0;
			thread_param[0].fin   = 3;
			thread_param[1].debut = 3;
			thread_param[1].fin   = 5;
			thread_param[2].debut = 5;
			thread_param[2].fin   = 7;
			thread_param[3].debut = 7;
			thread_param[3].fin   = 9;
		break;
		case(10):
		
			thread_param[0].debut = 0;
			thread_param[0].fin   = 3;
			thread_param[1].debut = 3;
			thread_param[1].fin   = 6;
			thread_param[2].debut = 6;
			thread_param[2].fin   = 8;
			thread_param[3].debut = 8;
			thread_param[3].fin   = 10;
		break;
		case(11):
		
			thread_param[0].debut = 0;
			thread_param[0].fin   = 3;
			thread_param[1].debut = 3;
			thread_param[1].fin   = 6;
			thread_param[2].debut = 6;
			thread_param[2].fin   = 9;
			thread_param[3].debut = 9;
			thread_param[3].fin   = 11;
		break;
		case(12):
		
			thread_param[0].debut = 0;
			thread_param[0].fin   = 3;
			thread_param[1].debut = 3;
			thread_param[1].fin   = 6;
			thread_param[2].debut = 6;
			thread_param[2].fin   = 9;
			thread_param[3].debut = 9;
			thread_param[3].fin   = 12;
		break;
		case(13):
		
			thread_param[0].debut = 0;
			thread_param[0].fin   = 4;
			thread_param[1].debut = 4;
			thread_param[1].fin   = 7;
			thread_param[2].debut = 7;
			thread_param[2].fin   = 10;
			thread_param[3].debut = 10;
			thread_param[3].fin   = 13;
		break;
	}


}
void sauvegarde_calib()
{/*
	Présentation : Fonction rapide nous permettant de sauvegarder les données de notre calibration.
	Explication: 
	1-Nous ouvrons un fichier ou le creeons s'il n'existe pas
	2-Nous ecrivons dans ce dernier toutes les positions que nous avons utilisé
	3-Nous fermons le fichier
	Précisions: fprintf nous permet d'ecrire directement dans un fichier e configurer en "w+"
*/
	int i=0;
	variables = fopen("sauvegarde.txt","w+");
	
	for(i=0;i<nombreporte*2;i++)
	{
		fprintf(variables,"%d ",X[i]);
	}
		fprintf(variables,"%d ",Y[0]);
		fprintf(variables,"%d ",Y[1]);
	fclose(variables);
}

void calibration(Mat CSource)
{
/*
	Présentation : Cette fonction nous permet de modifier les tailles de nos portes.
	Explications :
	1-Nous creeons deja des nouvelles feneters temporaire et utilisée uniquement dans cette fonction 
	2-Nous lancons un while pour gere dynamiquement les parametres de fenetres
	3-Nous creeons des barres de selections fesant bouger en meme temps les lignes 
	4-Nous avons en direct le rendu de la calibration
	5-Nous attendons la demande d'extinction pour quitter la fonction et nous detruisons les fenetres temporaire
	en meme temps
	Précisions : createTrackbar -> permet d'implémenter les curseurs de sélection
		     line -> permet de dessiner les lignes sur l'image principale

*/

	int trackbar=0;
	int coulLigne=0;
	
	
		for(trackbar=0;trackbar<nombreporte*2;trackbar++)
		{
			/*if(trackbar<=10)
			{
				sprintf(ligne,"ligne %d",trackbar);
				createTrackbar(ligne,"Calibration1",&X[trackbar], 640);
			}
			if(trackbar>10 && trackbar<=20)
			{
				sprintf(ligne,"ligne %d",trackbar);
				createTrackbar(ligne,"Calibration2",&X[trackbar], 640);
			}
			if(trackbar>20 && trackbar<=30)
			{
				sprintf(ligne,"ligne %d",trackbar);
				createTrackbar(ligne,"Calibration3",&X[trackbar], 640);
			}
			if(trackbar>30 && trackbar<=40)
			{
				sprintf(ligne,"ligne %d",trackbar);
				createTrackbar(ligne,"Calibration4",&X[trackbar], 640);
			}*/

			if(coulLigne>=2)
			{
				line(CSource, Point(X[trackbar], 0), Point(X[trackbar], 640), Scalar(0,255,255), 2);
				coulLigne=0;trackbar++;
				line(source, Point(X[trackbar], 0), Point(X[trackbar], 640), Scalar(0,255,255), 2);
			}
			else
			{
				line(CSource, Point(X[trackbar], 0), Point(X[trackbar], 640), Scalar(0,255,0), 2);
				coulLigne++;
			}
		
		}	

		line(CSource, Point(0, Y[0]), Point(720, Y[0]), Scalar(0,0,0), 2);
		line(CSource, Point(0, Y[1]), Point(720, Y[1]), Scalar(0,0,0), 2);
		imshow("Calibration",CSource);


}
void get_time()//fonction nous permettant de recuperer la date et l heure de la raspberry
{
/*
	Présentation: Ceci est une fonction générique et modifiée permettant d'acceder a la date et l'heure de la raspberry
	Explications:
	1- nous récupérons la date actuelle
	2- on test voir si nous sommes a une nouvelle date (ici 1seconde plus tard)
	3- on met a jour notre flag de detection de nouvelle data
	4- nous récuperons et formatons toutes les odnénes de dates comme nous en avons besoin
	Précisions : Cette fonction est GENERIQUE elle marche sur tout les raspberry par defaut aucun paquet n est nécessaire
*/
	timeval curTime;
	gettimeofday(&curTime, NULL);
	if (seconds_last == curTime.tv_sec)
	return;
	loop1=loop;
	loop=0;	
	
	
	seconds_last = curTime.tv_sec;
	
	strftime(DateString, 80, "%d-%m-%Y", localtime(&curTime.tv_sec));
	strftime(Jour, 80, "%d", localtime(&curTime.tv_sec));
	strftime(Minute, 80, "%M:", localtime(&curTime.tv_sec));
	strftime(HeureMinute, 80, "%H:%M", localtime(&curTime.tv_sec));
	strftime(Time,20,"%X",localtime(&curTime.tv_sec));
	strftime(sDate,80,"20%y-%m-%d %H:%M:%S",localtime(&curTime.tv_sec));
}
void window_create(void)// fonction creant les fenetres principales
{
	// ais-je vraiment besoin de l'expliquer?

	
	//namedWindow("Menu",CV_WINDOW_NORMAL);
	//namedWindow("Calibration",CV_WINDOW_NORMAL);
	
}
void passage(int i,int *deplacement,int *bisY, int *LastY,int *flag, int *entree, int *sortie)
{
/*
	Présentation : Cette fonction nous permet de compter le nombre de passage d'une abeille dans une porte. Les variables
	etant communes au programme la valeur"entree' et "sortie" compte les entrées sorties de toutes les abeilles de toutes
	les portes
	Explications:
	1- Nous determinloopons le sens detime_sleep déplacement des abeilles 
	2- Nous regardons dans quelles zones elles sont et distingons 3 cas (dans la zone "entrée", dans la zone"sortie" et 
	dans la zone "rien"
	3- Esuite si le mouvement respecte la postition, nous determinons le cas dans lequel nous sommes.
	4- Enfin on detecte que l'abeille quitte bien la zone de détection pour eviter un comptage inutile
	5- Pour finir, nous enregistrons la derniere position de l'abeille pour determiner ensuite son nouveau mouvement
	Précisions: 
	C'est comme se servir d'un vecteur ou nous cherchons a detecter sa direction, son amplitude n'ayant aucun effet. 
*/
	deplacement[i] = (bisY[i]-LastY[i]);
	//printf("deplacement :%d; bisY:%d; LastX:%d;\n",deplacement,bisY,LastX);

		if(deplacement[i]>0 && LastY[i]<Y[0]+10 && flag[i]==0 && deplacement[i]<20)
		{
			*sortie=*sortie+1;
			totalsortie++;
			flag[i]=1;		
		}
		if(deplacement[i]<0 && LastY[i]>Y[1]-10 && flag[i]==0 && deplacement[i]>-20)
		{	
			*entree=*entree+1;
			totalentree++;
			flag[i]=1;
		}
	if(LastY[i]>Y[0]+10 && LastY[i]<Y[1]-10)
	{
		flag[i]=0;
	}	
	bisY[i]=LastY[i];
	
	return;
}


void dessinligne(int i,Mat *image,int *posX,int *posY,int *LastX,int *LastY, int couleur)//dessine les lignes pour suivi d objet
{
/*
	Présentation: on pourrait croire cette fonction inutile vu son nom.. mais en fait elle est le coeur de la detection
	En effet elle permet de determiner le centre de l'abeille lors de son passage dans la porte.
	Explications:
	1-On défini un moment et nous nous en servons pour determiner une position relative du point dans l'image
	2-On se sert de ces moments pour recuperer la coordonnée du point que nous enregistrons dans une variable
	3-On fait un test improbable de sécuritée pour eviter d'avoir des données n'existant pas(negatives)
	4-On met en tampon la position de l'abeille.
	Précisions: l'affichage de la ligne rouge n'est PAS obligatoire. elle est la pour présentation de la detection et
	ne consomme aucune ressource processeur (ou tellement infime qu'elle est négligeable...
*/
	Moments Moments = moments(image[i]);

  	double M01 = Moments.m01;
 	double M10 = Moments.m10;
 	double Area = Moments.m00;

       // si area <= 400, cela signifie que l'objet est trop petit pour etre detecté 
	if (Area > 200)
 	{
	//calculate le centre du point
   	posX[i] = (M10 / Area)+X[i*2];
   	posY[i] = (M01 / Area)+Y[0];        
        
		if (LastX[i] >= 0 && LastY[i] >= 0 && posX[i] >= 0 && posY[i] >= 0)
   		{
    		//Draw a red line from the previous point to the current point
		switch(couleur)
		{
			case(1):
				line(source, Point(posX[i], posY[i]), Point(LastX[i], LastY[i]), Scalar(255,255,255), 2);
			break;
			case(2):
				line(source, Point(posX[i], posY[i]), Point(LastX[i], LastY[i]), Scalar(255,0,0), 2);
			break;
			case(3):
				line(source, Point(posX[i], posY[i]), Point(LastX[i], LastY[i]), Scalar(0,0,255), 2);
			break;
			case(4):
				line(source, Point(posX[i], posY[i]), Point(LastX[i], LastY[i]), Scalar(0,255,0), 2);
			break;
			case(5):
				line(source, Point(posX[i], posY[i]), Point(LastX[i], LastY[i]), Scalar(0,255,255), 2);
			break;
			default:
			break;
		}
    		
   		}

    	LastX[i] = posX[i];
   	LastY[i] = posY[i];
  	}
  	////imshow("flux_video", source); //show the original image
	
}



void sauvegarde_automatique() //contient aussi la sauvegarde de secours
{
/*
	Présentation: Voici la fonction de sauvegarde automatique des données. Cette sauvegarde s'occupe uniquement des
	données .csv pour une utilisation dans exel ou tout autre logiciel similaire.
	Explications:
	1-Si nous sommes un nouveau jour, nous recréeons un fichier vierge qui contiendra les données de la journée
	2-Ensuite nous fesons un test pour voir si nous sommes a une nouvelle minute de l'heure
	3-On teste notre compteur de minute pour voir s'il n est pas different de zero (si c est le cas aucune sauvgarde
	n est faite
	4-On teste ensuite notre compteur pour voir si nous sauvegarde a l'interval demandé par l'utilisateur
	5-Nous sauvegardons dans le fichier sous un format Heure:Minute entree sortie
	6-On reset les compteurs pour les sauvegardes ulterieures
	Précisions: Dans l absolu nous sauvegardons a deux endroits : dans le dossier du programme et dans la dossier pour
	la communication avec l exterieur

*/
	if(oldday!=Jour)
	{
		
		snprintf(image,sizeof(image),"Sauvegarde/Graphiques/Graphique_du_%s.jpg",DateString);
		snprintf(image2,sizeof(image),"/var/www/Sauvegarde/Graphiques/Graphique_du_%s.jpg",DateString);
		if(fopen(image,"r")==NULL)
		{
			Graph = imread("fond_graphique.jpg");
		}
		else 
		{
			Graph = imread(image);
		}
		imwrite(image,Graph);

		snprintf(nom,sizeof(nom),"/var/www/Sauvegarde/Fichier_csv/%s.csv",DateString);///var/www/html/
		snprintf(nom2,sizeof(nom2),"Sauvegarde/Fichier_csv/%s.csv",DateString);//on enregistre avec le fichier
	
		file=fopen(nom,"a+");	
		file2=fopen(nom2,"a+");
		//fprintf(file,"heure,entree,sortie\n");
		//fprintf(file2,"heure,entree,sortie\n");
		oldday=Jour;
		//calib_auto();

		sauvegarde_calib();
	
	}

	if(oldminute!=Minute)
	{
		compteurS++;
		oldminute=Minute;
		
	}
	if(minuteS!=0)
	{
		if(compteurS>=minuteS)
		{
			//sauvegarde_graphique();
			printf("%f ms \n %li clock\n",(tot)/pass,CLOCKS_PER_SEC);
			putText(source,"Sauvegarde", Point(250,240) , FONT_HERSHEY_SIMPLEX, 2, Scalar(0,0,255),2,false );
			file=fopen(nom,"a+");	
			file2=fopen(nom2,"a+");			
			fprintf(file ,"%s;%d;%d;%d;%d;%d;%d;%d;%d;%d;%d;%.1f\n",HeureMinute,entreeTotal,sortieTotal,entreeRouge,sortieRouge,entreeBleu,sortieBleu,entreeVert,sortieVert,entreeJaune,sortieJaune,fps/(60*minuteS));
			fprintf(file2,"%s;%d;%d;%d;%d;%d;%d;%d;%d;%d;%d;%.1f\n",HeureMinute,entreeTotal,sortieTotal,entreeRouge,sortieRouge,entreeBleu,sortieBleu,entreeVert,sortieVert,entreeJaune,sortieJaune,fps/(60*minuteS));
			fclose(file);
			fclose(file2);

			snprintf(sentreeTotal,sizeof(sentreeTotal),"%d",entreeTotal);
			snprintf(ssortieTotal,sizeof(ssortieTotal),"%d",sortieTotal);
			snprintf(sentreeBleu,sizeof(sentreeBleu),"%d",entreeBleu);
			snprintf(ssortieBleu,sizeof(ssortieBleu),"%d",sortieBleu);
			snprintf(sentreeRouge,sizeof(sentreeRouge),"%d",entreeRouge);
			snprintf(ssortieRouge,sizeof(ssortieRouge),"%d",sortieRouge);
			snprintf(sentreeVert,sizeof(sentreeVert),"%d",entreeVert);
			snprintf(ssortieVert,sizeof(ssortieVert),"%d",sortieVert);
			snprintf(sentreeJaune,sizeof(sentreeJaune),"%d",entreeJaune);
			snprintf(ssortieJaune,sizeof(ssortieJaune),"%d",sortieJaune);

			//sauvegarde_sql();
			
			entreeTotal=0;sortieTotal=0;
			entreeBleu=0 ;sortieBleu=0;
			entreeVert=0 ;sortieVert=0;
			entreeRouge=0;sortieRouge=0;
			entreeJaune=0;sortieJaune=0;
			fps=0;
			compteurS=0;
			
		
		}
	}

}

void sauvegarde_sql(void)
{
	MYSQL *mysql;
	char *sQLComplete = NULL;
	char *ChaineConcatene=NULL;


	//initialisation BDD
	mysql = mysql_init(NULL);
	if(mysql == NULL)
	{
		printf("impossible d'initialiser la BDD\n");
	}
	sQLComplete = (char*)malloc(strlen("INSERT INTO comptage(date,entreeTotal,sortieTotal,entreeRouge,sortieRouge,entreeBleu,sortieBleu,entreeVert,sortieVert,entreeJaune,sortieJaune) VALUES('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')")+40);
	//ChaineConcatene = (char*) malloc(NBTrame*145);
	//strcpy(ChaineConcatene,"");
	//printf("%s\n",sDate);
		
	if(NULL == mysql_real_connect(mysql,"10.2.2.58","root","proot","ruche_mind",0,NULL,0))
	{
		printf("Erreur de connection avec la BDD \n");
		free(ChaineConcatene);
		ChaineConcatene = NULL;
		free(sQLComplete);
		sQLComplete = NULL;
	}
	else
	{
		sprintf(sQLComplete,"INSERT INTO compteur(date,entreeTotal,sortieTotal,entreeRouge,sortieRouge,entreeBleu,sortieBleu,entreeVert,sortieVert,entreeJaune,sortieJaune) VALUES('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')",sDate,sentreeTotal,ssortieTotal,sentreeRouge,ssortieRouge,sentreeBleu,ssortieBleu,sentreeVert,ssortieVert,sentreeJaune,ssortieJaune);
		if(mysql_query(mysql,sQLComplete))
		{
			printf("echec de l'envoi");
		}
	}

	mysql_close(mysql);	
}	
void suppressbruit(Mat Pic)
{
/*
	Présentation : Ceci est une fonction donnée par OpenCV. Elle permet de réduire le bruit des images que nous
	traitons. En quelques sortes en regarde dans un cercle quel pixel est le plus présent et fait de cercle un cercle 
	de la couleur qui est la plus présente.
	Explications : N/A
	Précisions : N/A

*/

	//---------------------------------------------------------------------------------------//
	// ce paquet permettent d'enlever tout les bruits résiduels qui pourrait etre interprétés comme une autre couleur que le noir
	//morphological opening (removes small objects from the foreground) 
  		//erode(Pic, Pic, getStructuringElement(MORPH_ELLIPSE, Size(5,5)) );
  		//dilate(Pic, Pic, getStructuringElement(MORPH_ELLIPSE, Size(5,5)) ); 

  	 //morphological closing (removes small holes from the foreground)
  		//dilate(Pic, Pic, getStructuringElement(MORPH_ELLIPSE, Size(5,5)) ); 
  		//erode(Pic, Pic, getStructuringElement(MORPH_ELLIPSE, Size(5,5)) );
	//---------------------------------------------------------------------------------------//
	blur(Pic,Pic,Size(3,3));
}

void recall()

{	
/*
	Présentation:Cette fonction permet de récuperer les données de calibration qui ont ete sauvegardée avant l'extinction
	du programme.
	Explications:	
	1-Tout d'abord on ouvre le fichier de sauvegarde
	1.1-On test voir si le fichier c'est bien ouvert
	2-On récupere toutes les données du fichier que nous avons ouvert et les stockons dans un tableau
	3-Nous scrutons ce tableau jusqu'a sa fin 
	3.1-Pendant cette boucle on attends la detection d'un caractere spécial et pendant l'attente nous incrémentons un compteur
	3.2-Quand le caractere spécial est la on effectue un calcul pour passer des 2 (ou3) unitée a une dizaine(centaine)
	3.3-On somme et on sauvegarde dans une variable
	3.4-On sauvegarde cette variable dans sa variable propre ( X[] ou Y[])
	Précisions: On doit faire le calcul test=T_variable.... car on peut lire les caracteres que un a un et non comme
	une chaine de caractere. De ce fait nous devons attribuer le bon poid a chaque variable que nous lisons.

*/
	int c=0,i=0,j=0,compteur_recall=0,test=0,indice=0;

	variables=fopen("sauvegarde.txt","r");
	if (variables == NULL)
	{
		printf("Chargement des données impossible, valeurs par defaut... recall\n");
		return;
	}
	do{
		c=(int)getc(variables);
		T_variable[i]=c-48;
		i++;
	}while (c!=EOF);
	for(j=0;j<i;j++)
	{
		
		if(T_variable[j]==-16)
		{
			if(compteur_recall == 2)
			{
				test = T_variable[j-compteur_recall]*10 + T_variable[j-compteur_recall+1];
				j++;
			}
			if(compteur_recall == 3)
			{
				test = T_variable[j-compteur_recall]*100 + T_variable[j-compteur_recall+1]*10 + T_variable[j-compteur_recall+2];
				j++;
			}
			compteur_recall=0;
			if(indice<20)
			{
				X[indice] = test;
				//printf("\n x %d : %d\n",indice,test);
			}
			else
			{
				Y[indice-20]= test;
				//printf(" y %d : %d\n",indice,test);
			}
			indice++;
		}	
		
		compteur_recall++;
	}
	fclose(variables);

}

void sauvegarde_graphique()
{
/*
	Présentation:Cette fonction permet de récuperer les données de calibration qui ont ete sauvegardée avant l'extinction
	du programme.
	Explications:	
	1-Tout d'abord on ouvre le fichier de sauvegarde
	1.1-On test voir si le fichier c'est bien ouvert
	2-On récupere toutes les données du fichier que nous avons ouvert et les stockons dans un tableau
	3-Nous scrutons ce tableau jusqu'a sa fin 
	3.1-Pendant cette boucle on attends la detection d'un caractere spécial et pendant l'attente nous incrémentons un compteur
	3.2-Quand le caractere spécial est la on effectue un calcul pour passer des 2 (ou3) unitée a une dizaine(centaine)
	3.3-On somme et on sauvegarde dans une variable
	3.4-On sauvegarde cette variable dans sa variable propre ( T_data[])
	4-Ensuite avec ce tableau (T_data) nous effectuons une boucle
	4.1-Cette boucle scrute les parametre du tableau 3 par 3 (le tableau est rempli sous ce format (heure,entee,sorite,heur,entrée,sortie...)
	4.2-Grace aux fonctions lignes nous crées un support graphique et nous le remplissons aussi avec des barres
	de données.
	4.3-Enfin on ecrit sur le graphque l'heure par pas d'une heure
	Précisions: On doit faire le calcul test=T_variable.... car on peut lire les caracteres que un a un et non comme
	une chaine de caractere. De ce fait nous devons attribuer le bon poids a chaque variable que nous lisons.	
	On crée un graphique pour les deux parties, soit les entrées et les sorites.
*/	
	int time=0,i;
	char Heure[5];

		
		Graph = imread(image);
		//printf("%s",image);
		time=(HeureMinute[0]-48)*600 + (HeureMinute[1]-48)*60 + (HeureMinute[3]-48)*10 + (HeureMinute[4]-48);
		
			line(Graph,Point(time+50,180),Point(time+50,180-entreeTotal),Scalar(0,0,255),1);
			line(Graph,Point(time+50,400),Point(time+50,400-sortieTotal),Scalar(0,255,0),1);

			line(Graph,Point(50,180),Point(1490,180),Scalar(0,0,0),2);//horizontal entree
			line(Graph,Point(50,180),Point(50,25),Scalar(0,0,0),2);//vertical entree

			line(Graph,Point(50,400),Point(1490,400),Scalar(0,0,0),2);//horizontal sortie
			line(Graph,Point(50,400),Point(50,250),Scalar(0,0,0),2);//vertical sortie
			
			//les deux prochaines lignes sont pour l'affichage du texte sur le graphique
			putText(Graph,"entree", Point(5,15) , FONT_HERSHEY_SIMPLEX, 0.7, Scalar(0,0,0), 1,false );
			putText(Graph,"sortie", Point(5,240) , FONT_HERSHEY_SIMPLEX, 0.7, Scalar(0,0,0), 1,false );
			//les prochaines lignes sont la pour l'affichage de l'heure sur l axe des ordonnées

			for(i=0;i<24;i++)
			{	
				sprintf(Heure,"%d:00",i);
				putText(Graph,Heure, Point(50+60*i,190) , FONT_HERSHEY_SIMPLEX, 0.2, Scalar(0,0,0), 1,false );
				putText(Graph,Heure, Point(50+60*i,410) , FONT_HERSHEY_SIMPLEX, 0.2, Scalar(0,0,0), 1,false );
			}

		snprintf(image,sizeof(image),"Sauvegarde/Graphiques/Graphique_du_%s.jpg",DateString);
		imwrite(image,Graph);
		snprintf(image2,sizeof(image2),"/var/www/html/Sauvegarde/Graphiques/Graphique_du_%s.jpg",DateString);
		imwrite(image2,Graph);

	
	//line(src,Point(10,10),Point(10,100),Scalar(0,0,255),1);
	


}
//Déclaration des threads

// Le thread "1" gere la couleur Bleu
	
	void *thread_1(void* parametre)
	{
		struct thread_parametre* p = (struct thread_parametre*) parametre;
		int i=0;
	for(i=p->debut;i<p->fin;i++)
	{
		inRange(hsvcrop[i],Scalar(0,0,0,0),Scalar(180,255,90,0),masquecropTotal[i]);
		suppressbruit(masquecropTotal[i]);
		dessinligne(i,masquecropTotal,posXTotal,posYTotal,LastXTotal,LastYTotal,1);
		passage(i,deplacementTotal,bisYTotal,LastYTotal,flagTotal,&entreeTotal,&sortieTotal);
	}	
	if(flagdetectcouleur==1)
	{
		for(i=0;i<nombreporte;i++)
		{
			inRange(hsvcrop[i],Scalar(90,50,180,0),Scalar(130,255,255,0),masquecropBleu[i]);
			suppressbruit(masquecropBleu[i]);
				dessinligne(i,masquecropBleu,posXBleu,posYBleu,LastXBleu,LastYBleu,2);
				passage(i,deplacementBleu, bisYBleu, LastYBleu, flagBleu, &entreeBleu, &sortieBleu);		
		}
	}
	    pthread_exit(NULL);
	}

// Le thread "2" gere la couleur Rouge
	void *thread_2(void* parametre)
	{
		struct thread_parametre* p = (struct thread_parametre*) parametre;
		int i=0;
	for(i=p->debut;i<p->fin;i++)
	{
		inRange(hsvcrop[i],Scalar(0,0,0,0),Scalar(180,255,90,0),masquecropTotal[i]);	
		suppressbruit(masquecropTotal[i]);	
		dessinligne(i,masquecropTotal,posXTotal,posYTotal,LastXTotal,LastYTotal,1);
		passage(i,deplacementTotal,bisYTotal,LastYTotal,flagTotal,&entreeTotal,&sortieTotal);
	}
	if(flagdetectcouleur==1)
	{
		for(i=0;i<nombreporte;i++)
		{
			inRange(hsvcrop[i],Scalar(0,50,100,0),Scalar(15,255,255,0),masquesourcerouge1);
			inRange(hsvcrop[i],Scalar(140,50,100,0),Scalar(180,255,255,0),masquesourcerouge2);
			add(masquesourcerouge1,masquesourcerouge2,masquecropRouge[i]);	
			suppressbruit(masquecropRouge[i]);
			dessinligne(i,masquecropRouge,posXRouge,posYRouge,LastXRouge,LastYRouge,3);
			passage(i,deplacementRouge,bisYRouge,LastYRouge,flagRouge,&entreeRouge,&sortieRouge);
		}
	}
	    pthread_exit(NULL);
	}
// Le thread "3" gere la couleur Jaune
	void *thread_3(void* parametre)
	{
		struct thread_parametre* p = (struct thread_parametre*) parametre;
		int i=0;
	for(i=p->debut;i<p->fin;i++)
	{	
		inRange(hsvcrop[i],Scalar(0,0,0,0),Scalar(180,255,90,0),masquecropTotal[i]);
		suppressbruit(masquecropTotal[i]);
		dessinligne(i,masquecropTotal,posXTotal,posYTotal,LastXTotal,LastYTotal,1);
		passage(i,deplacementTotal,bisYTotal,LastYTotal,flagTotal,&entreeTotal,&sortieTotal);
	}
	if(flagdetectcouleur==1)
	{
		for(i=0;i<nombreporte;i++)
		{
			inRange(hsvcrop[i],Scalar(18,50,100,0),Scalar(25,255,255,0),masquecropJaune[i]);
			suppressbruit(masquecropJaune[i]);
			dessinligne(i,masquecropJaune,posXJaune,posYJaune,LastXJaune,LastYJaune,5);
			passage(i,deplacementJaune,bisYJaune,LastYJaune,flagJaune,&entreeJaune,&sortieJaune);
		}
	}
	    pthread_exit(NULL);
	}
// Le thread "4" gere la couleur VErt
	void *thread_4(void* parametre)
	{
		struct thread_parametre* p = (struct thread_parametre*) parametre;
		int i=0;
	for(i=p->debut;i<p->fin;i++)
	{	
		inRange(hsvcrop[i],Scalar(0,0,0,0),Scalar(180,255,90,0),masquecropTotal[i]);
		suppressbruit(masquecropTotal[i]);	
		dessinligne(i,masquecropTotal,posXTotal,posYTotal,LastXTotal,LastYTotal,1);
		passage(i,deplacementTotal,bisYTotal,LastYTotal,flagTotal,&entreeTotal,&sortieTotal);
	}
	if(flagdetectcouleur==1)
	{
		for(i=0;i<nombreporte;i++)
		{
			inRange(hsvcrop[i],Scalar(50,50,100,0),Scalar(65,255,255,0),masquecropVert[i]);
			suppressbruit(masquecropVert[i]);
			dessinligne(i,masquecropVert,posXVert,posYVert,LastXVert,LastYVert,4);
			passage(i,deplacementVert, bisYVert, LastYVert, flagVert, &entreeVert, &sortieVert);
		}
	}
	    pthread_exit(NULL);
	}
int main(int argc, char **argv)
{	
	Initialisation();
	//     Varaibles internes au Main    //
	int i=0;
	
	gtk_disable_setlocale();
	//     Initialisation des fenetres   //
	gtk_init(&argc,&argv);
	
	//     Initalisation des ports GPIO de la raspberry  //
	if (gpioInitialise() < 0)
 	{
     		fprintf(stderr, "pigpio initialisation échouée\n");
     		return 1;
  	}
	gpioSetMode(26, PI_INPUT);
	gpioSetMode(25, PI_OUTPUT);
	gpioWrite(25,0);

	//     Initialisation du flux video  //
	if(!capture.isOpened()){
	printf("impossible d'initialiser le flux video\n verifiez les branchements");
	return -1;
	}
	
	//     Initialisation de certains parametre du flux video
		
		capture.set(CV_CAP_PROP_FPS,60);// camera a 60fps
	//     Initialisation et déclaration des threads
	pthread_t thread1;
	pthread_t thread2;
	pthread_t thread3;
	pthread_t thread4;
	
	//     Creation des fenetres pour affichage du stream video vu par la camera //
	window_create();

	capture >> source;//Une premiere capture d'image pour notre fonction de calibration
	
	calib_auto();//nous recuperons le nombre de porte ici et leur positionnement
	
	GTK(); //  nous lancons nos fenetres
	clock_t start, end;
	start = clock();
	while(capture.read(source))
{	
	


	if(gpioRead(26) == 1)
	{
		//printf("entree a 1\n");
		sauvegarde_usb();
		while(gpioRead(26)==1)
		{}
	}
	waitKey(1);//dure 8ms normalement que 1ms <- normal, cette fonction attends au moins 1ms 
	get_time();
	
	loop++;fps++; //variables pour le comptage des fps 

	for(i=0;i<nombreporte;i++)
	{
		cvtColor(source(Rect(X[i*2],Y[0],X[i*2+1]-X[i*2],Y[1]-Y[0])),hsvcrop[i],CV_BGR2HSV);
	}
	//on decoupe l'image principale pour ne garder que les parties qui nous interessent

		
			if (pthread_create(&thread1, NULL, thread_1, &thread_param[0])) {
				perror("pthread_create");
				return EXIT_FAILURE;
			}
			if (pthread_create(&thread2, NULL, thread_2, &thread_param[1])) {
				perror("pthread_create");
				return EXIT_FAILURE;
			}
			if (pthread_create(&thread3, NULL, thread_3, &thread_param[2])) {
				perror("pthread_create");
				return EXIT_FAILURE;
			}
			if (pthread_create(&thread4, NULL, thread_4, &thread_param[3])) {
				perror("pthread_create");
				return EXIT_FAILURE;
			}
			
			
			if (pthread_join(thread1, NULL)) {
				perror("pthread_join");
				return EXIT_FAILURE;
			}		
			if (pthread_join(thread2, NULL)) {
				perror("pthread_join");
				return EXIT_FAILURE;
			}			
			if (pthread_join(thread3, NULL)) {
				perror("pthread_join");
				return EXIT_FAILURE;
			}			
			if (pthread_join(thread4, NULL)) {
				perror("pthread_join");
				return EXIT_FAILURE;
   			}

	sauvegarde_automatique();
	switch(video)
	{
	case 0:
		
		destroyWindow("flux_video");	
		break;
	case 1 :

		//Affichage dans le coin superieur gauche
		putText(source,Time, Point(20,23) , FONT_HERSHEY_SIMPLEX, .4, Scalar(18,220,252), 1,8, false );
		putText(source,DateString, Point(5,10) , FONT_HERSHEY_SIMPLEX, .4, Scalar(18,220,252),1,8,false );
		sprintf(name,"Ruche numero : %s",NumeroRuche);
		putText(source,name, Point(5,40) , FONT_HERSHEY_SIMPLEX, .45, Scalar(108,196,159), 1.5,8,false );
		sprintf(name,"FPS : %.0f",loop1);
    		putText(source,name, Point(5,65) , FONT_HERSHEY_SIMPLEX, .45, Scalar(236,131,150), 1.5,8,false );
		sprintf(name,"localisation : %s",NomLieu);
    		putText(source,name, Point(5,85) , FONT_HERSHEY_SIMPLEX, .45, Scalar(108,196,159), 1.5,8,false );
		sprintf(name,"Proprietaire : %s",NomProprio);
    		putText(source,name, Point(5,105) , FONT_HERSHEY_SIMPLEX, .45, Scalar(108,196,159), 1.5,8,false );
		sprintf(name,"Nombre de porte : %d",nombreporte);
    		putText(source,name, Point(300,10) , FONT_HERSHEY_SIMPLEX, .45, Scalar(108,196,159), 1.5,8,false );
		//Affichage dans le coin superieur droit
		if(flagdetectcouleur==1)
		{
			sprintf(name,"Sorties Rouge : %d",sortieRouge);
	    			putText(source,name, Point(450,20) , FONT_HERSHEY_SIMPLEX, .45, Scalar(0,0,255), 1,8,false );
			sprintf(name,"Sorties Bleu  : %d",sortieBleu);
	    			putText(source,name, Point(450,40) , FONT_HERSHEY_SIMPLEX, .45, Scalar(255,0,0), 1,8,false );
			sprintf(name,"Sorties Verte : %d",sortieVert);
	    			putText(source,name, Point(450,60) , FONT_HERSHEY_SIMPLEX, .45, Scalar(0,255,0), 1,8,false );
			sprintf(name,"Sorties Jaune : %d",sortieJaune);
    				putText(source,name, Point(450,80) , FONT_HERSHEY_SIMPLEX, .45, Scalar(0,255,255), 1,8,false );
		}
		sprintf(name,"Total sorties : %d",sortieTotal);
    		putText(source,name, Point(450,100) , FONT_HERSHEY_SIMPLEX, .45, Scalar(236,196,38), 1,8,false );

		//Affichage dans le coin inferieur droit
		if(flagdetectcouleur==1)
		{
			sprintf(name,"Entrees Rouge : %d",entreeRouge);
    				putText(source,name, Point(450,270),FONT_HERSHEY_SIMPLEX, .45, Scalar(0 ,0 ,255), 1,8,false );
			sprintf(name,"Entrees Bleu  : %d"  ,entreeBleu);
    				putText(source,name, Point(450,290),FONT_HERSHEY_SIMPLEX, .45, Scalar(255,0 ,0 ), 1,8,false );
			sprintf(name,"Entrees Verte : %d" ,entreeVert);
    				putText(source,name, Point(450,310),FONT_HERSHEY_SIMPLEX, .45, Scalar(0 ,255,0 ), 1,8,false );
			sprintf(name,"Entrees Jaune : %d" ,entreeJaune);
    				putText(source,name, Point(450,330),FONT_HERSHEY_SIMPLEX, .45, Scalar(0,255,255), 1,8,false );
		}
		sprintf(name,"Total entrees : %d",entreeTotal);
    		putText(source,name, Point(450,350) , FONT_HERSHEY_SIMPLEX, .45, Scalar(236,196,38), 1,8,false );

		//Affichage dans le coin inferieur gauche
		putText(source,"OneBee - OneCounter", Point(30,270),FONT_HERSHEY_SIMPLEX, .6, Scalar(229 ,117 ,48), 1,8,false );
		putText(source,"MIND Microtec", Point(30,300),FONT_HERSHEY_SIMPLEX, .6, Scalar(229 ,117 ,48), 1,8,false );
		putText(source,"Devoloppe par J-C Lebreton", Point(5,350),FONT_HERSHEY_SIMPLEX, .4, Scalar(255 ,0 ,255), 0.6,8,false );
		
		
		if(C==1)
		{
			calibration(source);
			destroyWindow("flux_video");
		}
		else
		{
			namedWindow("flux_video", CV_WINDOW_AUTOSIZE);
			imshow("flux_video", source);
						
		}
			
		break;
	case 2 :
		if(Time[7]%2)
		{

			src = imread( "image1.jpg");
			//namedWindow("flux_video", CV_WINDOW_AUTOSIZE);
			sprintf(name,"Entrees :%d",entreeTotal);
    			putText(src,name, Point(20,200) , FONT_HERSHEY_SIMPLEX, 3, Scalar(255,0,0), 2,8,false );
			sprintf(name,"Sorties :%d",sortieTotal);
    			putText(src,name, Point(20,380) , FONT_HERSHEY_SIMPLEX, 3, Scalar(255,0,0), 2,8,false );
			imshow("Compteur",src);
		}
		break;
	case 3 :
		
		imshow("Graphique", Graph);

		break;	
	}
	
	
	if(Quitter==1)
	{	
	
		printf("Fin du programme par demande de l'utilisateur\n");
		//gpioWrite(25,1);
    	    	//destroyAllWindows();
		gpioWrite(25,1);
	    	fclose(file2);  // faire attention ici 
		gpioTerminate();
	    	return 0;
	}
	//printf("%d %d : %d %d\n",HeureMinute[0],HeureMinute[1],HeureMinute[3],HeureMinute[4]);
	/*
	if(HeureMinute[0]==49 && HeureMinute[1]==55 && HeureMinute[3]==51 && HeureMinute[4]==48 && gpioRead(25)==0)
	{
		gpioWrite(25,1);
	}
	if(HeureMinute[0]==48 && HeureMinute[1]==56 && HeureMinute[3]==51 && HeureMinute[4]==48 && gpioRead(25)==1)
	{
		gpioWrite(25,0);
	}*/		
	end = clock();pass++;
	tot=tot+((double)end - start) *1000/ (CLOCKS_PER_SEC);
	start=clock();		
}

}

void GTK (void)
{
	int i=0; //compteur
	char recall[10]={0};
	
		
///////////////// Déclaration communes aux différents widgets ///////////////////
	//déclaration de la fenêtre
	pWindow1 = gtk_window_new(GTK_WINDOW_TOPLEVEL); // Fenetre principale avec
	pWindow2 = gtk_window_new(GTK_WINDOW_TOPLEVEL); // fenetre de lancement
	pWindow3[0] = gtk_window_new(GTK_WINDOW_TOPLEVEL); // Fenetre pour la calibration
	pWindow3[1] = gtk_window_new(GTK_WINDOW_TOPLEVEL); 
	pWindow3[2] = gtk_window_new(GTK_WINDOW_TOPLEVEL);
	pWindow4 = gtk_window_new(GTK_WINDOW_TOPLEVEL); // Fenetre d'initialisation

	//changement du titre
	gtk_window_set_title(GTK_WINDOW(pWindow1),"OneBee");
	gtk_window_set_title(GTK_WINDOW(pWindow2),"Lancement");
	gtk_window_set_title(GTK_WINDOW(pWindow3[0]),"Calibration");
	gtk_window_set_title(GTK_WINDOW(pWindow3[1]),"Calibration");
	gtk_window_set_title(GTK_WINDOW(pWindow3[2]),"Calibration");
	gtk_window_set_title(GTK_WINDOW(pWindow4),"Initialisation");

	//changement de la taille de la fenetre
	gtk_window_set_default_size(GTK_WINDOW(pWindow1),300,480);
	gtk_window_set_default_size(GTK_WINDOW(pWindow2),300,400);
	gtk_window_set_default_size(GTK_WINDOW(pWindow3[0]),300,700);
	gtk_window_set_default_size(GTK_WINDOW(pWindow3[1]),300,700);
	gtk_window_set_default_size(GTK_WINDOW(pWindow3[2]),300,(nombreporte*2-20)*70);
	gtk_window_set_default_size(GTK_WINDOW(pWindow4),300,480);
	
	//connection avec le signal destroy
	g_signal_connect(G_OBJECT(pWindow1),"destroy",G_CALLBACK(OnDestroy),NULL);// !!!!!!!
	g_signal_connect(G_OBJECT(pWindow2),"destroy",G_CALLBACK(OnDestroy),NULL);// !!!!!!!
	g_signal_connect(G_OBJECT(pWindow3[0]),"destroy",G_CALLBACK(OnDestroy),NULL);// !!!!!!!
	g_signal_connect(G_OBJECT(pWindow3[1]),"destroy",G_CALLBACK(OnDestroy),NULL);
	g_signal_connect(G_OBJECT(pWindow3[2]),"destroy",G_CALLBACK(OnDestroy),NULL);
	g_signal_connect(G_OBJECT(pWindow4),"destroy",G_CALLBACK(OnDestroy),NULL);

///////////////////////////////////////////////////////

/////////////////Creation des box///////////////////////
	//Box verticales
	pVBox1 = gtk_vbox_new(GTK_ORIENTATION_VERTICAL,5);
	pVBox2 = gtk_vbox_new(GTK_ORIENTATION_VERTICAL,5);
	pVBox3[0] = gtk_vbox_new(GTK_ORIENTATION_VERTICAL,5);
	pVBox3[1] = gtk_vbox_new(GTK_ORIENTATION_VERTICAL,5);
	pVBox3[2] = gtk_vbox_new(GTK_ORIENTATION_VERTICAL,5);
	pVBox4 = gtk_vbox_new(GTK_ORIENTATION_VERTICAL,5);
	
	//ajout de la box dans la fenetre
	gtk_container_add(GTK_CONTAINER(pWindow1),pVBox1);
	gtk_container_add(GTK_CONTAINER(pWindow2),pVBox2);
	gtk_container_add(GTK_CONTAINER(pWindow3[0]),pVBox3[0]);
	gtk_container_add(GTK_CONTAINER(pWindow3[1]),pVBox3[1]);
	gtk_container_add(GTK_CONTAINER(pWindow3[2]),pVBox3[2]);
	gtk_container_add(GTK_CONTAINER(pWindow4),pVBox4);
	
	//Creation de la box horizontale
	pHBox1 = gtk_hbox_new(GTK_ORIENTATION_HORIZONTAL,5);
////////////////////////////////////////////////////////////

/////////////////initialisation des labels//////////////////
	pLabel = gtk_label_new("1");
	pLabel1 = gtk_label_new(NULL);
	pLabel2 = gtk_label_new(NULL);
	pLabel3 = gtk_label_new(NULL);
	pLabel4 = gtk_label_new(NULL);
	
	// Labels pour la fenetre de présentation
	gtk_label_set_markup(GTK_LABEL(pLabel1),g_locale_to_utf8("<b>Projet OneBee</b>",-1,NULL,NULL,NULL));
	gtk_label_set_markup(GTK_LABEL(pLabel2),g_locale_to_utf8("<u>Réalisé en collaboration avec:</u>",-1,NULL,NULL,NULL));
	gtk_label_set_markup(GTK_LABEL(pLabel3),g_locale_to_utf8("<span foreground=\"#0000FF\"> <b>Mind</b> </span> et <span foreground=\"#0000FF\"><b>USMB </b></span>",-1,NULL,NULL,NULL));
	gtk_label_set_markup(GTK_LABEL(pLabel4),g_locale_to_utf8("<u>Réalisé par :</u><i> J-C Lebreton</i>",-1,NULL,NULL,NULL));
	
	// Labels pour la fenetre de calibration
	for(i=0;i<(nombreporte*2);i++)
	{	
		snprintf(recall,sizeof(recall),"%d",X[i]);
		pLabelT[i]=gtk_label_new(recall);
	}
	pLabelT1= gtk_label_new("Cliquer sur 'calibration' dans la");
	pLabelT2= gtk_label_new("fentre principale pour quitter.");

	//pImage = gtk_image_new_from_file("./logo.jpg");
	//pImage1 = gtk_image_new_from_file("./logo1.jpg");

	// Labels pour la fenetre initialisation
	pLabelI1= gtk_label_new("Inserer le numero de la ruche:");
	pLabelI2= gtk_label_new("Inserer le lieu de la ruche :");
	pLabelI3= gtk_label_new("Inserer votre nom :");
////////////////////////////////////////////////////////////

///////////////Initialisation du Gtk Entry pour la saisir au clavier ////:
	pEntryI1 = gtk_entry_new();
	pEntryI2 = gtk_entry_new();
	pEntryI3 = gtk_entry_new();

///////////////Creation des boutons //////////
	// boutons pour la fenetre princiaple
	pButton[0]=gtk_button_new_with_label("1 - Calibration");
	pButton[1]=gtk_button_new_with_label("2 - Aucun Affichage");
	pButton[2]=gtk_button_new_with_label("3 - Affichage de la camera");
	pButton[3]=gtk_button_new_with_label("4 - Affichage du compteur seul");
	pButton[4]=gtk_button_new_with_label("5 - Affichage du graphique");
	pButton[5]=gtk_button_new_with_label("6 - Eclairage");
	pButton[6]=gtk_button_new_with_label("7 - Détection couleur");
	pButton[7]=gtk_button_new_with_label("-");
	pButton[8]=gtk_button_new_with_label("+");
	pButton[9]=gtk_button_new_with_label("Quitter");
	
	//Creation des boutons de la fenetre de lancement ////////
	pButton1=gtk_button_new_with_label("Lancer");
	
	// boutons pour la fentre d'initialisation 
	pButtonI=gtk_button_new_with_label("Valider");

	
/////////////////////////////////////////////////////////////////////////


//////creation des trackbars et du bouton de la fenetre calibration ////
	
	for(i=0;i<nombreporte*2;i++)
	{
		Adjust[i] = gtk_adjustment_new(X[i],1,641,1,10,1);
		pScroll[i]=gtk_hscrollbar_new(GTK_ADJUSTMENT(Adjust[i]));
		
	}
///////////////////////////////////////////////////////////////////////////////////////

/////////// Creation de l'interieur des fentres ///////////////////////////////

	//ajout des bouttons dans la box de le fenetre Principale
	gtk_box_pack_start(GTK_BOX(pVBox1),pButton[0],TRUE,FALSE,0);
	gtk_box_pack_start(GTK_BOX(pVBox1),pButton[1],TRUE,FALSE,0);
	gtk_box_pack_start(GTK_BOX(pVBox1),pButton[2],TRUE,FALSE,0);
	gtk_box_pack_start(GTK_BOX(pVBox1),pButton[3],TRUE,FALSE,0);
	gtk_box_pack_start(GTK_BOX(pVBox1),pButton[4],TRUE,FALSE,0);
	gtk_box_pack_start(GTK_BOX(pVBox1),pButton[5],TRUE,FALSE,0);
	gtk_box_pack_start(GTK_BOX(pVBox1),pButton[6],TRUE,FALSE,0);
	gtk_box_pack_start(GTK_BOX(pVBox1),pHBox1,TRUE,TRUE,0);	
	gtk_box_pack_start(GTK_BOX(pHBox1),pButton[7],TRUE,FALSE,0);
	gtk_box_pack_start(GTK_BOX(pHBox1),pLabel,TRUE,FALSE,0);
	gtk_box_pack_start(GTK_BOX(pHBox1),pButton[8],TRUE,FALSE,0);
	gtk_box_pack_start(GTK_BOX(pVBox1),pButton[9],TRUE,FALSE,0);

	//ajout dans la box de la fentre de présentation
	
	gtk_box_pack_start(GTK_BOX(pVBox2),pLabel1,TRUE,FALSE,0);
	gtk_box_pack_start(GTK_BOX(pVBox2),pLabel2,TRUE,FALSE,0);
	gtk_box_pack_start(GTK_BOX(pVBox2),pLabel3,TRUE,FALSE,0);
	gtk_box_pack_start(GTK_BOX(pVBox2),pButton1,TRUE,FALSE,0);
	gtk_box_pack_start(GTK_BOX(pVBox2),pLabel4,TRUE,FALSE,0);
	//gtk_box_pack_start(GTK_BOX(pVBox2),pImage,TRUE,FALSE,0);	
	//gtk_box_pack_start(GTK_BOX(pVBox2),pImage1,TRUE,FALSE,0);
	
	//ajout dans la box de la fenetre calibration 1
	for(i=0;i<10;i++)
	{
		gtk_box_pack_start(GTK_BOX(pVBox3[0]),pLabelT[i],TRUE,FALSE,0);
		gtk_box_pack_start(GTK_BOX(pVBox3[0]),pScroll[i],TRUE,FALSE,0);
	}

	//ajout dans la box de la fenetre calibration 2
	for(i=10;i<20;i++)
	{
		gtk_box_pack_start(GTK_BOX(pVBox3[1]),pLabelT[i],TRUE,FALSE,0);
		gtk_box_pack_start(GTK_BOX(pVBox3[1]),pScroll[i],TRUE,FALSE,0);
	}

	//ajout dans la box de la fenetre calibration 3
	for(i=20;i<nombreporte*2;i++)
	{
		gtk_box_pack_start(GTK_BOX(pVBox3[2]),pLabelT[i],TRUE,FALSE,0);
		gtk_box_pack_start(GTK_BOX(pVBox3[2]),pScroll[i],TRUE,FALSE,0);
	}
	gtk_box_pack_start(GTK_BOX(pVBox3[2]),pLabelT1,TRUE,FALSE,0);
	gtk_box_pack_start(GTK_BOX(pVBox3[2]),pLabelT2,TRUE,FALSE,0);

	//ajout dans la box de la fenetre d'initialisation
	gtk_box_pack_start(GTK_BOX(pVBox4),pLabelI1,TRUE,FALSE,0);
	gtk_box_pack_start(GTK_BOX(pVBox4),pEntryI1,TRUE,FALSE,0);
	gtk_box_pack_start(GTK_BOX(pVBox4),pLabelI2,TRUE,FALSE,0);
	gtk_box_pack_start(GTK_BOX(pVBox4),pEntryI2,TRUE,FALSE,0);
	gtk_box_pack_start(GTK_BOX(pVBox4),pLabelI3,TRUE,FALSE,0);
	gtk_box_pack_start(GTK_BOX(pVBox4),pEntryI3,TRUE,FALSE,0);
	gtk_box_pack_start(GTK_BOX(pVBox4),pButtonI,TRUE,FALSE,0);

//////////////////////////////////////////////////////////////////////////////////////////


////////////////////////// Déclaration des evenements dans les fenetres/////////////////////////
	
	//Dans la fentre principale
	g_signal_connect(G_OBJECT(pButton[0]),"clicked",G_CALLBACK(FlagModifier), pButton[0]);
	g_signal_connect(G_OBJECT(pButton[1]),"clicked",G_CALLBACK(FlagModifier), pButton[1]);
	g_signal_connect(G_OBJECT(pButton[2]),"clicked",G_CALLBACK(FlagModifier), pButton[2]);
	g_signal_connect(G_OBJECT(pButton[3]),"clicked",G_CALLBACK(FlagModifier), pButton[3]);
	g_signal_connect(G_OBJECT(pButton[4]),"clicked",G_CALLBACK(FlagModifier), pButton[4]);
	g_signal_connect(G_OBJECT(pButton[5]),"clicked",G_CALLBACK(FlagModifier), pButton[5]);
	g_signal_connect(G_OBJECT(pButton[6]),"clicked",G_CALLBACK(FlagModifier), pButton[6]);
	g_signal_connect(G_OBJECT(pButton[7]),"clicked",G_CALLBACK(Minutem), pLabel);
	g_signal_connect(G_OBJECT(pButton[8]),"clicked",G_CALLBACK(Minutep), pLabel);
	g_signal_connect(G_OBJECT(pButton[9]),"clicked",G_CALLBACK(FlagModifier), pButton[9]);

	//dans la fenetre de présentation
	g_signal_connect(G_OBJECT(pButton1),"clicked",G_CALLBACK(OnInit),pWindow2);

	//dans la fenetre de calibration
	for(indiceGtk=0;indiceGtk<nombreporte*2;indiceGtk++)
	{	
		g_signal_connect(G_OBJECT(pScroll[indiceGtk]),"value_changed",G_CALLBACK(OnScrollChange),gpointer(indiceGtk));
	}
	//dans la fentetre d'initialisation
	g_signal_connect(G_OBJECT(pEntryI1), "activate", G_CALLBACK(on_activate_entry), (GtkWidget*) pLabelI1);
	g_signal_connect(G_OBJECT(pEntryI2), "activate", G_CALLBACK(on_activate_entry), (GtkWidget*) pLabelI2);
	g_signal_connect(G_OBJECT(pEntryI3), "activate", G_CALLBACK(on_activate_entry), (GtkWidget*) pLabelI3);
	g_signal_connect(G_OBJECT(pButtonI), "clicked", G_CALLBACK(on_copier_button), (GtkWidget*) pVBox4);

	g_signal_connect(G_OBJECT(pButtonI),"clicked",G_CALLBACK(OnValid),pWindow4);
	//Affichage de la fenêtre de présentation
	gtk_widget_show_all(pWindow2);
	
	//gestion de l'affichage de la fenetre de calibration
	/*if(C==1)
	{
		gtk_widget_show_all(pWindow3[0]);
		gtk_widget_show_all(pWindow3[1]);g_signal_connect(G_OBJECT(pButton), "clicked", G_CALLBACK(on_copier_button), (GtkWidget*) pVBox);
		gtk_widget_show_all(pWindow3[2]);
	}
	else
	{
		gtk_widget_hide(pWindow3[0]);
		gtk_widget_hide(pWindow3[1]);
		gtk_widget_hide(pWindow3[2]);
	}*/
	//Démarrage de la boucle evenementielle
	gtk_main();

}

void OnDestroy(GtkWidget *pWidget, gpointer pData)
{
	//arret de la boucle
	gtk_main_quit();
}
void OnValid(GtkWidget *pWidget, gpointer pData)
{
	gtk_main_quit();
	video=1;
	gtk_widget_destroy(GTK_WIDGET(pData));
	gtk_widget_show_all(pWindow1);
	

}
void OnInit(GtkWidget *pWidget, gpointer pData)
{
	video=0;
	gtk_widget_destroy(GTK_WIDGET(pData));
	if(initialisation == 0)
	{
		gtk_widget_show_all(pWindow1);
	}
	else
	{
		gtk_widget_show_all(pWindow4);
	}
}
void Minutep(GtkWidget *pWidget, GtkLabel *pLabel)
{
	const gchar *texte;
	gchar valeur=0;
	gchar data [5]={0};
	texte = gtk_label_get_text(GTK_LABEL(pLabel));
	valeur = (gchar) *texte - 48; // !!!!!!
	if(valeur<9)
	{
		valeur = valeur+1;
		//printf("%d",valeur);
		snprintf(data,sizeof(data),"%d",valeur);
		gtk_label_set_text (GTK_LABEL(pLabel),data);
		minuteS=valeur;
	}
	
}
void Minutem(GtkWidget *pWidget, GtkLabel *pLabel)
{
	const gchar *texte;
	gchar valeur=0;
	gchar data [5]={0};
	texte = gtk_label_get_text(GTK_LABEL(pLabel));
	valeur = (gchar) *texte - 48; // !!!!!!
	if(valeur!=0)
	{
		valeur = valeur-1;
		//printf("%d",valeur);
		snprintf(data,sizeof(data),"%d",valeur);
		gtk_label_set_text (GTK_LABEL(pLabel),data);	
		minuteS=valeur;
	}
}
void FlagModifier( GtkWidget *pWidget, GtkButton *pButton)
{
	destroyWindow("Graphique");
	destroyWindow("Compteur");
	destroyWindow("flux_video");
	switch(gtk_button_get_label(pButton)[0])
	{
		case('1'):
		if(C==0)
		{
			C=1;
			gtk_widget_show_all(pWindow3[0]);
			gtk_widget_show_all(pWindow3[1]);
			gtk_widget_show_all(pWindow3[2]);

		}
		else
		{
			C=0;
			gtk_widget_hide(pWindow3[0]);
			gtk_widget_hide(pWindow3[1]);
			gtk_widget_hide(pWindow3[2]);
			destroyWindow("Calibration");

		}
		break;

		case('2'):
			video=0;
		break;

		case('3'):
			video=1;
			moveWindow("flux_video",0,0);
		break;

		case('4'):
			video=2;
		break;

		case('5'):
			video=3;
		break;

		case('6'):
			if(gpioRead(25)==0)
			{
				gpioWrite(25,1);
			}
			else
			{
				gpioWrite(25,0);
			}
		break;

		case('7'):
			if(flagdetectcouleur==1)
			{
				flagdetectcouleur=0;
			}
			else
			{
				flagdetectcouleur=1;
			}
		break;

		case('Q'):
			Quitter=1;
		break;
	}

}
void OnScrollChange( GtkWidget *pWidget, gpointer pData)
{
	gchar* sLabel;
	gint iValue;

	iValue = gtk_range_get_value(GTK_RANGE(pWidget));
	sLabel = g_strdup_printf("%d",iValue);
	X[(int)pData]=iValue;
	gtk_label_set_text(GTK_LABEL(pLabelT[(int)pData]),sLabel);
}

/* Fonction callback execute lors du signal "activate" */
void on_activate_entry(GtkWidget *pEntry, gpointer data)
{
    const gchar *sText;
 
    /* Recuperation du texte contenu dans le GtkEntry */
    sText = gtk_entry_get_text(GTK_ENTRY(pEntry));
 
    /* Modification du texte contenu dans le GtkLabel */
    gtk_label_set_text(GTK_LABEL((GtkWidget*)data), sText);
}
 
/* Fonction callback executee lors du signal "clicked" */
void on_copier_button(GtkWidget *pButton, gpointer data)
{
	GtkWidget *pTempEntry;

    	GList *pList;
    	const gchar *sText;
 
    		/* Recuperation de la liste des elements que contient la GtkVBox */
    	pList = gtk_container_get_children(GTK_CONTAINER((GtkWidget*)data));
 
    		/* Passage a l element suivant : le entryI1 */
    	pList = g_list_next(pList);

    		/* L'element ici est le entry I1 */
    	pTempEntry = GTK_WIDGET(pList->data);     
 	sText =  gtk_entry_get_text(GTK_ENTRY(pTempEntry));
    	strcpy(NumeroRuche,sText);

    		/* Passage a l element suivant : le entry I2 */
   	pList = g_list_next(pList);
	pList = g_list_next(pList);
	
		/* L'element ici est le entry I2 */
    	pTempEntry = GTK_WIDGET(pList->data);     
 	sText =  gtk_entry_get_text(GTK_ENTRY(pTempEntry));
    	strcpy(NomLieu,sText);

    		/* Passage a l element suivant : le entry I3 */
   	pList = g_list_next(pList);
	pList = g_list_next(pList);
	
		/* L'element ici est le entry I3 */
    	pTempEntry = GTK_WIDGET(pList->data);  
     	sText =  gtk_entry_get_text(GTK_ENTRY(pTempEntry));
    	strcpy(NomProprio,sText);

	Finitialisation=fopen("Initialisation.txt","w+");
	fprintf(Finitialisation,"%s\n%s\n%s\n",NumeroRuche,NomLieu,NomProprio);
 
    	/* Liberation de la memoire utilisee par la liste */
    	g_list_free(pList);
}