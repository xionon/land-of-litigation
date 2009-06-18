<?php
require_once('conn.inc');
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
    var $completed = false;
    var $experience = 0;
    var $gold = 0;
    var $item = 0;
    
    function adventure($adventureIn, $levelMax)
    {
        $this->location = $adventureIn;
        $this->type = $this->rolldice(1,2);
        switch ($this->type)
        {
            case 1:
                //go on an adventure
                $this->experience = 5;
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
                    $toReturn .= "YOU ARE ON AN ADVENTURE!<br/>";
                    break;
                
                case 2:
                    $toReturn .= "There is a {$this->monster['monstername']} for you to do glorious battle with!<br/>";
                    break;
            }
        }
        else
        {
            $toReturn = "Congratulations, you completed your adventure.<br/>";
            switch ($this->type)
            {
                case 1:
                    $toReturn .= "<br/>5 experience points!";
                    break;
                case 2:
                    $toReturn .= "You get a reward of {$this->monster['gold']} gold";
                    if ($this->monster['item'] != 0)
                        $toReturn .= " and the {$this->monster['item']} item.";
                    else
                        $toReturn .=".<br/>";
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
        if ($this->type == 2) {
            $this->monster['item'] = 0;
            $this->monster['gold'] = 0;
        }
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
    
    function monster($max)
    {
        //create a new monster
        $query = "SELECT monstername,level,hp,str,dex,gold,item FROM monsters WHERE level<{$max} AND location={$this->location}";
        $result = mysql_query($query) or die ("error loading monster from database");
        
        //select a monster to use if more than one is found
        if (mysql_num_rows($result)>1)
        {
            while ( $row = mysql_fetch_array( $result ) )
            {
                $keeper=$row;
                $roll = $this->rolldice(1,3);
                //users have a better chance of hitting a lower level monster
                if ($roll < 2)
                    break;
            }
        }
        else
        {
            $keeper = mysql_fetch_array($result);
        }
        $this->monster = $keeper;
        $this->experience = max( 1, ($this->monster['level'] - ($max-5) + 10) );
        $this->item = $this->monster['item'];
        $this->gold = $this->monster['gold'];
    }
    
    function performCommand($command,$userstats)
    {
        $msg .= "";
        switch ($this->type)
        {
            case 1:
                break;//case 1
            case 2:
                switch ($command)
                {
                    case "Attack":
                        $msg .= $this->attackMonster($userstats);
                        break; //case "Attack"
                    case "Run":
                        $msg .= $this->tryToRun($userstats);
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
                    $msg .= "<br/>You killed the fucker!";
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