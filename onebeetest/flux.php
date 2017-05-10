<!DOCTYPE html>
<html lang="fr">
<head>
  <title>One Bee - Accueil</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
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
      <span class="navbar-brand">One Bee</span>
    </div>
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

<!--<meta http-equiv="refresh" content="1; URL=index.php" />-->
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
?>
<form action="flux.php" method="post">
<input type="datetime-local" name="debut">
<button type="submit">changer</button>
</form>
<div id="compteur">
</div>

</body>
</html>
