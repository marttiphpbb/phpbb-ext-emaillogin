<?php
/**
* phpBB Extension - marttiphpbb showphpbbevents
* @copyright (c) 2014 - 2018 marttiphpbb <info@martti.be>
* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace marttiphpbb\showphpbbevents\util;

class generate_php_listener
{
	const FILE = __DIR__ . '/../event/php_event_listener.php';

	const TEMPLATE_FILE = <<<'EOT'
<?php
/**
* phpBB Extension - marttiphpbb showphpbbevents
* @copyright (c) 2014 - 2018 marttiphpbb <info@martti.be>
* @license GNU General Public License, version 2 (GPL-2.0)
* This file was generated with the ext-showphpbbevents:generate command
*/

namespace marttiphpbb\showphpbbevents\event;

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

	/**
	* @param array
	* @return void
	*/
	public static function write_file(array $php_events)
	{
		file_put_contents(self::FILE, self::get($php_events));
	}

	/** 
	 * @return $string
	 */
	public static function read_file():string
	{
		return file_get_contents(self::FILE);
	}

	/**
	* @param array
	* @return string
	*/
	public static function get(array $php_events):string
	{
		$str = '';

		foreach ($php_events as $name => $ary)
		{
			$str .= "\t\t\t'$name' => 'add',\n";
		}

		return str_replace('%ary%', $str, self::TEMPLATE_FILE);

		file_put_contents(self::FILE, $str);
	}
}