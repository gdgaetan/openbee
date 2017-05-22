<?php
	// connexion à la base de données
	try
	{
<<<<<<< HEAD
		$bdd = new PDO('mysql:host=localhost;dbname=comptage_video', 'root', '');
=======
		$bdd = new PDO('mysql:host=localhost;dbname=comptage_video', 'onebee', '');
>>>>>>> 1d50c5ba5d81dc3b40b685b1845eb37653fbcd15
	}
	catch(Exception $e)
	{
		// erreur
		die('Erreur : '.$e->getMessage());
	}
?>
