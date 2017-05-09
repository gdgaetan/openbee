<?php 
session_start();
include 'connection.php';
?>
<div id="compteur">
<?php
if ( isset($_SESSION['debut']))
{
	$requete = $bdd->query ('SELECT * FROM compteur');
	$res = $requete->fetch();
	$requete = $bdd->query ('SELECT * FROM abeille where dateEnregistrement <= "'.$_SESSION['debut'].'" order by dateEnregistrement desc');
	$res2 = $requete->fetch();
	echo 'Depuis le '.$_SESSION['debut'].' il y a ' . ($res['inTotal']-$res2['compteurEntree']) . ' entrées et ' . ($res['outTotal']-$res2['compteurSortie']) . ' sorties et donc '. (($res['inTotal']-$res2['compteurEntree']) - ($res['outTotal']-$res2['compteurSortie'])) .' de différence.';
}
else
{
	$requete = $bdd->query ('SELECT * FROM compteur');
	$res = $requete->fetch();
	echo 'Il y a ' . $res['inTotal'] . ' entrées et ' . $res['outTotal'] . ' sorties et donc '. ($res['inTotal'] - $res['outTotal']) .' de différence.';
}

?>
</div>