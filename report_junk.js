/* Report-as-Junk plugin script */

function rcmail_report_junk_dialog(prop) {
    rcmail.http_post('plugin.report_junkfetchdialog','','');
}

function rcmail_report_junk_showdialog(result) {
	var dialog = $('<div>')
		.attr('id', 'report_junkdialog')
		.attr('style', 'z-index:100;white-space:normal;line-height:1.5em;position:absolute;top:50%;left:10%;max-width:500px;margin-left:-250px;padding:20px;border:1px solid black;background-color:white;')
		.html(result.text),
	  always = $('<input>')
		.attr('type', 'button')
		.attr('name', 'always')
		.attr('value', result.always)
		.attr('style', 'background-color:blue;color:white;padding:5px;'),
	  once = $('<input>')
		.attr('type', 'button')
		.attr('name', 'once')
		.attr('value', result.once)
		.attr('style', 'background-color:green; margin-left:20px;color:white;padding:5px;'),
	  no = $('<input>')
		.attr('type', 'button')
		.attr('name', 'no')
		.attr('value', result.no)
		.attr('style', 'background-color:red; margin-left:20px;color:white;padding:5px;');
	
	dialog.append(always);
	dialog.append(once);
	dialog.append(no);
	dialog.append('</p><p><small>Powered by <a style="float:none;display:inline;padding-right:0px;" href="http://www.tralios.de">Tralios IT</a> and <a style="float:none;display:inline;padding-right:0px;" href="http://www.blackhole.mx">Blackhole.MX</a></small></p>');
	
	// add and register
	rcmail.add_element(dialog, 'toolbar');
	
	always.click(rcmail_report_junk_hidedialog);
	once.click(rcmail_report_junk_hidedialog);
	no.click(rcmail_report_junk_hidedialog);
}

function rcmail_report_junk_hidedialog(prop)
{
	var tmp = new function(){};
	if ( typeof(prop.currentTarget) != 'undefined' && typeof(prop.currentTarget.name) != 'undefined' )
		tmp.proceed = prop.currentTarget.name;
	
	// Remove the dialog div.
	$('#report_junkdialog').remove();
	
	if ( tmp.proceed == 'always' ) {
		rcmail.register_command('plugin.report_junkdialog', rcmail_report_junk, rcmail.env.uid);
	}
	if ( tmp.proceed != 'no' ){
		rcmail_report_junk(tmp);
	}
}


function rcmail_report_junk(prop)
{
  if (!rcmail.env.uid && (!rcmail.message_list || !rcmail.message_list.get_selection().length))
    return;
	var proceed = '';
	if ( typeof(prop.proceed) != 'undefined' )
		proceed = '&proceed='+prop.proceed;
    var uids = rcmail.env.uid ? rcmail.env.uid : rcmail.message_list.get_selection().join(','),
      lock = rcmail.set_busy(true, 'loading');
	// create custom button

    rcmail.http_post('plugin.report_junk', '_uid='+uids+'&_mbox='+urlencode(rcmail.env.mailbox)+proceed, lock);
}

// callback for app-onload event
if (window.rcmail) {
  rcmail.addEventListener('init', function(evt) {
    
    // register command (directly enable in message view mode)
	rcmail.register_command('plugin.report_junk', rcmail_report_junk, rcmail.env.uid);
    rcmail.register_command('plugin.report_junkdialog', rcmail_report_junk_dialog, rcmail.env.uid);

    rcmail.addEventListener('plugin.showjunkdialog', rcmail_report_junk_showdialog);
	
    // add event-listener to message list
    if (rcmail.message_list)
      rcmail.message_list.addEventListener('select', function(list){
        rcmail.enable_command('plugin.report_junk', list.get_selection().length > 0);
        rcmail.enable_command('plugin.report_junkdialog', list.get_selection().length > 0);
      });
  })
}
