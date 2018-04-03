<?php
/**
* phpBB Extension - marttiphpbb templateevents
* @copyright (c) 2014 - 2018 marttiphpbb <info@martti.be>
* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace marttiphpbb\templateevents\console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use phpbb\console\command\command;
use phpbb\user;
use marttiphpbb\templateevents\service\events_cache;

class generate extends command
{
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

	/** @var events_cache */
	private $events_cache;

	public function __construct(user $user, events_cache $events_cache)
	{
		$this->events_cache = $events_cache;
		parent::__construct($user);
	}

	/**
	* {@inheritdoc}
	*/
	protected function configure()
	{
		$this
			->setName('ext-templateevents:generate')
			->setDescription('For Development: Generate (and write) the event listener files from the data of events_data.json.')
			->setHelp('This command was created for the development of the marttiphpbb-templateevents extension.')
			->addArgument('type', InputArgument::OPTIONAL, 'all (default), template, acp or php')
			->addOption('write', 'w', InputOption::VALUE_NONE, 'Write the respective file(s).')
		;
	}

	/**
	* @param InputInterface 
	* @param OutputInterface
	* @return void
	*/
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$io = new SymfonyStyle($input, $output);

		$outputStyle = new OutputFormatterStyle('white', 'black', ['bold']);
		$output->getFormatter()->setStyle('v', $outputStyle);
	
		$type = $input->getArgument('type');
		$force = $input->getOption('force');
		$write = $input->getOption('write');

		$events = $this->events_cache->get_all();

		if (!$events)
		{
			$io->writeln('<info>no events were found in cache.</>');
			return;
		}

		$php_events = $events['php'];

		$io->writeln('<comment>php events in cache: </><v>' . count($events['php']) . '</>');

		if (!$force)
		{
			return;
		}

		$str = '';

		foreach ($php_events as $name => $ary)
		{
			$str .= "\t\t\t'$name' => 'add',\n";
		}

		$str = str_replace('%ary%', $str, self::TEMPLATE_FILE);

		file_put_contents(__DIR__ . '/../event/php_event_listener.php', $str);
		$io->writeln('<info>write: </><v>php_event_listener.php</>');
	}
}
