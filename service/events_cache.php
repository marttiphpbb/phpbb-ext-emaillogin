<?php
/**
* phpBB Extension - marttiphpbb templateevents
* @copyright (c) 2014 - 2018 marttiphpbb <info@martti.be>
* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace marttiphpbb\templateevents\service;

use phpbb\cache\driver\driver_interface as cache;

class events_cache
{
	const LOCATION = '_marttiphpbb_templateevents_events';
	const FILE = __DIR__ . '/../events_data.json';

	/** @var cache */
    private $cache;
    
    /** @var array */
    private $events = [];

	public function __construct(cache $cache)
	{
		$this->cache = $cache;			
	}

    private function load()
    {
		$this->events = $this->cache->get(self::LOCATION);
		
		if ($this->events)
		{
			return;
		}
        
        $this->refresh();
    }

    public function refresh()
    {
        $events = file_get_contents(self::FILE);
		$this->events = json_decode($events, true);
		$this->cache->put(self::LOCATION, $this->events);		
    }

    public function set_all(array $events)
    {
		$this->events = $events;
		$this->cache->put(self::LOCATION, $this->events);
	}

	public function write_file():bool
	{
		$this->events = $this->cache->get(self::LOCATION);
		
		if (!$this->events)
		{
			return false;
		}

		file_put_contents(self::FILE, json_encode($this->events, JSON_PRETTY_PRINT));

		return true;
	}
	
	public function get_all():array 
	{
		if (!$this->events)
		{
			$this->load();
		}

		return $this->events;
	}

	public function get(string $type, string $key):array
	{
		if (!$this->events)
		{
			$this->load();
		}

		return $this->events[$type][$key];
	}
}
