<?php
/**
* phpBB Extension - marttiphpbb emaillogin
* @copyright (c) 2018 marttiphpbb <info@martti.be>
* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace marttiphpbb\emaillogin\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use phpbb\event\data as event;
use phpbb\request\request;
use phpbb\language\language;
use phpbb\user;

class listener implements EventSubscriberInterface
{
	/** @var request */
	protected $request;

	/** @var user */
	protected $user;

	/** @var language */
	protected $language;

	/**
	 * @param request $request
	*/
	public function __construct(
		request $request,
		user $user,
		language $language
	)
	{
		$this->request = $request;
		$this->user = $user;
		$this->language = $language;
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
		$this->language->add_lang('login', 'marttiphpbb/emaillogin');
		$this->language->add_lang('error', 'marttiphpbb/emaillogin');
	}

	public function core_index_modify_page_title(event $event)
	{
		$this->language->add_lang('login', 'marttiphpbb/emaillogin');
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
