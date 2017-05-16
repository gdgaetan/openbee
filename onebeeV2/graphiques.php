<!-- balise qui va devenir le graphique -->
<div id="myChart" style="width:100%; height:400px;"></div>

<?php

// connexion à la base de données
include 'connexion.php';

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

// avec le formulaire rempli, on modifie les variables associées
// sinon on garde les valeurs par défaut
if(!empty($_POST["dateFin"]) && !empty($_POST["dateDebut"]))
{
	$dateFin = new DateTime($_POST["dateFin"]);
	$dateDebut = new DateTime($_POST["dateDebut"]);
}
if (!empty($_POST["granul"]))
{
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

// la fonction est décrite juste après
getDonnees($debut, $fin, $granularite);



/**
récupérer les données dans la bdd et afficher les graphiques avec ces nouvelles données
il faut fournir des dates dans le même format que dans une requête MySQL
la granularité est une chaîne de caractères

pour rajouter les abeilles pollen/faux bourdon, les lignes de code nécessaires sont déjà présentes, mais commentées
il faudra aussi apporter des modifications à graphiques.js
*/
function getDonnees($dateDebut, $dateFin, $granularite){
	global $bdd;
	// récupérer les données :
	//$reponse = $bdd->query('SELECT * FROM abeille'); // récupère toutes les données
	//$reponse = $bdd->query("SELECT * from abeille WHERE dateEnregistrement BETWEEN '2017-03-21 11:00:00' AND '2017-03-21 15:00:00'"); // exemple avec les dates "en dur"
	$reponse = $bdd->query("SELECT * from abeille WHERE dateEnregistrement BETWEEN '".$dateDebut."' AND '".$dateFin."'");
	
	// tableaux des données à fournir pour le graphique
	$dates = array(); // pour les valeurs en x
	$entrees = array(); // nb d'abeilles entrées dans la ruche, par unité de temps
	$sorties = array(); // nb d'abeilles sorties de la ruche, par unité de temps
	/*$entreesPollen = array();
	$sortiesPollen = array();
	$entreesFauxBourdon = array();
	$sortiesFauxBourdon = array();*/
	
	$index = -1; // pour regrouper les données ensemble
	
	// pour manipuler les données selon la granularité choisie
	$currDate; // date courante
	$nextDate = new DateTime($dateDebut); // prochaine date, pour savoir quand il faudra créer un "nouveau point"
	$intervalle; // dépend de la granularité, durée entre 2 points
	
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
			}
	}
		
	if ($parDefaut == false) // pas besoin si granularité = minute
	{
		// on n'a pas forcément des données au début de l'intervalle demandé
		// utile pour la première itération du while
		$vraieDateDebutTrouvee = false;
		// on a besoin d'une nouvelle 'prochaine date'
		// on a besoin de la trouver en début de boucle, selon la nouvelle date courante
		$trouverProchaineDate = false;
		
		// on récupère les données une par une
		while ($donnees = $reponse->fetch())
		{				
			// date courante récupérée par la requête
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
				// prochaine date = date courante + intervalle
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
	
	// pour avoir des tableaux reconnus par JavaScript
	$datesChart = "[\"".implode("\",\"",$dates)."\"]";
	$entreesChart = "[".implode(",",$entrees)."]";
	$sortiesChart = "[".implode(",",$sorties)."]";
	/*$entreesPollenChart = "[".implode(",",$entrees)."]";
	$sortiesPollenChart = "[".implode(",",$sorties)."]";
	$entreesFauxBourdonChart = "[".implode(",",$entrees)."]";
	$sortiesFauxBourdonChart = "[".implode(",",$sorties)."]";*/
	$entreesSortiesChart = "[".implode(",",$entreesSorties)."]";
	$courbesChart = "[".$entreesChart.",".$sortiesChart.","/*.$entreesPollenChart.",".$sortiesPollenChart.",".$entreesFauxBourdonChart.",".$sortiesFauxBourdonChart.","*/.$entreesSortiesChart."]";
	
	// appel de la fonction pour dessiner les graphes
	// fonction JavaScript, dans le fichier graphiques.js
	echo '<script>drawChart(chart'.$_POST["id"].', '.$datesChart.', '.$courbesChart.');</script>';
	
	// fin de la communication avec la base de données
	$reponse->closeCursor();
}
?>