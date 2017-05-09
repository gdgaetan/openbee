<!DOCTYPE html>
<?php

// connexion à la base de données
try
{
    $bdd = new PDO('mysql:host=localhost;dbname=testonebee', 'onebee', '');
        }
        catch(Exception $e)
        {
            // erreur
            die('Erreur : '.$e->getMessage());
        }

?>

<html lang="fr">
        <head>
                <meta charset="utf-8" />
                <meta name="description" content="Quelle belle description !" />
                <meta name="author" content="Solène Demars" />
                <!-- Shortcut Icon -->
                <!--<link rel="icon" type="image/png" href="img/favicon.png" />-->

                <link href="style.css" rel="stylesheet" type="text/css" />
                <script src="Chart.bundle.min.js"></script>
                <script src="graphiques.js"></script>

                <title>Comment ça marche déjà...</title>	
        </head>

        <body> <!--onload="init_graph()"-->

                <h1>C'est un titre</h1>

                <div class="mainContent">

                        <p>Et ça c'est du contenu.</p>

                        <div class="liste">

                                <h2>Merveilleuse liste</h2>

                                <ul>
                                        <li>Element 1</li>
                                        <li>Element 2</li>
                                        <li>Element 42</li>
                                </ul>

                        </div>

                        <div class="formulaire">

                                <h2>Formulaire extraordinaire</h2>

                                <form method="post" action="index.php">
                                        <p><label>Champ <abbr alt="numéro">n°1</abbr></label> : <input type="text" name="chUn" placeholder="Lui, il sert à rien en fait"/> </p>
                                        <p><label>Champ <abbr alt="numéro">n°2</abbr></label> : <input type="password" name="chDeux" placeholder="Important"/> </p>
                                        <input type="submit" name="valider" value="Ne pas cliquer"/>
                                </form>

<form method="post" action="generate.php">
    <input type="submit" name="generator" value="Generate 10" />
</form>

                                <div class="resultForm">
                                        <p>
<?php

        if(isset($_POST['valider']))
        {
            if (!empty($_POST["chDeux"]))
            {
                echo $_POST["chDeux"];
                echo ", quel beau mot de passe !";
            }
            else
            {
                echo "Donne-moi ton mot de passe !";
            }
        }
?>
                                        </p>

                                </div>
                        </div>

                        <div class="chartTest">
                                <canvas id="myChart"></canvas>	

<?php
        function getDonnees(){
            global $bdd;
            $reponse = $bdd->query('SELECT * FROM abeille');
            $dates = array();
            $entrees = array();
            $sorties = array();



            // changer l'affichage de la date ?
                                                /*for ($i=0; $i<count($dates); $i++)
                                                {
                                                        echo $dates[$i];
                                                }*/

            $datesChart = "[\"".implode("\",\"",$dates)."\"]";
            $entreesChart = "[".implode(",",$entrees)."]";
            $sortiesChart = "[".implode(",",$sorties)."]";
            $courbesChart = "[".$entreesChart.",".$sortiesChart."]";

            echo '<script>drawChart('.$datesChart.', '.$courbesChart.');</script>';

            echo "getDonnees() done";

            $reponse->closeCursor();
        }

        getDonnees(); // page onload
?>		

                                <button id="clickme">Cliquez-moi !</button>

        <script>
        var element = document.getElementById('clickme');

        element.addEventListener('click', function() {
            getDonnees(); // fonction php... TODO
        });
        </script>

                        </div>

                </div>

        </body>

</html>
