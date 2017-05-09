<!DOCTYPE html>
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
	
	/*echo "getDonnees---------";
	getDonnees('2017-03-21 11:00:00', '2017-03-21 15:00:00');*/
	
	/*echo "<script>
		chartAbeille.update();
	</script>";
	//header('Location: index.php');*/

?>

<html lang="fr">
	<head>
		<meta charset="utf-8" />
		<meta name="description" content="Quelle belle description !" />
		<meta name="author" content="Solène Demars" />
		<!-- Shortcut Icon -->
		<!--<link rel="icon" type="image/png" href="img/favicon.png" />-->
		
		<link href="style.css" rel="stylesheet" type="text/css" />
		<script src="Chart.bundle.min.js"></script>
		<script src="graphiques.js"></script>
		
		<title>Test pour les graphiques</title>	
	</head>
	
	<body>
		
		<h1>C'est un titre</h1>
		
		<div class="mainContent">
		
			<p>Et ça c'est du contenu.</p>
			
			<div class="liste">

			<h2>Flux</h2>
			<!--<iframe src="http://192.168.43.39:8031/"
			width="460" height="345" class="smart_sizing_iframe noresize"
			frameborder="0" scrolling="no" >
			</iframe>-->
<img class="shrinkToFit" alt="http://192.168.43.39:8081/" src="http://192.168.43.39:8081/" height="236" width="288">
			</div>

			<div class="formulaire">

			<h2>Formulaire extraordinaire</h2>

			<form method="post" action="index.php">
			<p><label>Champ <abbr alt="numéro">n°1</abbr></label> : <input type="text" name="chUn" placeholder="Lui, il sert à rien en fait"/> </p>
			<p><label>Champ <abbr alt="numéro">n°2</abbr></label> : <input type="password" name="chDeux" placeholder="Important"/> </p>
			<input type="submit" name="valider" value="Ne pas cliquer"/>
			</form>

			<div class="resultForm">
			<p>
			<?php

			if(isset($_POST['valider']))
{
	if (!empty($_POST["chDeux"]))
	{
		echo $_POST["chDeux"];
		echo ", quel beau mot de passe !";
	}
	else
	{
		echo "Donne-moi ton mot de passe !";
	}
}
?>
</p>

</div>
</div>

<div class="chartTest">
<canvas id="myChart"></canvas>	

<?php
// dès le début, on récupère le contenu des formulaires
// (+ valeurs par défaut, avec isset)

// quel intervalle à afficher ?
// récupérer la date actuelle (date de fin à afficher)
$timezone = new DateTimeZone("Europe/Paris");
$dateFin = new DateTime();
$dateFin->setTimezone( $timezone );

// calculer la (date de début à afficher, par défaut : la dernière heure)
$dateDebut = new DateTime();
$dateDebut->setTimezone( $timezone );
$dateDebut->sub(new DateInterval('PT1H')); // moins 1 heure

$granularite = "minute";

// avec le formulaire rempli
if(isset($_POST["update"]))
{
	if (!empty($_POST["selectDateFin"]))
	{
		$dateFin = new DateTime($_POST["selectDateFin"]);
	}
	if (!empty($_POST["selectDateDebut"]))
	{
		$dateDebut = new DateTime($_POST["selectDateDebut"]);
	}
	if (!empty($_POST["selectGranularite"]))
	{
		switch ($_POST["selectGranularite"]) {
			case "heure":
				$granularite = "heure";
				break;
			case "jour":
				$granularite = "jour";
				break;
			case "semaine":
				$granularite = "semaine";
				break;
			case "mois":
				$granularite = "mois";
				break;
			case "annee":
				$granularite = "annee";
				break;
			default: // minute
				$granularite = "minute";
		}
	}

}

// on a besoin de chaînes de caractères
$fin = $dateFin->format('Y-m-d H:i:s');
$debut = $dateDebut->format('Y-m-d H:i:s');

getDonnees($debut, $fin, $granularite); // page onload
?>		

<!-- des boutons... -->
<form method="post" action="index.php">
<!-- format date s'affiche sur Chrome et Edge, pour Firefox on ne voit qu'un champ de texte, format américain "mm/jj/aaaa hh:mm" -->
<input type="datetime-local" name="selectDateDebut" />
<input type="datetime-local" name="selectDateFin" />
<span id="lblGranul">Granularité</span>
<select name="selectGranularite">
<option value="minute">Minute</option>
<option value="heure">Heure</option>
<option value="jour">Jour</option>
<option value="semaine">Semaine</option>
<option value="mois">Mois</option>
<option value="annee">Année</option>
</select>
<input type="submit" name="update" value="Mettre à jour" />
</form>
<form method="post" action="generate.php">
<input type="submit" name="generator" value="Generate 100000" />
</form>


<!--<script>
var element = document.getElementById('update');

element.addEventListener('click', function() {
		alert("chart update...");

		// MODIFIER LE GRAPHE
		// (changer les dates, l'échelle, etc.)

		//myChart.labels = ["1","2","3"];
		//myChart.data.labels = ["1","2","3"];
		//myChart.data.datasets[0].data = [1,2,3];
		//myChart.data.datasets[1].data = [4,5,6];
		//myChart.data.datasets[2].data = [7,8,9];

		//myChart.data.datasets[0].data[2] = 50;

		//myChart.update();
		//getDonnees(); // fonction php... TODO

		/*myChart.destroy();
		  var ctx = document.getElementById("myChartLine").getContext("2d");
		  myChart = new Chart(ctx).Line(data, options);*/



});
</script>-->

</div>

</div>

</body>

</html>
