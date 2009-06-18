<?php
class item
{
	var $id = 0;
	var $itemname = "";
	var $description = "";
	var $type = 0;
	var $isWearable = 0;
	var $str = 0;
	var $dex = 0;
	var $hp = 0;
	var $isEquipped = 0;
	
	function item($itemRow)
	{
		$this->id = $itemRow['invID'];
		$this->itemname = $itemRow['itemname'];
		$this->type = $itemRow['itemtype'];
		$this->isWearable = $itemRow['isWearable'];
		$this->str = $itemRow['strAdded'];
		$this->dex = $itemRow['dexAdded'];
		$this->hp = $itemRow['hpAdded'];
		$this->isEquipped = $itemRow['isEquipped'];
	}
	
	function getID()
	{
		return $this->id;
	}
	
	function getItemName()
	{
		return $this->itemname;
	}
	
	function getDescription()
	{
		return "Descriptions not yet implemented, sorry";
	}
	
	function getType()
	{
		return $this->type;
	}
	
	function getIsWearable()
	{
		if ($this->isWearable == 0) return false; else return true;
	}
	
	function getStr()
	{
		return $this->str;
	}
	
	function getDex()
	{
		return $this->dex;
	}
	
	function getHP()
	{
		return $this->hp;
	}
	
	function getIsEquipped()
	{
		if ($this->isEquipped == 0) return false; else return true;
	}
	
		
	function getAll()
	{
		if ($this->isWearable == 0) {$isWearableB = "No";} else {$isWearableB = "Yes";}
		if ($this->isEquipped == 0) {$isEquippedB = "No";} else {$isEquippedB = "Yes";}
		$typeStr = $this->getTypeStr();
		$toReturn =  "<tr>
 <td>{$this->itemname}</td>
 <td>{$typeStr}</td>
 <td>{$isWearableB}</td>
 <td>{$this->str}</td>
 <td>{$this->dex}</td>
 <td>{$this->hp}</td>
 <td>{$isEquippedB}</td>
</tr>";

		return $toReturn;
	}
	
	function getTypeStr()
	{
		$strType="unknown";
		switch ($this->type)
		{
			case 0:
				$strType = "Usable";
				break;
			case 1:
			case 2:
			case 3:
				$strType = "Weapon";
				break;
			case 4:
			case 5:
			case 6:
				$strType = "Armor";
				break;
			case 7:
			case 8:
			case 9:
				$strType = "Accessory";
				break;
			case 10:
				$strType = "Quest";
				break;
			case 11: 
				$strType = "Vendor Trash";
				break;
		}
		return $strType;
	}
}
?>