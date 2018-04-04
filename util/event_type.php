<?php
/**
* phpBB Extension - marttiphpbb templateevents
* @copyright (c) 2014 - 2018 marttiphpbb <info@martti.be>
* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace marttiphpbb\templateevents\util;

class event_type
{
	const LOCATION = [
		'php' 			=> '',
		'template'		=> 'styles/prosilver/template/',
		'template_acp'	=> 'adm/style/',
	];

	const LISTENER_LOCATION = [
		'php'			=> '',
		'template'		=> 'styles/all/template/event/',
		'template_acp'	=> 'adm/style/event/',
	];

	const LANG = [
		'template'		=> 'template events',
		'template_acp'	=> 'acp template events',
		'php'			=> 'PHP events',
	];	

	/** @var string */
	private $type;

	public function __construct(string $type)
	{
		if (!isset(self::LOCATION[$type]))
		{
			throw new \exception(sprintf('Event type %s is not defined.', $type));
		}

		$this->type = $type;
	}

	public function get():string
	{
		return $this->type;
	}

	public function __toString():string
	{
		return $this->type;
	}

	public function get_location():string 
	{
		return self::LOCATION[$this->type];
	}

	public static function get_all_type_locations():array 
	{
		return self::LOCATIONS;
	}

	public static function get_all_types():array 
	{
		return array_keys(self::LOCATIONS);
	}

	public static function cli_selector(string $type):array
	{
		switch($type)
		{
			case 'template':
				return ['template'];
				break;
			case 'template_acp':
			case 'acp':
				return ['template_acp'];
				break;
			case 'php':
				return ['php'];
				break;
			case '':
			case 'all':
				return ['php', 'template', 'template_acp'];
				break;			
			default: 
				return [];
				break;
		}

		return [];
	}
}