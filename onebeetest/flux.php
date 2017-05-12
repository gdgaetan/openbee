<!DOCTYPE html>
<html lang="fr">
<head>
  <title>One Bee - Flux video</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="bootstrap.min.css" rel="stylesheet">
  <link href="flux.css" rel="stylesheet">
  <script src="jquery-3.2.1.min.js"></script>
  <script src="bootstrap.min.js"></script>
  <script type="text/javascript">
		function writediv(texte, endroit)
		{
		document.getElementById(endroit).innerHTML = texte;
		}
		function afficher()
		{
		if(texte = file('compteur.php'))
		{
		 writediv('<p align="left">'+texte+'</p>', 'compteur');
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
		setInterval('afficher()', 500); // nombre de milisecondes entre deux rafraichissements : ici 10 secondes
  </script>
</head>
<body style="margin-top:80px">

		<nav class="navbar navbar-inverse navbar-fixed-top">
	  <div class="container-fluid">
		<div class="navbar-header">
		 <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>                        
      </button>
      <a class="navbar-brand" href="index.php">One Bee</a>
    </div>
    <div class="collapse navbar-collapse" id="myNavbar">
      <ul class="nav navbar-nav">
          <li><a href="index.php">ACCUEIL</a></li>
		  <li><a href="graphe.php">GRAPHIQUE</a></li>
		  <li class="active"><a href="flux.php">FLUX VIDEO</a></li>
      </ul>
      <ul class="nav navbar-nav navbar-right">
		  <li><a href="#">REDEMARER</a></li>
		</ul>
	  </div>
	</nav>

	<?php 
	session_start();
	if(!empty($_POST['debut']))
	{
		$_SESSION['debut'] = $_POST['debut'];
	}
	else
	{
		unset($_SESSION['debut']);
	}
	// Permet d'afficher le flux vidéo de motion sur une page web.
	// N'oubliez pas de configurer l'adresse IP et le port correctement.
	?>
	<img alt="http://127.0.0.1:8081/" src="http://127.0.0.1:8081/">
	<form action="flux.php" method="post">
	date de début (aaaa-mm-jjThh:mm)</br>
	<input type="datetime-local" name="debut">
	<button type="submit">changer</button>
	</form>
	<div id="compteur">
	</div>

</body>
</html>
