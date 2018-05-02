<?php

/**
* phpBB Extension - marttiphpbb emaillogin
* @copyright (c) 2018 marttiphpbb <info@martti.be>
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

	'MARTTIPHPBB_EMAILLOGIN_SHOW'				=> 'Show',
	'MARTTIPHPBB_EMAILLOGIN_SHOW_EXPLAIN'		=> 'Email Login',
	'MARTTIPHPBB_EMAILLOGIN_HIDE'				=> 'Hide',
	'MARTTIPHPBB_EMAILLOGIN_HIDE_EXPLAIN'		=> 'Hide phpBB Events',
	'MARTTIPHPBB_EMAILLOGIN_HEAD_EXPLAIN'		=> 'inside html head',
	'MARTTIPHPBB_EMAILLOGIN_SELECT_EXPLAIN'	=> 'inside html select',
	'MARTTIPHPBB_EMAILLOGIN_PHP_EVENT_NAME'	=> 'PHP Event',
	'MARTTIPHPBB_EMAILLOGIN_PHP_EVENT_COUNT'	=> 'Count',
	'MARTTIPHPBB_EMAILLOGIN_SINCE'				=> 'Since',
	'MARTTIPHPBB_EMAILLOGIN_FILENAME'			=> 'Filename',
]);
