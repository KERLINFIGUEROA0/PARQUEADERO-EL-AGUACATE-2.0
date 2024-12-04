<?php

function autoload($classes){
    require_once ($classes.".php");
}

spl_autoload_register("autoload");
?>
