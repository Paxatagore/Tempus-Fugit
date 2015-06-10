<?php
//scripts qui initie les lieux
require_once("../inc/centrale.php") ;
$e = new evenement() ;
$e->select() ;
while ($e->next()) {
	if ($e->latitude != 0 or $e->longitude != 0) {
		$e->lieu = $e->latitude . ", " . $e->longitude ;
		echo "Lieu de l'évènement $e->num : $e->lieu <br/>" ;
		$e->save() ;
	}
}
