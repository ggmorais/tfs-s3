<?php 

//auto-require classes files
spl_autoload_register(function($class_name)
{
	require_once $class_name . '.php';
});

//credentials
$env = parse_ini_file('env.ini', true);
