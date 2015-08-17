<?php
//script qui enlève les liens vers les évènements, personnes... détruits
require_once("../inc/centrale.php") ;

function nettoie($table1, $table2, $champ = "") {
	if ($champ == "") $champ = $table2 ;
	$q = 'SELECT t1.num FROM '.$table1.' AS t1 LEFT JOIN '.$table2.' AS t2 ON t1.'.$champ.' = t2.num WHERE t2.num IS NULL' ;
	mysqldb::send($q) ;
	$liste = mysqldb::$req->fetchAll(PDO::FETCH_COLUMN, 0);
	if (count($liste) > 0) {
		$liste = join($liste, ", ") ;
		$q2 = 'DELETE FROM '.$table1.' WHERE num IN ('.$liste.')' ;
		echo $q2.'<br/>Suppression des '.$table1.' suivant en lien avec '.$table2.' (champ : '.$champ.') : '.$liste.' <br />' ;
		mysqldb::send($q2) ;
	}
	else {
		echo ('Il n\'y a rien à supprimer dans '.$table1.' en lien avec '.$table2.' (champ : '.$champ.')<br/>') ;
	}
}

nettoie("lienel", "evenement") ;
nettoie("lienel", "tag") ;
nettoie("lienep", "evenement") ;
nettoie("lienep", "personne") ;
nettoie("lienpf", "evenement") ;
nettoie("lienpf", "personne") ;
nettoie("lienpf", "fonction") ;
nettoie("lienpl", "personne") ;
nettoie("lienpl", "tag", "lieu") ;
nettoie("lientt", "tag", "tag1") ;
nettoie("lientt", "tag", "tag2") ;
nettoie("relationpersonnes", "personne", "personne1") ;
nettoie("relationpersonnes", "personne", "personne2") ;
?>

