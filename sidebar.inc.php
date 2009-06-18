<?php
function outputSidebar($ts)
{
	return "
		<div class=\"sidebar\">
			<ul>
				<li><a href=\"userProfile.php\">character<br/>sheet</a></li>
				<li><a href=\"mainMap.php\">main map</a></li>
				<li><a href=\"{$ts}.php?task=rest\">rest</a></li>
				<li><a href=\"{$ts}.php?task=logout\">logout</a></li>
			</ul>
		</div>";
}
?>