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
    'ptype:',
    'logdir:'
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

$projectPath = str_replace('/vendor/kovey/kovey/bin', '', __DIR__);
$path = $opt['path'] ?? dirname($projectPath);
$project = $opt['project'] ?? basename($projectPath);

if (isset($opt['handler'])) {
    Command::run($opt['type'], 'handler',  $path, $project, $opt['handler']);
}

if (isset($opt['service'])) {
    Command::run($opt['type'], 'service',  $path, $project, $opt['service']);
}

if (isset($opt['controller'])) {
    Command::run($opt['type'], 'controller',  $path, $project, $opt['controller']);
}

if (!isset($opt['ptype'])) {
    Command::run('help', 'common');
}

Command::run($opt['type'], 'project', $path, $project, $opt['ptype'], $opt['logdir'] ?? '');
