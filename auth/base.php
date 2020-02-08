<?php
/**
* phpBB Extension - marttiphpbb emaillogin
* @copyright (c) 2018 - 2020 marttiphpbb <info@martti.be>
* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace marttiphpbb\emaillogin\auth;

use phpbb\auth\provider\db as db_provider;
use phpbb\captcha\factory;
use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\passwords\manager;
use phpbb\request\request_interface;
use phpbb\user;
use marttiphpbb\emaillogin\event\listener;

class base extends db_provider
{
	protected $listener;

	public function __construct(
		factory $captcha_factory,
		config $config,
		driver_interface $db,
		manager $passwords_manager,
		request_interface $request,
		user $user,
		string $phpbb_root_path,
		string $php_ext,
		listener $listener
	)
	{
		parent::__construct(
			$captcha_factory,
			$config, $db,
			$passwords_manager,
			$request,
			$user,
			$phpbb_root_path,
			$php_ext
		);

		$this->listener = $listener;
	}

	protected function login_by_email(string $email, string $password):array
	{
		$count = 0;

		$email = strtolower($email);

		$sql = 'select username
			from ' . USERS_TABLE . '
			where user_email = \'' . $this->db->sql_escape($email) . '\'';
		$result = $this->db->sql_query($sql);

		while($field = $this->db->sql_fetchfield('username'))
		{
			$count++;
			$username = $field;
		}

		$this->db->sql_freeresult($result);

		if (!$count)
		{
			return [
				'status'	=> LOGIN_ERROR_USERNAME,
				'error_msg'	=> 'MARTTIPHPBB_EMAILLOGIN_LOGIN_ERROR_EMAIL',
				'user_row'	=> ['user_id' => ANONYMOUS],
				'marttiphpbb_emaillogin_err_sprintf'
					=> $this->get_email_err_sprintf_args($email),
			];
		}

		if ($count > 1)
		{
			return [
				'status'	=> LOGIN_ERROR_USERNAME,
				'error_msg'	=> 'MARTTIPHPBB_EMAILLOGIN_ERROR_EMAIL_DUPLICATE',
				'user_row'	=> ['user_id' => ANONYMOUS],
				'marttiphpbb_emaillogin_err_sprintf'
					=> $this->get_email_err_sprintf_args($email),
			];
		}

        return parent::login($username, $password);
	}

	protected function get_email_err_sprintf_args(string $email):array
	{
		return [
			$email,
			'<a href="' . append_sid($this->phpbb_root_path . 'memberlist.' . $this->php_ext, 'mode=contactadmin') . '">',
			'</a>',
		];
	}
}
