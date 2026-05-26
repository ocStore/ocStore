<?php
// Version
define('VERSION', '4.1.0.3b');
define('VERSION_CORE', 'ocStore');
define('VERSION_BUILD', '0001');
define('VERSION_LANGPACK', 'UK-EN');

// Configuration
if (is_file('config.php')) {
	require_once('config.php');
}

// Install
if (!defined('DIR_APPLICATION')) {
	header('Location: install/index.php');
	exit();
}

// Startup
require_once(DIR_SYSTEM . 'startup.php');

// Framework
require_once(DIR_SYSTEM . 'framework.php');
