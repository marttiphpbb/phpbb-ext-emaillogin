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
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use phpbb\console\command\command;
use phpbb\user;
use marttiphpbb\templateevents\service\events_cache;
use marttiphpbb\templateevents\model\file_location;
use Goutte\Client;

class scrape extends command
{
	const ROOT_PATH = __DIR__ . '/../../../../';
 
	const SEARCH_LINE_PLACEHOLDER = [
		'php'			=> '(\'%s\'',
		'template'		=> '%s',
		'template_acp'	=> '%s',
	];

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
			->setName('ext-templateevents:scrape')
			->setDescription('Scrape events data from https://wiki.phpbb.com/Event_List and load into cache.')
			->setHelp('This command was created for the development of the marttiphpbb-templateevents extension.')
			->addOption('line', 'l', InputOption::VALUE_NONE, 'find line numbers (for links to repo). Cache must filled first.')	
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

		$find_line = $input->getOption('line');

		if ($find_line)
		{
			$events = $this->events_cache->get_all();

			if (!$events)
			{
				$io->writeln('<info>No events in cache</>');
				return;
			}

			foreach ($events as $type => $event)
			{
				$dir = self::ROOT_PATH . file_location::DIRECTORY[$type];
				$search_line_placeholder = self::SEARCH_LINE_PLACEHOLDER[$type];

				foreach ($event as $name => $ev_data)
				{
					foreach($ev_data['loc'] as $filename => $line)
					{
						$search = sprintf($search_line_placeholder, $name);
						$line = false;
						$count = 0;
						$file = $dir . $filename;

						if ($handle = fopen($file, 'r')) 
						{
							while (($str = fgets($handle, 4096)) !== false) 
							{
								$count++;
		
								if (strpos($str, $search) !== false)
								{
									$line = $count;
									break;
								}
							}
					
							fclose($handle);

							if ($line)
							{
								$events[$type][$name]['loc'][$filename] = $line;
								$io->writeln('<info>Event ' . $name . ' in ' . $filename . ': </><v>' . $line . '</>');
							}
							else
							{
								$io->writeln('<error>Event </><v>' . $name . '</><error> not found in ' . $filename . '</>');
							}
						}
						else
						{
							$io->writeln('<error>Could not open file ' . $file . ' for event ' . $name . '</>');
						}
					}
				}
			}

			$this->events_cache->set_all($events);
			
			return;
		}

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

			$t = array_map('trim', $t);

			$files = explode(',', $t[1]);
		
			$files = array_map('trim', $files);

			$loc = array_fill_keys($files, false);

			if (strpos($t[0], 'core.') === 0)
			{
				$events['php'][$t[0]] = [
					'loc'		=> $loc,
					'vars'		=> $t[2],
					'since'		=> $t[3],
					'explain'	=> $t[4],
				];

				continue;
			}

			if (strpos($t[0], 'acp_') === 0)
			{
				$events['template_acp'][$t[0]] = [
					'loc'		=> $loc,
					'since'		=> $t[2],
					'explain'	=> $t[3],
				];

				continue;
			}

			$events['template'][$t[0]] = [
				'loc'		=> $loc,
				'since'		=> $t[2],
				'explain'	=> $t[3],
			];
		}	

		$this->events_cache->set_all($events);

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
