<?php
/**
* phpBB Extension - marttiphpbb showphpbbevents
* @copyright (c) 2014 - 2018 marttiphpbb <info@martti.be>
* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace marttiphpbb\showphpbbevents\console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Finder\Finder;
use phpbb\console\command\command;
use phpbb\user;
use marttiphpbb\showphpbbevents\service\events_cache;
use marttiphpbb\showphpbbevents\util\event_type;

class extract extends command
{
	const ROOT_PATH = __DIR__ . '/../../../../';
 
	const SEARCH_LINE_PLACEHOLDER = [
		'php'			=> '(\'%s\'',
		'template'		=> '%s',
		'template_acp'	=> '%s',
	];

	const HEADER_FILES = [
		'template'	=> ['overall_header.html', 'simple_header.html'],
		'template_acp'	=> ['overall_header.html', 'simple_header.html'],
	];

	const FOOTER_FILES = [
		'template'	=> ['overall_footer.html', 'simple_footer.html'],
		'template_acp'	=> ['overall_footer.html', 'simple_footer.html'],
	];

	const INCLUDE_CSS = [
		'template'	=> [
			'overall_header_head_append',
			'simple_header_head_append',
		],
		'template_acp'	=> [
			'acp_overall_header_head_append',
			'acp_simple_header_head_append',
		],
	];

	const TEMPLATE_EVENT_TAG = [
		['<!-- EVENT ', ' -->'],
		['{% EVENT ', ' %}'],
		['{%- EVENT ', ' -%}'],
		['{% EVENT ', ' -%}'],
		['{%- EVENT ', ' %}'],
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
			->setName('ext-showphpbbevents:extract')
			->setDescription('For Development: Extract events data from the local phpBB files and load into cache.')
			->setHelp('This command was created for the development of the marttiphpbb-showphpbbevents extension.')
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

		$outputStyle = new OutputFormatterStyle('white', 'blue', ['bold']);
		$output->getFormatter()->setStyle('f', $outputStyle);

		$events = $this->events_cache->get_all();

		if (!$events)
		{
			$io->writeln('<info>No events in cache. Run ext-showphpbbevents:scrape first</>');
			return;
		}

		$io->writeln([
			'',
			'<comment>Event line numbers.</>',
			'<comment>-------------------</>',
			'',
		]);

		foreach ($events as $type => $event)
		{
			$dir = self::ROOT_PATH . event_type::LOCATION[$type];
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
							$io->writeln('<info>Event </><v>' . $name . '</><info> in </><f>' . $filename . '</><info>: </><v>' . $line . '</>');
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

		$io->writeln([
			'',
			'<comment>Include CSS.</>',
			'<comment>------------</>',
			'',
		]);

		foreach (self::INCLUDE_CSS as $type => $include_css_events)
		{
			foreach ($include_css_events as $name)
			{
				$events[$type][$name]['include_css'] = true;
				$io->writeln('<v>' . $name . '</>');
			}
		}

		$io->writeln([
			'',
			'<comment>Last in body (for rendering PHP Events table).</>',
			'<comment>----------------------------------------------</>',
			'',
		]);

		foreach (self::FOOTER_FILES as $type => $footer_files)
		{
			$dir = self::ROOT_PATH . event_type::LOCATION[$type];

			foreach ($footer_files as $filename)
			{
				$file = $dir . $filename;
				$event_name = '';

				if ($handle = fopen($file, 'r')) 
				{
					while (($str = fgets($handle, 4096)) !== false) 
					{
						$event_name = $this->get_template_event($str) ?: $event_name;
					
						if (strpos($str, '</body>') !== false)
						{
							break;
						}
					}
			
					fclose($handle);
				}

				if ($event_name)
				{
					$events[$type][$event_name]['last_in_body'] = true;
					$io->writeln('<v>' . $event_name . '</>');
				}
			}
		}

		$io->writeln([
			'',
			'<comment>Head events and first in body (show/hide button head events).</>',
			'<comment>-------------------------------------------------------------</>',
			'',
		]);

		foreach (self::HEADER_FILES as $type => $header_files)
		{
			$dir = self::ROOT_PATH . event_type::LOCATION[$type];

			foreach ($header_files as $filename)
			{
				$head_started = false;
				$head_finished = false;
				$body_started = false;
				$file = $dir . $filename;
				$delayed_head_events = [];
				$first_event_in_body = '';

				if ($handle = fopen($file, 'r')) 
				{
					while (($str = fgets($handle, 4096)) !== false) 
					{
						if (strpos($str, '<head') !== false)
						{
							$head_started = true;
						}

						if (!$head_started)
						{
							continue;
						}

						if (strpos($str, '</head>') !== false)
						{
							$head_finished = true;
						}

						if (!$head_finished)
						{
							$event_name = $this->get_template_event($str);
							
							if ($event_name)
							{
								$delayed_head_events[] = $event_name;
								$events[$type][$event_name]['in_head'] = true;
								$io->writeln('<info>In head: </><v>' . $event_name . '</>');
							}

							continue;
						}

						if (strpos($str, '<body') !== false)
						{
							$body_started = true;
						}

						if ($body_started)
						{
							$event_name = $this->get_template_event($str);

							if ($event_name)
							{
								$events[$type][$event_name]['first_in_body'] = true;
								$events[$type][$event_name]['delayed_head_events'] = $delayed_head_events;
								$io->writeln(['', '<info>First event in body: </><v>' . $event_name . '</>', '']);
								break;
							}
						}
					}
			
					fclose($handle);
				}
			}
		}

		$io->writeln([
			'',
			'<comment>Search Template Events in html select.</>',
			'<comment>--------------------------------------</>',
			'',
		]);

		foreach ($events as $type => $event)
		{
			if ($type === 'php')
			{
				continue;
			}

			$io->writeln([
				'',
				'<info>' . $type . '</>',
				'',
			]);
	
			$dir = self::ROOT_PATH . event_type::LOCATION[$type];

			$finder = new Finder();
			$files = $finder->files()->in($dir)->sortByName();
			$found = false;

			foreach ($files as $name)
			{
				$in_select = false;
	
				if ($handle = fopen($name, 'r')) 
				{
					while (($str = fgets($handle, 4096)) !== false) 
					{
						if (strpos($str, '<select') !== false)
						{
							$in_select = true;
						}

						if (strpos($str, '</select>') !== false)
						{
							$in_select = false;
						}

						if (!$in_select)
						{
							continue;
						}

						$event_name = $this->get_template_event($str);
					
						if ($event_name)
						{
							$io->writeln('<v>' . $event_name . '</>');
							$events[$type][$event_name]['is_select_option'] = true;
							$found = true;
						}
					}
			
					fclose($handle);
				}
			}

			if (!$found)
			{
				$io->writeln(['None found.', '']);
			}
		}

		$this->events_cache->set_all($events);
	}

	private function get_template_event(string $line):string 
	{
		foreach (self::TEMPLATE_EVENT_TAG as $tag)
		{
			$event_name = $this->get_content_between_tags($line, $tag[0], $tag[1]);

			if ($event_name)
			{
				return trim($event_name);
			}
		}

		return '';
	}

	private function get_content_between_tags(string $string, string $start_tag, string $end_tag):string
	{
		$start = strpos($string, $start_tag);

		if ($start === false)
		{
			return '';
		}

		$start += strlen($start_tag);

		$end = strpos($string, $end_tag, $start);

		if ($end === false)
		{
			return '';
		}

		return substr($string, $start, $end - $start);
	}
}
