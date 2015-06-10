<?php
//script qui affiche la liste de toutes les personnes
require_once("../inc/centrale.php") ;

?>
<table >
	<thead class="legendeTableau">
	<td>Num</td>
	<td>Nom</td>
	<td>Vie</td>
	<td>Occurences</td>
	</thead>
	
	<tbody>

<?php
$p = new personne() ;
$p->select("order by nom") ;
while($p->next()) {
	echo "<tr class='uneLigne'>
		<td>$p->num</td>
		<td>$p->nom</td>
		<td>$p->annaissance - $p->anmort</td>
		<td>$p->occurence</td>
		</tr>" ;
}
?>
	</tbody>
</table>	
<?php
die() ;
?>