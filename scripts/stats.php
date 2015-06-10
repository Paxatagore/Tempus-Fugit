<?php
//script qui affiche quelques statistiques
require_once("../inc/centrale.php") ;
echo "<h2>Statistiques sur la base de donnée.</h2>" ;

$q = 'SELECT table_name AS "Tables", round(((data_length + index_length) / 1024 ), 2), TABLE_ROWS AS "Taille (Ko)" FROM information_schema.tables WHERE table_schema = "CTFA"';
$q = mysqldb::send($q) ;
$poidstotal = 0 ;
echo '<table><thead><tr><td>Tables</td><td>Taille</td><td>Nombre d\'enregistrements</td></tr></thead>' ;
while ($row = mysqldb::$req->fetch()) {
	echo '<tr><td>'.$row[0].'</td><td>'.$row[1].'</td><td>'.$row[2].'</td></tr>' ;
	$poidstotal += $row[1] ;
}

echo '<tr><td>Total</td><td>'.$poidstotal.'</td></tr></table><p></p>' ;

$q = 'Select COUNT(*) FROM personne WHERE valide=0' ;
$n = mysqldb::unik($q) ;

echo "Nombre de personnes à valider : $n <br />" ;

echo '<br/>Le script a bien été exécuté.' ;
?>
