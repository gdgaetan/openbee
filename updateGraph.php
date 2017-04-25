<?php

	include 'graphiques.php';
	
	/*
	on envoie une variable cachée avec le formulaire
	au démarrage, elle n'existe pas, on donne les valeurs par défaut
	sinon, on lit ce qu'il y a dans le formulaire, etc.
	*/
	
	//echo "<script>
	//	chartAbeille.destroy();
	//</script>";
	
	echo "<h1>test</h1><canvas id=\"myChart\"></canvas>";
	
	getDonnees('2017-03-21 11:00:00', '2017-03-21 15:00:00');
	
	echo "<script>
		chartAbeille.update();
	</script>";
	//header('Location: index.php');
?>