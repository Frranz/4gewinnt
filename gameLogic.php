<?php
	$servername = 'localhost';
	$username = 'root';
	$password = '';
	$db_name = '4gewinnt';
	session_start();
	
	$json = '{newGame:';
	
	if(isset($_GET['createNewGame'])){
		$my_db = mysqli_connect($servername,$username,$password,$db_name) or die("db connection konnte nicht hergestellt werden");
		if(!$my_db->connect_error){
			$query = "SELECT currentGameId FROM PLAYERS WHERE playerId=".mysqli_real_escape_string($my_db,$_SESSION['playerId']);
			$result = $my_db->query($query);
			$result = mysqli_fetch_assoc($result);
			if($result['currentGameId']){//==0){
				echo createNewGame();
				echo "creating new game";
			}else{
				echo "user already in game ".$result['currentGameId'];
			}
		}else{
			print_r($my_db->connect_error);
			echo "no connection error";
		}
	}else{
		echo "createnewgame is not set";
	}
	
	function createNewGame(){
		$servername = 'localhost';
		$username = 'root';
		$password = '';
		$db_name = '4gewinnt';
		
		$my_db = mysqli_connect($servername,$username,$password,$db_name) or die("db connection konnte nicht hergestellt werden");
		
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
		
		$retString = "{board:$board,youreNext:-1}";
		return $retString;
	}
?>
