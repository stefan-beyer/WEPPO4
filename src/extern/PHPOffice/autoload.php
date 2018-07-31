<?php

spl_autoload_register(function ($class) {
	if (strpos($class, 'PhpOffice\\') !== 0) return;
	$class = substr($class, 10);
	$cn = explode('\\', $class);
	$module = array_shift($cn);
	$fn = WEPPO_ROOT . 'extern/PHPOffice/' . $module . '/src/' . $module . '/' . implode('/', $cn) . '.php';
	//echo o($fn);
	if (!file_exists($fn)) {
		echo o($fn);
		return;
	}
	require_once $fn;
});
