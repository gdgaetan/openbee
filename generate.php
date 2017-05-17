<!DOCTYPE html>
<?php
	echo "Generating...";
	
		$db =  new SQLite3('mysqlitedb.db');

        echo "working... <br/>";
		$db->exec("DELETE FROM abeille");
		//$requete = $bdd->query ('SELECT * FROM compteur');
		//$res = $requete->fetch();
		$totalIn = 0; //$res['inTotal'];
		$totalOut = 0; //$res['outTotal'];
		$ladate = date("d-m-Y H:i");
		echo "$ladate  <br/>";
        for ($i = 0; $i < 500; $i++){
            $abeilleIn = rand(0,50);
            $abeilleOut= rand(0,50);
            $bourdonIn= rand(0,50);
            $bourdonOut= rand(0,50);
            $pollenIn= rand(0,50);
            $totalIn = $abeilleIn + $bourdonIn + $pollenIn + $totalIn;
            $totalOut = $abeilleOut + $bourdonOut + $totalOut;
			$db->exec("INSERT INTO abeille (idAbeille, dateEnregistrement, nbEntreesAbeille, nbSortiesAbeille, NBEFauxBourdon, NBSFauxBourdon, NBEAbeillePollen, compteurEntree, compteurSortie)
				VALUES ($i,datetime('now','localtime','+$i minutes'), $abeilleIn, $abeilleOut, $bourdonIn, $bourdonOut, $pollenIn, $totalIn, $totalOut)");
			$db->exec("UPDATE compteur SET inTotal = ".$totalIn.", outTotal = ".$totalOut." WHERE idCompteur = 1");
         
        }
        echo "done. <br/>";

        //header('location:index.php');
?>		

