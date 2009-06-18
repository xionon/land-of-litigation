<?php 
include ("conn.inc");
include ("item.inc.php");
/*******

Inventory object
an inventory is composed of multiple items
the inventoryTable string can be returned to easilly print out an inventory from anywhere, huzzah

*******/
class inventory
{
	var $items = array();
	var $user_name = "";
	var $equippedArmor = 0;
	var $equippedWeapon = 0;
	var $equippedAccessory = 0;

	function inventory($user)
	{
		$this->user_name = $user;
		//is this necessary?  it is instantiated above...
		//$this->items = array();
		$query = "SELECT v.invID, i.itemname, i.itemtype, i.isWearable, i.strAdded, i.dexAdded, i.hpAdded, v.isEquipped FROM inventory v, items i WHERE v.itemtype = i.itemID AND v.owner = '{$this->user_name}'";

		$result = mysql_query($query) or die("problem getting inventory");

		while ( $row = mysql_fetch_array( $result ) )
		{
			$this->items[] = new item( $row );
		}
	}

	function showInventory()
	{
		$inv = "<table border=\"2\"><tr><th colspan=\"10\">{$this->user_name}'s Inventory</th></tr><tr><th>Item Name</th><th>Item Type</th><th>Is it wearable?</th><th>Streingth Bonus</th><th>Dexterity Bonus</th><th>HP Bonus</th><th>Equipped?</th></tr>";
		foreach ($this->items as $i => $curr)
		{
			$inv .= $this->items[$i]->getAll();
		}
		$inv .= "</table>";
	return $inv;
	}
	
	function showWeaponInventory()
	{
		$inv = "<table border=\"2\"><tr><th colspan=\"10\">{$this->user_name}'s Inventory</th></tr><tr><th>Item Name</th><th>Item Type</th><th>Is it wearable?</th><th>Streingth Bonus</th><th>Dexterity Bonus</th><th>HP Bonus</th><th>Equipped?</th></tr>";
		
		foreach ($this->items as $i => $curr)
		{
			$iT = $this->items[$i]->getTypeStr(); 
			if ( $iT == "Weapon") $inv .= $this->items[$i]->getAll();
		}
		$inv .= "</table>";
	return $inv;
	}
	
	function showArmorInventory()
	{
		$inv = "<table border=\"2\"><tr><th colspan=\"10\">{$this->user_name}'s Inventory</th></tr><tr><th>Item Name</th><th>Item Type</th><th>Is it wearable?</th><th>Streingth Bonus</th><th>Dexterity Bonus</th><th>HP Bonus</th><th>Equipped?</th></tr>";
		
		foreach ($this->items as $i => $curr)
		{
			$iT = $this->items[$i]->getTypeStr(); 
			if ( $iT == "Armor") $inv .= $this->items[$i]->getAll();
		}
		$inv .= "</table>";
	return $inv;
	}
	
	function showAccessoryInventory()
	{
		$inv = "<table border=\"2\"><tr><th colspan=\"10\">{$this->user_name}'s Inventory</th></tr><tr><th>Item Name</th><th>Item Type</th><th>Is it wearable?</th><th>Streingth Bonus</th><th>Dexterity Bonus</th><th>HP Bonus</th><th>Equipped?</th></tr>";
		
		foreach ($this->items as $i => $curr)
		{
			$iT = $this->items[$i]->getTypeStr(); 
				if ( $iT == "Accessory") $inv .= $this->items[$i]->getAll();
		}
		$inv .= "</table>";
	return $inv;

	}
	
	function addItem($itemID)
	{   
        //Write the new item to the inventory database
        $query = "INSERT INTO inventory VALUES (null,0,'{$this->user_name}',{$itemID})";
        $result = mysql_query( $query ) or die ( "error adding new item to inventory; check item id" );

        //Get the item's stats from the database with id type $itemID
		$query = "SELECT v.invID, i.itemname, i.itemtype, i.isWearable, i.strAdded, i.dexAdded, i.hpAdded, v.isEquipped  FROM inventory v, items i WHERE v.itemtype = i.itemID AND v.owner = '{$this->user_name}'";
		$result = mysql_query($query) or die("problem getting inventory");

		while ( $row = mysql_fetch_array( $result ) )
		{
			$this->items[] = new item( $row );
		}
	}
	
	function equip($invID)
	{
        /*
        To equip an item, first we need to know what kind of item it is (armor, weapon, accessory)
        We can assume that the equip command will not have been sent for an item that is not equipable.
        Then we will have to check to see if there is an item of that type already equipped.  If so,
        that item will be unequipped.  Then the item that has been requested will be marked as equipped
        and the appropriate variable updated.
        */

        
	}
}
?>