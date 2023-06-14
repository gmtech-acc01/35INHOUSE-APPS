<?php

// default timezone setting. - should be set on a per-server setup.
date_default_timezone_set("Africa/Nairobi");

function __autoload($class) {
	$fileName = str_replace("\\", "/", $class) . ".php";
	require_once($fileName);
}
?>