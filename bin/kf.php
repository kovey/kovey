#!/usr/bin/php
<?php
/**
 *
 * @description 命令行工具
 *
 * @package     bin
 *
 * @time        2019-12-17 23:00:48
 *
 * @file  /Users/kovey/Documents/php/kovey/bin/kf.php
 *
 * @author      kovey
 */
require_once __DIR__ . '/command/main.php';

use Command\Command;

$opt = getopt('hp:vp:', array(
	'help',
	'project:',
	'type:',
	'handler:',
	'service:',
	'controller:',
	'version',
	'path:',
	'ptype:'
));

if (empty($opt)
	|| isset($opt['h'])
	|| isset($opt['help'])
) {
	Command::run('help', 'common');
}

if (isset($opt['v'])
	|| isset($opt['version'])
) {
	Command::run('version', 'common');
}

if (!isset($opt['type'])) {
	Command::run('help', 'common');
}

$path = $opt['path'] ?? __DIR__ . '/..';

if (isset($opt['handler'])) {
	if (!isset($opt['project'])) {
		Command::run('help', 'common');
	}

	Command::run($opt['type'], 'handler',  $path, $opt['project'], $opt['handler']);
}

if (isset($opt['service'])) {
	if (!isset($opt['project'])) {
		Command::run('help', 'common');
	}

	Command::run($opt['type'], 'service',  $path, $opt['project'], $opt['service']);
}

if (isset($opt['controller'])) {
	if (!isset($opt['project'])) {
		Command::run('help', 'common');
	}

	Command::run($opt['type'], 'controller',  $path, $opt['project'], $opt['controller']);
}

if (isset($opt['project'])) {
	if (!isset($opt['ptype'])) {
		Command::run('help', 'common');
	}

	Command::run($opt['type'], 'project', $path, $opt['project'], $opt['ptype']);
}
