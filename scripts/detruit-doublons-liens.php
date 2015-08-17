<?php
//script qui nettoie les doublons
require_once("../inc/centrale.php") ;

$q = "ALTER IGNORE TABLE lienel ADD UNIQUE INDEX(evenement,tag) ; 
ALTER IGNORE TABLE lienep ADD UNIQUE INDEX(evenement,personne) ;  
ALTER IGNORE TABLE lienll ADD UNIQUE INDEX(lieu1,lieu2, evenement) ; 
ALTER IGNORE TABLE lienpf ADD UNIQUE INDEX(fonction, personne, evenementdebut,evenementfin) ; 
ALTER IGNORE TABLE lienpl ADD UNIQUE INDEX(personne,lieu) ; 
ALTER IGNORE TABLE lientt ADD UNIQUE INDEX(tag1,tag2, relation, evenement) ;" ;
mysqldb::send($q) ;
echo "<br/>Le script a bien été exécuté.<br/>" ;
?>