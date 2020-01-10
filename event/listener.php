<?php
/**
* phpBB Extension - marttiphpbb emaillogin
* @copyright (c) 2018 - 2020 marttiphpbb <info@martti.be>
* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace marttiphpbb\emaillogin\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use phpbb\event\data as event;
use phpbb\template\template;
use phpbb\language\language;
use phpbb\user;
use phpbb\config\config;

class listener implements EventSubscriberInterface
{
	protected $user;
	protected $template;
	protected $language;
	protected $config;
	protected $is_admin_login = false;

	public function __construct(
		user $user,
		template $template,
		language $language,
		config $config
	)
	{
		$this->user = $user;
		$this->template = $template;
		$this->language = $language;
		$this->config = $config;
	}

	static public function getSubscribedEvents():array
	{
		return [
			'core.index_modify_page_title'	=> 'core_index_modify_page_title',
			'core.login_box_before'			=> 'core_login_box_before',
			'core.login_box_failed'			=> 'core_login_box_failed',
		];
	}

	public function core_login_box_before(event $event):void
	{
		$admin = $event['admin'];

		if ($admin)
		{
			$this->is_admin_login = true;
			return;
		}

		$this->language->add_lang('error', 'marttiphpbb/emaillogin');

		$this->login_input_page();
	}

	public function is_admin_login():bool
	{
		return $this->is_admin_login;
	}

	public function core_index_modify_page_title(event $event):void
	{
		if ($this->user->data['user_id'] != ANONYMOUS)
		{
			return;
		}

		$this->login_input_page();
	}

	protected function login_input_page():void
	{
		$auth_method = $this->config['auth_method'];

		if (!in_array($auth_method, ['db_username_or_email', 'db_email']))
		{
			return;
		}

		$this->language->add_lang('login', 'marttiphpbb/emaillogin');

		$this->template->assign_vars([
			'PROVIDER_TEMPLATE_FILE'		=> '@marttiphpbb_emaillogin/loginbox.html',
			'MARTTIPHPBB_EMAILLOGIN_AUTH' 	=> $auth_method,
		]);
	}

	public function core_login_box_failed(event $event):void
	{
		$err = $event['err'];
		$result = $event['result'];

		if (isset($result['marttiphpbb_emaillogin_err_sprintf']))
		{
			$err = vsprintf($err, $result['marttiphpbb_emaillogin_err_sprintf']);
			$event['err'] = $err;
		}
	}
}
