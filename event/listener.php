<?php
/**
* phpBB Extension - marttiphpbb emaillogin
* @copyright (c) 2018 marttiphpbb <info@martti.be>
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
	/** @var user */
	protected $user;

	/** @var template */
	protected $template;

	/** @var language */
	protected $language;

	/** @var config */
	protected $config;

	/** @var bool */
	protected $admin;

	/**
	 * @param request $request
	*/
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

	static public function getSubscribedEvents()
	{
		return [
			'core.index_modify_page_title' => 'core_index_modify_page_title',
			'core.login_box_before'	=> 'core_login_box_before',
			'core.login_box_failed'	=> 'core_login_box_failed',
		];
	}

	public function core_login_box_before(event $event)
	{
		$admin = $event['admin'];

		if ($admin)
		{
			$this->admin = true;
			return;
		}
	
		$this->language->add_lang('error', 'marttiphpbb/emaillogin');

		$this->login_input_page();
	}

	public function is_admin_login()
	{
		return $this->admin;
	}

	public function core_index_modify_page_title(event $event)
	{
		if ($this->user->data['user_id'] != ANONYMOUS)
		{
			return;
		}

		$this->login_input_page();
	}

	protected function login_input_page()
	{
		$auth_method = $this->config['auth_method'];

		if (!in_array($auth_method, ['db_username_or_email', 'db_email']))
		{
			return;
		}

		error_log($auth_method);

		$this->language->add_lang('login', 'marttiphpbb/emaillogin');

		$this->template->assign_vars([
			'PROVIDER_TEMPLATE_FILE'		=> '@marttiphpbb_emaillogin/loginbox.html',
			'MARTTIPHPBB_EMAILLOGIN_AUTH' 	=> $auth_method,
		]);
	}

	public function core_login_box_failed(event $event)
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
