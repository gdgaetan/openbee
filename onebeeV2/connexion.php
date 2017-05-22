<?php
	// connexion à la base de données
	try
	{
		$bdd = new PDO('mysql:host=localhost;dbname=comptage_video', 'root', '');
	}
	catch(Exception $e)
	{
		// erreur
		die('Erreur : '.$e->getMessage());
	}
?>
