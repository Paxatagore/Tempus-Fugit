<?php
//Script qui a servi une fois, pour renommer en masse des images de tags
$dirname = '../img_tags/';
$dir = opendir($dirname); 

while(false !== ($file = readdir($dir))) {
	if($file != '.' && $file != '..' && !is_dir($dirname.$file)) {
		
		if (substr($file, 0, 12) == "drapeau_tag_") {
			echo "$file<br/>" ;
			$n = substr($file, 12) ;
			$o = rename($dirname.$file, $dirname."drapeau_".$n ) ;
			if ($o) {
				echo "Je renomme $file en drapeau_".$n."<br/>" ;
			}
			else {
				echo "Echec dans le renommage de $file en drapeau_".$n."<br/>" ;
				$errors= error_get_last();
				echo "ERROR: ".$errors['type'];
				echo "<br />\n".$errors['message'];
			}
		}
	}
}

closedir($dir) ;
echo "Script achevé." ;
?>
