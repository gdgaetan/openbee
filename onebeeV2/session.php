<?php 
session_start();/*on démare une session pour les varriables de session que l'on va avoir, ici la seule varrible de session que l'on a est la varrible "debut"*/
$_SESSION['dateCourante'] = new DateTime();//varriable comtenant la date courante
$_SESSION['dateCourante']->setTimezone(new DateTimeZone("Europe/Paris")); // pour avoir la bonne heure
?>