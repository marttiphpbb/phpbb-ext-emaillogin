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
use marttiphpbb\templateevents\service\events_cache;
use marttiphpbb\templateevents\model\file_location;

class extension extends \Twig_Extension
{
	const LINK_BASE = 'https://github.com/phpbb/phpbb/tree/prep-release-3.2.2/phpBB/';

	/** @var language */
	private $language;

	/** @var request */
	private $request;

	/** @var user */
	private $user;

	/** @var php_event_listener */
	private $php_event_listener;

	/** @var events_cache */
	private $events_cache;

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
	* @param events_cache
	*/
	public function __construct(
		request $request, 
		user $user, 
		language $language, 
		php_event_listener $php_event_listener, 
		events_cache $events_cache
	)
	{
		$this->request = $request;
		$this->user = $user;
		$this->language = $language;
		$this->php_event_listener = $php_event_listener;
		$this->events_cache = $events_cache;
	}

	/**
	* @return array
	*/
	public function getFunctions()
	{
		return [
			new \Twig_SimpleFunction('marttiphpbb_templateevents_render', [$this, 'marttiphpbb_templateevents_render']),
		];
	}

	public function marttiphpbb_templateevents_render(string $event_file, bool $first_and_last_in_html_body = false)
	{
		$event_name = explode('.', explode('/', $event_file)[2])[0];
		$template = '';

		$event_type = strpos($event_name, 'acp_') === 0 ? 'template_acp' : 'template';
		$event_data = $this->events_cache->get($event_type, $event_name);

		if (!$this->html_body)
		{
			if (!$first_and_last_in_html_body)
			{
				$this->events_in_html_head[] = [
					'name'	=> $event_name,
					'since'	=> $event_data['since'],
					'loc'	=> $event_data['loc'],			
				];
				return;
			}

			$first_and_last_in_html_body = false;

			$template .= $this->get_show_hide_button();

			$this->html_body = true;
			
			$this->render = $this->request->variable('templateevents', 0) ? true : false;

			if ($this->render)
			{
				foreach ($this->events_in_html_head as $e)
				{
					$template .= $this->render_template_event($event_type, $e['name'], $e, true);
				}
			}
		}

		if ($this->render)
		{
			$template .= $this->render_template_event($event_type, $event_name, $event_data);

			if ($first_and_last_in_html_body)
			{
				$template .= $this->render_php_events();
			}
		}

		return $template;
	}

	private function render_template_event(string $type, string $name, array $data, bool $in_html_head = false):string
	{
		$link_base = self::LINK_BASE . file_location::DIRECTORY[$type];
		$link = reset($data['loc']);

		if (count($data['loc']) > 1)
		{
			list($script_name) = explode('.', $this->user->page['page_name']);

			while (strpos(key($data['loc']), $script_name) !== 0 && $link !== false) 
			{
				$link = next($data['loc']);
			}
		}

		if ($link)
		{
			$link = $link_base . key($data['loc']) . '#L' . $link;
		}		

		$files = implode(', ', array_keys($data['loc']));

		$template = '<span class="templateevents';
		$template .= $in_html_head ? '-head' : '';
		$template .= '" title="' . $data['since'] . '&#10;' . $files . '">';
		$template .= $link ? '<a href="' . $link . '">' . $name . '</a>' : $name;
		$template .= '</span>';

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
		$template .= '</th><th>';
		$template .= $this->language->lang('MARTTIPHPBB_TEMPLATEEVENTS_SINCE');
		$template .= '</th><th>';
		$template .= $this->language->lang('MARTTIPHPBB_TEMPLATEEVENTS_FILENAME');	
//		$template .= '</th><th>';
//		$template .= 'Vars';	
		$template .= '</th></tr></thead><tbody>';

		$php_event_count_ary = $this->php_event_listener->get_count_ary();

		$link_base = self::LINK_BASE . file_location::DIRECTORY['php'];

		foreach ($php_event_count_ary as $name => $count)
		{
			$ev = $this->events_cache->get('php', $name);

			$files = [];

			foreach ($ev['loc'] as $file => $line)
			{
				if ($line)
				{
					$files[] = '<a href="' . $link_base . $file . '#L' . $line . '">' . $file . '</a>';
					continue;
				}

				$files[] = $file;
			}

			$files = implode('<br>', $files);

			$template .= '<tr><td>';
			$template .= $name;
			$template .= '</td><td>';
			$template .= $count;
			$template .= '</td><td>';
			$template .= $ev['since'];
			$template .= '</td><td>';
			$template .= $files;
//			$template .= '</td><td>';
//			$template .= $ev['vars'];
			$template .= '</td>';
		}

		$template .= '</tbody></table>';

		return $template;
	}
}
