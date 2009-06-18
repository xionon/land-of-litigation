<?php
include_once("conn.inc");
//LAST UPDATED 12-10-05

/*******
Adventure object
Users get assigned an adventure object, which determines if they are going to fight
a monster or participate in an encounter.  Encounters could be traps or random treasures.
Fights are obviously fights with monsters.
*******/

class adventure
{
    var $location = 1;
    var $type = 1;
    var $monster = array();
	var $myAdventure = array();
    var $resource = "";
    var $completed = false;
    var $experience = 0;
    var $gold = 0;
    var $item = 0;
    var $lastMonsterDamage=0;
    
    function adventure($adventureIn, $levelMax)
    {
        $adventureIn = str_replace("_"," ",$adventureIn);
        $query = "SELECT name,location,level FROM places WHERE name='{$adventureIn}'";
        $result = mysql_query($query) or die ("error loading location from database");
        $row = mysql_fetch_array( $result );
        
        $this->location = $row['location'];
        $this->type = $this->rolldice(1,2);
        switch ($this->type)
        {
            case 1:
                //go on an adventure
                $this->newAdventure($levelMax-5);
                break;
            case 2:
                //fight a monster
                $this->monster($levelMax);
                break;
        }
        
    }
	
    function showAdventure()
    {
        $toReturn;
        if ( !$this->isCompleted() )
        {
            switch ($this->type)
            {
                case 1:
                    $toReturn .= "<strong>" . $this->myAdventure['name'] . "</strong> <br />";
					$toReturn .= "<img src=\"{$this->resource}\" /><br />";
					$toReturn .= $this->myAdventure['description'];
                    break;
                
                case 2:
                    $toReturn .= "There is a {$this->monster['monstername']} for you to do glorious battle with!<br/>";
                    $toReturn .= "<img src=\"{$this->resource}\" /><br />";
                    break;
            }
        }
        else
        {
            switch ($this->type)
            {
                case 1:
                    $toReturn .= "<strong>" . $this->myAdventure['name'] . "</strong>";
					$toReturn .= "<img src=\"{$this->resource}\" /><br />";
					$toReturn .= $this->myAdventure['description'];
                    break;
                case 2:
                    $toReturn = "You defeated the monster!<br/>";
                    break;
            }
        }
        return $toReturn;
    }
    
    function complete()
    {
        $this->completed = true;
    }
    
    function fail()
    {
        $this->completed = true;
/*		if ($this->type == 1) {
			$this->myAdventure['rewardItemID'] = 0;
		}
        if ($this->type == 2) {
            $this->monster['item'] = 0;
            $this->monster['gold'] = 0;
        }
*/

		$this->item = 0;
		$this->gold = 0;
        $this->experience = 0;
    }
    
    function getReward()
    {
        $reward = array();
        $reward['experience'] = $this->experience;
        $reward['gold'] = $this->gold;
        $reward['item'] = $this->item;
        return $reward;
    }
    
	function newAdventure($userlevel)
	{
		//Select all adventures for this location
		$query = "SELECT adventureID,name,description,testAgainst,testValue,completeText,failText,rewardExperience,rewardItemID,location,level FROM adventures WHERE location={$this->location}";	
		$result = mysql_query($query) or die ("error finding an adventure");
		//Select a single one to use
		$num = mysql_num_rows($result);
		if ($num > 1 && $num != 0) {
			//If there is more than one adventure for this location in the database, we need to randomly pick one of them
			//Roll a random number between zero and the number of adventures-1.  mysql_data_seek will move the pointer for
			//the next data in the result array to be whatever the die roll is, and then we will fetch the associative array.
			$newnum = $this->rolldice(0, $num-1);
			mysql_data_seek($result, $newnum);
			$keeper = mysql_fetch_assoc($result);
		
		} elseif ($num != 0) {
			$keeper = mysql_fetch_assoc($result);
		}
		$this->myAdventure = $keeper;
/*		
		$levelDiff = $this->myAdventure['level'] - $userlevel;
		$levelMod = $levelDiff * 10;
		$this->experience = max(1, $this->myAdventure['rewardExperience'] + $levelMod);
*/

		$baseExp = $this->myAdventure['rewardExperience'];
		$levelMod = ($this->myAdventure['level'] - $userlevel) * 2;
		$this->experience = max(1, ($baseExp + $levelMod));

		$this->experience = 10;
		if ($this->myAdventure["rewardItemID"] > 0) {
			$this->item = $this->myAdventure["rewardItemID"];
		}
		$this->resource = "images/adventures/{$this->myAdventure['adventureID']}.gif";
	}

    function monster($max)
    {
        //create a new monster
        $query = "SELECT monstername,level,hp,str,dex,gold,item, itemChance FROM monsters WHERE location={$this->location} ORDER BY level";
        $result = mysql_query($query) or die ("error loading monster from database");
        
        //select a monster to use if more than one is found
        if (mysql_num_rows($result)>1)
        {
            while ( $row = mysql_fetch_array( $result ) )
            {
                $keeper=$row;
                $roll = $this->rolldice(1,3);
                //users have a better chance of hitting a lower level monster
                if ($roll < 3)
                    break;
            }
        }
        else
        {
            $keeper = mysql_fetch_array($result);
        }
        $this->monster = $keeper;
		//experience = 10 + the difference in the monsters level and the player's level
		//So a monster with a higher level than the player will give the player more exp
		//and a monster with a lower level than the player will give the player less exp
		//Max is user level + 5

        //$this->experience = max( 1, (10 + ( $this->monster['level'] - ( $max-5 ) ) ) );

		$baseExp = 10;
		$levelMod = ( $this->monster['level'] - ($max-5) ) * 2;
		$this->experience = max(1, $baseExp + $levelMod);
		
        //Determine if user will recieve an item from this monster
        //Roll against itemChance; if lessthan or equal to itemchance, then you get the item
        //Maybe someday luck will play a factor in this...
        $roll = $this->rolldice(1,100);
        if ($roll < $this->monster['itemChance'] || $roll == $this->monster['itemChance']) {
			$this->item=$this->monster['item'];
        } else {
			$this->item = 0;
        }
        
        
        $this->gold = $this->monster['gold'];
        $this->resource = "images/monsters/" . str_replace(" ", "_", $this->monster['monstername']) . ".gif";
    }
    
	function testAdventure($userstats)
	{
		$msg = array();
		switch ($this->myAdventure['testAgainst']) {
			case 'dex':
				if ($userstats['dex'] > $this->myAdventure['testValue']) {
					$this->complete();
					$msg["response"] = "<strong id=\"response\">" . $this->myAdventure["completeText"] . "</strong>";
				} else {
				$this->fail();
				$msg["response"] = "<strong id=\"response\">" . $this->myAdventure["failtext"] . "</strong>";
				}
				break;
			case 'str':
				if ($userstats['str'] > $this->myAdventure['testValue']) {
					$this->complete();
					$msg["response"] = "<strong id=\"response\">" . $this->myAdventure["completeText"] . "</strong>";
				} else {
					$this->fail();
					$msg["response"] = "<strong id=\"response\">" . $this->myAdventure["failtext"] . "</strong>";
				}
				break;
			default:
				$this->complete();
				$msg["response"] = "<strong id=\"response\">" . $this->myAdventure["completeText"] . "</strong>";
				break;
		}
		return $msg;
	}
	
    function performCommand($command,$userstats)
    {
        $msg = array();
        switch ($this->type)
        {
            case 1:
                break; //case 1
            case 2:
                switch ($command)
                {
                    case "Attack":
                        $msg['message'] = $this->attackMonster($userstats);
                        if ($this->isCompleted() == false) {
                            $msg['response'] = "<strong id=\"response\">" . $this->monsterAttacks($userstats) . "</strong>"; 
                            $msg ['damage'] = $this->lastMonsterDamage;
                        }
                        break; //case "Attack"
                    case "Run":
                        $msg['message'] = $this->tryToRun($userstats);
                        if ($this->isCompleted() == false) {
                            $msg['response'] = "<strong id=\"response\">" . $this->monsterAttacks($userstats) . "</strong>"; 
                            $msg ['damage'] = $this->lastMonsterDamage;
                        }
                        break; //case "Run"
                }
                break; //case 2
        }
        return $msg;
    }
    
    function tryToRun ($userstats)
    {
        $msg = "";
        //try to run
        $udex = $userstats['dex'];
        $mdex = $this->monster['dex'];
        if ( $udex > $mdex ) $bonus = $udex - $mdex; else $bonus = 0;
        $roll = $this->rolldice(1,6) + $bonus;
        if ( $roll > 4 ) 
        {
            $msg .= "You ran, you coward.";
            $this->fail(); 
        }
        else
        {
            $msg .= "You failed to run!";
        }
        return $msg;
    }

    function monsterAttacks ($userstats)
    {
        $msg = "";
        if ($this->rollToHit( $this->monster['dex'], $userstats['dex'] ) ) 
        {
                $this->lastMonsterDamage = $this->rollDamage( $this->monster['str'], $userstats['str'] );
                $msg .= "<br/>The {$this->monster['monstername']} hits for {$this->lastMonsterDamage} damage!";
        } else $msg .= "<br/>The {$this->monster['monstername']} misses!";
        return $msg;
    }
  
    function attackMonster($userstats)
    {
        $msg .= "";
        //attack the monster
        for ($classAttacks = $userstats['attacks']; $classAttacks > 0 && $this->isCompleted() == false; $classAttacks--)
        {
            //if the user manages to hit the monster, roll for damage
            if( $this->rollToHit( $userstats['dex'], $this->monster['dex'] ) ) 
            {
                $damage = $this->rollDamage( $userstats['str'],$this->monster['str'] );
                $this->monster['hp'] -= $damage;
                $msg .= "<br/>You hit for {$damage} damage!";
                if ($this->monster['hp'] <= 0)
                {
                    $msg .= "<br/>You killed your enemy!";
                    $this->complete();
                }
            }
            else $msg .= "<br/>You miss!";
        }
        return $msg;
    }

    function rollToHit( $aDex, $dDex )
    {
        //aDex = attacker's dexterity, dDex = defender's dexterity
        //boolean
        if ( $aDex > $dDex ) $bonus = $aDex - $dDex; else $bonus = 0;
        $roll = $this->rolldice(1,6) + $bonus;
        if ($roll > 4) return true; else return false;
    }
    
    function rollDamage( $aStr, $dStr )
    {
        $dammin = max( $aStr - $dStr, 1);
        $damage = $this->rolldice( $dammin, $aStr );
        return $damage;
    }


    function isCompleted()
    {
        return $this->completed;
    }

    function rolldice($low,$high)
    {
        return round(rand($low,$high));
    }
        
    function getType()
    {
        return $this->type;
    }
    
}
?>
