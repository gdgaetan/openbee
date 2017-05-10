Bonjour,

Ce répertoire regroupe la version 5 du compteur. Cette version n'influence en rien le traitement video. il ajoute l'utilisation de la librairie SQL pour enrgistrer les fichiers sur un serveur distant. il est toutdefois possible d'utiliser cet ajout pour effectuer une sauvegarde sous un BBD interne a la raspberry / pc

Il garde les options de base:

  - Sauvegarde automatique toutes les minutes
  - Sauvegarde dans une BDD
  - Sauvegarde sur une clef USB
  - Programme d'initialisation
  - Création et sauvegarde d'un graphique
  - Interface graphique grace a opencv
  - Traitement video
  - Comptage
  
Et ajoute :

  - Utilisation optimisée des threads
  
Il suffit de compiler le fichier "camera.c" sous linux avec les librairies openCV gtk mysql et pigpio pour essayer le programme 
g++ -Wall -pthread -o camerapio camera.c -lpigpio -lrt `pkg-config --cflags --libs opencv gtk+-2.0` `mysql_config --cflags --libs` 

Que sont les threads?

Les threads sont des taches pouvant s'éxecuter en parrallèle. Ainsi, dans notre programme nous divisons équitablement le traitement 
de l'image avec chacun des threads pour qu'ils s'éxécutent tous en même temps. 
Nous gagnons ainsi 57% de temps de traitement. Ce qui est, bien évidemment, non négligeable!
