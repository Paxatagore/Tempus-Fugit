<?php
//script qui permet d'uploader une carte
//require_once("../inc/centrale.php") ;
if(isset($_FILES['carte'])) { 
     $dossier = '../cartes/';
     $fichier = basename($_FILES['carte']['name']);
     if(move_uploaded_file($_FILES['carte']['tmp_name'], $dossier . $fichier)) { //Si la fonction renvoie TRUE, c'est que ça a fonctionné... 
          echo 'Upload effectué avec succès !';
     }
     else { //Sinon (la fonction renvoie FALSE).
          echo 'Echec de l\'upload !';
     }
}
else {
	echo "Pas de fichier joint." ;
}
echo '<p></p><a href="../cartes">La liste des cartes</a>' ;
?>
