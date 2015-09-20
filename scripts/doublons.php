<?php
//script qui identifie les personnes qui ont le même nom
require_once("../inc/centrale.php") ;
$p 		= new personne() ;
$p2		= new personne() ;
$lienep	= new lienep() ;
$lienpl	= new lienpl() ; 
$rpp	= new relationpersonnes() ;
$p->select() ;

$q = "SELECT COUNT( nom ) AS nbr_doublon, nom FROM personne GROUP BY nom HAVING COUNT( nom ) >1" ;
mysqldb::send($q) ;
$personnes = array() ;
while ($row = mysqldb::$req->fetch()) {
	$personnes[] = $row["nom"] ;
}
mysqldb::$req->closeCursor() ;

foreach ($personnes as $pnom) {
	//étape 1 - on identifie les différents numéros
	echo "J'ai identifié le doublon $pnom. <br/>" ;
	$q2 = 'SELECT num, autreNom, dynastie, url FROM personne WHERE nom = "'.$pnom.'"' ;
	mysqldb::send($q2) ;
	$row = mysqldb::$req->fetch() ;
	$num1 = $row[0] ;
	$autreNom = $row[1] ;
	$dynastie = $row[2] ;
	$url	= $row[3] ;
	$num2 = array() ;
	$autreNoms2 = array() ;
	while ($row = mysqldb::$req->fetch()) {
		$num2[] = $row[0] ;
		if ($row[1] != "") $autreNoms2[] = $row[1] ;
		if ($dynastie == 0 && $row[2] !=0) $dynastie = $row[2] ;	
		if ($url == 0 && $row[3] !=0) $url = $row[3] ;			
	}
	mysqldb::$req->closeCursor() ;
	$num2 = join($num2, ",") ;
	echo "Le numéro originel est ".$num1.". Par la suite, il y a ".count($num2)." doublon(s) : ".$num2.". <br/>" ;
	
	//1ère étape de la fusion : les liens EL sont tous transférés sur le premier individu
	$q3 = "UPDATE lienep SET personne = $num1 WHERE personne IN ($num2)" ;
	echo "Requête de changement des liens EP : $q3<br/>" ;
	mysqldb::send($q3) ;
	
	//2e étape de la fusion : les liens PL
	$q4 = "UPDATE lienpl SET personne = $num1 WHERE personne IN ($num2)" ;
	echo "Requête de changement des liens PL : $q4<br/>" ;
	mysqldb::send($q4) ;		
		
	//3e étape de la fusion : les liens PP
	$q5 = "UPDATE relationpersonnes SET personne1 = $num1 WHERE personne1 IN ($num2)" ;
	echo "Requête de changement des liens PL : $q4<br/>" ;
	mysqldb::send($q5) ;	
	$q6 = "UPDATE relationpersonnes SET personne2 = $num1 WHERE personne2 IN ($num2)" ;
	echo "Requête de changement des liens PL : $q4<br/>" ;
	mysqldb::send($q6) ;	
	
	//4e étape - fusion des autres noms
	if (count($autreNoms2) > 0) {
		$autreNom .= ",".join(autreNoms2, ",") ;
	}
	
	//5e étape - fusion des dynasties
	//déjà fait plus haut
	//6e étape - fusion des url
	//déjà fait plus haut

	$valide = 0 ;
	//on sauve la personne 1
	$q7 = 'UPDATE personne SET autreNom = "'.$autreNom.'", dynastie = "'.$dynastie.'", url = "'.$url.'", valide = 0 WHERE num = '.$num1 ;
	echo "Requête d'enregistrement de la personne : $q7<br/>" ;
	mysqldb::send($q7) ;
	//on supprime les autres
	$q8 = "DELETE FROM personne WHERE num IN ($num2)" ;
	echo "Requête de suppression des autres personne : $q8<br/>" ;
	mysqldb::send($q8) ;
}

echo "<br/>Le script a bien été exécuté.<br/>" ;
?>