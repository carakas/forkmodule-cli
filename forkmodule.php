#!/usr/bin/env php
<?php
// Require the composer autoloader
require_once(__DIR__ . '/vendor/autoload.php');

// Create new pimple instance
$app = new Pimple();


// Set template directory
$app['themedir'] = __DIR__ . '/src/templates';


/**
 * Services
 */
// Create shared twig filesystem loader
$app['twig.loader'] = $app->share(function() use ($app) {
	return new Twig_Loader_Filesystem($app['themedir']);
});

// Create shared twig template engine
$app['twig'] = $app->share(function() use ($app) {
	return new Twig_Environment($app['twig.loader'], array('debug' => true));
});

// Create forkmodule object
$app['forkmodule'] = $app->share(function() use ($app) {
	return new Forkmodule\Forkmodule($app);
});


/**
 * Output method
 */
$app['output'] = $app->protect(function($string, $mode = 'normal') use ($app) {
	switch ($mode) {
		case 'welcome':
			echo "\033[1;31m".$string."\033[0m\n";
			break;

		case 'title':
			echo "\n--> \033[0;32m".$string."\033[0m\n";
			break;

		case 'notice':
			echo "\033[0;36m".$string."\033[0m\n";
			break;

		case 'error':
			echo "\033[0;31m".$string."\033[0m\n";
			break;

		case 'normal':
		default:
			echo $string."\n";
			break;
	}
});


/**
 * Initialize the application
 */
$app['init'] = $app->protect(function() use ($app, $argv) {
	/**
	 * Get the module name
	 */
	// Did we get a name on the command line?
	if (isset($argv[1]) && trim($argv[1]) !== '') {
		$app['module.name'] = mb_strtolower(trim($argv[1]));
	}

	// Ask for it
	else {
		$app['output']('Name of the module:', 'notice');
		$answer = stream_get_line(STDIN, 1024, PHP_EOL);
		if (trim($answer) !== '') {
			$app['module.name'] = mb_strtolower(trim($answer));
		}
		else {
			$app['module.name'] = 'demo';
		}
	}


	/**
	 * Get the location for the installation
	 */
	// Current directory
	$wd = getcwd();
	$app['forkdir'] = false;

	// Is this a forkcms directory? Or any of the above directories?
	while (empty($app['forkdir']) && $wd !== '/') {
		// Is this a forkcms directory?
		if (is_dir($wd . '/frontend/modules') && is_dir($wd . '/backend/modules')) {
			$app['forkdir'] = $wd;
		}
		else {
			$wd = dirname($wd);
		}
	}

	// Did we find a directory?
	if ($app['forkdir'] === false) {
		$app['output']('This is not a forkcms directory.', 'error');
		exit;
	}


	/**
	 * Check if the module exists
	 */
	if (
		is_dir($app['forkdir'] . '/frontend/modules/' . $app['module.name'])
		|| is_dir($app['forkdir'] . '/backend/modules/' . $app['module.name'])
	) {
		$app['output']('A module with this name already exists.', 'error');
		exit;
	}


	/**
	 * Which actions and widgets should we create?
	 */
	foreach (array('frontend', 'backend') as $application) {
		foreach (array('actions', 'widgets') as $controller) {
			$app[$application.'.'.$controller] = array();
			$answer = 'index';

			while (!empty($answer)) {
				$app['output']('Create '.$application.' '.$controller.' (Empty answer to continue):', 'notice');
				$answer = stream_get_line(STDIN, 1024, PHP_EOL);
				if (trim($answer) !== '') {
					$app[$application.'.'.$controller] = array_merge($app[$application.'.'.$controller], array(mb_strtolower(trim($answer))));
				}
			}
		}
	}


	/**
	 * Create a summary
	 */
	$app['output']('Summary', 'title');
	$app['output']('Module name:    ' . $app['module.name'], 'normal');
	$app['output']('Fork directory: ' . $app['forkdir'], 'normal');
	$app['output']('Is this info correct? (Y/n)', 'notice');
	$answer = stream_get_line(STDIN, 1024, PHP_EOL);
	if ($answer !== 'Y') {
		$app['output']('Aborting...', 'error');
		exit;
	}
});


/**
 * Main
 */
$app['run'] = $app->protect(function() use ($app) {
	// Output welcome message
	$app['output']('Forkmodule', 'welcome');

	// Initialize
	$app['output']('Initializing...', 'title');
	$app['init']();

	// Create the module
	$app['output']('Creating directory structure...', 'title');
	$app['forkmodule']();
});


/**
 * Run the main routine
 */
$app['run']();
