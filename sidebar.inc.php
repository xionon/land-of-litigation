<?php
function outputSidebar($fromHere)
{
	echo "
		<div class=\"sidebar\">
			<ul>
				<li><a href=\"userProfile.php\">character<br/>sheet</a></li>
				<li><a href=\"mainMap.php\">main map</a></li>
				<li><a href=\"{$fromHere}.php?task=logout\">logout</a></li>
			</ul>
		</div>";
}
?>