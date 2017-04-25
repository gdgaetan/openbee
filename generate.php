<!DOCTYPE html>
<?php
echo "Generating...";

	// connexion à la base de données
	try
	{
		$bdd = new PDO('mysql:host=localhost;dbname=testonebee', 'root', '');
	}
	catch(Exception $e)
	{
		// erreur
		die('Erreur : '.$e->getMessage());
	}

        echo "working... <br/>";
        for ($i = 0; $i < 100000; $i++){
            $in = rand(0,50);
            $out= rand(0,50);
            $total = $in + $out;
            if ($bdd->query("INSERT INTO testonebee.abeille (idAbeille, dateEnregistrement, nbEntrees, nbSorties, compteur) VALUES (NULL, DATE_ADD(NOW(), INTERVAL $i MINUTE), $in, $out, $total);") === TRUE) {
                echo "New record created successfully";
            }
        }

        echo "done. <br/>";

        //header('location:index.php');
?>		

