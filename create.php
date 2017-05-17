<?php
$db = new SQLite3('mysqlitedb.db');

$db->exec("DROP TABLE IF EXISTS compteur");
$db->exec("DROP TABLE IF EXISTS abeille");

$db->exec('CREATE TABLE IF NOT EXISTS abeille (	idAbeille INT PRIMARY KEY,dateEnregistrement TIMESTRING ,nbEntreesAbeille INT, nbSortiesAbeille INT,NBEFauxBourdon INT,NBSFauxBourdon INT,NBEAbeillePollen INT,compteurEntree INT, compteurSortie INT)');
$db->exec('CREATE TABLE IF NOT EXISTS compteur (idCompteur INT PRIMARY KEY,inTotal INT , outTotal INT)');
$db->exec("INSERT INTO compteur (idCompteur,inTotal,outTotal) VALUES (1,0,0)");

?>
<a href='index.php'>retour</a>

</br> Base cree !!