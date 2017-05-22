<?php 
session_start();/*on démare une session pour les varriables de session que l'on va avoir*/
include 'connexion.php';/*on ajoute le code qui nous permet de nous loguer à la bdd*/
$requete = $bdd->query ('SELECT * FROM compteur');/*on selectionne tous ce que la table compteur contient, soit une ligne*/
$res = $requete->fetch();/*et on met le résultat dans la varriable res*/
$requete = $bdd->query ('SELECT * FROM abeille where dateEnregistrement <= "'.$_SESSION['dateCourante'].'" order by dateEnregistrement desc');/*on choisit toutes les dates plus pettites ou égale à la date
	courante et on met la date la plus résente en première*/
$res3 = $requete->fetch();
if ( isset($_SESSION['debut']))/*si l'utilisateur a renseigné la date à laquelle sont comteur doit démarer*/
{
	$debut= new DateTime($_SESSION["debut"]);//on change le format de date en Y-m-d H:i
	$debut = $debut->format('Y-m-d H:i');
	$requete = $bdd->query ('SELECT * FROM abeille where dateEnregistrement <= "'.$debut.'" order by dateEnregistrement desc');/*on choisit toutes les dates plus pettites ou égale à la date
	renseigné et on met la date la plus résente en première*/
	$res2 = $requete->fetch();/*on prend la première ligne ,soit la ligne avec la date la plus récente, et on l met dans la varrible res2*/
	echo '
	<div class="containerCompteur"> <!-- classe containerCompteur qui redimensionne, dans flux.css, le tableau créé par la mise en page de bootstrap  -->
	  <!-- mise en page permettent d avoir un jolie tableau grâce à bootstrap -->
	  <table class="table">
		<thead>
		  <tr>
			<th>Date</th>
			<th>Entrées</th>
			<th>Sorties</th>
			<th>Différence</th>
		  </tr>
		</thead>
		<tbody>
		  <tbody>
		  <tr>
			<td></td>
			<td>' . $res['inTotal'] . '</td> <!-- on affiche le nombre total d abeille entrente -->
			<td>' . $res['outTotal'] . '</td> <!-- on affiche le nombre total d abeille sortante -->
			<td>'. ($res['inTotal'] - $res['outTotal']) . '</td> <!-- on affiche la différence entre les 2 affichage précédent -->
		  </tr>
		  <tr>
			<td>'.$_SESSION['dateCourante'].'</td><!-- on affiche dapuis quelle date le comteur compte -->
			<td>' . ($res['inTotal']-$res3['compteurEntree']) . '</td> <!-- on affiche la différence entre le nombre total d abeille entrentre et le nombre d abeille entrente avant la date courante -->
			<td>' . ($res['outTotal']-$res3['compteurSortie']) . '</td></td> <!-- on affiche la différence entre le nombre total d abeille sortante et le nombre d abeille sortante avant la date courante -->
			<td>'. (($res['inTotal']-$res3['compteurEntree']) - ($res['outTotal']-$res3['compteurSortie'])) . '</td></td> <!-- on affiche la différence entre les 2 affichage précédent -->
		  </tr>
		  <tr>
			<td>'.$debut.'</td><!-- on affiche dapuis quelle date le comteur compte -->
			<td>' . ($res['inTotal']-$res2['compteurEntree']) . '</td> <!-- on affiche la différence entre le nombre total d abeille entrentre et le nombre d abeille entrente avant la date sélectionné -->
			<td>' . ($res['outTotal']-$res2['compteurSortie']) . '</td></td> <!-- on affiche la différence entre le nombre total d abeille sortante et le nombre d abeille sortante avant la date sélectionné -->
			<td>'. (($res['inTotal']-$res2['compteurEntree']) - ($res['outTotal']-$res2['compteurSortie'])) . '</td></td> <!-- on affiche la différence entre les 2 affichage précédent -->
		  </tr>
		</tbody>
		</tbody>
	  </table>
	</div>';
}
else/*si l'utilisateur n'a pas renseigné de date*/
{
	echo'
	<div class="containerCompteur"> <!-- classe containerCompteur qui redimensionne, dans flux.css, le tableau créé par la mise en page de bootstrap  -->
	  <!-- mise en page permettent d avoir un jolie tableau grâce à bootstrap -->
	  <table class="table">
		<thead>
		  <tr>
			<th>Date</th>
			<th>Entrées</th>
			<th>Sorties</th>
			<th>Différence</th>
		  </tr>
		</thead>
		<tbody>
		  <tr>
			<td></td>
			<td>' . $res['inTotal'] . '</td> <!-- on affiche le nombre total d abeille entrente -->
			<td>' . $res['outTotal'] . '</td> <!-- on affiche le nombre total d abeille sortante -->
			<td>'. ($res['inTotal'] - $res['outTotal']) . '</td> <!-- on affiche la différence entre les 2 affichage précédent -->
		  </tr>
		  <tr>
			<td>'.$_SESSION['dateCourante'].'</td><!-- on affiche dapuis quelle date le comteur compte -->
			<td>' . ($res['inTotal']-$res3['compteurEntree']) . '</td> <!-- on affiche la différence entre le nombre total d abeille entrentre et le nombre d abeille entrente avant la date courante -->
			<td>' . ($res['outTotal']-$res3['compteurSortie']) . '</td></td> <!-- on affiche la différence entre le nombre total d abeille sortante et le nombre d abeille sortante avant la date courante -->
			<td>'. (($res['inTotal']-$res3['compteurEntree']) - ($res['outTotal']-$res3['compteurSortie'])) . '</td></td> <!-- on affiche la différence entre les 2 affichage précédent -->
		  </tr>
		</tbody>
	  </table>
	</div>';
}

?>