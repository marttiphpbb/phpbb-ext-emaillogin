<?php
/**
* phpBB Extension - marttiphpbb emaillogin
* @copyright (c) 2018 marttiphpbb <info@martti.be>
* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace marttiphpbb\emaillogin\auth;

use marttiphpbb\emaillogin\auth\base;

class username_or_email extends base
{
	public function login($username_or_email, $password)
	{
		$listener = $this->phpbb_container->get('marttiphpbb.emaillogin.listener');

		if ($listener->is_admin_login())
		{
			return parent::login($username_or_email, $password);
		}

        if (!$username_or_email)
        {
			error_log('no username or email');

            return [
				'status'	=> LOGIN_ERROR_USERNAME,
				'error_msg'	=> 'MARTTIPHPBB_EMAILLOGIN_ERROR_NO_USERNAME_OR_EMAIL',
				'user_row'	=> ['user_id' => ANONYMOUS],
            ];
        }

        if (filter_var($username_or_email, FILTER_VALIDATE_EMAIL))
        {
			return parent::login_by_email($username_or_email, $password);
		}
		
		return parent::login($username_or_email, $password);
	}
}
