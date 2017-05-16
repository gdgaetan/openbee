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
<body >
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
			<li class="active"><a href="index.php">ACCUEIL</a></li> 
			<li><a href="graphe.php">GRAPHIQUE</a></li>
			<li><a href="flux.php">FLUX VIDEO</a></li>
      </ul>
      <ul class="nav navbar-nav navbar-right">
		  <li><a href="#">REDEMARRER</a></li>
		</ul>
	  </div>
	</nav>
<!--Fin de la barre des menus -->


	<div class="chartTest">
		<div id="chart1"></div>
		
		<!-- des boutons... -->
		<form method="post">
			<!-- format date s'affiche sur Chrome et Edge, pour Firefox on ne voit qu'un champ de texte, format américain "mm/jj/aaaa hh:mm" -->
			<span class="formulaire">
				<label>Date début</label>
				<input type="datetime-local" id="selectDateDebut" placeholder="mm/jj/aaaa hh:mm" />
			</span>
			<span class="formulaire">
				<label>Date fin</label>
				<input type="datetime-local" id="selectDateFin" placeholder="mm/jj/aaaa hh:mm" />
			</span>
			<span class="formulaire">
				<label>Granularité</label>
				<select id="selectGranularite">
					<option value="minute">Minute</option>
					<option value="heure">Heure</option>
					<option value="jour">Jour</option>
					<option value="semaine">Semaine</option>
					<option value="mois">Mois</option>
					<option value="annee">Année</option>
				</select>
			</span>
		</form>
		<button id="update">Mettre à jour</button>

		<script>
			// quand on clique sur le bouton "mettre à jour"
			$(function() {
				$('#update').click(function() {				
					$("#chart1").load("graphiques.php", {
						dateDebut: $("#selectDateDebut").val(),
						dateFin: $("#selectDateFin").val(),
						granul: $("#selectGranularite").val()
					});
				}); 
			});
			// page onload
			$("#chart1").load("graphiques.php");
		</script>
	
	</div>
</div>
		
</html>
</body>

