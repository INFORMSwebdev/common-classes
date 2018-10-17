<?php

function __autoload($classname) {
    $filename = "/common/classes/" . $classname . ".php";
    include_once($filename);
}

?>