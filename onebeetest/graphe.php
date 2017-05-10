<!DOCTYPE html>
<html lang="fr">
<head>
  <title>One Bee - Graphique</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
  
</head>
<body>

<nav class="navbar navbar-inverse navbar-fixed-top">
  <div class="container-fluid">
    <div class="navbar-header">
      <span class="navbar-brand">One Bee</span>
    </div>
    <ul class="nav navbar-nav">
      <li><a href="index.php">ACCUEIL</a></li>
      <li class="active"><a href="graphe.php">GRAPHIQUE</a></li>
      <li><a href="flux.php">FLUX VIDEO</a></li>
    </ul>
	 <ul class="nav navbar-nav navbar-right">
      <li><a href="#">REDEMARER</a></li>
    </ul>
  </div>
</nav>
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
		<script src="highcharts.js"></script>
		<script src="graphiques.js"></script>
		
		<title>Test pour les graphiques</title>	
	</head>
	
	<body>
		
				
				
				
			<div class="chartTest">
				<div id="myChart" style="width:100%; height:400px;"></div>
				
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
				<form method="post" action="graphe.php">
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
				
				<button onclick="saveImg()">Enregistrer le graphique</button>
				
				
				
				<!--
					bouton pour enregistrer le canvas en image (sachant que le clic droit fonctionne aussi)
					http://stackoverflow.com/questions/10673122/how-to-save-canvas-as-an-image-with-canvas-todataurl
				-->
				
				
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
		


</html>
</body>
</html>
