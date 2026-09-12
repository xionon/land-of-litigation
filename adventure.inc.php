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
    
    function __construct($adventureIn, $levelMax)
    {
        $adventureIn = str_replace("_"," ",$adventureIn);
        $params = [$adventureIn];
        $query = "SELECT name,location,level FROM places WHERE name=?";
        $result = db_query($query, $params) or die ("error loading location from database");
        $row = $result->fetch();
        
        if (!$row) { $this->type = 0; $this->complete(); return; }
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
        $toReturn = $this->type == 0 ? "There is nothing to encounter here." : "";
        if ( !$this->isCompleted() )
        {
            switch ($this->type)
            {
                case 1:
                    $toReturn .= "<strong>" . $this->myAdventure['name'] . "</strong> <br />";
					if ($this->resource !== "") $toReturn .= "<img src=\"{$this->resource}\" /><br />";
					$toReturn .= $this->myAdventure['description'];
                    break;
                
                case 2:
                    $toReturn .= "There is a {$this->monster['monstername']} for you to do glorious battle with!<br/>";
                    if ($this->resource !== "") $toReturn .= "<img src=\"{$this->resource}\" /><br />";
                    break;
            }
        }
        else
        {
            switch ($this->type)
            {
                case 1:
                    $toReturn .= "<strong>" . $this->myAdventure['name'] . "</strong>";
					if ($this->resource !== "") $toReturn .= "<img src=\"{$this->resource}\" /><br />";
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
        $this->type = 1;
        $this->completed = false;
        $this->gold = 0;
        $this->item = 0;
        $this->experience = 0;
        $this->resource = '';
        $rows = db_query('SELECT * FROM adventures WHERE location = ?', [$this->location])->fetchAll();
        if (!$rows) { $this->type = 2; $this->monster($userlevel + 5); return; }
        $this->myAdventure = $rows[$this->rolldice(0, count($rows) - 1)];
        $this->experience = 10;
        $this->item = $this->myAdventure['rewardItemID'] ?? 0;
        $path = "images/adventures/{$this->myAdventure['adventureID']}.gif";
        $this->resource = is_file(__DIR__ . '/' . $path) ? $path : '';
    }


    function monster($max)
    {
        $rows = db_query('SELECT monstername,level,hp,str,dex,gold,item,itemChance FROM monsters WHERE location = ? ORDER BY level', [$this->location])->fetchAll();
        $keeper = false;
        foreach ($rows as $row) {
            $keeper = $row;
            // Keep the original two-in-three preference for lower-level monsters.
            if (count($rows) === 1 || $this->rolldice(1, 3) < 3) break;
        }
        if (!$keeper) { $this->type = 0; $this->complete(); return; }
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
				$msg["response"] = "<strong id=\"response\">" . $this->myAdventure["failText"] . "</strong>";
				}
				break;
			case 'str':
				if ($userstats['str'] > $this->myAdventure['testValue']) {
					$this->complete();
					$msg["response"] = "<strong id=\"response\">" . $this->myAdventure["completeText"] . "</strong>";
				} else {
					$this->fail();
					$msg["response"] = "<strong id=\"response\">" . $this->myAdventure["failText"] . "</strong>";
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
        $this->lastMonsterDamage = 0;
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
        $msg = "";
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
        $damage = $this->rolldice($dammin, max($dammin, $aStr));
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
