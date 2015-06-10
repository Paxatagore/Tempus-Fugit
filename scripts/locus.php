<?php
require_once("../inc/centrale.php") ;
$e = new evenement() ;
$l = new locus() ;
$e->select("where latitude <> 0 and longitude <> 0 and lieu <> ''") ; 

while ($e->next()) {
	echo("<p></p>Evénement $e->num. Se déroule à $e->lieu ($e->latitude, $e->longitude). <br/>") ;
	$l->get("nom", trim($e->lieu)) ;
	if ($l->num == 0) {
		echo("Je ne connais pas cet endroit, que je crée. </br>") ;
		$l->nom = $e->lieu ;
		$l->latitude = $e->latitude ;
		$l->longitude = $e->longitude ;
		$l->save() ;
	}
	else echo("Je connais cet endroit ($l->num). </br>") ;
	$e->locus = $l->num ;
	$l->num = 0 ;
	$e->save() ;
}

?>