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
use marttiphpbb\templateevents\util\event_type;
use marttiphpbb\templateevents\util\generate_php_listener;
use marttiphpbb\templateevents\util\generate_template_listener;
use Symfony\Component\Finder\Finder;
use marttiphpbb\templateevents\model\template;

class verify extends command
{
	const ROOT_PATH = '/../../../../';

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
			->setName('ext-templateevents:verify')
			->setDescription('For Development: Verify current events in this extension against cache.')
			->setHelp('This command was created for the development of the marttiphpbb-templateevents extension.')
			->addArgument('type', InputArgument::OPTIONAL, 'all (default), template, acp or php')
			->addOption('content', 'c', InputOption::VALUE_NONE, 'Verify content of files.')
			->addOption('list', 'l', InputOption::VALUE_NONE, 'List files & size.')		
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
		$content = $input->getOption('content');
		$list = $input->getOption('list');
		$force = false;

		$type_ary = event_type::cli_selector($type);

		if (!count($type_ary))
		{
			$io->writeln('<error>Invalid argument. The argument should be all(default), template, acp or php.</>');
			return;
		}

		$events = $this->events_cache->get_all();

		if (!$events)
		{
			$io->writeln('<info>no events were found in cache.</>');
			return;
		}

		foreach ($type_ary as $type)
		{
			$type_lang = event_type::LANG[$type];

			$io->writeln([
				'',
				'<comment>' . $type_lang . '</comment>',
				'<comment>' . str_repeat('-', strlen($type_lang)) . '</comment>',
				'<comment>' . $type_lang . ' in cache: </><v>' . count($events[$type]) . '</>',
				'']);

			if ($type === 'php')
			{
				$new = generate_php_listener::get($events['php']);
				$stored = generate_php_listener::read_file();

				if ($new === $stored)
				{
					$io->writeln([
						'<info>The stored php listener is equal to the newly generated.',
						'The length is </><v>' . strlen($new) . '</><info> bytes.</>',
						'',
					]);

					continue;
				}

				$io->writeln([
					'<error>The stored php listener is NOT equal to the newly generated.</>',
					'<info>The length of the stored file is </><v>' . strlen($stored) . '</><info> bytes </>',
					'<info>The new length is </><v>' . strlen($new) . '</><info> bytes</>',
					'',
				]);

				continue;
			}

			$dir = self::ROOT_PATH . event_type::LISTENER_LOCATION[$type];

			$finder = new Finder();
			$current_event_files = $finder->files()->in($dir)->sortByName();
	
			$count = 0;
			$ev_files = $ev_cache = $ev_size = $to_delete = [];

			foreach ($current_event_files as $file)
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

			$io->writeln([
				'',
				'<comment>' . $type_lang . ' files currently in extension: </><v>' . $count . '</>',
				'',
			]);

			if ($count !== count($events[$type]))
			{
				$io->writeln([
					'<error>Number of files doesn\'t match the cache.</>',
					'',
				]);
			}

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
						$io->writeln('<comment>file to be deleted: </><error>' . $filename . '</>');
					}
				}

				$to_add = array_diff($ev_cache, $ev_files);

				if ($force)
				{
					foreach ($to_add as $name)
					{
						$str = $this->get_template($name);
						file_put_contents($dir . '/' . $name . '.html', $str);
						$io->writeln('<info>write: </><v>' . $name . '</>');				
					}
				}
				else
				{
					foreach ($to_add as $name)
					{
						$io->writeln('<comment>file to add to ext: </><v>' . $name . '</>');
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
	}

	private function get_template(string $name):string
	{
		$str = isset(self::FIRST_AND_LAST_IN_BODY[$name]) ? self::TEMPLATE_FIRST_AND_LAST_IN_BODY : self::TEMPLATE;
		$str .= isset(self::CSS[$name]) ? self::TEMPLATE_CSS : '';
		return $str;
	}
}
