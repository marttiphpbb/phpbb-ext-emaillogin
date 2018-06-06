<?php
/**
* phpBB Extension - marttiphpbb emaillogin
* @copyright (c) 2018 marttiphpbb <info@martti.be>
* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace marttiphpbb\emaillogin\auth;

use phpbb\auth\provider\db as db_provider;

class base extends db_provider
{
	protected function login_by_email(string $email, string $password):array
	{
		$count = 0;

		$sql = 'select username
			from ' . USERS_TABLE . ' 
			where user_email_hash = ' . phpbb_email_hash($email);

		$result = $this->db->sql_query($sql);

		while($field = $this->db->sql_fetchfield('username'))
		{
			$count++;
			$username = $field;
		}

		$this->db->sql_freeresult($result);

		if (!$count)
		{
			error_log('no email for user');

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
			error_log('duplicate email for user');

			return [
				'status'	=> LOGIN_ERROR_USERNAME,
				'error_msg'	=> 'MARTTIPHPBB_EMAILLOGIN_ERROR_EMAIL_DUPLICATE',	
				'user_row'	=> ['user_id' => ANONYMOUS],
				'marttiphpbb_emaillogin_err_sprintf' 
					=> $this->get_email_err_sprintf_args($email),
			];
		}

		error_log('base login: ' . $username);

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
