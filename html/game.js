var boardH = 6;
var boardW = 7;

console.log("loaded game.js");

/*
var board = '[[0,0,0,0,0,0,0],' + 	
	'[0,0,0,0,0,0,0], '+
	'[0,0,0,0,0,0,0], '+
	'[0,0,0,1,0,0,0], '+
	'[0,0,2,2,1,0,0], '+
	'[0,0,1,2,1,1,0] '+
']';
	
var boardJson = JSON.parse(board);*/

var oldBoardStateHash = -1;
	
/*function getBoardState(){

	
	var boardEl = document.getElementById("boardTable");
	var row;
	var td;
	var stone;
	for(var i = 0;i<boardJson.length;i++){
		row = document.createElement("tr");
		
		for(var j = 0;j<boardJson[i].length;j++){
			td = document.createElement("td");
			stone = document.createElement("div");
			stone.classList.add("stone");
			stone.classList.add("stoneP0");
			stone.classList.add("stoneP"+boardJson[i][j]);
			
			
			td.appendChild(stone);
			row.appendChild(td);
		}
		boardEl.appendChild(row);
	}
}*/

function startGame(){
	var startButton = document.getElementById("startButton");
	var modal = document.getElementById('myModal');
	var span = document.getElementsByClassName("close")[0];
	console.log("startGame");
	startButton.disabled = true;
	modal.style.display = "block";
	
	window.onclick = function(event) {
		if (event.target == modal) {
			modal.style.display = "none";
			startButton.disabled = false;
		}
	}
	
	span.onclick = function(){
		modal.style.display = "none";
		startButton.disabled = false;
	};
/*	document.getElementById("themeDialog").showModal();
	document.getElementById("cancelDialog").onclick = function(){
		startButton.disabled = false;
	}*/
	
	document.getElementById("submitTheme").onclick = function(e){
		var selectedTheme = document.querySelector('input[name="theme"]:checked').value;
		modal.style.display = "none";
		
		request("gameLogic.php?action=createNewGame&theme="+selectedTheme,"GET",null,function(status,xhttp){
		if(status==200){
			console.log("status on startGame: "+status);
			resJson = JSON.parse(xhttp.responseText);
			console.log(resJson);
			gameLoop(resJson);
		}else{
			startButton.disabled = false;
			alert(xhttp.status+": "+xhttp.responseText);
			//was passiert, wenn kein neues spiel erstellt werden kann
		}
	});
	}
}

function test(){
	console.log("Test");
	request("gameLogic.php?test","GET",null,function(status,xhttp){
		if(status==200){
			alert("deleted currentGameId for all");
		}
	});
}

function test2(){
	request("gameLogic.php?joinGame=24","GET",null,function(status,xhttp){
		if(status==200){
			resJson = JSON.parse(xhttp.responseText);			
			console.log(resJson);
			gameLoop(resJson);
		}else{
			alert(xhttp.status+": "+xhttp.responseText);
		}
	});
}

function deleteGames(){
	request("gameLogic.php?test2","GET",null,function(status,xhttp){
		if(status==200){
			alert("alles gelöscht");
		}else{
			alert(xhttp.status+": "+xhttp.responseText);
		}
	});
}

function setPiece(col){
	var boardEl = document.getElementById("boardTable");
	request("gameLogic.php?setPiece&col="+col,"GET",null,function(status,xhttp){
		if(status==200){
			console.log("piece was set at:"+xhttp.responseText+"|"+col);
		}else{
			alert(xhttp.responseText);
		}
	})
	
/*	for(var i = -1;i<boardH-1;i++){
		if(boardJson[i+1][col]!=0){
			break;
		}
	}
	
	if(i===-1){
		alert("Reihe voll");
		console.log("Reihe voll illegal move");
	}else{
		boardJson[i][col] = playerNumber;
		boardEl.childNodes[i].childNodes[col].firstChild.classList.add("stoneP"+playerNumber);
		console.log("put piece at column: "+col+"|row:"+i);
	}*/
}

function mouserOverCol(e){
	var boardEl = document.getElementById("boardTable");
	var col = Array.from(e.currentTarget.parentElement.children).indexOf(e.currentTarget); 
	
	for(var i = 0;i<boardH;i++){
		boardEl.childNodes[i].childNodes[col].classList.add("columnMarked");
	}
	
	console.log("marked column "+col);
}

function mouseOutCol(e){
	var boardEl = document.getElementById("boardTable");
	var col = Array.from(e.currentTarget.parentElement.children).indexOf(e.currentTarget); 
	
	for(var i = 0;i<boardH;i++){
		boardEl.childNodes[i].childNodes[col].classList.remove("columnMarked");
	}
	
	console.log("demarked column "+col);
}

function selectColumn(e){
	//index der spalte
	console.log("spalte: "+index+"wurde geclicked");
	
	var index = Array.from(e.currentTarget.parentElement.children).indexOf(e.currentTarget); 
	setPiece(index);
}

function gameLoop(state){
	var gameState = state;
	updateBoard(gameState);
	var loop = setInterval(function(){
		updateGameState(loop);
	},500);
}

function updateBoard(gameState){
	//check if state of game has changed
	if(gameState.stateHash != oldBoardStateHash){
		var boardEl = document.getElementById("boardTable");
		boardEl.innerHTML = "";
		initializeBoard(gameState);
		oldBoardStateHash = gameState.stateHash;
	}

}

function updateGameState(loop){
	console.log("trying to update gameState");
	
	request("gameLogic.php?getGameState","GET",null,function(status,request){
		if(status == 200){
			console.log(request.responseText);
			resJson = JSON.parse(request.responseText);
			board = resJson.board;
			if(resJson.gameOver){
				clearInterval(loop);
				updateBoard(resJson);
				alert(resJson.winner+" hat das Spiel gewonnen");
				document.getElementById("recentPlayer").innerHTML = "GameOver!"
				document.getElementById("startButton").disabled = false;
			
			}else{
				if(resJson.youreNext){
					updateBoard(resJson);
				}else{
					updateBoard(resJson);
				}
			}
		}else{
			console.log(status+": "+request.responseText);
		}
		
	});
}

function initializeBoard(gameState){
	var boardEl = document.getElementById("boardTable");
	var board = JSON.parse(gameState.board);
	var nextP = gameState.youreNext;
	var interactiveBoard = (gameState.youreNext>0)?true:false;
	var row;
	var td;
	var stone;
	
	for(var i = 0;i<board.length;i++){
		row = document.createElement("tr");
		
		for(var j = 0;j<board[i].length;j++){
			td = document.createElement("td");
			if(interactiveBoard){
				td.onmouseover = mouserOverCol;
				td.onclick = selectColumn;
				td.onmouseout = mouseOutCol;
				
				document.getElementById("recentPlayer").innerHTML = "Du bist am Zug";
			}else{
				document.getElementById("recentPlayer").innerHTML = "Warten auf Gegner...";
			}
			
			stone = document.createElement("div");
			stone.classList.add("stone");
			stone.classList.add("stoneP0");
			stone.classList.add("theme"+gameState.theme+"Pl"+board[i][j]);
			
			
			td.appendChild(stone);	
			row.appendChild(td);
		}
		boardEl.appendChild(row);
	}
}

function loadDialog(){
	
}

function request(url,method,dataIfPost,callback){
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
	if (this.readyState == 4) {
		callback(this.status,xhttp);
	}
	};
	xhttp.open(method, url, true);
	xhttp.send(dataIfPost);
}
