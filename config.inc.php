<?php

# Add more services as you need here:
$rcmail_config['report_junk_services'] = array('blackhole','other');

# Each service named above has the following settings:
$rcmail_config['report_junk_blackhole_url'] = 'http://void.blackhole.mx/api/report';
$rcmail_config['report_junk_blackhole_name'] = 'Blackhole.MX Spamreport';
$rcmail_config['report_junk_blackhole_config'] = array(
	'event_source' => '<YOUR EVENT SOURCE>',
	'event_version' => 1,
	'event_class' => "RawEmail",
	'profile' => '<YOUR PROFILE>',
);


# here we have another service. Just remove it from the list above to not use it.
$rcmail_config['report_junk_other_url'] = '<some other address>';
$rcmail_config['report_junk_other_name'] = 'Your other Service';
$rcmail_config['report_junk_other_config'] = array(
	'other_field' => '<YOUR_VALUE>',
);

?>
