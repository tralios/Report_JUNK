Report_JUNK
===========

Plugin for Roundcube Webmail

This plugin is the result of [our](http://www.tralios.de) commitment to helping in the global struggle against SPAM.

The plugin offers a new button in the Roundcube Webmailer which will POST anonymized Header-Information of SPAM-Mails to services such as [Blackhole.MX](http://blackhole.mx)

You can also use it as a means of reporting SPAM to your local scripts since the configuration allows for multiple services to be enabled.


Installation
------------

Move the folder to your RoundCube Plugins directory. Please make sure to remove the .git directory for security reasons.

Change the config.inc.php according to your needs and add the plugin to your configuration in config/main.inc.php like so:

$rcmail_config['plugins'] = array('report_junk');

You should now be ready to go.

Thank you for your help in the global struggle against SPAM. Any feedback is welcome at info@tralios.de.

If you wish to contribute, feel free to contact us.
