<?php
//script qui affiche les villes et permet de les localiser
require_once("../inc/centrale.php") ;

?>
<form action="villes2.php" method="post">
<table>
	<thead>
	<td>Num</td>
	<td>Nom</td>
	<td>Occurences</td>
	<td>Localisation</td>
	</thead>
	<tbody>
<?php
$t = new tag() ;
$t->select("WHERE nature = 1 OR nature = 5 ORDER BY nom") ;

while($t->next()) {
	if ($t->latitude == 0 AND $t->longitude == 0) {
		echo "<tr>
			<td>$t->num</td>
			<td>$t->nom</td>
			<td>$t->occurence</td>
			<td><input type='text' value='$t->latitude' name='latitude$t->num'><input type='text' value='$t->longitude' name='longitude$t->num'>
			<td><input type='submit' value='ok'>
			</tr>" ;
	}
}
?>
	</tbody>
</table>
</form>
</body>
</html>
