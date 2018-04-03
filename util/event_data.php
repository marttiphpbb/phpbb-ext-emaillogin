<?php
/**
* phpBB Extension - marttiphpbb templateevents
* @copyright (c) 2014 - 2018 marttiphpbb <info@martti.be>
* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace marttiphpbb\templateevents\util;

class event_data
{
	/** @var event_type */
	private $type;

	/** @var event_name */
	private $name;

	public function __construct(
		event_type $type,
		event_name $name,
		event_loc $loc,
		event_since $since


	)
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