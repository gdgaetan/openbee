<!DOCTYPE html>
<head>
  <title>One Bee - Graphique</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="bootstrap.min.css" rel="stylesheet">
  <meta charset="utf-8" />
	<meta name="description" content="Quelle belle description !" />
	<meta name="author" content="Solène Demars" />
	<!-- Shortcut Icon -->
	<!--<link rel="icon" type="image/png" href="img/favicon.png" />-->

	<link href="graphe.css" rel="stylesheet" type="text/css" />
	<script src="highcharts.js"></script>
	<script src="graphiques.js"></script>
	<script src="exporting.js"></script>
	<script src="canvas-tools.js"></script>
	<script src="jspdf.min.js"></script>
	<script src="highcharts-export-clientside.js"></script>

	<title>Test pour les graphiques</title>	
</head>
<body style="marging-top :100 px">

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

<html lang="fr">
					
			<div class="chartTest">
				<div id="chart1"></div>
					
				
				<!-- des boutons... -->
				<form method="post">
					<!-- format date s'affiche sur Chrome et Edge, pour Firefox on ne voit qu'un champ de texte... format à préciser -->
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
				</form>
				<input type="submit" name="update" value="Mettre à jour" />
				
				
				<script>
					// mettre à jour les graphiques
					$(function() {
						$('#update').click(function() {
							alert("click OK");
							$("#chart1").load("graphiques.php", {
								selectDateDebut:$("selectDateDebut").val(),
								selectDateFin:$("selectDateFin").val(),
								selectGranularite:$("selectGranularite").val()
							});
						}); 
					});
					
					// page onload
					$("#chart1").load("graphiques.php", {
						selectDateDebut:$("selectDateDebut").val(),
						selectDateFin:$("selectDateFin").val(),
						selectGranularite:$("selectGranularite").val()
					});
				</script>

			</div>
		
		</div>
		


</html>
</body>