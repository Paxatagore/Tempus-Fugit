<?php
//script qui permet d'uploader une carte
?>
<form method='post' action='upload_carte2.php' enctype="multipart/form-data">
Fichier : <input type="file" accept=image/*" name="carte">
<input type="hidden" name="MAX_FILE_SIZE" value="500000">
<input type="submit" value="Uploader" >
</form>
