<?php 
session_start();/*on démare une session pour les varriables de session que l'on va avoir, ici la seule varrible de session que l'on a est la varrible "debut"*/
$_SESSION['dateCourante'] = date("Y-m-d H:i");//varriable comtenant la date courante
?>