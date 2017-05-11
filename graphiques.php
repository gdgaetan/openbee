<div id="myChart" style="width:100%; height:400px;"></div>

<?php

// connexion à la base de données
include 'connection.php';


// dès le début, on récupère le contenu des formulaires
// (+ valeurs par défaut, avec isset)

// quel intervalle à afficher ?
// récupérer la date actuelle (date de fin à afficher)
$timezone = new DateTimeZone("Europe/Paris");
$dateFin = new DateTime();
$dateFin->setTimezone( $timezone );

// calculer la (date de début à afficher, par défaut : la dernière heure)
$dateDebut = new DateTime();
$dateDebut->setTimezone( $timezone );
$dateDebut->sub(new DateInterval('PT1H')); // moins 1 heure

$granularite = "minute";

// TEST
//echo "<script>alert(".$_POST["dateFin"].");</script>";
/*echo "<script>console.log('données pour php :');</script>";
foreach ($_POST as $key => $value) {
        echo "<script>console.log(".$key.");</script>";
        echo "<script>console.log(".$value.");</script>";
}*/
/*
echo "données pour php :<br/>";
foreach ($_POST as $key => $value) {
        echo "<tr>";
        echo "<td>";
        echo $key;
        echo "</td>";
        echo "<td>";
        echo $value;
        echo "</td>";
        echo "</tr>";
		echo "<br/>";
}*/


// avec le formulaire rempli
if(!empty($_POST["dateFin"]) && !empty($_POST["dateDebut"]))
{
// pb, il n'aime pas les dates (?)
//echo "<script>console.log('dates OK');</script>";
echo $_POST["dateFin"];
	$dateFin = new DateTime($_POST["dateFin"]);
echo $dateFin->format('Y-m-d H:i');
	//echo "<br/>date fin ";
	//echo $dateFin->format('Y-m-d H:i:s');
	$dateDebut = new DateTime($_POST["dateDebut"]);

}

if (!empty($_POST["granul"]))
	{ //echo "<script>console.log('granularité OK');</script>";
		switch ($_POST["granul"]) {
			case "heure":
				$granularite = "heure";
				break;
			case "jour":
				$granularite = "jour";
				break;
			case "semaine":
				$granularite = "semaine";
				break;
			case "mois":
				$granularite = "mois";
				break;
			case "annee":
				$granularite = "annee";
				break;
			default: // minute
			   $granularite = "minute";
		}
}
// on a besoin de chaînes de caractères
$fin = $dateFin->format('Y-m-d H:i');
$debut = $dateDebut->format('Y-m-d H:i');

echo "<br/>";
echo $fin;
echo "<br/>";
echo $debut;
echo "<br/>";

getDonnees($debut, $fin, $granularite);



/**
récupérer les données dans la bdd et afficher les graphiques avec ces nouvelles données

pour rajouter les abeilles pollen/faux bourdon, les lignes de code nécessaires sont déjà présentes, mais commentées
il faudra apporter des modifications à graphiques.js
*/
function getDonnees($dateDebut, $dateFin, $granularite){
	global $bdd;
	//$reponse = $bdd->query('SELECT * FROM abeille'); // récupère toutes les données
	//$reponse = $bdd->query("SELECT * from abeille WHERE dateEnregistrement BETWEEN '2017-03-21 11:00:00' AND '2017-03-21 15:00:00'"); // exemple avec les dates "en dur"
	$reponse = $bdd->query("SELECT * from abeille WHERE dateEnregistrement BETWEEN '".$dateDebut."' AND '".$dateFin."'");
	
	$dates = array();
	$entrees = array();
	$sorties = array();
	/*$entreesPollen = array();
	$sortiesPollen = array();
	$entreesFauxBourdon = array();
	$sortiesFauxBourdon = array();*/
	
	$index = -1; // pour regrouper les données ensemble
	
	$currDate;
	$currDateGrad = new DateTime($dateDebut);
	$nextDate = new DateTime($dateDebut);
	$intervalle;
	
	// pour la granularité par minute, pas besoin de faire la même chose puisque la base de données devrait être remplie de façon à ce que ça fonctionne (stockage des enrgeistrements par minute, classement dans l'ordre croissant des dates)
	$parDefaut = false;
	
	switch ($granularite) {			
		case "heure":
			//$intervalle = new DateInterval('PT1H'); // 1 heure
			$intervalle = new DateInterval('PT59M'); // 59 minutes
			break;
		case "jour":
			$intervalle = new DateInterval('P1D'); // 1 jour
			//$intervalle = new DateInterval('PT23H59M'); // 23h59
			break;
		case "semaine":
			$intervalle = new DateInterval('P7D'); // 7 jours
			break;
		case "mois":
			$intervalle = new DateInterval('P1M'); // 1 mois
			break;
		case "annee":
			$intervalle = new DateInterval('P1Y'); // 1 an
			break;
		default: // minute
			$parDefaut = true;
			while ($donnees = $reponse->fetch())
			{
				$dates[] = $donnees['dateEnregistrement'];
				$entrees[] = $donnees['nbEntreesAbeille'];
				$sorties[] = $donnees['nbSortiesAbeille'];
				//echo $donnees['dateEnregistrement'] ."<br/>"; //test
			}
	}
		
	if ($parDefaut == false)
	{
		// on n'a pas forcément des données au début de l'intervalle demandé
		// utile pour la première itération du while
		$vraieDateDebutTrouvee = false;
		// on a besoin d'une nouvelle 'prochaine date'
		// on a besoin de la trouver en début de boucle, selon la nouvelle date courante
		$trouverProchaineDate = false;
		
		while ($donnees = $reponse->fetch())
		{				
			$currDate = new DateTime($donnees['dateEnregistrement']);
			
			// il faut trouver la première date courante effective
			// càd la première pour laquelle il y a des données
			if ($vraieDateDebutTrouvee == false)
			{
				// on ne commence pas à la graduation prêt...
				// exemple : on met la date de début à 8h30
				// mais le premier enregistrement est à 8h45
				$nextDate = clone $currDate;
				$vraieDateDebutTrouvee = true;
			}
			
			// on a changé de 'colonne', on a besoin d'une nouvelle 'prochaine date'
			// on ne peut pas le faire l'itération précédente, à la fin de 'if ($currDate >= $nextDate)'
			if ($trouverProchaineDate == true)
			{
				$nextDate = clone $currDate;
				$nextDate = $nextDate->add($intervalle);
				$trouverProchaineDate = false;
			}
			
			// on a dépassé l'intervalle, on passe à un nouveau 'point'
			// on aura besoin de trouver la prochaine date à l'itération suivante, quand on connaîtra la date suivante dans la base de données (puisque les données ne sont pas forcément continues...)
			if ($currDate >= $nextDate) {
				$index++; // nouvelle 'colonne'
				
				$dates[$index] = $donnees['dateEnregistrement'];
				$entrees[$index] = 0;
				$sorties[$index] = 0;
				/*$entreesPollen[$index] = 0;
				$sortiesPollen[$index] = 0;
				$entreesFauxBourdon[$index] = 0;
				$sortiesFauxBourdon[$index] = 0;*/
				
				$trouverProchaineDate = true;
			}
			
			// on rajoute les données
			$entrees[$index] += $donnees['nbEntreesAbeille'];
			$sorties[$index] += $donnees['nbSortiesAbeille'];
			/*$entreesPollen[$index] += $donnees['nbEntreesPollen'];
			$sortiesPollen[$index] += $donnees['nbSortiesPollen'];
			$entreesFauxBourdon[$index] += $donnees['nbEntreesFauxBourdon'];
			$sortiesFauxBourdon[$index] += $donnees['nbSortiesFauxBourdon'];*/
			
		}
	}
	
	// changer l'affichage de la date
	
	// pour l'affichage des mois en toutes lettres, avec gestion des espaces
	$search  = array('-01-', '-02-', '-03-', '-04-', '-05-', '-06-', '-07-', '-08-', '-09-', '-10-', '-11-', '-12-');
	$replace = array(' Jan ', ' Fév ', ' Mar ', ' Avr ', ' Mai ', ' Juin ', ' Juil ', ' Aoû ', ' Sept ', ' Oct ', ' Nov ', ' Dec ');
	for ($i=0; $i<count($dates); $i++)
	{
		// pour que ce soit considéré comme une date, pour le formattage
		$dates[$i] = strtotime($dates[$i]);
		// on change l'ordre des éléments de la date pour respecter l'ordre français, on ne met pas les secondes
		$dates[$i] = date('d-m-Y H:i', $dates[$i]);
		// on remplace le chiffre du mois par son équivalent en lettres
		$dates[$i] = str_replace($search, $replace, $dates[$i]);
	}
	
	// différence E/S
	$entreesSorties = array();
	for ($i=0; $i<count($dates); $i++)
	{
		$entreesSorties[$i] = $entrees[$i] - $sorties[$i];
	}
	
	$datesChart = "[\"".implode("\",\"",$dates)."\"]";
	$entreesChart = "[".implode(",",$entrees)."]";
	$sortiesChart = "[".implode(",",$sorties)."]";
	/*$entreesPollenChart = "[".implode(",",$entrees)."]";
	$sortiesPollenChart = "[".implode(",",$sorties)."]";
	$entreesFauxBourdonChart = "[".implode(",",$entrees)."]";
	$sortiesFauxBourdonChart = "[".implode(",",$sorties)."]";*/
	$entreesSortiesChart = "[".implode(",",$entreesSorties)."]";
	$courbesChart = "[".$entreesChart.",".$sortiesChart.","/*.$entreesPollenChart.",".$sortiesPollenChart.",".$entreesFauxBourdonChart.",".$sortiesFauxBourdonChart.","*/.$entreesSortiesChart."]";
	
	echo '<script>drawChart('.$datesChart.', '.$courbesChart.');</script>';
	
	$reponse->closeCursor();
}
?>