<?php
	$servername = 'localhost';
	$username = 'root';
	$password = '';
	$db_name = '4gewinnt';
	session_start();
	
	if(isset($_GET['action'])){
		
		//erstellt neues spiel
		$result = getCurrentGameIdFromPlayerId();
		if($result['currentGameId']==0){//){
			http_response_code(200);
			header('Content-Type: application/json');
			echo createNewGame();
		}else{
			http_response_code(403);
			echo "user already in game ".$result['currentGameId'];
		}
		
		
	}elseif(isset($_GET['test'])){
		
		
		$my_db = mysqli_connect($servername,$username,$password,$db_name) or die("db connection konnte nicht hergestellt werden");
		$delCurrentGameId = "UPDATE players SET currentGameId=0";
		$result = $my_db->query($delCurrentGameId);
		
		if(!$result){
			print_r($result);
		}
		
		
	}elseif(isset($_GET['getGameState'])){
		
		
		//get game from db and return json to client
		$currentGameId = getCurrentGameIdFromPlayerId();
		if(!$currentGameId){
			die("error getting current gameId from Player");
		}
		$currentGameId = $currentGameId['currentGameId'];
		$currentGame = getCurrentGame($currentGameId);
		if(!$currentGame){
			die("error getting current game");
		}
		
		$youreNext = ($currentGame['nextTurn']==$_SESSION['playerId'])?1:-1;
		$resJson = ["board" => $currentGame['board'],
					"youreNext" => $youreNext,
					"gameOver" => false
		];
		echo json_encode($resJson);
		
		
	}elseif(isset($_REQUEST['joinGame'])){
		
		
		$gameId = $_REQUEST['joinGame'];
		$result = joinGame($gameId);
		echo $result;
		
	}else{
		http_response_code(400);
		echo "action is not set";
	}
	
	function getCurrentGameIdFromPlayerId(){	
		$my_db = mysqli_connect($GLOBALS['servername'],$GLOBALS['username'],$GLOBALS['password'],$GLOBALS['db_name']) or die("db connection konnte nicht hergestellt werden");
		if(!$my_db->connect_error){
			$query = "SELECT currentGameId FROM PLAYERS WHERE playerId=".mysqli_real_escape_string($my_db,$_SESSION['playerId']);
			$result = $my_db->query($query);
			$result = mysqli_fetch_assoc($result);
			return $result;
		}else{
			echo "database connection not available";
			return null;
		}
	}
	
	function getCurrentGame($gameId){
		$my_db = mysqli_connect($GLOBALS['servername'],$GLOBALS['username'],$GLOBALS['password'],$GLOBALS['db_name']) or die("db connection konnte nicht hergestellt werden");
		if(!$my_db->connect_error){
			$query = "SELECT * FROM games WHERE gameId=".$gameId;
			$result = $my_db->query($query);
			$result = mysqli_fetch_assoc($result);
			return $result;
		}else{
			echo "database connection not available";
			return null;
		}
	}
	
	function createNewGame(){		
		$my_db = mysqli_connect($GLOBALS['servername'],$GLOBALS['username'],$GLOBALS['password'],$GLOBALS['db_name']) or die("db connection konnte nicht hergestellt werden");
		
		//add new game to games table
		$board = json_encode([[0,0,0,0,0,0,0],[0,0,0,0,0,0,0],[0,0,0,0,0,0,0],[0,0,0,0,0,0,0],[0,0,0,0,0,0,0],[0,0,0,0,0,0,0]]);
		$playerId = $_SESSION['playerId'];
		$query = "INSERT INTO `games` (`board`, `player1`, `player2`, `nextTurn`) VALUES ('$board', $playerId, 0, 0)";
		$result = $my_db->query($query);
		if(!$result){
			die("insert game into database did not work");
		}
		
		//get game id from newly created game
		$getGameId = "SELECT gameId FROM games WHERE player1=".$_SESSION['playerId'];
		$result = $my_db->query($getGameId);
		if(!$result){
			die("getting gameId from newly created game failed");
		}
		
		$result = mysqli_fetch_assoc($result);
		$gameId = $result['gameId'];
		
		$addCurentGameQuery = "UPDATE players SET currentGameId=$gameId WHERE playerId=".$_SESSION['playerId'];
		$result = $my_db->query($addCurentGameQuery);
		if(!$result){
			die("failed to update currentGameId in player database");
		}
		
		$retJson = ["board"=>$board,
		"youreNext"=>-1];
		return json_encode($retJson);
	}
	
	
	function startGame($gameIdSave){
		$my_db = mysqli_connect($GLOBALS['servername'],$GLOBALS['username'],$GLOBALS['password'],$GLOBALS['db_name']) or die("db connection konnte nicht hergestellt werden");
		
		//get dataset of recently joined game^
		$getGameQuery = "SELECT * FROM games WHERE gameId=".$gameIdSave;
		$game = $my_db->query($getGameQuery);
		if(!$game){
			die("failed to get game @startGame");
		}
		
		//set nextTurn to id of player1
		$game = mysqli_fetch_assoc($game);
		if($game['player1']!="" AND $game['player2']!="" AND $game['nextTurn']==0){
			$setNextTurnQuery = "UPDATE games SET nextTurn=".$game['player1']." WHERE gameId=".$gameIdSave;
			$result = $my_db->query($setNextTurnQuery);
			if(!$result){
				die("error while setting nextTurn at start of game");
			}
		}
	}
	
	
	function joinGame($gameId){
		$my_db = mysqli_connect($GLOBALS['servername'],$GLOBALS['username'],$GLOBALS['password'],$GLOBALS['db_name']) or die("db connection konnte nicht hergestellt werden");
		$gameIdSave = mysqli_real_escape_string($my_db,$gameId);
		
		//check if user is already in another game
		$result = getCurrentGameIdFromPlayerId();
		if($result['currentGameId']){
			http_response_code(403);
			$currentGameId = $result['currentGameId'];
			die("user already in game $currentGameId");
		}
		
		//check if game is already full
		$my_db = mysqli_connect($GLOBALS['servername'],$GLOBALS['username'],$GLOBALS['password'],$GLOBALS['db_name']) or die("db connection konnte nicht hergestellt werden");
		$gameQuery = "SELECT * FROM games WHERE gameId=".$gameIdSave;
		$game = $my_db->query($gameQuery);
		$game = mysqli_fetch_assoc($game);
		if(!$game){
			die("game does not exist (anymore)");
		}
		
		//gamei s full
		if($game['player1']!=0 AND $game['player2']!=0){
			die("game is already full");
		}
		
		$freePlayer;
		//take free spot
		if($game['player1']==0){
			$freePlayer = "player1";
		}else{
			$freePlayer = "player2";
		}
		
		//update currentGameId from joining player
		$addCurentGameQuery = "UPDATE players SET currentGameId=$gameId WHERE playerId=".$_SESSION['playerId'];
		$result = $my_db->query($addCurentGameQuery);
		if(!$result){
			die("failed to update currentGameId in player record");
		}
		
		//give player freePlayer spot
		$addPlayerToGameQuery = "UPDATE games SET ".$freePlayer."=".$_SESSION['playerId']." WHERE gameId=".$gameIdSave;
		$result = $my_db->query($addPlayerToGameQuery);
		if(!$result){
			die("failed to update playerspot in game record");
		}
		
		//prepare return
		$youreNext = ($game['nextTurn']==$_SESSION['playerId'])?1:-1;
		$resJson = ["board" => $game['board'],
					"youreNext" => $youreNext,
					"gameOver" => false
		];
		
		startGame($gameIdSave);
		
		return json_encode($resJson);
	}
	

?>
