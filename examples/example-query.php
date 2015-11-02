<?php
    include('../vendor/autoload.php');
    include('../src/Webmaster/Webmaster.php');

    $service_email = '681219933803-um3geotp505pab1b91cj7r7npamh6nsn@developer.gserviceaccount.com';
    $private_key = file_get_contents(__DIR__.'/../src/config/API Project-c37d185c9cf9.p12');

    $webmaster_obj = new Webmaster($service_email, $private_key);
    $webmaster_obj->test();
