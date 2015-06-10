<?php
require_once("../inc/centrale.php") ;

$lpf	= new lienpf() ;
$e		= new evenement() ;
$f		= new fonction() ;

$lpf->select() ;
while ($lpf->next()) {
	$f->get($lpf->fonction) ;
	$e->get($lpf->evenement) ;
	$e->fonction = $lpf->fonction ;
	$e->save() ;
}
echo "Ok." ;
?>
	