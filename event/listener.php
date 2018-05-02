<?php
/**
* phpBB Extension - marttiphpbb showphpbbevents
* @copyright (c) 2014 - 2018 marttiphpbb <info@martti.be>
* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace marttiphpbb\showphpbbevents\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use phpbb\event\data as event;
use phpbb\request\request;
use phpbb\user;
use marttiphpbb\showphpbbevents\service\events_cache;
use marttiphpbb\showphpbbevents\event\php_event_listener;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	/** @var request */
	private $request;

	/** @var user */
	private $user;

	/** @var events_cache */
	private $events_cache;

	/** @var php_event_listener */
	private $php_event_listener;

	/**
	 * @param request $request
	*/
	public function __construct(
		request $request,
		user $user,
		events_cache $events_cache,
		php_event_listener $php_event_listener
	)
	{
		$this->request = $request;
		$this->user = $user;
		$this->events_cache = $events_cache;
		$this->php_event_listener = $php_event_listener;
	}

	static public function getSubscribedEvents()
	{
		return [
			'core.user_setup'		=> 'core_user_setup',
			'core.append_sid'		=> 'core_append_sid',
			'core.twig_environment_render_template_before'
				=> ['core_twig_environment_render_template_before', -1],
		];
	}

	public function core_user_setup(event $event)
	{
		$lang_set_ext = $event['lang_set_ext'];

		$lang_set_ext[] = [
			'ext_name' => 'marttiphpbb/showphpbbevents',
			'lang_set' => 'common',
		];

		$event['lang_set_ext'] = $lang_set_ext;
	}

	public function core_append_sid(event $event)
	{
		$params = $event['params'];

		if (is_string($params))
		{
			if (strpos($params, 'showphpbbevents=0') !== false)
			{
				return;
			}
		}

		if ($this->request->variable('showphpbbevents', 0))
		{
			if (is_string($params))
			{
				if ($params !== '')
				{
					$params .= '&';
				}

				$params .= 'showphpbbevents=1';
			}
			else
			{
				if ($params === false)
				{
					$params = [];
				}

				$params['showphpbbevents'] = 1;
			}

			$event['params'] = $params;
		}
	}

	public function core_twig_environment_render_template_before(event $event)
	{
		$context = $event['context'];

		$page_name = $this->user->page['page_name'];
		$query_string = $this->user->page['query_string'];	
		$query_string = str_replace(['&showphpbbevents=1', '&showphpbbevents=0'], '', $query_string);
		$query_string = str_replace(['showphpbbevents=1', 'showphpbbevents=0'], '', $query_string);
		$query_string = trim($query_string, '&');
		$query_string .= $query_string ? '&' : '';

		$php_count_ary = $this->php_event_listener->get_count_ary();
		$php_events = [];
		$events = $this->events_cache->get_all();

		foreach ($php_count_ary as $name => $count)
		{
			$php_events[$name] = $events['php'][$name];
			$php_events[$name]['count'] = $count;
		}

		$template = [
			'enable'	=> $this->request->variable('showphpbbevents', 0) ? true : false,
			'u_hide'	=> append_sid($page_name, $query_string . 'showphpbbevents=0'),
			'u_show'	=> append_sid($page_name, $query_string . 'showphpbbevents=1'),
			'php'		=> $php_events,
		];

		$context['marttiphpbb_showphpbbevents'] = $template;
		$event['context'] = $context;		
	}	
}
