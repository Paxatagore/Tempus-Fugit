<?php
require_once("../inc/centrale.php") ;

$t = new tag() ;

foreach($_POST as $tag => $value) {
	if ($value <> 0) {
		if (substr($tag, 0, 3) == "lat") {
			$n = substr($tag, 8) ;
			$t->get($n) ;
			if ($t->latitude <> $value) {
				$t->latitude = $value ;
				//echo "LATITUDE : $n : $value </br>" ;
				$t->save() ;
			}
		}
		else {
			$n = substr($tag, 9) ;
			$t->get($n) ;
			if ($t->longitude <> $value) {
				$t->longitude = $value ;
				//echo "LONGITUDE : $n : $value </br>" ;
				$t->save() ;
			}
		}
	}
}
redirect("villes.php") ;
?>

