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
		echo '
		<div class="container">
		  <p>Depuis le '.$_SESSION['debut'].':</p>            
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
	else
	{
		$requete = $bdd->query ('SELECT * FROM compteur');
		$res = $requete->fetch();
		echo'
		<div class="container">          
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
</div>