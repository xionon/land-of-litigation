<?php
function outputSidebar($ts)
{
    $ts = str_replace("_","",$ts);
	return "
		<div id=\"sidebar\">
			<a class=\"sidebar\" href=\"userProfile.php\">character sheet</a>
			<a class=\"sidebar\" href=\"mainMap.php\">main map</a>
			<a class=\"sidebar\" href=\"{$ts}.php?task=rest\">rest</a>
			<a class=\"sidebar\" href=\"{$ts}.php?task=logout\">logout</a>
		</div>";
}
?>