<html>
<head>
<title>4 gewinnt</title>
<link rel="stylesheet" href="game.css">
<script src="game.js"></script>
</head>
<body>
	<div class="header">
		<h1>4 gewinnt online!</h1>
	</div>
	<div class="body">
		<button class="startButton" id="startButton" onclick="startGame();">New Game</button>
		<div class="game">
			<h1 class="recentPlayer" id="recentPlayer">Du bist am Zug</h1>
			<table id="boardTable" class="boardTable"></table>
		</div>
	</div>
	<button onclick="test();">test</button>
</body>
</html>
<?php
	session_start();
	echo $_SESSION['playerId'];
	print_r($_SESSION);
?>