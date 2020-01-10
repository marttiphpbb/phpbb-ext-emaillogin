<?php
/**
* phpBB Extension - marttiphpbb emaillogin
* @copyright (c) 2018 - 2020 marttiphpbb <info@martti.be>
* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace marttiphpbb\emaillogin\auth;

use marttiphpbb\emaillogin\auth\base;

class email extends base
{
	public function login($email, $password)
	{
		if ($this->listener->is_admin_login())
		{
			return parent::login($email, $password);
		}

        if (!$email)
        {
            return [
				'status'	=> LOGIN_ERROR_USERNAME,
				'error_msg'	=> 'MARTTIPHPBB_EMAILLOGIN_ERROR_NO_EMAIL',
				'user_row'	=> ['user_id' => ANONYMOUS],
            ];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        {
            return [
				'status'	=> LOGIN_ERROR_USERNAME,
				'error_msg'	=> 'MARTTIPHPBB_EMAILLOGIN_ERROR_NO_VALID_EMAIL',
				'user_row'	=> ['user_id' => ANONYMOUS],
				'marttiphpbb_emaillogin_err_sprintf'
					=> $this->get_email_err_sprintf_args($email),
            ];
		}

		return parent::login_by_email($email, $password);
	}
}
