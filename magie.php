<?php
	
	if (!empty($_POST["chDeux"]))
	{
		echo $_POST["chDeux"];
		echo ", quel beau mot de passe !";
	}
	else
	{
		echo "Donne-moi ton mot de passe !";
	}
?>