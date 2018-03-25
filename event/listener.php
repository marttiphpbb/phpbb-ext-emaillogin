<?php
/**
* phpBB Extension - marttiphpbb templateevents
* @copyright (c) 2014 - 2018 marttiphpbb <info@martti.be>
* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace marttiphpbb\templateevents\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use phpbb\event\data as event;
use phpbb\request\request;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	/* @var request */
	protected $request;

	/**
	 * @param request $request
	*/
	public function __construct(
		request $request
	)
	{
		$this->request = $request;
	}

	static public function getSubscribedEvents()
	{
		return [
			'core.user_setup'		=> 'core_user_setup',
			'core.append_sid'		=> 'core_append_sid',
		];
	}

	public function core_user_setup(event $event)
	{
		$lang_set_ext = $event['lang_set_ext'];

		$lang_set_ext[] = [
			'ext_name' => 'marttiphpbb/templateevents',
			'lang_set' => 'common',
		];

		$event['lang_set_ext'] = $lang_set_ext;
	}

	public function core_append_sid(event $event)
	{
		$params = $event['params'];

		if (is_string($params))
		{
			if (strpos($params, 'templateevents=0') !== false)
			{
				return;
			}
		}

		if ($this->request->variable('templateevents', 0))
		{
			if (is_string($params))
			{
				if ($params !== '')
				{
					$params .= '&';
				}

				$params .= 'templateevents=1';
			}
			else
			{
				if ($params === false)
				{
					$params = [];
				}

				$params['templateevents'] = 1;
			}

			$event['params'] = $params;
		}
	}
}
