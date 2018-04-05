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
use Symfony\Component\Finder\Finder;
use phpbb\console\command\command;
use phpbb\user;
use marttiphpbb\templateevents\service\events_store;
use marttiphpbb\templateevents\util\event_type;
use marttiphpbb\templateevents\util\generate_php_listener;
use marttiphpbb\templateevents\util\generate_template_listener;

class generate extends command
{
	const PATH = __DIR__ . '/../';

	/** @var events_store */
	private $events_store;

	public function __construct(user $user, events_store $events_store)
	{
		$this->events_store = $events_store;
		parent::__construct($user);
	}

	/**
	* {@inheritdoc}
	*/
	protected function configure()
	{
		$this
			->setName('ext-templateevents:generate')
			->setDescription('For Development: Generate and write the event listener files from the data of events_data.json.')
			->setHelp('This command was created for the development of the marttiphpbb-templateevents extension.')
			->addArgument('type', InputArgument::OPTIONAL, 'all (default), template, acp or php')
			->addOption('delete', 'd', InputOption::VALUE_NONE, 'Delete obsolete files (events not present in events_data.json)')
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
		$delete = $input->getOption('delete');

		$type_ary = event_type::cli_selector($type ?? '');

		if (!count($type_ary))
		{
			$io->writeln('<error>Invalid argument. The argument should be all(default), template, acp or php.</>');
			return;
		}

		$events = $this->events_store->get_all();

		if (!$events)
		{
			$io->writeln('<info>no events were found in store.</>');
			return;
		}

		foreach ($type_ary as $type)
		{
			$type_lang = event_type::LANG[$type];

			$io->writeln([
				'<comment>',
				$type_lang,
				str_repeat('-', strlen($type_lang)),
				'</>',
			]);
		
			if ($type === 'php')
			{
				if ($delete)
				{
					$io->writeln([
						'<info>The delete option is not applicable for PHP events.',
						'</>',
					]);
					continue;
				}

				generate_php_listener::write_file($events['php']);

				$io->writeln([
					'<info>The PHP event listener has been generated for </><v>' . count($events['php']) . '</><info> events.',
					'</>',
				]);

				continue;
			}

			$dir = self::PATH . event_type::LISTENER_LOCATION[$type];

			if ($delete)
			{
				$finder = new Finder();
				$current_event_files = $finder->files()->in($dir)->sortByName();
		
				$files_were_deleted = false;

				foreach ($current_event_files as $file)
				{
					$count++;

					$filename = $file->getRelativePathname();
					list($name) = explode('.', $filename);

					if (!isset($events[$type][$name]))
					{
						unlink($dir . $filename);
						$files_were_deleted = true;
						$io->writeln('<info>Deleted: </><v>' . $filename . '</>');
					}
				}

				if (!$files_were_deleted)
				{
					$io->writeln('<info>No event files were to be deleted.</>');
				}
	
				$io->writeln('');

				continue;
			}

			$event_type = new event_type($type);

			foreach ($events[$type] as $name => $data)
			{
				if (count($data['loc']) === 0)
				{
					$io->writeln('<error>No loc for event ' . $name . '</>');
					continue;
				}

				$content = generate_template_listener::get($events, $event_type, $name);

				file_put_contents($dir . $name . '.html', $content);

				$io->writeln('<info>Listener generated: </><v>' . $name . '</>');
			}

			$io->writeln('');
		}
	}
}
