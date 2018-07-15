var boardH = 6;
var boardW = 7;

var board = '[[0,0,0,0,0,0,0],' + 	
	'[0,0,0,0,0,0,0], '+
	'[0,0,0,0,0,0,0], '+
	'[0,0,0,1,0,0,0], '+
	'[0,0,2,2,1,0,0], '+
	'[0,0,1,2,1,1,0] '+
']';
	
var boardJson = JSON.parse(board);
	
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
	console.log("startGame");
	startButton.disabled = true;
	request("gameLogic.php?action=createNewGame","GET",null,function(status,xhttp){
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

function test(){
	console.log("Test");
	request("gameLogic.php?test","GET",null,function(status,xhttp){
		if(status==200){
			console.log("alles gut im test");
		}
	});
	//gameLoop();
}

function test2(){
	request("gameLogic.php?joinGame=1296","GET",null,function(status,xhttp){
		if(status==200){
			resJson = JSON.parse(xhttp.responseText);			
			console.log(resJson);
			gameLoop(resJson);
		}else{
			alert(xhttp.status+": "+xhttp.responseText);
		}
	});
}

function setPiece(playerNumber,col){
	var boardEl = document.getElementById("boardTable");
	for(var i = -1;i<boardH-1;i++){
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
	}
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
	var index = Array.from(e.currentTarget.parentElement.children).indexOf(e.currentTarget); 
	setPiece(1,index);
	console.log("spalte: "+index+"wurde geclicked");
}

function gameLoop(state){
	var gameState = state;
	updateBoard(gameState);
	var loop = setInterval(updateGameState,500)
}

function updateBoard(gameState){
	var boardEl = document.getElementById("boardTable");
	boardEl.innerHTML = "";
	initializeBoard(gameState);
}

function updateGameState(){
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
			stone.classList.add("stoneP"+board[i][j]);
			
			
			td.appendChild(stone);	
			row.appendChild(td);
		}
		boardEl.appendChild(row);
	}
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
