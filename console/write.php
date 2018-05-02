<?php
/**
* phpBB Extension - marttiphpbb emaillogin
* @copyright (c) 2018 marttiphpbb <info@martti.be>
* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace marttiphpbb\emaillogin\console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use phpbb\console\command\command;
use phpbb\user;
use marttiphpbb\emaillogin\service\events_cache;
use marttiphpbb\emaillogin\service\events_store;

class write extends command
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
			->setName('ext-emaillogin:write')
			->setDescription('For Development: Write events_data.json from cache (use ext-emaillogin:scrape and ext-emaillogin:extract first).')
			->setHelp('This command was created for the development of the marttiphpbb-emaillogin extension.')
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

		$events = $this->events_cache->get_all();

		if (!$events)
		{
			$io->writeln('<info>no events were found in cache.</>');
			return;
		}

		$this->events_store->set_all($events);

		$io->writeln('<info>file written: </><v>events_data.json</>');
	}
}
