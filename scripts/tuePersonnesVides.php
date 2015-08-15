<?php
//script qui tue les personnes qui n'ont pas de nom.
require_once("../inc/centrale.php") ;
$p = new personne() ;
$p->select('WHERE nom=""') ;
$isin = array() ;
while($p->next()) {
	echo $p->num." n'a pas de nom : ".$p->nom.". Je le tue. <br/>" ;
	$p->delete() ;
	$isin[] = $p->num ;
}

$isin = join($isin, ",") ;

$q = "DELETE FROM relationpersonnes WHERE personne1 IN ($isin)" ;
mysqldb::send($q) ;
$q = "DELETE FROM relationpersonnes WHERE personne2 IN ($isin)" ;
mysqldb::send($q) ;

echo "<br/>Le script a bien été exécuté.<br/>" ;
?>