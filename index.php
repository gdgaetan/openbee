<?php
$dbname='base';
if(!class_exists('SQLite3'))
  die("SQLite 3 NOT supported.");

$base=new SQLite3($dbname, 0666);
echo "SQLite 3 supported."; 

?>
</br>
<a href='create.php'>creer la base</a>
<a href='generate.php'>generer bdd</a></br>
<a href='flux.php'>flux</a></br>
<a href='modif.php'>modif</a></br>
<a href='graphe.php'>graphe</a></br>
</br>