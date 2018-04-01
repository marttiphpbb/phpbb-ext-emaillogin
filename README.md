# PhpBB Extension - marttiphpbb Template Events

This phpBB extension for developers shows all core template events at their location in the board. Also the core PHP events that were triggered for the current request are shown at the bottom of the page. All template and PHP events are linked to their location in the [phpBB/phpBB github repository](https://github.com/phpbb/phpbb) so their context can be easily reviewed.

**This extension is a helper tool for developing other extenstions. It should not be used on a live forum.**

![Screenshot](/doc/screenshot.png)

## Quick Install

You can install this on the latest release of phpBB 3.2 by following the steps below:

* Create `marttiphpbb/templateevents` in the `ext` directory.
* Download and unpack the repository into `ext/marttiphpbb/templateevents`
* Enable `Template Events` in the ACP at `Customise -> Manage extensions`.

## Uninstall

* Disable `Template Events` in the ACP at `Customise -> Extension Management -> Extensions`.
* To permanently uninstall, click `Delete Data`. Optionally delete the `/ext/marttiphpbb/templateevents` directory.

## Support

* Report bugs and other issues to the [Issue Tracker](https://github.com/marttiphpbb/templateevents/issues).
* Support requests should be posted and discussed in the [Template Events topic at phpBB.com](https://www.phpbb.com/community/viewtopic.php?f=456&t=2283446).

## License

[GPL-2.0](license.txt)
