#!/usr/bin/env php
<?php

// var_dump( $_SERVER );

define( "BASE_PATH", __DIR__ );
require_once BASE_PATH . "/vendor/autoload.php";

/* Run web app */
\App\Admin\Module::createApp()->runConsoleApp();
