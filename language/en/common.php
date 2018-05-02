<?php

/**
* phpBB Extension - marttiphpbb showphpbbevents
* @copyright (c) 2014 - 2018 marttiphpbb <info@martti.be>
* @license GNU General Public License, version 2 (GPL-2.0)
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

$lang = array_merge($lang, [

	'MARTTIPHPBB_SHOWPHPBBEVENTS_SHOW'				=> 'Show',
	'MARTTIPHPBB_SHOWPHPBBEVENTS_SHOW_EXPLAIN'		=> 'Show phpBB Events',
	'MARTTIPHPBB_SHOWPHPBBEVENTS_HIDE'				=> 'Hide',
	'MARTTIPHPBB_SHOWPHPBBEVENTS_HIDE_EXPLAIN'		=> 'Hide phpBB Events',
	'MARTTIPHPBB_SHOWPHPBBEVENTS_HEAD_EXPLAIN'		=> 'inside html head',
	'MARTTIPHPBB_SHOWPHPBBEVENTS_SELECT_EXPLAIN'	=> 'inside html select',
	'MARTTIPHPBB_SHOWPHPBBEVENTS_PHP_EVENT_NAME'	=> 'PHP Event',
	'MARTTIPHPBB_SHOWPHPBBEVENTS_PHP_EVENT_COUNT'	=> 'Count',
	'MARTTIPHPBB_SHOWPHPBBEVENTS_SINCE'				=> 'Since',
	'MARTTIPHPBB_SHOWPHPBBEVENTS_FILENAME'			=> 'Filename',
]);
