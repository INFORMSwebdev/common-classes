<?php

// use this autoloader when the application requires another library that has its own autoloader already

function common_autoload($classname) {
    $filename = "/common/classes/" . $classname . ".php";
    include_once($filename);
}

spl_autoload_register('common_autoload');

?>