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

	public function get_location():string
	{
		return self::LOCATION[$this->type];
	}

	public function get_listener_location():string 
	{
		return self::LISTENER_LOCATION[$this->type];
	}

	public function __toString():string
	{
		return $this->type;
	}

	public static function get_all_type_locations():array 
	{
		return self::LOCATIONS;
	}

	public static function get_all_types():array 
	{
		return array_keys(self::LOCATIONS);
	}
}