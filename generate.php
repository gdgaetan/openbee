<!DOCTYPE html>
<?php
echo "Generating...";
include "onebee/connection.php";

        echo "working... <br/>";
        for ($i = 0; $i < 1000; $i++){
            $in = rand(0,50);
            $out= rand(0,50);
            $total = $in + $out;
            if ($bdd->query("INSERT INTO onebee.abeille (idAbeille, dateEnregistrement, nbEntrees, nbSorties, compteur) VALUES (NULL, DATE_ADD(NOW(), INTERVAL $i MINUTE), $in, $out, $total);") === TRUE) {
                echo "New record created successfully";
            }else{
		    print_r($bdd->errorInfo());
	    }
	}

echo "done. <br/>";

header('location:index.php');
?>		

