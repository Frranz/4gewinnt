<html>
<head>
<title>4 gewinnt</title>
<link rel="stylesheet" href="http://localhost/4gewinnt/html/game.css">
<script src="http://localhost/4gewinnt/html/game.js"></script>
</head>
<body>
	<div class="header">
		<h1>4 gewinnt online!</h1>
	</div>
	<div class="body">
		<button class="startButton" id="startButton" onclick="startGame();">New Game</button>
		<div class="game">
			<h1 class="recentPlayer" id="recentPlayer">Mach was!</h1>
			<table id="boardTable" class="boardTable"></table>
		</div>
	</div>
	<button onclick="test();">clear curentGameId Row</button>
	<button onclick="test2();">test2</button>
	<button onclick="deleteGames();">delete allGames</button>
	<button onclick="reloadCurrentGame();">load currentGame</button>
	
	
	<div id="myModal" class="modal">
	  <div class="modal-content">
		<span class="close">&times;</span>
		<form method="dialog" >
			<h1>Wähle ein Design:</h1>
			<section id="optionMenu">
				<input type="radio" name="theme" value="1"><div class="circle theme1Pl1"></div><div class="circle theme1Pl2"></div><br/><br/>
				<input type="radio" name="theme" value="2"><div class="circle theme2Pl1"></div><div class="circle theme2Pl2"></div><br/><br/>
				<input type="radio" name="theme" value="3"><div class="circle theme3Pl1"></div><div class="circle theme3Pl2"></div><br/><br/>
			</section>
			<menu>
			  <button id="submitTheme" type="submit">Bestätigen</button>
			</menu>
		<form>
	  </div>
	</div>
<!--	<dialog id="themeDialog">
		<form method="dialog" >
			<h1>Wähle ein Design:</h1>
			<section id="optionMenu">
				<input type="radio" name="theme" value="1"><div class="circle theme1Pl1"></div><div class="circle theme1Pl2"></div></br></br>
				<input type="radio" name="theme" value="2"><div class="circle theme2Pl1"></div><div class="circle theme2Pl2"></div></br></br>
				<input type="radio" name="theme" value="3"><div class="circle theme3Pl1"></div><div class="circle theme3Pl2"></div></br></br>
			</section>
			<menu>
			  <button id="cancelDialog" type="reset">Abbrechen</button>
			  <button id="submitTheme" type="submit">Bestätigen</button>
			</menu>
		<form>
	</dialog>-->
</body>
</html>
<?php
	session_start();
	echo $_SESSION['playerId'];
	print_r($_SESSION);
?>