<!DOCTYPE html>
<html lang="fr">
<head>
  <title>One Bee - Accueil</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="bootstrap.min.css" rel="stylesheet">
  <link href="index.css" rel="stylesheet">
  <script src="jquery-3.2.1.min.js"></script>
  <script src="bootstrap.min.js"></script>
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
	<!-- galerie d'image contenant 5 colonnes -->
	<div class="contient">
	<div class="img-responsive">
	<!-- colonne n°1 :vide, permet de décaler l'affichage vers la droite pour le centrer-->
	<div class="col-md-2"></div>
	<!-- colonne n°2 :image mind -->
	  <div  class="col-md-2" id="collection"   >
		<img src="mind.png" alt="mind" id="mind" >
	  </div>
	<!-- colonne n°2 :image onebee -->
	  <div class="col-md-2"  >
		<img src="onebee.png" alt="onebee" id="onebee" >
	  </div>
	<!-- colonne n°2 :image de l'université de savoie -->
	  <div class="col-md-2" id="univsav" >
		<img src="univsavoie.png" alt="univsavoie" id="univ">
		
	  </div>
	</div>
	</div>
	<!-- container : classe bootstrap permettant d'ajuster la taille du texte avec les balises h1 et de le mettre en gras avec la balise strong -->
	<div class="container">
		<h1><p class="text-center" id="texte" ><strong>
		Bienvenue dans l'application One Bee
		</strong></p></h1>
	</div>
	
  
	
</body>
</html>
