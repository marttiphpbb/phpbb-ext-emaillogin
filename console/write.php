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
use phpbb\cache\driver\driver_interface as cache;
use Symfony\Component\Finder\Finder;

class write extends command
{


	/** @var cache */
	private $cache;

	public function __construct(user $user, cache $cache)
	{
		$this->cache = $cache;
		parent::__construct($user);
	}

	/**
	* {@inheritdoc}
	*/
	protected function configure()
	{
		$this
			->setName('ext-templateevents:write')
			->setDescription('Write events_data.json from cache (use ext-templateevents:scrape first).')
			->setHelp('This command was created for the development of the marttiphpbb-templateevents extension.')	
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

		$events = $this->cache->get('_marttiphpbb_templateevents_events');

		if (!$events)
		{
			$io->writeln('<info>no events were found in cache.</>');
			return;
		}

		file_put_contents(__DIR__ . '/../events_data.json', json_encode($events, JSON_PRETTY_PRINT));

		$io->writeln('<info>file written: </><v>events_data.json</>');
	}
}
