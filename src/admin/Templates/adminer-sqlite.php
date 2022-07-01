<?php

function adminer_object() {
	include_once __DIR__ . "/plugin.php";
	include_once __DIR__ . "/login-password-less.php";
	return new AdminerPlugin(array(
		// TODO: inline the result of password_hash() so that the password is not visible in source codes
		// new AdminerAlterTable(),
		new AdminerLoginPasswordLess(password_hash("admin", PASSWORD_DEFAULT)),
	));
}

//set_include_path( __DIR__ . '/adminer' );
include __DIR__ . '/adminer.php';
//include __DIR__ . '/adminer/index.php';
