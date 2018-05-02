# phpBB Extension - marttiphpbb Show phpBB Events

**This extension is a helper tool for developing (other extenstions and styles). It should not be used on a live forum.**

**This extension was formerly called "Template Events", but since it shows now also the PHP events it has been renamed.**

This phpBB extension for developers shows all core phpBB template and PHP events in the board. The template events are rendered at their location as black labels. The PHP events triggered on the page are shown at the bottom in order of occurance. All template and PHP events are linked to their location in the [phpBB github repository](https://github.com/phpbb/phpbb) so their context can be easily reviewed.

## Requirements

* phpBB 3.2.x
* PHP 7+

## Quick Install

You can install this on the latest release of phpBB 3.2 by following the steps below:

* Create `marttiphpbb/showphpbbevents` in the `ext` directory.
* Download and unpack the repository into `ext/marttiphpbb/showphpbbevents`
* Enable `Show phpBB Events` in the ACP at `Customise -> Manage extensions`.

## Uninstall

* Disable `Show phpBB Events` in the ACP at `Customise -> Extension Management -> Extensions`.
* To permanently uninstall, click `Delete Data`. Optionally delete the `/ext/marttiphpbb/showphpbbevents` directory.

## Support

* Report bugs and other issues to the [Issue Tracker](https://github.com/marttiphpbb/phpbb-ext-showphpbbevents/issues).

## License

[GPL-2.0](license.txt)

## Screenshots

### Template Events

![Template Events](/doc/template_events.png)

### ACP

![ACP](/doc/acp.png)

### PHP Events

![PHP Events](/doc/php_events.png)

## Link to phpBB on Github

![Github link](/doc/code.png)
