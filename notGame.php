<html>
<head>
</head>
<body>
<a href="game.php">lets start a new game</a>
</body>
</html>
<?php
	session_start();
	$_SESSION['playerId'] = $_GET['newId'];
	print_r($_SESSION);
?>