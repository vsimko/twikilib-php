# FAQ #

# Q: Problem with phpunit #
After checking out the twikilib-php project from SVN, my Eclipse IDE shows an error **"Unbound buildpath container: 'phpunit' in project twikilib-php"** in "Problems" view

  * Make sure that `phpunit` is installed on your system (On Debian-based systems try `sudo apt-get install phpunit`)
    * Navigate into Window->Preferences->PHP->PHP Libraries
    * Add 'phpunit' entry using "New..."
    * Click on the newly added phpunit entry and "Add External folder..." (navigate to the directory where PHPUnit is installed, on Ubuntu it is `/usr/share/php`)

# Q: Problem with xdebug #
  * Make sure you installed xdebug: `sudo apt-get install php5-xdebug`
  * Navigate into Window->Preferences->PHP->PHP Executables
  * Click "Add..."
  * Fill the form fields as follows (tested on Ubuntu 10.04.3 LTS):
    * Name = php5xdebug
    * Executable Path = /usr/bin/php5
    * PHP ini file (optional) = /etc/php5/conf.d/xdebug.ini
    * SAPI Type = CLI
    * PHP debugger = XDebug
  * Content of the xdebug.ini file (adjust version to your needs):
> > `zend_extension=/usr/lib/php5/20090626+lfs/xdebug.so`