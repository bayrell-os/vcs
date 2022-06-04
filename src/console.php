#!/usr/bin/env php
<?php

define( "BASE_PATH", __DIR__ );
require_once BASE_PATH . "/vendor/autoload.php";

global $app;

/* Run app */
$app = create_app_instance();
$app->init();
$app->runConsoleApp();
