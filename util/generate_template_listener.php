<?php
/**
* phpBB Extension - marttiphpbb showphpbbevents
* @copyright (c) 2014 - 2018 marttiphpbb <info@martti.be>
* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace marttiphpbb\showphpbbevents\util;

use marttiphpbb\showphpbbevents\util\event_type;

class generate_template_listener
{
	const LINK_BASE = 'https://github.com/phpbb/phpbb/tree/prep-release-3.2.2/phpBB/';
	const LINK_LINE = '#L';
	const INCLUDECSS = "{%- INCLUDECSS '@marttiphpbb_showphpbbevents/showphpbbevents.css' -%}\n";
	const INCLUDEJS = "{%- INCLUDEJS '@marttiphpbb_showphpbbevents/js/showphpbbevents.js' -%}\n";
	const ENABLE = "{%- if marttiphpbb_showphpbbevents.enable -%}\n%content%{%- endif -%}\n";
	const DISABLE = "{%- if not marttiphpbb_showphpbbevents.enable -%}\n%content%{%- endif -%}\n";
	const BUTTON_HIDE = <<<'EOT'
<a class="showphpbbevents-hide" href="{{- marttiphpbb_showphpbbevents.u_hide -}}" title="{{- lang('MARTTIPHPBB_SHOWPHPBBEVENTS_HIDE_EXPLAIN') -}}">
	{{- lang('MARTTIPHPBB_SHOWPHPBBEVENTS_HIDE') -}}
</a>
EOT;
	const BUTTON_SHOW = <<<'EOT'
<a class="showphpbbevents-show" href="{{- marttiphpbb_showphpbbevents.u_show -}}" title="{{- lang('MARTTIPHPBB_SHOWPHPBBEVENTS_SHOW_EXPLAIN') -}}">
	{{- lang('MARTTIPHPBB_SHOWPHPBBEVENTS_SHOW') -}}
</a>
EOT;
	const SCRIPT_NAME_CONDITION = "{%- if SCRIPT_NAME == '%script_name%' -%}\n%content%{%- endif -%}\n";
	const TITLE_NEWLINE = '&#10;';
	const THIS_FILE_INDICATOR = '*';
	const CLASS_TEMPLATE_EVENT = 'showphpbbevents';
	const CLASS_TEMPLATE_EVENT_HEAD = 'showphpbbevents-head';
	const EVENT_LINK = "<a class=\"%class%\" title=\"%title%\" href=\"%link%\">%name%</a>\n";
	const EVENT_LINK_TITLE_SPAN = "<a class=\"%class%\" href=\"%link%\"><span title=\"%title%\">%name%</span></a>\n";
	const EVENT_HEAD_COMMENT = "{# Rendering of the head events is delayed until the first event in the body #}\n";
	const EVENT_LISTENER_COMMENT = "{# This file was generated with the ext-showphpbbevents:generate command. #}\n";
	const PHP_EVENTS = <<<'EOT'
<br>
<table class="marttiphpbb-showphpbbevents-php">
	<thead>
		<tr>
			<th>{{- lang('MARTTIPHPBB_SHOWPHPBBEVENTS_PHP_EVENT_NAME') -}}</th>
			<th>{{- lang('MARTTIPHPBB_SHOWPHPBBEVENTS_PHP_EVENT_COUNT') -}}</th>
			<th>{{- lang('MARTTIPHPBB_SHOWPHPBBEVENTS_SINCE') -}}</th>
			<th>{{- lang('MARTTIPHPBB_SHOWPHPBBEVENTS_FILENAME') -}}</th>
		</tr>
	</thead>
	<tbody>
	{%- for name, e in marttiphpbb_showphpbbevents.php -%}
		<tr>
			<td>{{- name -}}</td>
			<td>{{- e.count -}}</td>
			<td>{{- e.since -}}</td>
			<td>
			{%- for file, line in e.loc -%}
				{%- if line -%}
					<a href="%link_base%{{- file -}}#L{{- line -}}">
						{{- file -}}
					</a>
				{%- else -%}
					{{- file -}}
				{%- endif -%}
				{%- if not loop.last -%}<br>{%- endif -%}
			{%- endfor -%}
			</td>
		</tr>
	{%- endfor -%}
	</tbody>
</table>

EOT;

	public static function get(array $template_events, event_type $type, string $name):string
	{
		$data = $template_events[$type->get()][$name];
		$in_head = $data['in_head'] ?? false;
		$render_button = $data['first_in_body'] ?? false;
		$render_php_events = $data['last_in_body'] ?? false;
		$include_js = $data['last_in_body'] ?? false;
		$include_css = $data['include_css'] ?? false;
		$since = $data['since'] ?? '';
		$loc = $data['loc'] ?? [];
		$delayed_head_events = [];

		if (isset($data['delayed_head_events']) && is_array($data['delayed_head_events']))
		{
			foreach($data['delayed_head_events'] as $delayed_head_event)
			{
				$delayed_head_events[$delayed_head_event] = $template_events[$type->get()][$delayed_head_event];
			}
		}

		$content = self::get_template_event_listener(
			$type, $name, $loc,
			$since, $in_head,
			$delayed_head_events, $include_css,
			$render_button, $render_php_events,
			$include_js
		);

		return self::EVENT_LISTENER_COMMENT . $content;
	}

	private static function get_template_event_listener(
		event_type $type,
		string $name,
		array $loc,
		string $since = '',
		bool $in_head = false,
		array $delayed_head_events = [],
		bool $include_css = false,
		bool $render_button = false,
		bool $render_php_events = false,
		bool $include_js):string
	{
		$str = $include_css ? self::INCLUDECSS : '';

		if ($in_head)
		{
			return self::EVENT_HEAD_COMMENT . $str;
		}

		if ($render_button)
		{
			$str .= str_replace('%content%', self::BUTTON_HIDE, self::ENABLE);
			$str .= str_replace('%content%', self::BUTTON_SHOW, self::DISABLE);
		}

		$content = '';

		if (count($delayed_head_events))
		{
			foreach($delayed_head_events as $head_event_name => $ary)
			{
				$content .= self::get_template_event($type, $head_event_name, $ary['loc'], $ary['since'], true);
			}
		}

		$content .= self::get_template_event($type, $name, $loc, $since);

		if ($render_php_events)
		{
			$content .= str_replace('%link_base%', self::LINK_BASE, self::PHP_EVENTS);
		}

		if ($include_js)
		{
			$content .= self::INCLUDEJS;
		}

		return $str . str_replace('%content%', $content, self::ENABLE);
	}

	private static function get_template_event(
		event_type $type,
		string $name,
		array $loc,
		string $since,
		bool $is_head_event = false):string
	{
		reset($loc);

		if (count($loc) === 1)
		{
			$link = key($loc);
			return self::get_template_event_link($type, $name, $loc, $link, $since, $is_head_event);
		}

		$str = '';

		foreach ($loc as $file => $line)
		{
			list($script_name) = explode('_', $file);

			$content = self::get_template_event_link($type, $name, $loc, $file, $since, $is_head_event);

			$search = ['%script_name%', '%content%'];
			$replace = [$script_name, $content];

			$str .= str_replace($search, $replace, self::SCRIPT_NAME_CONDITION);
		}

		return $str;
	}

	private static function get_template_event_link(
		event_type $type,
		string $name,
		array $loc,
		string $link,
		string $since,
		bool $is_head_event = false):string
	{
		$files = array_keys($loc);

		if (count($loc) > 1)
		{
			foreach ($files as &$file)
			{
				if ($link === $file)
				{
					$file .= self::THIS_FILE_INDICATOR;
					break;
				}
			}
		}

		$line = $loc[$link];
		$link_base = self::LINK_BASE . $type->get_location();
		$link = $line ? $link_base . $link . self::LINK_LINE . $line : false;

		$since = $since ? [$since] : [];
		$title = implode(self::TITLE_NEWLINE, array_merge($since, $files));
		$class = $is_head_event ? self::CLASS_TEMPLATE_EVENT_HEAD : self::CLASS_TEMPLATE_EVENT;

		$search = ['%class%', '%title%', '%link%', '%name%'];
		$replace = [$class, $title, $link, $name];

		// because the title attribute inside '.breadcrumbs a' gets replaced
		// by some Javascript in prosilver, it's moved to a <span>
		$template = strpos($name, 'breadcrumb_') === false ? self::EVENT_LINK : self::EVENT_LINK_TITLE_SPAN;

		return str_replace($search, $replace, $template);
	}
}
