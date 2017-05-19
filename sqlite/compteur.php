<?php 
session_start();/*on démare une session pour les varriables de session que l'on va avoir, ici la seule varrible de session que l'on a est la varrible "debut"
dans laquelle on a la date(aaaa-mm-jjThh:mm) à laquelle le compteur doit démarrer*/
$bdd = new SQLite3('mysqlitedb.db');/*on se connecte à la bdd*/
$requete = $bdd->query ('SELECT * FROM compteur');/*on selectionne tous ce que la table compteur contient, soit une ligne*/
$res = $requete->fetchArray();/*et on met le résultat dans la varriable res*/
	if ( isset($_SESSION['debut']))/*si l'utilisateur a renseigné la date à laquelle sont comteur doit démarer*/
	{
		$debut= new DateTime($_SESSION["debut"]);//on change le format de date en Y-m-d H:i
		$debut = $debut->format('Y-m-d H:i');
		
		$requete = $bdd->query ('SELECT * FROM abeille where dateEnregistrement <= "'.$debut.'" order by dateEnregistrement desc');/*on choisit toutes les dates plus pettites ou égale à la date
	renseigné et on met la date la plus résente en première*/
		$res2 = $requete->fetchArray();/*on prend la première ligne ,soit la ligne avec la date la plus récente, et on l met dans la varrible res2*/
		
		echo '
		<div class="containerCompteur">
		  <p>Depuis le '.$debut.' </p>            
		  <table class="table">
			<thead>
			  <tr>
				<th>Entrées</th>
				<th>Sorties</th>
				<th>Différence</th>
			  </tr>
			</thead>
			<tbody>
			  <tr>
				<td>' . ($res['inTotal']-$res2['compteurEntree']) . '</td>
				<td>' . ($res['outTotal']-$res2['compteurSortie']) . '</td>
				<td>'. (($res['inTotal']-$res2['compteurEntree']) - ($res['outTotal']-$res2['compteurSortie'])) . '</td>
			  </tr>
			</tbody>
		  </table>
		</div>';
	}
	else/*si l'utilisateur n'a pas renseigné de date*/
	{
		echo'
		<div class="containerCompteur">          
		  <table class="table">
			<thead>
			  <tr>
				<th>Entrées</th>
				<th>Sorties</th>
				<th>Différence</th>
			  </tr>
			</thead>
			<tbody>
			  <tr>
				<td>' . $res['inTotal'] . '</td>
				<td>' . $res['outTotal'] . '</td>
				<td>'. ($res['inTotal'] - $res['outTotal']) . '</td>
			  </tr>
			</tbody>
		  </table>
		</div>';
	}

	?>
