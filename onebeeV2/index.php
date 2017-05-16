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
<body style="margin-top:80px" >
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
        <li class="active"><a href="index.php">ACCUEIL</a></li>
		  <li><a href="graphe.php">GRAPHIQUE</a></li>
		  <li><a href="flux.php">FLUX VIDEO</a></li>
      </ul>
      <ul class="nav navbar-nav navbar-right">
		  <li><a href="#">REDEMARRER</a></li>
		</ul>
	  </div>
	</nav>
	
	<!-- galerie d'image -->
	<div class="contient">
	<div class="img-responsive">
	<div class="col-md-2"></div>

	  <div  class="col-md-2" id="collection"   >
		<img src="mind.png" alt="mind" id="mind" >
	  </div>
	  <div class="col-md-2"  >
		<img src="onebee.png" alt="onebee" id="onebee" >
	  </div>
	  <div class="col-md-2" id="univsav" >
		<img src="univsavoie.png" alt="univsavoie" id="univ">
		
	  </div>
	</div>
	</div>
	<div class="container"   >
		<h1><p class="text-center" id="texte" ><strong>
		Bienvenue dans l'application One Bee
		</strong></p></h1>
	</div>
	
  
	
</body>
</html>
