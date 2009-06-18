<?php
include_once ("conn.inc");
include_once ("inventory.inc.php");

/***********
Object oriented user management
When a user logs in to the site, a new USER object is created and stored in their session.  This USER object contains all information about the user themselves - i.e., all information stored in the database is put into the object.  This reduces the number of database calls I'll need to make in the program.

I got this idea from a script I downloaded, modified it to suit my needs.  Not sure if object oriented was the way to go for user management, but wanted to exparament with it, anyway.

Passwords are, right now, probably unsecure.
*/

class user 
{
    //The user's password should not be stored in this object... if they passed
    //the initial login, why would it need to be stored?
	    
	var $user_name = "";
	var $logged_in = false;
	var $permissions = "";
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
        
    //Initialization function
    function user($user,$pass)
    {
        if ($user != "" && $pass != "")	$this->log_in($user,$pass);
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
			0 = Town
			1 = TheBlueRiver
			2 = TheDarkForrest
			3 = TheDeepCave
		*/
		switch ($this->numLoc)
		{
			case 0:
				return "Town";
				break;
			case 1:
				return "TheBlueRiver";
				break;
			case 2:
				return "TheDarkForrest";
				break;
			case 3:
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
		}
		return $toReturn;
	}

	function calculateStats()
	{
		//this function calculates the players statistics based on class and level
		//eventually i should implement stats that grow naturally rather than just linearly....
		switch ($this->numericClass)
		{
			//1=zombie
			//2=pirate
			//3=ninja
			case 1:
				$this->maxHP = round(5 * $this->get_Level());
				$this->str   = round(3.21*$this->get_Level());
				$this->dex   = round(1.12*$this->get_Level());
				break;
			case 2:
				$this->maxHP = round(2.8 * $this->get_Level());
				$this->str   = round(2.53*$this->get_Level());
				$this->dex   = round(2.27*$this->get_Level());
				break;
			case 3:
                $this->attacks = 2;
				$this->maxHP = round(1.56 * $this->get_Level());
				$this->str   = round(1.23*$this->get_Level());
				$this->dex   = round(5.7*$this->get_Level());
				break;
		}

	}
	
	function move($moveTo)
	{
		if ($this->turns > 1) {
			switch ($moveTo)
			{
				/*
					0 = Town
					1 = TheBlueRiver
					2 = TheDarkForrest
					3 = TheDeepCave
				*/
				case "Town":
					$this->numLoc = 0;
					break;
				case "TheBlueRiver":
					$this->numLoc = 1;
					break;
				case "TheDarkForrest":
					$this->numLoc = 2;
					break;
				case "TheDeepCave":
					$this->numLoc = 3;
					break;
			}
			$this->turns --; 
			mysql_query("update users set turns={$this->turns},location={$this->numLoc} where username = '{$this->user_name}'") or die("error moving user");
			return true; 
		}
		else {return false;}
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
	
    function addItem ($itemID)
	{
        $query = "SELECT * FROM items WHERE itemID = {$itemID}";
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
	
	function addGold ($g)
	{
        $this->gold += $g;
        mysql_query("UPDATE users set gold={$this->gold} where username = '{$this->user_name}'") or die("gold update error");
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
	
    function isLoggedIn()
    {
        if ($this->logged_in == true) { return true; }
        else { return false; }
    }
    
    function log_out()
    {
        //this function should eventually send a message to the database flagging the user as logged out
        
        $this->user_name = "";
        $this->permissions = "";
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
        $query = "SELECT now()+0 as now, email, class, permissions, experience, lastTurn, turns, location, hp FROM users WHERE username = '{$user}' AND password = (password('{$pass}'))";
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

			$this->calculateStats();
			$this->hp = $row['hp'];
			$this->numLoc = $row['location'];
				
			$this->turns = $row['turns'];
			$this->timeOfLastTurn = $row['lastTurn'];
			$this->timeOfLastLogin = $row['now'];
			$this->calculateTurns();

			if ($this->timeOfLastTurn == $this->timeOfLastLogin)
			{
				mysql_query("UPDATE users SET lastTurn = now(),turns={$this->turns} WHERE username = '{$user}'")
					or die ("error updating lastturn");
			}
			$this->myInventory = new inventory($this->user_name);
		}
        else
        {
            //Just to be sure, set things to "" if the user fails the login.
            $this->user_name = "";
            $this->permissions = "";
            $this->email = "";
            $this->logged_in = false;
        }
	}

    function register($user,$pass,$class,$email)
    {
        //check for existing users with that username
        $query = "SELECT username FROM users WHERE username='" .
            $this->user_name . "';";
        $result = mysql_query($query)
            or die(mysql_error());
        
        if (mysql_num_rows($result) <= 0)
        {
			$query = "INSERT INTO users VALUES ('{$user}', (password('{$pass}')), '{$email}', {$class}, 1)";
			if (mysql_query($query)) 
            {
                $this->log_in($user,$pass);
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
}
?>