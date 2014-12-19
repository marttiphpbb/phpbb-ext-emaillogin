<?php
/**
*  phpBB Extension - marttiphpbb templateevents
* @copyright (c) 2014 marttiphpbb <info@martti.be>
* @license http://opensource.org/licenses/MIT
*/

namespace marttiphpbb\templateevents\event;

use phpbb\auth\auth;
use phpbb\request\request;
use phpbb\template\twig\twig as template;
use phpbb\user;

/**
* @ignore
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class main_listener implements EventSubscriberInterface
{
	/* @var auth */
	protected $auth;

	/* @var request */
	protected $request;
	
	/* @var template */
	protected $template;
	
	/* @var user */
	protected $user;	

	/* @var string */
	protected $phpbb_root_path;
	
	/* @var string */
	protected $php_ext;

	/**
	 * @param auth $auth
	 * @param request $request
	 * @param template $template
	 * @param user $user
	 * @param string $phpbb_root_path
	 * @param string $php_ext
	*/
	public function __construct(
		auth $auth,
		request $request,
		template $template,
		user $user,
		$phpbb_root_path,
		$php_ext
	)
	{	
		$this->auth = $auth;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'		=> 'load_language_on_setup',
			'core.page_footer'		=> 'core_page_footer',
			'core.append_sid'		=> 'core_append_sid',
		);
	}

	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'marttiphpbb/templateevents',
			'lang_set' => 'common',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}
	
	public function core_page_footer($event)
	{
		
		if ($this->auth->acl_get('a_board'))
		{
			$templateevents = ($this->request->variable('templateevents', 0)) ? true : false;
			
			
			if ($templateevents)
			{		
				$this->template->assign_var('U_TEMPLATEEVENTS_HIDE', append_sid($this->phpbb_root_path . 'index.' . $this->php_ext, array('templateevents' => 0)));
				$this->template->assign_var('S_TEMPLATEEVENTS', 1);
			}
			else
			{
				$this->template->assign_var('U_TEMPLATEEVENTS_SHOW', append_sid($this->phpbb_root_path . 'index.' . $this->php_ext, array('templateevents' => 1)));
			}
		}
	}
	
	public function core_append_sid($event)
	{
		$params = $event['params'];
		
		if (!(isset($params['templateevents']) && is_array($params) && $params['templateevents'] === 0)
			&& $this->request->variable('templateevents', 0)
			&& $this->auth->acl_get('a_board'))
		{
			if (is_string($params) && $params != '')
			{
				$params .= '&templateevents=1';
			}
			else
			{
				if ($params === false)
				{
					$params = array();
				}		
				$params['templateevents'] = 1;		
			}
			$event['params'] = $params;			
		}
	}
}
