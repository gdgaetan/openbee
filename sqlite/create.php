 <!-- script en php à lancer 1 fois pour créer la bdd -->
<?php
//permet de créer la bdd
$db = new SQLite3('mysqlitedb.db');

//permet de créer les tables de la bdd
$db->exec('CREATE TABLE IF NOT EXISTS abeille (	idAbeille INT PRIMARY KEY,dateEnregistrement TIMESTRING ,nbEntreesAbeille INT, nbSortiesAbeille INT,NBEFauxBourdon INT,NBSFauxBourdon INT,NBEAbeillePollen INT,compteurEntree INT, compteurSortie INT)');
$db->exec('CREATE TABLE IF NOT EXISTS compteur (idCompteur INT PRIMARY KEY,inTotal INT , outTotal INT)');
//permet de créer la ligne dans compteur
$db->exec("INSERT INTO compteur (idCompteur,inTotal,outTotal) VALUES (1,0,0)");

?>
<a href='index.php'>retour</a>

</br> Base cree !!