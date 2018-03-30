<?php
/**
* phpBB Extension - marttiphpbb templateevents
* @copyright (c) 2014 - 2018 marttiphpbb <info@martti.be>
* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace marttiphpbb\templateevents;

use phpbb\extension\base;
use marttiphpbb\templateevents\twig\extension;
use marttiphpbb\templateevents\service\events_cache;
use marttiphpbb\templateevents\event\php_event_listener;

class ext extends base
{
	/**
	* @param mixed $old_state State returned by previous call of this method
	* @return mixed Returns false after last step, otherwise temporary state
	* @access public
	*/
	public function enable_step($old_state)
	{
		switch ($old_state)
		{
            case '':
                // If phpBB was following a Post-Redirect-Get pattern then this was not necessary.
                // To circumvent phpBB not redirecting after enabling the extension
                // and failing to add the new Twig Extension it's add here in order to prevent
                // a blank page on first response.
                $twig = $this->container->get('template.twig.environment');
                $request = $this->container->get('request');
                $user = $this->container->get('user');
                $language = $this->container->get('language');
                $cache = $this->container->get('cache.driver');
                $php_event_listener = new php_event_listener();
                $events_cache = new events_cache($cache);
                $twig_extension = new extension($request, $user, $language, $php_event_listener, $events_cache);
                $twig->addExtension($twig_extension);
                $language->add_lang('common', 'marttiphpbb/templateevents');
				return 'add_twig_extension';
			break;
			default:
				return parent::enable_step($old_state);
			break;
		}
	}

}
