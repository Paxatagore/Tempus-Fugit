<?php
//scripts qui chopent les tags thématiques et les mets sous le nouveau format
require_once("../inc/centrale.php") ;

//UPDATE evenement SET thematiques="000000000"


function convertitTT($tt, $i) {
	/*$lienel = new lienel() ;
	$e		= new evenement() ;
	$lienel->select("WHERE tag=$tt") ;
	$n = 0 ;
	while ($lienel->next()) {
		$e->get($lienel->evenement) ;
		$e->thematiques[$i] = "1" ;
		$e->save() ;
		$n++ ;
	}*/
	$q = "DELETE FROM lienel WHERE tag=$tt" ;
	$n = mysqldb::send($q) ;
	return $n ;
}

echo "Art : ".convertitTT(19, 0)."<br/>" ;
echo "Idées :".convertitTT(22, 1)."<br/>" ;
echo "Militaire :".convertitTT(16, 2)."<br/>" ;
echo "Politique:".convertitTT(15, 3)."<br/>" ;
echo "Religion:".convertitTT(17, 4)."<br/>" ;
echo "Sciences:".convertitTT(21, 5)."<br/>" ;
echo "Société :".convertitTT(18, 6)."<br/>" ;
echo "Diplomatie :".convertitTT(79, 7)."<br/>" ;
echo "Sports :".convertitTT(20, 8)."<br/>" ;

?>