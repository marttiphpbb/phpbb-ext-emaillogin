<?php
/**
* phpBB Extension - marttiphpbb templateevents
* @copyright (c) 2014 - 2018 marttiphpbb <info@martti.be>
* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace marttiphpbb\templateevents\util;

class write_php_listener
{
	const FILE = __DIR__ . '/../event/php_event_listener.php';

	const TEMPLATE_FILE = <<<'EOT'
<?php
/**
* phpBB Extension - marttiphpbb templateevents
* @copyright (c) 2014 - 2018 marttiphpbb <info@martti.be>
* @license GNU General Public License, version 2 (GPL-2.0)
* This file was generated with the ext-templateevents:generate-php-listener command
*/

namespace marttiphpbb\templateevents\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;

class php_event_listener implements EventSubscriberInterface
{
	private $count_ary = [];

	static public function getSubscribedEvents()
	{
		return [
%ary%
		];
	}

	public function add(Event $event)
	{
		$name = $event->getName();
	
		if (isset($this->count_ary[$name]))
		{
			$this->count_ary[$name]++;
			return;
		}

		$this->count_ary[$name] = 1;
	}

	public function get_count_ary():array 
	{
		return $this->count_ary;
	}
}
EOT;

	public function __construct()
	{
	}

	/**
	* @param array
	* @return void
	*/
	public function execute(array $php_events)
	{
		foreach ($php_events as $name => $ary)
		{
			$str .= "\t\t\t'$name' => 'add',\n";
		}

		$str = str_replace('%ary%', $str, self::TEMPLATE_FILE);

		file_put_contents(self::FILE, $str);
	}
}