<?php
/**
* phpBB Extension - marttiphpbb templateevents
* @copyright (c) 2014 - 2018 marttiphpbb <info@martti.be>
* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace marttiphpbb\templateevents\twig;

use phpbb\request\request;
use phpbb\user;
use phpbb\language\language;
use marttiphpbb\templateevents\event\php_event_listener;

class extension extends \Twig_Extension
{
	/** @var language */
	private $language;

	/** @var request */
	private $request;

	/** @var user */
	private $user;

	/** @var php_event_listener */

	/** @var bool */
	private $html_body = false;

	/** @var bool */
	private $render = false;

	/** @var array */
	private $events_in_html_head = [];

	/**
	* @param request
	* @param user
	* @param language
	* @param php_event_listener
	*/
	public function __construct(request $request, user $user, language $language, php_event_listener $php_event_listener)
	{
		$this->request = $request;
		$this->user = $user;
		$this->language = $language;
		$this->php_event_listener = $php_event_listener;
	}

	/**
	* @return array
	*/
	public function getFunctions()
	{
		return array(
			new \Twig_SimpleFunction('marttiphpbb_templateevents_render', [$this, 'marttiphpbb_templateevents_render']),
		);
	}

	public function marttiphpbb_templateevents_render(string $event_file, bool $first_and_last_in_html_body = false)
	{
		$event_name = explode('.', explode('/', $event_file)[2])[0];
		$template = '';

		if (!$this->html_body)
		{
			if (!$first_and_last_in_html_body)
			{
				$this->events_in_html_head[] = $event_name;
				return;
			}

			$first_and_last_in_html_body = false;

			$template .= $this->get_show_hide_button();

			$this->html_body = true;
			
			$this->render = $this->request->variable('templateevents', 0) ? true : false;

			if ($this->render)
			{
				foreach ($this->events_in_html_head as $name)
				{
					$template .= sprintf('<span class="templateevents-head">%s</span>', $name);
				}
			}
		}

		if ($this->render)
		{
			$template .= sprintf('<span class="templateevents">%s</span>', $event_name);

			if ($first_and_last_in_html_body)
			{
				$template .= $this->render_php_events();
			}
		}

		return $template;
	}

	private function get_show_hide_button()
	{
		$page_name = $this->user->page['page_name'];
		$query_string = $this->user->page['query_string'];

		$query_string = str_replace(['&templateevents=1', '&templateevents=0'], '', $query_string);
		$query_string = str_replace(['templateevents=1', 'templateevents=0'], '', $query_string);
		$query_string = trim($query_string, '&');
		$query_string .= $query_string ? '&' : '';
	
		$templateevents = $this->request->variable('templateevents', 0) ? true : false;

		if ($templateevents)
		{
			$class = 'templateevents-hide';
			$path = append_sid($page_name, $query_string . 'templateevents=0');
			$lang = $this->language->lang('MARTTIPHPBB_TEMPLATEEVENTS_HIDE');
		}
		else
		{
			$class = 'templateevents-show';
			$path = append_sid($page_name, $query_string . 'templateevents=1');
			$lang = $this->language->lang('MARTTIPHPBB_TEMPLATEEVENTS_SHOW');
		}
		
		return sprintf('<a class="%s" href="%s">%s</a>', $class, $path, $lang);
	}

	private function render_php_events()
	{
		$template = '<br><table class="marttiphpbb-templateevents-php"><thead><tr><th>';
		$template .= $this->language->lang('MARTTIPHPBB_TEMPLATEEVENTS_PHP_EVENT_NAME');
		$template .= '</th><th>';
		$template .= $this->language->lang('MARTTIPHPBB_TEMPLATEEVENTS_PHP_EVENT_COUNT');
		$template .= '</th></tr></thead><tbody>';

		$php_event_count_ary = $this->php_event_listener->get_count_ary();

		foreach ($php_event_count_ary as $name => $count)
		{
			$template .= '<tr><td>';
			$template .= $name;
			$template .= '</td><td>';
			$template .= $count;
			$template .= '</td>';
		}

		$template .= '</tbody></table>';

		return $template;
	}
}
