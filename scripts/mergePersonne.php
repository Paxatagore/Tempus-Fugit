<?php
//script qui lance la fusion de deux personnes
require_once("../inc/centrale.php") ;
?>
<script src="https://ajax.googleapis.com/ajax/libs/prototype/1.7.0.0/prototype.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/scriptaculous/1.9.0/scriptaculous.js?load=effects,controls,dragdrop"></script>
<form method='get' action='mergePersonne2.php'>
Personne 1 : <input type='text' value="" tabindex='1' name='p1' id='personne1' size='100'>
<div class='autocomplete' id='upd_personne1'></div>
<script type='text/javascript'>
	new Ajax.Autocompleter ("personne1", "upd_personne1", "../mainalpha/o_autocomplete.php", {method: 'post', paramName: 'premice', parameters: 'objet=personne&champ=nom', minChars: 2});
</script>
Personne 2 : <input type='text' value="" tabindex='1' name='p2' id='personne2' size='100'>
<div class='autocomplete' id='upd_personne2'></div>
<script type='text/javascript'>
	new Ajax.Autocompleter ("personne2", "upd_personne2", "../mainalpha/o_autocomplete.php", {method: 'post', paramName: 'premice', parameters: 'objet=personne&champ=nom', minChars: 2});
</script>
<input type="submit" value="Fusionner">
</form>
