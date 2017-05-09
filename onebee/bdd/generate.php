<!DOCTYPE html>
<?php
echo "Generating...";

include 'connection.php';

        echo "working... <br/>";
		$requete = $bdd->query ('SELECT * FROM compteur');
		$res = $requete->fetch();
		$totalIn = $res['inTotal'];
		$totalOut = $res['outTotal'];
        for ($i = 0; $i < 1000; $i++){
            $abeilleIn = rand(0,50);
            $abeilleOut= rand(0,50);
            $bourdonIn= rand(0,50);
            $bourdonOut= rand(0,50);
            $pollenIn= rand(0,50);
            $totalIn = $abeilleIn + $bourdonIn + $pollenIn + $totalIn;
            $totalOut = $abeilleOut + $bourdonOut + $totalOut;
            if ($bdd->query("INSERT INTO onebee.abeille (idAbeille, dateEnregistrement, nbEntreesAbeille, nbSortiesAbeille, NBEFauxBourdon, NBSFauxBourdon, NBEAbeillePollen, compteurEntree, compteurSortie) VALUES (NULL, DATE_ADD(NOW(), INTERVAL $i MINUTE), $abeilleIn, $abeilleOut, $bourdonIn, $bourdonOut, $pollenIn, $totalIn, $totalOut);UPDATE compteur SET inTotal = ".$totalIn.", outTotal = ".$totalOut." WHERE idCompteur = 1;") === TRUE) {
                echo "New record created successfully";
            }
        }

        echo "done. <br/>";

        //header('location:index.php');
?>		

