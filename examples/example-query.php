<?php
    include('../vendor/autoload.php');
    include('../src/Webmaster/Webmaster.php');
    $webmaster_obj = new Webmaster();
    echo 'Hello '.$webmaster_obj->test();