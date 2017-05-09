<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>One Bee - Accueil</title>
  <link rel="stylesheet" href="flux.css">
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
<body>
<!--<meta http-equiv="refresh" content="1; URL=index.php" />-->
<?php 
session_start();
include 'bandeau.php';
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