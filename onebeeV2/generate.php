<!DOCTYPE html>
<?php
	echo "Generating...";//on affiche "Generating..." quand on arrive sur la page

	include 'connexion.php';//on se connecte à la bdd

        echo "working... <br/>";//une fois la connection effectué on affiche "working..."
		$bdd->query('DELETE FROM abeille');//on vide la table abeille
		// les varriable totalIn et totalOut vont remplacer les valeur corespondante de la seule ligne de la table compteur
		$totalIn = 0;//au début on à aucune abeille entrente
		$totalOut = 0;//au début on à aucune abeille sortente
        for ($i = 0; $i < 2000; $i++){//on génère 2000 ligne pour la table abeille
			//on met un nombre aléatoire dans chaque champ de la ligne
            $abeilleIn = rand(0,50);
            $abeilleOut= rand(0,50);
            $bourdonIn= rand(0,50);
            $bourdonOut= rand(0,50);
            $pollenIn= rand(0,50);
            $totalIn = $abeilleIn + $bourdonIn + $pollenIn + $totalIn;//on met à jour les abeille entrente avec les valeurs de la ligne que l'on vient de générer
            $totalOut = $abeilleOut + $bourdonOut + $totalOut;//on met à jour les abeille sortente avec les valeurs de la ligne que l'on vient de générer
			//on éxécute une requette qui permet d'ajouter notre nouvelle ligne et on test si la requette s'éxécute correctement
            if ($bdd->query("INSERT INTO abeille (idAbeille, dateEnregistrement, nbEntreesAbeille, nbSortiesAbeille, NBEFauxBourdon, NBSFauxBourdon, NBEAbeillePollen, compteurEntree, compteurSortie) VALUES (NULL, DATE_ADD(NOW(), INTERVAL $i MINUTE), $abeilleIn, $abeilleOut, $bourdonIn, $bourdonOut, $pollenIn, $totalIn, $totalOut);UPDATE compteur SET inTotal = ".$totalIn.", outTotal = ".$totalOut." WHERE idCompteur = 1;") === TRUE) {
                echo "New record created successfully";//on affiche le message "New record created successfully" si la requette s'est bien éxécutée
            }
        }

        echo "done. <br/>";//on affiche "done." à la fin du scrip

        header('location:index.php');//on redirige sur l'index
?>		

