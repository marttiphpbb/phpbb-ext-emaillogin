<?php

/**
* phpBB Extension - marttiphpbb emaillogin
* @copyright (c) 2018 - 2020 marttiphpbb <info@martti.be>
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
	'MARTTIPHPBB_EMAILLOGIN_ERROR_NO_EMAIL'
		=> 'You need to specify an email in order to login.',
	'MARTTIPHPBB_EMAILLOGIN_ERROR_NO_USERNAME_OR_EMAIL'
		=> 'You need to specify a username or email 
			in order to login.',
	'MARTTIPHPBB_EMAILLOGIN_ERROR_NO_VALID_EMAIL'
		=> 'The email address %1$s is not valid.',
	'MARTTIPHPBB_EMAILLOGIN_LOGIN_ERROR_EMAIL'
		=> 'The email (%1$s) you have specified is incorrect. 
			Please check your email and try again. 
			If you continue to have problems please 
			contact the %2$sBoard Administrator%3$s.',
	'MARTTIPHPBB_EMAILLOGIN_ERROR_EMAIL_DUPLICATE'
		=> 'The email %1$s can not be used because 
		it is present multiple times in the database.
		Please contact the %2$sBoard Administrator%3$s.',		
]);
