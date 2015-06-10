<?php
if(!@copy("http://upload.wikimedia.org/wikipedia/commons/thumb/7/7a/Agen.svg/80px-Agen.svg.png", "../dataimg/80px-Agen.svg.png"))
{
    $errors= error_get_last();
    echo "COPY ERROR: ".$errors['type'];
    echo "<br />\n".$errors['message'];
} else {
    echo "File copied from remote!";
}
?>