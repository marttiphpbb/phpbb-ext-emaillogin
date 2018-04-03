<?php
/**
* phpBB Extension - marttiphpbb templateevents
* @copyright (c) 2014 - 2018 marttiphpbb <info@martti.be>
* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace marttiphpbb\templateevents\util;

class event_name
{
	/** @var string */
	private $name;

	public function __construct(string $name)
	{
		if (!$name)
		{
			throw new \exception('The event name cannot be empty.');
		}

		$this->name = $name;
	}

	public function get():string
	{
		return $this->name;
	}

	public function __toString():string
	{
		return $this->name;
	}
}