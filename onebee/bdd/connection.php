<?php
	// connexion à la base de données
	try
	{
		$bdd = new PDO('mysql:host=localhost;dbname=onebee', 'onebee', 'onebee');
	}
	catch(Exception $e)
	{
		// erreur
		die('Erreur : '.$e->getMessage());
	}
?>
