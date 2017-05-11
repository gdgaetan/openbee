<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>One Bee - Accueil</title>
  <link rel="stylesheet" href="graphe.css">
  <script src="script.js"></script>
</head>
<body>
<?php include 'bandeau.php'; ?>

<form action="graphe.php" method="post">

échelle : 
<select>
  <option value="minute">minute</option>
  <option value="heure">heure</option>
  <option value="jour">jour</option>
  <option value="semaine">semaine</option>
  <option value="mois">mois</option>
  <option value="annee">annee</option>
</select>

date de début : 
<input type="date" name="debut">
<input type="time" name="debut">


date de fin : 
<input type="date" name="fin">
<input type="time" name="fin">

<INPUT TYPE="submit" NAME="soumission" VALUE=" Afficher ">


</form>
</body>
</html>