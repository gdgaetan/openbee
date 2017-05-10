<!DOCTYPE html>

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
		<script src="jquery-3.2.1.min.js"></script>
		
		<title>Test pour les graphiques</title>	
		
		<!--<script type="text/javascript">
			function writediv(texte, endroit)
			{
				console.log(endroit);
				console.log(texte);
				document.getElementById(endroit).innerHTML = texte;
			}
			function afficher()
			{
				if(texte = file('graphiques.php'))
				{
					writediv(texte, 'chart1');
				}
			}
			function file(fichier)
			{
				if(window.XMLHttpRequest) // FIREFOX
				xhr_object = new XMLHttpRequest();
				else if(window.ActiveXObject) // IE
				xhr_object = new ActiveXObject("Microsoft.XMLHTTP");
				else
				return(false);
				xhr_object.open("GET", fichier, false);
				xhr_object.send(null);
				if(xhr_object.readyState == 4) return(xhr_object.responseText);
				else return(false);
			}
			afficher();
		</script>-->
  
	</head>
	
	<body>
		
		<h1>C'est un titre</h1>
		
		<div class="mainContent">
		
			<p>Et ça c'est du contenu.</p>
			
			<div class="liste">
			
				<h2>Merveilleuse liste</h2>
				
				<ul>
					<li>Element 1</li>
					<li>Element 2</li>
					<li>Element 42</li>
				</ul>
				
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
				<div id="chart1"><p>test</p></div>
				
				<!-- des boutons... -->
				<form method="post">
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
					<button id="update">Mettre à jour</button>
				</form>
				
				<script>
	/*				$(function() {
						$('#update').click(function() {
							console.log("click OK");
							$("#chart1").load("graphiques.php", {
								selectDateDebut:$("selectDateDebut").val(),
								selectDateFin:$("selectDateFin").val(),
								selectGranularite:$("selectGranularite").val()
							});
						}); 

					});/*
					$(function() {
						$('#update').click(afficher()); 
					});*/
$("#chart1").load("graphiques.php", {
								selectDateDebut:$("selectDateDebut").val(),
								selectDateFin:$("selectDateFin").val(),
								selectGranularite:$("selectGranularite").val()
							});
				</script>
				
				<form method="post" action="generate.php">
					<input type="submit" name="generator" value="Generate 100000" />
				</form>
				
			
			</div>
		
		</div>
		
	</body>

</html>
