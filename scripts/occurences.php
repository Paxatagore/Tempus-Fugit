<?php
//script qui décompte les occurences de chaque lieu et chaque personne
require_once("../inc/centrale.php") ;

echo "Lancement du script... <br/>" ;

function occurences($requete, $table) {
	$req = mysqldb::send($requete) ;
	//while ($row = mysql_fetch_array($req)) {
	while ($row = mysqldb::$req->fetch()) {	
		$num = $row["num"] ;
		$ocFuture = $row["COUNT(*)"] ;
		$updateReq = "update $table set occurence = $ocFuture where num = $num ; " ;
		$upReq = mysqldb::send($updateReq) ;
		
	}
	mysqldb::$req->closeCursor() ;
	return 1 ;
}

//occurence des liens
$q = "SELECT tag.num, tag.occurence, COUNT(*) FROM tag, lienel WHERE lienel.tag = tag.num GROUP BY (lienel.tag) HAVING tag.occurence <> COUNT( * ) ORDER BY tag.num" ;
occurences($q, "tag") ;
//occurence des personnes
$q = "SELECT personne.num, personne.occurence, COUNT(*) FROM personne, lienep WHERE lienep.personne = personne.num GROUP BY (lienep.personne) HAVING personne.occurence <> COUNT( * ) ORDER BY personne.num" ;
occurences($q, "personne") ;
//occurence des dynasties
$q = "SELECT dynastie.num, dynastie.occurence, COUNT(*) FROM dynastie, personne WHERE personne.dynastie = dynastie.num GROUP BY (dynastie.num) HAVING dynastie.occurence <> COUNT(*) ORDER BY dynastie.num" ;
occurences($q, "dynastie") ;
echo "Modification des occurences des dynasties." ;
//occurence des fonctions
$q = "SELECT fonction.num, fonction.occurence, COUNT(*) FROM fonction, evenement WHERE fonction.num = evenement.fonction GROUP BY (fonction.num) HAVING fonction.occurence <> COUNT( * ) ORDER BY fonction.num" ;
occurences($q, "fonction") ;


echo "<br/>Le script a bien été exécuté.<br/>" ;
?>