<?php
$db = new SQLite3('mysqlitedb.db');
$db->exec("replace INTO compteur (idCompteur,inTotal, outTotal) VALUES ('1','400','253') ");
?>
<a href='index.php'>retour</a>

</br> Base modifie !!