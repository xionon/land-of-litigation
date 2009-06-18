<?php
//LAST UPDATED 12-10-05
include_once ("conn.inc");
include_once ("inventory.inc.php");
/************************************************************************************
Object oriented user management
When a user logs in to the site, a new USER object is created and stored in their 
session.  This USER object contains all information about the user themselves - i.e., 
all information stored in the database is put into the object.  This reduces the number 
of database calls I'll need to make in the program.

I got this idea from a script I downloaded, modified it to suit my needs.  Not sure 
if object oriented was the way to go for user management, but wanted to exparament 
with it, anyway.

Passwords are, right now, probably unsecure.
*************************************************************************************/
class user 
{
    //The user's password should not be stored in this object... if they passed
    //the initial login, why would it need to be stored?
	    
	var $user_name = "";
	var $logged_in = false;
	var $permissions = 0;
	var $email = "";
	var $numericClass = 0;
	var $PlayerClass = "";
	var $gold = 0;
	var $experience = 0;
	var $str = 1;
	var $dex = 1;
	var $hp = 1;
	var $maxHP = 1;
	var $turns = 0;
	var $timeOfLastTurn;
	var $timeOfLastLogin;
	var $isUnc = false;
	var $numLoc = 0;
	var $myInventory;
	var $attacks = 1;
	var $calculated = 0;
        
    //Initialization function
    function user($user,$pass)
    {
        if ($user != "" && $pass != "")	$this->log_in($user,$pass);
        return $this->logged_in;
    }

    function get_calculated()
    {
        return "Calculated " . $this->calculated . " times";
    }

    function get_username()
    {
        if($this->user_name != "")
		{
			return $this->user_name;
		}
        
    }
	
	function get_Turns()
	{
		return $this->turns;
	}

	function get_Level()
	{
		//players earn a new level for every 100 experience points
		return (floor($this->experience / 100) + 1);
	}
	
	function get_Experience()
	{
		return $this->experience;
	}
	
	function get_PlayerClass()
	{
		return $this->PlayerClass;
	}
	
	function get_Str()
	{
		return $this->str;
	}
    
	function get_Dex()
	{
		return $this->dex;
	}

	function get_lastTurnTime()
	{
		return $this->timeOfLastTurn;
	}

	function get_lastLoginTime()
	{
		return $this->timeOfLastLogin;
	}
	
	function get_CurrentHP()
	{
		return $this->hp;
	}

	function get_MaxHP()
	{
		return $this->maxHP;
	}
	
	function get_Location()
	{
		/*
			1 = Town
			2 = TheBlueRiver
			3 = TheDarkForrest
			4 = TheDeepCave
		*/
		switch ($this->numLoc)
		{
			case 1:
				return "Town";
				break;
			case 2:
				return "TheBlueRiver";
				break;
			case 3:
				return "TheDarkForrest";
				break;
			case 4:
				return "TheDeepCave";
				break;
		}
		return "Town";
	}
	
	function get_PlayerInventory($whichInventory)
	{
		$toReturn = $this->myInventory->showInventory();
		switch ($whichInventory)
		{
			case "all":
				$toReturn = $this->myInventory->showInventory();
				break;
			case "weapon":
				$toReturn = $this->myInventory->showWeaponInventory();
				break;
			case "armor":
				$toReturn = $this->myInventory->showArmorInventory();
				break;
			case "accessory":
				$toReturn = $this->myInventory->showAccessoryInventory();
				break;
            case "potionlist":
                $toReturn = $this->myInventory->showPotionForm();
                break;
            case "selllist":
                $toReturn = $this->myInventory->showSellList();
                break;
        }
		return $toReturn;
	}

	function move($moveTo)
	{
        $moveTo = str_replace("_"," ",$moveTo);
		if ($this->turns > 0) {
            
            $result = mysql_query("SELECT location FROM places WHERE name = '{$moveTo}'") or die("error finding place");
            $row = mysql_fetch_array($result);
            
            $this->numLoc = $row['location'];
			$this->turns --; 
			
			mysql_query("update users set turns={$this->turns},location={$this->numLoc} where username = '{$this->user_name}'") or die("error moving user");
			return true; 
		}
		else {return false;}
	}
	
	function rest()
	{
        //Resting allows the player to use a turn to regain some hitpoints.
        //At first, they regain 50% of their max hp per rest, but, to encourage potion use,
        //as the player levels up, resting is less usefull!
        if ( $this->numLoc == 1 || $this->move("Town") )
        {
            $this->addHP( round( ($this->maxHP * .5) / $this->get_Level()) ); 
        }
	}
	
	function calculateTurns()
	{
	 	//Give a player 40 turns every 24 hours
		//first, calculate number of hours since last login
				
		$timeDiff = $this->timeOfLastLogin - $this->timeOfLastTurn;
//		$daysDiff = floor((($timeDiff / 60) / 60) / 24);
		$daysDiff = floor(($timeDiff / 60) / 60); //for testing purposes, time to get turns lowered drastically
		if ($daysDiff > 1)
		{
			$this->turns += $daysDiff * 10;
			$this->timeOfLastTurn = $this->timeOfLastLogin;
			$this->addHP($daysDiff * ($this->maxHP * .25));
		}
		if ($this->turns > 40)
			$this->turns = 40;
	}
	
	function addHP ($hpToAdd)
	{
		$this->hp += $hpToAdd;
		if ($this->hp > $this->maxHP)
		{
			$this->hp = $this->maxHP;
		}
		mysql_query("UPDATE users set hp={$this->hp} where username = '{$this->user_name}'") or die(" hp {this->hp}, {$this->user_name}");
	}
	
	function looseHP ($hpToLoose)
	{
		$this->hp -= $hpToLoose;
		if ($this->hp < 0)
		{
			$this->hp = 0;
			$this->playerDies();
		}
		mysql_query("UPDATE users set hp={$this->hp} where username = '{$this->user_name}'") or die(" hp");
	}
	
	function buy ($itemID)
	{
        $spent = 0;
        $query = "SELECT itemID,itemname,itemtype,isWearable,strAdded,dexAdded,hpAdded FROM items WHERE itemID = {$itemID}";
        $result = mysql_query( $query ) or die ("Error finding item; check item id");
        if (mysql_num_rows($result) == 1) 
        {
			$row = mysql_fetch_array($result);
            $spent = round(( max($row['strAdded']*3,0) + max($row['dexAdded']*3,0) + max($row['hpAdded'],0) + max((isWearable*25),0)) * 1.15);
            if ($this->get_Gold() > $spent)
            {   
                $this->addItem($itemID);
                $this->spendGold($spent);
            }
            else $spent = 0;
        }
        return $spent;
	}
	
    function addItem ($itemID)
	{
        $query = "SELECT itemID,itemname,itemtype,isWearable,strAdded,dexAdded,hpAdded FROM items WHERE itemID = {$itemID}";
        $result = mysql_query( $query ) or die ("Error finding item; check item id");
        if (mysql_num_rows($result) == 1) 
        {
            $this->myInventory->addItem($itemID); 
            $toReturn = true;
        } 
        else 
            $toReturn = false;
        
        return $toReturn;
	}
	
	function equip ($itemID)
	{
        if ( $this->myInventory->equip($itemID) ) {
            $this->calculateStats();
            return true;
        }
        else {
            $this->calculateStats();
            return false;
        }
	}
	
    function unequip ($itemID)
    {
        if ( $this->myInventory->unequip($itemID) ) {
            $this->calculateStats();
            return true;
        }
        else {
            $this->calculateStats();
            return false;
        }
    }

	function addGold ($g)
	{
        $this->gold += $g;
        mysql_query("UPDATE users set gold={$this->gold} where username = '{$this->user_name}'") or die("gold update error");
	}

    function spendGold ($g)
    {
        $this->gold -= $g;
        mysql_query("UPDATE users set gold={$this->gold} where username = '{$this->user_name}'") or die("gold update error");
    }

    function get_Gold()
    {
        return $this->gold;
    }
	
	function addExp ($e)
	{
        $this->experience += $e;
        mysql_query("UPDATE users set experience={$this->experience} where username = '{$this->user_name}'") or die("exp update error");
        $this->calculateStats();
	}
	
	function playerDies()
	{
		//erm...
		$this->isUnc = true;
	}
	
	function isPlayerUnc()
	{
        return $this->isUnc;
	}
	
    function isLoggedIn()
    {
        if ($this->logged_in == true) { return true; }
        else { return false; }
    }

	function calculateStats()
	{
		//this function calculates the players statistics based on class and level
		//eventually i should implement stats that grow naturally rather than just linearly....
		$bonus = $this->myInventory->getBonus();
		
		switch ($this->numericClass)
		{
			//1=zombie
			//2=pirate
			//3=ninja
			case 1:
                $this->maxHP = round(7 * $this->get_Level()) + $bonus['hp'] + 5;
				$this->str   = round(3.21*$this->get_Level()) + $bonus['str'] + 3;
				$this->dex   = round(1.12*$this->get_Level()) + $bonus['dex'] + 1;
				break;
			case 2:
				$this->maxHP = round(4.8 * $this->get_Level()) + $bonus['hp'] + 4;
				$this->str   = round(2.53*$this->get_Level()) + $bonus['str'] + 3;
				$this->dex   = round(2.27*$this->get_Level()) + $bonus['dex'] + 3;
				break;
			case 3:
                $this->attacks = 2;
				$this->maxHP = round(3.56 * $this->get_Level()) + $bonus['hp'] + 2;
				$this->str   = round(1.23*$this->get_Level()) + $bonus['str'] + 1;
				$this->dex   = round(5.7*$this->get_Level()) + $bonus['dex'] + 6;
				break;
		}
		if ($this->hp > $this->maxHP) 
            $this->hp = $this->maxHP;
	}
    
    function log_out()
    {
        //this function should eventually send a message to the database flagging the user as logged out
        
        $this->user_name = "";
        $this->permissions = 0;
        $this->logged_in = false;
    }
    
    function log_in($user,$pass)
    {
		/*log_in
		1. Query the database for the permissions of the user with the username and password supplied.
		2. Check to be sure there is a row with that username and password(password).
		3. Set user permissions.
		4. Set username.
		5. Set the user's logged_in status to 'true' on the database.
			
		2a. Set everything to nothing.
		*/
        //Get permissions for user from users database.
        $query = "SELECT now()+0 as now, email, class, permissions, experience, lastTurn, turns, location, hp, gold FROM users WHERE username = '{$user}' AND password = (password('{$pass}'))";
        $result = mysql_query($query)
            or die( "Problem with selecting permissions and email" );
        //mysql_fetch_array should return an array of all information gathered
        //in the above query.  
		$row = mysql_fetch_array( $result );

        if ( mysql_num_rows( $result ) == 1)
        {
            $this->user_name = $user;
			$this->numericClass = $row['class'];
			switch ($row['class'])
			{
				case 1:
					$this->PlayerClass = "Zombie";
					break;
				case 2:
					$this->PlayerClass = "Pirate";
					break;
				case 3:
					$this->PlayerClass = "Ninja";
					break;
			}		
            $this->permissions = $row['permissions'];
            $this->email = $row['email'];
            $this->logged_in = true;
			if ($row['experience'] != null)
				$this->experience = $row['experience'];
			else $this->experience = 100;
			$this->myInventory = new inventory($this->user_name, $this->numericClass);
			$this->hp = $row['hp'];
			$this->calculateStats();
			$this->numLoc = $row['location'];
			$this->gold = $row['gold'];	
			$this->turns = $row['turns'];
			$this->timeOfLastTurn = $row['lastTurn'];
			$this->timeOfLastLogin = $row['now'];
			$this->calculateTurns();
			if ($this->timeOfLastTurn == $this->timeOfLastLogin)
			{
				mysql_query("UPDATE users SET lastTurn = now(),turns={$this->turns} WHERE username = '{$user}'")
					or die ("error updating lastturn");
			}
		}
        else
        {
            //Just to be sure, set things to "" if the user fails the login.
            $this->user_name = "";
            $this->permissions = 0;
            $this->email = "";
            $this->logged_in = false;
        }
	}

    function register($u,$p,$c,$e)
    {
        //check for existing users with that username
        $query = "SELECT username FROM users WHERE username='{$u}'";
        $result = mysql_query($query)
            or die(mysql_error());
        
        if (mysql_num_rows($result) <= 0)
        {
            $per = 1;
            $g = 0;
            $h = 20;
            $ex = 0;
            $l = 0;
            $t = 40;
			//username,password,email,permissions,class,gold,hp,experience,location,turns,lastTurn
			$query = "INSERT INTO users VALUES ('{$u}', (password('{$p}')), '{$e}', {$per}, {$c}, {$g}, {$h}, {$ex}, {$l}, {$t}, now())";
			if (mysql_query($query)) 
            {
                $this->log_in($u,$p);
                return true;
            }
        }
        return false;
    }
    function get_Stats()
    {
        $toReturn['str']     = $this->str;
        $toReturn['dex']     = $this->dex;
        $toReturn['hp']      = $this->hp;
        $toReturn['class']   = $this->numericClass;
        $toReturn['attacks'] = $this->attacks;
        return $toReturn;
    }
    
    function getUserInfoTable()
    {
        $lv = $this->get_Level();
        $toReturn = "<!--USER INFO TABLE--> <table id=\"character_sheet\" width=\"600\" border = \"1\" bordercolor=\"black\" noshadow> <tr> " .
        "<th align=\"right\" width=\"125\">Character Name:</th> <td width=\"75\">{$this->user_name}</td><th align=\"right\" width=\"100\"> " .
        "Level: </th><td width=\"100\">{$lv}</td><th align=\"right\" width=\"125\">Total Experience:</th><td width=\"75\">" . 
        "{$this->experience}</td></tr> <tr><th align=\"right\">Streingth:</th><td>{$this->str}" .
        "</td><th align=\"right\">Dexterity:</th><td>{$this->dex}</td><th align=\"right\">Hit Points:</th>" .
        "<td>{$this->hp} / {$this->maxHP}</td></tr><tr><th align=\"right\" colspan=\"3\">" . 
        "Turns Remaining:</th><td colspan=\"3\">{$this->turns}</td></tr></table>";

    return $toReturn;
    }
    
    function usePotion($id)
    {
        //item must be decremented in inventory and any status effects calculated
        $hpchange = $this->myInventory->usePotion($id);
        $this->addHP($hpchange);
        return $hpchange;
    }

    function sell($id)
    {
        //item must be decremented in inventory and any status effects calculated
        $goldChange = $this->myInventory->sell($id);
        $this->addGold($goldChange);
        return $goldChange;
    }
    
    function get_PlayerBuyList()
    {
        $toreturn = "<div id=\"PlayerBuyList\">Buy something?<br/>";
        $query = "SELECT itemID, itemname, itemtype, isWearable, strAdded, dexAdded, hpAdded
FROM items
WHERE itemtype <> 10
ORDER BY itemtype";
        $result = mysql_query( $query ) or die("error getting playerbuylist");
        
        while ( $row = mysql_fetch_array( $result ) )
        {
            $toreturn .= "<a href=\"shop.php?task=buy&itemid={$row['itemID']}\">buy {$row['itemname']}</a><br/>";
        }
        $toreturn .= "</div>";
        return $toreturn;
    }

}
?>
