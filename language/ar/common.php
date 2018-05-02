<?php

/**
*  phpBB Extension - marttiphpbb showphpbbevents
* @copyright (c) 2014 - 2018 marttiphpbb <info@martti.be>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* Translated By : Basil Taha Alhitary - www.alhitary.net
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

	'MARTTIPHPBB_SHOWPHPBBEVENTS_SHOW'					=> 'عرض',
	'MARTTIPHPBB_SHOWPHPBBEVENTS_HIDE'					=> 'اخفاء',
	'MARTTIPHPBB_SHOWPHPBBEVENTS_INSIDE_HTML_HEAD'		=> 'ضمن ترويسة html',
	'MARTTIPHPBB_SHOWPHPBBEVENTS_PHP_EVENT_NAME'			=> 'PHP Event',
	'MARTTIPHPBB_SHOWPHPBBEVENTS_PHP_EVENT_COUNT'		=> 'Count',
	'MARTTIPHPBB_SHOWPHPBBEVENTS_SINCE'					=> 'Since',
	'MARTTIPHPBB_SHOWPHPBBEVENTS_FILENAME'				=> 'Filename',
]);
