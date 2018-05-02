<?php
/**
* phpBB Extension - marttiphpbb emaillogin
* @copyright (c) 2018 marttiphpbb <info@martti.be>
* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace marttiphpbb\emaillogin\service;

class events_store
{
	const FILE = __DIR__ . '/../events_data.json';

    /** @var array */
    private $events = [];

	public function __construct()
	{
	}

    private function load()
    {
		if (!$this->events)
		{
			$events = file_get_contents(self::FILE);
			$this->events = json_decode($events, true);
		}
	}

	private function write()
	{
		file_put_contents(self::FILE, json_encode($this->events, JSON_PRETTY_PRINT));
	}

    public function set_all(array $events)
    {
		$this->events = $events;
		$this->write();
	}

	public function get_all():array
	{
		$this->load();
		return $this->events;
	}

	public function get(string $type, string $event_name):array
	{
		$this->load();
		return $this->events[$type][$event_name];
	}

	public function set(string $type, string $event_name, array $event_data)
	{
		$this->load();
		$this->events[$type][$event_name] = $event_data;
		$this->write();
	}

	public function mset(string $type, string $event_name, string $key, $value)
	{
		$this->load();
		$this->events[$type][$event_name][$key] = $value;
		$this->write();
	}
}
