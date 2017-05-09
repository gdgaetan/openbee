<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>One Bee - Calibrage</title>
  <script src="script.js"></script>
</head>
<body>
<?php
exec('D: & \wamp\www\onebee\test.exe');
header("refresh:3;url=index.php");
?>
<h1>Auto-calibrage éffectué</h1>
<p>vous allez être redirigé automatiquement dans 3 secondes. Si vous n'êtes pas redirigé <a href="index.php">cliquez ici</a><p>
</body>
</html>