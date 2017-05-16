<!DOCTYPE html>

<html lang="fr">
<head>
	  <title>One Bee - Graphique</title>
	  <meta charset="utf-8">
	  <meta name="viewport" content="width=device-width, initial-scale=1">
	  <link href="bootstrap.min.css" rel="stylesheet">
	  <script src="jquery-3.2.1.min.js"></script>
	  <script src="bootstrap.min.js"></script>
	  <meta charset="utf-8" />

	<link href="graphe.css" rel="stylesheet" type="text/css" />
	<script src="highcharts.js"></script>
	<script src="graphiques.js"></script>
	<script src="exporting.js"></script>
	<script src="canvas-tools.js"></script>
	<script src="jspdf.min.js"></script>
	<script src="highcharts-export-clientside.js"></script>

	<title>Test pour les graphiques</title>	
</head>
<body>
<!-- barre des menus en haut de la page (avec bootstraps) inverse pour fixer les menus à gauche, fixed-top pour avoir la barre affiché quand on scroll vers le bas -->
	<nav class="navbar navbar-inverse navbar-fixed-top">
	  <div class="container-fluid">
		<div class="navbar-header">
		<!--titre de la barre des menus qui permet aussi de revenir à l'acceuil -->
		 <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar"></button>
      <a class="navbar-brand" href="index.php">One Bee</a>
    </div>
    <div class="collapse navbar-collapse" id="myNavbar">
      <ul class="nav navbar-nav">
	  <!-- les 3 boutons du menu. celui qui est "active" est en surbrillance -->
			<li><a href="index.php">ACCUEIL</a></li> 
			<li class="active"><a href="graphe.php">GRAPHIQUE</a></li>
			<li><a href="flux.php">FLUX VIDEO</a></li>
      </ul>
      <ul class="nav navbar-nav navbar-right">
		  <li><a href="#">REDEMARRER</a></li>
		</ul>
	  </div>
	</nav>
	<!--Fin de la barre des menus -->

	<!-- début des graphiques -->
	<div class="charts">
		<!-- graphique qui affiche la dernière heure
			 se met à jour toutes les 60 secondes -->
		<div id="chart1"></div>
		
		<hr/>
		
		<div id="chart2"></div>
		
		<!-- des boutons pour le 2e graphique -->
		<form method="post">
			<!-- format date s'affiche sur Chrome et Edge, pour Firefox on ne voit qu'un champ de texte, format américain "mm/jj/aaaa hh:mm" -->
			<span class="formulaire">
				<label>Date début</label>
				<input type="datetime-local" id="selectDateDebut2" placeholder="mm/jj/aaaa hh:mm" />
			</span>
			<span class="formulaire">
				<label>Date fin</label>
				<input type="datetime-local" id="selectDateFin2" placeholder="mm/jj/aaaa hh:mm" />
			</span>
			<span class="formulaire">
				<label>Granularité</label>
				<select id="selectGranularite2">
					<option value="minute">Minute</option>
					<option value="heure">Heure</option>
					<option value="jour">Jour</option>
					<option value="semaine">Semaine</option>
					<option value="mois">Mois</option>
					<option value="annee">Année</option>
				</select>
			</span>
		</form>
		<button id="update2">Mettre à jour</button>

		<hr/>
		
		<div id="chart3"></div>
		
		<!-- des boutons pour le 3e graphique -->
		<form method="post">
			<!-- format date s'affiche sur Chrome et Edge, pour Firefox on ne voit qu'un champ de texte, format américain "mm/jj/aaaa hh:mm" -->
			<span class="formulaire">
				<label>Date début</label>
				<input type="datetime-local" id="selectDateDebut3" placeholder="mm/jj/aaaa hh:mm" />
			</span>
			<span class="formulaire">
				<label>Date fin</label>
				<input type="datetime-local" id="selectDateFin3" placeholder="mm/jj/aaaa hh:mm" />
			</span>
			<span class="formulaire">
				<label>Granularité</label>
				<select id="selectGranularite3">
					<option value="minute">Minute</option>
					<option value="heure">Heure</option>
					<option value="jour">Jour</option>
					<option value="semaine">Semaine</option>
					<option value="mois">Mois</option>
					<option value="annee">Année</option>
				</select>
			</span>
		</form>
		<button id="update3">Mettre à jour</button>

		<script>
			/**
				permet de charger le graphique désigné par l'entier en paramètre
				le 1er est un cas spécial : il n'a pas de formulaire pour préciser l'intervalle de temps et la granularité d'affichage
			*/
			function loadGraphique(num){
				if (num==1) {
					$("#chart"+num).load("graphiques.php", {id: num});
				} else {
					$("#chart"+num).load("graphiques.php", {
						id: num,
						dateDebut: $("#selectDateDebut"+num).val(),
						dateFin: $("#selectDateFin"+num).val(),
						granul: $("#selectGranularite"+num).val()
					});
				}
			}
			// quand on clique sur le bouton "mettre à jour", pour les 2 boutons
			$(function() {
				$('#update2').click(function() {				
					loadGraphique(2);
				}); 
			});
			$(function() {
				$('#update3').click(function() {				
					loadGraphique(3);
				}); 
			});
			// page onload : on charge tous les graphiques
			loadGraphique(1);
			loadGraphique(2);
			loadGraphique(3);
			// reload le 1er graphique toutes les 60 secondes
			setInterval(function () {
				loadGraphique(1);
			},60000);
		</script>
	
	</div>
</div>
		
</html>
</body>

