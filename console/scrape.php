<?php
/**
* phpBB Extension - marttiphpbb templateevents
* @copyright (c) 2014 - 2018 marttiphpbb <info@martti.be>
* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace marttiphpbb\templateevents\console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use phpbb\console\command\command;
use phpbb\user;
use phpbb\cache\driver\driver_interface as cache;
use Goutte\Client;

class scrape extends command
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
			->setName('ext-templateevents:scrape')
			->setDescription('Scrape events data from https://wiki.phpbb.com/Event_List and load into cache.')
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

		$client = new Client();

		$crawler = $client->request('GET', 'https://wiki.phpbb.com/Event_List');

		$table = $crawler->filter('table')->filter('tr')->each(function ($tr, $i) {
			return $tr->filter('td')->each(function ($td, $i) {
				return trim($td->text());
			});
		});

		$events = [];

		foreach ($table as $t)
		{
			if (!is_array($t) || !count($t))
			{
				continue;
			}

			array_walk($t, function($val){
				return trim($val);
			});

			if (strpos($t[0], 'core.') === 0)
			{
				$events['php'][$t[0]] = [
					'file'		=> $t[1],
					'vars'		=> $t[2],
					'since'		=> $t[3],
					'explain'	=> $t[4],
				];

				continue;
			}

			if (strpos($t[0], 'acp_') === 0)
			{
				$events['template_acp'][$t[0]] = [
					'file'		=> $t[1],
					'since'		=> $t[2],
					'explain'	=> $t[3],
				];

				continue;
			}

			$events['template'][$t[0]] = [
				'file'		=> $t[1],
				'since'		=> $t[2],
				'explain'	=> $t[3],
			];
		}	

		$this->cache->put('_marttiphpbb_templateevents_events', $events);

		$io->writeln([
			'', 
			'<info>written to cache.</>',
			'<info>=================</>',
			'<comment>php events: </><v>' . count($events['php']) . '</>',
			'<comment>template events: </><v>' . count($events['template']) . '</>',
			'<comment>acp template events: </><v>' . count($events['template_acp']) . '</>',
			'',
		]);
	}
}
