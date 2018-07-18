<?php
	$board = [	[0,0,0,0,0,0,0],
				[0,0,0,0,0,0,0],
				[0,0,0,0,0,0,0],
				[0,0,1,0,1,0,0],
				[0,0,0,1,0,0,0],
				[0,1,1,1,1,1,0]];
	$playerNumber = 1;
	$row = 3;
	$col = 4;
	
	echo checkWin($board,$row,$col,$playerNumber);


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