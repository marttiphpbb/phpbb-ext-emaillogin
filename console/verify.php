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
use Symfony\Component\Finder\Finder;

class verify extends command
{
	const TEMPLATE = "{{- marttiphpbb_templateevents_render(_self) -}}\n";
	const TEMPLATE_FIRST_AND_LAST_IN_BODY = "{{- marttiphpbb_templateevents_render(_self, true) -}}\n";
	const TEMPLATE_CSS = "{%- INCLUDECSS '@marttiphpbb_templateevents/templateevents.css' -%}\n";
	const FIRST_AND_LAST_IN_BODY = [
		'overall_header_body_before'		=> true,
		'simple_header_body_before'			=> true,
		'acp_overall_header_body_before'	=> true,
		'acp_simple_header_body_before'		=> true,
		'overall_footer_body_after'			=> true,
		'simple_footer_after'				=> true,
		'acp_overall_footer_after'			=> true,
		'acp_simple_footer_after'			=> true,
	];
	const CSS = [
		'overall_header_head_append'		=> true,
		'simple_header_head_append'			=> true,
		'acp_overall_header_head_append'	=> true,
		'acp_simple_header_head_append'		=> true,
	];
	const EVENTS_TYPE_LANG = [
		'template'		=> 'template events',
		'template_acp'	=> 'acp template events',
		'php'			=> 'php events',
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

		$events = $this->events_cache->get_all();

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

	private function get_template(string $name):string
	{
		$str = isset(self::FIRST_AND_LAST_IN_BODY[$name]) ? self::TEMPLATE_FIRST_AND_LAST_IN_BODY : self::TEMPLATE;
		$str .= isset(self::CSS[$name]) ? self::TEMPLATE_CSS : '';
		return $str;
	}
}
