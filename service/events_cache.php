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

	/** @var cache */
    private $cache;
    
    /** @var array */
    private $events = [];

	public function __construct(cache $cache)
	{
        $this->cache = $cache;
        $this->load_events();
	}

    private function load_events()
    {
        $this->events = $this->cache->get(self::LOCATION); 
        
        if (!$this->events)
        {
            $this->refresh();
            $this->events = $this->cache->get(self::LOCATION);
        }
    }

    public function refresh()
    {
        $events = file_read_contents(__DIR__ . '../events_data.json');
        $this->events = json_decode($events, true);
        $this->cache->put('_marttiphpbb_templateevents_events');     
    }

    public function set(array $events)
    {

    }

    public_function get_all()
    {
        if ($this->events)
        {
            return $this->events;
        }

        
    }



		if (!$events)
		{
			$io->writeln('<info>no events were found in cache.</>');
			return;
		}

		$type_lang = self::EVENTS_TYPE_LANG[$type];

		$io->writeln('<comment>' . $type_lang . ' in cache: </><v>' . count($events[$type]) . '</>');

		if ($type === 'php')
		{
			return;
		}

		$dir = __DIR__;
		$dir .= $type === 'template' ? '/../styles/all/template/event' : '/../adm/style/event';

		$finder = new Finder();
		$event_files = $finder->files()->in($dir)->sortByName();

		$count = 0;

		$to_delete = [];

		$ev_files = $ev_cache = $ev_size = [];

		foreach ($event_files as $file)
		{
			$count++;

			$filename = $file->getRelativePathname();
			list($name) = explode('.', $filename);

			if (!isset($events[$type][$name]))
			{
				$to_delete[] = $filename;
			}

			$ev_files[] = $name;
			$ev_size[] = $file->getSize();
		}

		foreach ($events[$type] as $name => $ary)
		{
			$ev_cache[] = $name;
		}

		$io->writeln('<comment>' . $type_lang . ' files currently in ext: </><v>' . $count . '</>');

		if (!$content && !$list)
		{
			if ($force)
			{
				foreach ($to_delete as $filename)
				{
					unlink($dir . '/' . $filename);
					$io->writeln('<info>delete ' . $type_lang . ' file in ext: </><del>"'. $filename . '"</>');				
				}
			}
			else
			{
				foreach ($to_delete as $filename)
				{
					$io->writeln('<comment>file to be deleted: </><del>' . $filename . '</>');
				}
			}

			$to_add = array_diff($ev_cache, $ev_files);

			if ($force)
			{
				foreach ($to_add as $name)
				{
					$str = $this->get_template($name);
					file_put_contents($dir . '/' . $name . '.html', $str);
					$io->writeln('<info>write: </><add>' . $name . '</>');				
				}
			}
			else
			{
				foreach ($to_add as $name)
				{
					$io->writeln('<comment>file to add to ext: </><add>' . $name . '</>');
				}			
			}

			return;
		}

		if ($list)
		{
			foreach($ev_files as $k => $name)
			{
				$io->writeln('<info>' . $name . ': </><v>' . $ev_size[$k] . '</>');

			}

			return;
		}

		$has_diff = false;

		foreach ($ev_files as $name)
		{
			$filename = $dir . '/' . $name . '.html';
			$str = $this->get_template($name);
			$str_check = file_get_contents($filename);

			if ($str !== $str_check)
			{
				if ($force)
				{
					file_put_contents($filename, $str);
					$io->writeln('<info>Content updated in file: </><v>' . $name . '</>');
				}
				else
				{
					$io->writeln('<comment>Invalid content in file: </><v>' . $name . '</>');
				}

				$has_diff = true;
			}
		}

		if (!$has_diff)
		{
			$io->writeln('<comment>No invalid content found.</>');
		}
	}

	private function get_template(string $name):string
	{
		$str = isset(self::FIRST_AND_LAST_IN_BODY[$name]) ? self::TEMPLATE_FIRST_AND_LAST_IN_BODY : self::TEMPLATE;
		$str .= isset(self::CSS[$name]) ? self::TEMPLATE_CSS : '';
		return $str;
	}
}
