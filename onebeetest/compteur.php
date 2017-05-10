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
	echo 'Depuis le '.$_SESSION['debut'].'</br>';
	echo 'Entrées : ' . ($res['inTotal']-$res2['compteurEntree']) . '</br>Sorties : ' . ($res['outTotal']-$res2['compteurSortie']) . '</br>Différence : '. (($res['inTotal']-$res2['compteurEntree']) - ($res['outTotal']-$res2['compteurSortie']));
}
else
{
	$requete = $bdd->query ('SELECT * FROM compteur');
	$res = $requete->fetch();
	echo 'Entrées : ' . $res['inTotal'] . '</br>Sorties : ' . $res['outTotal'] . '</br>Différence : '. ($res['inTotal'] - $res['outTotal']);
}

?>
</div>