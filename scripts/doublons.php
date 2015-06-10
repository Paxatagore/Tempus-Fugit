<?php
//script qui identifie les personnes qui ont le même nom
require_once("../inc/centrale.php") ;
$p 		= new personne() ;
$p2		= new personne() ;
$lienep	= new lienep() ;
$lienpl	= new lienpl() ; 
$rpp	= new relationpersonnes() ;
$p->select() ;
while($p->next()) {
	$p2->select('WHERE num > '.$p->num.' AND nom = "'.$p->nom.'"') ;
	while($p2->next()) {
		echo "$p->num et $p2->num sont un doublons ($p->nom). <br/>" ;
		//1ère étape de la fusion : les liens EL sont tous transférés sur le premier individu
		$lienep->select("WHERE personne = $p2->num") ;
		while ($lienep->next()) {
			$lienep->personne = $p->num ;
			echo "Le lien ep sur l'événement $lienep->evenement est changé. <br/>" ;
			$lienep->save() ;
		}
		//2e étape de la fusion : les liens PL
		$lienpl->select("WHERE personne = $p2->num") ;
		while ($lienpl->next()) {
			$lienpl->personne = $p->num ;
			echo "Le lien pl sur le tag $lienpl->lieu est changé. <br/>" ;
			$lienpl->save() ;
		}
		//3e étape de la fusion : les liens PP
		$rpp->select("WHERE personne1 = $p2->num") ;
		while ($rpp->next()) {
			$rpp->personne1 = $p->num ;
			echo "Le lien RPP $rpp->num est changé. <br/>" ;
			$rpp->save() ;
		}
		$rpp->select("WHERE personne2 = $p2->num") ;
		while ($rpp->next()) {
			$rpp->personne2 = $p->num ;
			echo "Le lien RPP $rpp->num est changé. <br/>" ;
			$rpp->save() ;
		}
		//4e étape - suppression du 2e personnage.
		if ($p->autreNom == "" & $p2->autreNom != "") {
			$p->autreNom = $p2->autreNom ;
			echo "Je prends l'autre nom ($p->autreNom) du 2e personnage.  <br/>" ;
		}
		if ($p->dynastie == "" & $p2->dynastie != "") {
			$p->dynastie = $p2->dynastie ;
			echo "Je prends la dynastie ($p->dynastie) du 2e personnage.  <br/>" ;
		}
		if ($p->url == "" & $p2->url != "") {
			$p->url = $p2->url ;
			echo "Je prends l'url ($p->url) du 2e personnage.  <br/>" ;
		}
		if ($p->sexe == "" & $p2->sexe != "") {
			$p->sexe = $p2->sexe ;
			echo "Je prends le sexe ($p->sexe) du 2e personnage.  <br/>" ;
		}
		if ($p->notice == "" & $p2->notice != "") {
			$p->notice = $p2->notice ;
			echo "Je prends la notice ($p->notice) du 2e personnage.  <br/>" ;
		}
		$p->valide = 0 ;
		echo "Maintenant, je supprime p2 et je sauve p. <p></p>" ;
		$p->save() ;
		$p2->delete() ;
	}
}

echo "<br/>Le script a bien été exécuté.<br/>" ;
?>