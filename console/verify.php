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

class verify extends command
{
	const TEMPLATE = "{{- marttiphpbb_templateevents_render(_self) -}}\n";
	const TEMPLATE_FIRST_IN_BODY = "{{- marttiphpbb_templateevents_render(_self, true) -}}\n{%- INCLUDECSS '@marttiphpbb_templateevents/templateevents.css' -%}\n";
	const FIRST_IN_BODY = [
		'overall_header_body_before'		=> true,
		'simple_header_body_before'			=> true,
		'acp_overall_header_body_before'	=> true,
		'acp_simple_header_body_before'		=> true,
	];
	const EVENTS_TYPE_LANG = [
		'template'		=> 'template events',
		'template_acp'	=> 'acp template events',
		'php'			=> 'php events',
	];

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
			->setName('ext-templateevents:verify')
			->setDescription('Verify current events in this extension against cache (use ext-templateevents:scrape first).')
			->setHelp('This command was created for the development of the marttiphpbb-templateevents extension.')
			->addArgument('type', InputArgument::OPTIONAL, 'template (default), acp or php')
			->addOption('force', 'f', InputOption::VALUE_NONE, 'Force update of files.')
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
	
		$outputStyle = new OutputFormatterStyle('white', 'green', ['bold']);
		$output->getFormatter()->setStyle('add', $outputStyle);
	
		$outputStyle = new OutputFormatterStyle('white', 'red', ['bold']);
		$output->getFormatter()->setStyle('del', $outputStyle);

		$type = $input->getArgument('type');
		$force = $input->getOption('force');
		$content = $input->getOption('content');
		$list = $input->getOption('list');

		$type = $type ?? 'template';
		$type = $type === 'acp' ? 'template_acp' : $type;

		if (!in_array($type, ['template', 'template_acp', 'php']))
		{
			$io->writeln('<error>Invalid argument. The argument should be template, acp or php.</>');
			return;
		}

		$events = $this->cache->get('_marttiphpbb_templateevents_events');

		if (!$events)
		{
			$io->writeln('<info>no events were found in cache.</>');
			return;
		}

		$type_lang = self::EVENTS_TYPE_LANG[$type];

		$io->writeln('<comment>' . $type_lang . ' in cache: </><v>' . count($events[$type]) . '</>');

		if ($type === 'php')
		{
			return;
		}

		$dir = __DIR__;
		$dir .= $type === 'template' ? '/../styles/all/template/event' : '/../adm/style/event';

		$finder = new Finder();
		$event_files = $finder->files()->in($dir)->sortByName();

		$count = 0;

		$to_delete = [];

		$ev_files = $ev_cache = $ev_size = [];

		foreach ($event_files as $file)
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

		$io->writeln('<comment>' . $type_lang . ' files currently in ext: </><v>' . $count . '</>');

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
					$io->writeln('<comment>file to be deleted: </><del>' . $filename . '</>');
				}
			}

			$to_add = array_diff($ev_cache, $ev_files);

			if ($force)
			{
				foreach ($to_add as $name)
				{
					$str = self::FIRST_IN_BODY[$name] ? self::TEMPLATE_FIRST_IN_BODY : self::TEMPLATE;
					file_put_contents($dir . '/' . $name . '.html', $str);
					$io->writeln('<info>write: </><add>' . $name . '</>');				
				}
			}
			else
			{
				foreach ($to_add as $name)
				{
					$io->writeln('<comment>file to add to ext: </><add>' . $name . '</>');
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
			$str = self::FIRST_IN_BODY[$name] ? self::TEMPLATE_FIRST_IN_BODY : self::TEMPLATE;
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
