<?php
/**
* phpBB Extension - marttiphpbb templateevents
* @copyright (c) 2014 - 2018 marttiphpbb <info@martti.be>
* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace marttiphpbb\templateevents\console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use phpbb\console\command\command;
use phpbb\user;
use marttiphpbb\templateevents\service\events_cache;
use marttiphpbb\templateevents\service\events_store;
use marttiphpbb\templateevents\util\event_type;

class show extends command
{
	/** @var events_cache */
	private $events_cache;

	/** @var events_store */
	private $events_store;

	public function __construct(user $user, events_cache $events_cache, events_store $events_store)
	{
		$this->events_cache = $events_cache;
		$this->events_store = $events_store;
		parent::__construct($user);
	}

	/**
	* {@inheritdoc}
	*/
	protected function configure()
	{
		$this
			->setName('ext-templateevents:show')
			->setDescription('For Development: Show data in cache from a event.')
			->setHelp('This command was created for the development of the marttiphpbb-templateevents extension.')
			->addArgument('name', InputArgument::REQUIRED, 'The name of the event')
			->addOption('file', 'f', InputOption::VALUE_NONE, 'Show data from events_data.json file instead of cache.')
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

		$name = $input->getArgument('name');
		$from_store = $input->getOption('file');

		$type = 'template';

		if (strpos($name, 'core.') === 0)
		{
			$type = 'php';
		}

		if (strpos($name, 'acp_') === 0)
		{
			$type = 'template_acp';
		}

		if ($from_store)
		{
			$event = $this->events_store->get($type, $name);
		}
		else
		{
			$event = $this->events_cache->get($type, $name);
		}

		$io->writeln(['', '<comment>Type: ' . $type, '']);

		$io->writeln([json_encode($event, JSON_PRETTY_PRINT), '', '']);
	}
}
