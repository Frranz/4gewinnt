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
		
		//currentGameId set to 0 for all players
		$my_db = mysqli_connect($servername,$username,$password,$db_name) or die("db connection konnte nicht hergestellt werden");
		$delCurrentGameId = "UPDATE players SET currentGameId=0";
		$result = $my_db->query($delCurrentGameId);
		
		if(!$result){
			print_r($result);
		}
		
		
	}elseif(isset($_GET['test2'])){
		
		//delete all games
		$my_db = mysqli_connect($servername,$username,$password,$db_name) or die("db connection konnte nicht hergestellt werden");
		$delAllGamesQuery = "DELETE FROM games";
		$result = $my_db->query($delAllGamesQuery);
		if(!$result){
			http_response_code(500);
			die("error deleting alll games");
		}
		
		echo "games deleted succesfully";
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
		$gameOver = ($currentGame['gameOver']==0)?False:True;
		$resJson = ["board" => $currentGame['board'],
					"youreNext" => $youreNext,
					"gameOver" => $gameOver,
					"stateHash" => hash("md5",json_encode($currentGame)),
					"theme" => $currentGame['theme']
		];
		
		if($currentGame['gameOver']){
			$winner = getPlayerName($currentGame['winner']);
			if(!$winner){
				$resJson += ["winner" => "jemand"];
			}else{
				$resJson += ["winner" => $winner['name']];
			}
		}
		echo json_encode($resJson);
		
		
	}elseif(isset($_REQUEST['joinGame'])){
		
		
		$gameId = $_REQUEST['joinGame'];
		$result = joinGame($gameId);
		echo $result;
		
	}elseif(isset($_REQUEST['setPiece'])){
		$col = $_REQUEST['col'];
		if(!is_numeric($col)){
			http_response_code(400);
			die("column not send");
		}
		
		$row = setPiece($col);
		echo $row;
		
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
	
	function getPlayerName($playerId){
		$my_db = mysqli_connect($GLOBALS['servername'],$GLOBALS['username'],$GLOBALS['password'],$GLOBALS['db_name']) or die("db connection konnte nicht hergestellt werden");
		if(!$my_db->connect_error){
			$getNameQuery = "SELECT name FROM players WHERE playerId=".$playerId;
			$result = $my_db->query($getNameQuery);
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
		if(isset($_GET['theme'])){
			$theme = htmlspecialchars($_GET['theme']);
		}else{
			$theme = 1;
		}
		
		$query = "INSERT INTO `games` (`board`, `player1`, `player2`, `nextTurn`,`theme`) VALUES ('$board', $playerId, 0, 0,$theme)";
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
		
		$retJson = [	"board"=>$board,
						"youreNext"=>-1,
						"stateHash"=>0
		];
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
	
	function setPiece($col){
		
		//get game id and current game
		$gameId = getCurrentGameIdFromPlayerId();
		if(!$gameId['currentGameId']){
			http_response_code(500);
			die("error getting currentGameId of player");
		}
	
		$gameId = $gameId['currentGameId'];
		$game = getCurrentGame($gameId);
		if(!$game){
			http_response_code(500);
			die("error getting current game of player");
		}
		
		//check if player is on turn
		if($game['nextTurn'] != $_SESSION['playerId']){
			http_response_code(409);
			die("its not your turn");
		}
		
		//set piece on board
		$board = json_decode($game['board']);
		for($i = -1;$i<count($board)-1;$i++){
			if($board[$i+1][$col]!=0){
				break;
			}
		}
			
		if($i==-1){
			http_response_code(409);
			die("column is full");
		}
		
		//set piece on board
		if($game['player1']==$_SESSION['playerId']){
			$nextTurnPlayer = $game['player2'];
			$playerNumber = 1;
			$board[$i][$col] = $playerNumber;
		}else{
			$nextTurnPlayer = $game['player1'];
			$playerNumber = 2;
			$board[$i][$col] = $playerNumber;
		}
		
		//save updated board
		$my_db = mysqli_connect($GLOBALS['servername'],$GLOBALS['username'],$GLOBALS['password'],$GLOBALS['db_name']) or die("db connection konnte nicht hergestellt werden");
		$updateGameQuery = "UPDATE games SET board='".json_encode($board)."',nextTurn=".$nextTurnPlayer." WHERE gameId=".$game['gameId'];
		$result = $my_db->query($updateGameQuery);
		if(!$result){
			http_response_code(500);
			die("error udpating game in database");
		}		
		
		$gameOver = checkWin($board,$i,$col,$playerNumber);
		if($gameOver){
			$gameOverQuery = "UPDATE games SET gameOver=1,nextTurn=0,winner=".$_SESSION['playerId']." WHERE gameId=".$game['gameId'];
			$result = $my_db->query($gameOverQuery);
			
			if(!$result){
				http_response_code(500);
				die("error updating gameOver field");
			}
		}
	}
	
	function checkWin($board,$row,$col,$playerNumber){
		$rowSave = $row;
		$colSave = $col;
		$boardH = count($board);
		$boardW = count($board[0]);
		//actual checkWinAlgorithm
		$streakCounter = 1;
		
		//check horizontal
		//check left
		$col--;
		while($col>-1){
			if($board[$row][$col]!=$playerNumber){
				break;
			}
			$streakCounter ++;
			$col--;
		}
		
		//check right
		$col = $colSave + 1;
		while($col<$boardW){
			if($board[$row][$col]!=$playerNumber){
				break;
			}
			$streakCounter ++;
			$col++;
		}
		
		//check if won
		if($streakCounter>3){
			return True;
		}
		
		
		//check vertical
		$col = $colSave;
		$row = $rowSave;
		$streakCounter = 1;
		
		$row++;
		while($row<$boardH){
			if($board[$row][$col]!=$playerNumber){
				break;
			}
			$streakCounter ++;
			$row++;
		}
		
		//check if won		
		if($streakCounter>3){
			return true;
		}
		
		//check diagonal left up to right down
		$col = $colSave -1;
		$row = $rowSave -1;
		$streakCounter = 1;
		
		//check up left
		while($row>-1 && $col>-1){
			if($board[$row][$col]!=$playerNumber){
				break;
			}
			$streakCounter ++;
			$col--;
			$row--;
		}
		
		//check down right
		$col = $colSave +1;
		$row = $rowSave +1;
		while($row<$boardH && $col<$boardW){
			if($board[$row][$col]!=$playerNumber){
				break;
			}
			$streakCounter ++;
			$col++;
			$row++;
		}
		
		if($streakCounter>3){
			return true;
		}
		
		
		//check diagonal left down to right up
		$col = $colSave -1;
		$row = $rowSave +1;
		$streakCounter = 1;
		
		while($row<$boardH && $col>-1){
			if($board[$row][$col]!=$playerNumber){
				break;
			}
			$streakCounter ++;
			$col--;
			$row++;
		}
		
		
		//check right up
		$col = $colSave +1;
		$row = $rowSave -1;
		
		while($row>-1 && $col<$boardW){
			if($board[$row][$col]!=$playerNumber){
				break;
			}
			$streakCounter ++;
			$col++;
			$row--;
		}
		
		if($streakCounter>3){
			return true;
		}
		
		
		//no win condition achieved
		return False;
	}

?>
