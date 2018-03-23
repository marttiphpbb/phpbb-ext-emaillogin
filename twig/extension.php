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


class extension extends \Twig_Extension
{
	/** @var language */
	private $language;

	/** @var request */
	private $request;

	/** @var user */
	private $user;

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
	*/
	public function __construct(request $request, user $user, language $language)
	{
		$this->request = $request;
		$this->user = $user;
		$this->language = $language;
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

	public function marttiphpbb_templateevents_render(string $event_file, bool $first_event_in_html_body = false)
	{
		$event_name = explode('.', explode('/', $event_file)[2])[0];
		$template = '';

		if (!$this->html_body)
		{
			if (!$first_event_in_html_body)
			{
				$this->events_in_html_head[] = $event_name;
				return;
			}

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
}


