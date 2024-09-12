<?php
    require __DIR__ . '/../vendor/autoload.php';
    require __DIR__ . "/../src/bootstrap.php";

    Tester\Environment::setup();
    date_default_timezone_set('Europe/Prague');

    // type in terminal: vendor/bin/tester tests/