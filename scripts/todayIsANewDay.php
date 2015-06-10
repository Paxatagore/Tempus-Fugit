<?php
require_once("../inc/centrale.php") ;

$q = 'update dynastie set occurenceJour="0" where occurenceJour > 0' ;
$q = mysqldb::send($q) ;
$q = 'update personne set occurenceJour="0" where occurenceJour > 0' ;
$q = mysqldb::send($q) ;
$q = 'update tag set occurenceJour="0" where occurenceJour > 0' ;
$q = mysqldb::send($q) ;

echo "<br/>Le script a bien été exécuté et les occurrences journalières ont été remises à 0.<br/>" ;
?>