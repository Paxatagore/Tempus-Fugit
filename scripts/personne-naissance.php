<?php
require_once("../inc/centrale.php") ;

//on met les événéments naissances à 13
$q = "SELECT evenement.num AS numero FROM personne, evenement WHERE personne.naissance_evenementId = evenement.num AND evenement.typeeve != 13" ;
mysqldb::send($q) ;
$listep = array() ;
while ($row = mysqldb::$req->fetch()) {
	$listep[] = $row[0] ;
}
mysqldb::$req->closeCursor() ;
if (count($listep) > 0) {
	$q2 = "UPDATE evenement SET typeeve = 13 WHERE num IN (".join($listep, ",").")" ;
	echo "Requête de correction des événéments naissance : $q2<br/>" ;
	mysqldb::send($q2) ;
	mysqldb::$req->closeCursor() ;

}

echo "<br/>Le script a bien été exécuté.<br/>" ;
?>