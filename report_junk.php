<?php

/**
 * Report as Junk - Send headers of Junk-Mails to external services like Blackhole.mx
 * or your own Spam-Reporting-Script.
 * Based on the mark_as_junk plugin by Thomas Bruederli
 * 
 * @version 0.1
 * @authors Tralios IT GmbH: Justus Wingert, Thomas Witzenrath
 */

class report_junk extends rcube_plugin
{
  public $task = 'mail';

  function init()
  {
    $rcmail = rcmail::get_instance();

    $this->register_action('plugin.report_junk', array($this, 'request_action'));
    $this->register_action('plugin.report_junkfetchdialog', array($this, 'fetch_dialog'));
    
	// Load the user preferences for the service flag
	$useservice = $rcmail->config->get('use_report_junk');
	
	// Default is to show the dialog.
	$command = 'plugin.report_junk';
	// If flag is not set show the selection dialog.
	if ( !$useservice ) {
		$command = 'plugin.report_junkdialog';
	}
	
    if ($rcmail->action == '' || $rcmail->action == 'show') {
      $skin_path = $this->local_skin_path();
      $this->include_script('report_junk.js');
      $this->add_texts('localization', true);
      $this->add_button(array(
      	'type' => 'link',
        'class' => 'button',
	'style' => 'background-image:url(plugins/report_junk/skins/larry/report_act.png);background-position:10px 0px;',
	'command' => $command,
        'width' => 32,
        'height' => 32,
	'label' => 'report_junk.report_junk_buttontext',
        'title' => 'report_junk.report_junk_buttontitle'), 'toolbar');
    }
  }
  
  /*
   * Fetches the different language versions of the dialog.
   */
  function fetch_dialog(){
    $this->add_texts('localization');
	$rcmail = rcmail::get_instance();
	
	$this->load_config();

	$text = '<h1>';
	$text.= $this->gettext('report_junk_question');
	$text.= '</h1>';
	$text.= '<p>';
	$text.= $this->gettext('report_junk');
	$text.= '</p><ul>';

	foreach ( $rcmail->config->get('report_junk_services') as $service){
		$text.='<li>';
		$text.=$rcmail->config->get('report_junk_'.$service.'_name');
		$text.='</li>';
	}

	$text.= '</ul><p>';
	$text.= $this->gettext('report_junk_personaldata');


	$rcmail->output->command('plugin.showjunkdialog',array(
		'text' => $text,
		'always' => $this->gettext('report_junk_always'),
		'once' => $this->gettext('report_junk_once'),
		'no' => $this->gettext('report_junk_no'),
	));
  }
  
  
  function request_action()
  {
    $this->add_texts('localization');

    $GLOBALS['IMAP_FLAGS']['JUNK'] = 'Junk';
    $GLOBALS['IMAP_FLAGS']['NONJUNK'] = 'NonJunk';
    
	
    $uids = get_input_value('_uid', RCUBE_INPUT_POST);
    $mbox = get_input_value('_mbox', RCUBE_INPUT_POST);
	$proceed = get_input_value('proceed', RCUBE_INPUT_POST);
	
    $rcmail = rcmail::get_instance();
	
    $preferences = $rcmail->user->get_prefs();
	
    // Sets the user choice for the service
    if ( $proceed == 'always' ){
 	$proceed = true;
	
	// workaround to unexpected config->set() behaviour
	$preferences['use_report_junk'] = $proceed;
	$rcmail->user->save_prefs($preferences);
	$rcmail->output->command('display_message', $this->gettext('report_junk_settingsstored'), 'confirmation');
    }

    $useservice = $rcmail->config->get('use_report_junk');
	
    if ( $proceed != 'once' && (is_null($useservice) || $useservice === false )) {
	return;
    }
	
    $this->generate_reports($mbox,$uids);
	
	
    // Old Code to move the junk messages to the JUNK Folder and set the appropriate imap flags
	
    // $rcmail->imap->unset_flag($uids, 'NONJUNK');
    // $rcmail->imap->set_flag($uids, 'JUNK');
    
    // if (($junk_mbox = $rcmail->config->get('junk_mbox')) && $mbox != $junk_mbox) {
      // $rcmail->output->command('move_messages', $junk_mbox);
    // }
    
    // $rcmail->output->command('display_message', $this->gettext('reportedasjunk'), 'confirmation');
    // $rcmail->output->send();
  }
  
  /*
   *
   */

  function generateUUID(){
	/* Function for generating a UUID as per RFC4122. Taken from http://www.php.net/manual/de/function.uniqid.php#88400 */

        $pr_bits = false;
        if (is_a ( $this, 'uuid' )) {
            if (is_resource ( $this->urand )) {
                $pr_bits .= @fread ( $this->urand, 16 );
            }
        }
        if (! $pr_bits) {
            $fp = @fopen ( '/dev/urandom', 'rb' );
            if ($fp !== false) {
                $pr_bits .= @fread ( $fp, 16 );
                @fclose ( $fp );
            } else {
                // If /dev/urandom isn't available (eg: in non-unix systems), use mt_rand().
                $pr_bits = "";
                for($cnt = 0; $cnt < 16; $cnt ++) {
                    $pr_bits .= chr ( mt_rand ( 0, 255 ) );
                }
            }
        }
        $time_low = bin2hex ( substr ( $pr_bits, 0, 4 ) );
        $time_mid = bin2hex ( substr ( $pr_bits, 4, 2 ) );
        $time_hi_and_version = bin2hex ( substr ( $pr_bits, 6, 2 ) );
        $clock_seq_hi_and_reserved = bin2hex ( substr ( $pr_bits, 8, 2 ) );
        $node = bin2hex ( substr ( $pr_bits, 10, 6 ) );
        
        /**
         * Set the four most significant bits (bits 12 through 15) of the
         * time_hi_and_version field to the 4-bit version number from
         * Section 4.1.3.
         * @see http://tools.ietf.org/html/rfc4122#section-4.1.3
         */
        $time_hi_and_version = hexdec ( $time_hi_and_version );
        $time_hi_and_version = $time_hi_and_version >> 4;
        $time_hi_and_version = $time_hi_and_version | 0x4000;
        
        /**
         * Set the two most significant bits (bits 6 and 7) of the
         * clock_seq_hi_and_reserved to zero and one, respectively.
         */
        $clock_seq_hi_and_reserved = hexdec ( $clock_seq_hi_and_reserved );
        $clock_seq_hi_and_reserved = $clock_seq_hi_and_reserved >> 2;
        $clock_seq_hi_and_reserved = $clock_seq_hi_and_reserved | 0x8000;
        
        return sprintf ( '%08s-%04s-%04x-%04x-%012s', $time_low, $time_mid, $time_hi_and_version, $clock_seq_hi_and_reserved, $node );
  }

  function anonymize_headers($id){
  	/**
	 * Remove personal information from Headers
	*/
	$rcmail = rcmail::get_instance();
	$header=$rcmail->imap->get_raw_headers($id);
	$header=preg_replace('/^To:.*\n/m',"To:\n",$header);
	$header=preg_replace('/^X-Original-To:.*\n/m',"X-Original-To:\n",$header);
	#$header=preg_replace('/^Delivered-To:.*\n/m',"Delivered-To:\n",$header);
	$header=preg_replace('/^\tfor <.*>;/m',"\tfor <>;",$header);
	return $header;
  }

  function generate_reports($mbox,$uids){
    $this->add_texts('localization');
    $this->load_config();
	$uids = explode(',',$uids);
	
	$rcmail = rcmail::get_instance();
	$oldmb = $rcmail->imap->get_mailbox_name();
	
	$rcmail->imap->set_mailbox($mbox);
	
	$success=array();
	foreach ( $uids as $id ) {
		$result=$this->send_report(array(
			'mail' => $this->anonymize_headers($id),
			'event_id' => $this->generateUUID(),
			'event_timestamp' => time(),
		));
		foreach (array_keys($result) as $key){
			if ($result[$key] != 200){
				$success[$key]=0;
			} else {
				$success[$key]=1;
			}
		}
	}
	$successsvc="";
	$failsvc="";
	foreach ($success as $key=>$value){
		if ($value==1) {
			$successsvc.='<li>'.$rcmail->config->get('report_junk_'.$key.'_name')."</li>";
		} else {
			$failsvc.='<li>'.$rcmail->config->get('report_junk_'.$key.'_name')."</li>";
		}
	}
	if ($failsvc==""){
		$rcmail->output->command('display_message', $this->gettext('report_junk_reportsgenerated').'<ul>'.$successsvc.'</ul>', 'confirmation');
	}
	else {
		$rcmail->output->command('display_message', $this->gettext('report_junk_reportsgenerated').'<ul>'.$successsvc.'</ul><br />'.$this->gettext('report_junk_reportsfailed').'<ul>'.$failsvc.'</ul>', 'confirmation');
	}
	$rcmail->imap->set_mailbox($oldmb);
  }
  
  /*
   *
   */
  function send_report($data) {
	$rcmail = rcmail::get_instance();
	$this->load_config();
	$retval=array();

	foreach ( $rcmail->config->get('report_junk_services') as $service){
		$thedata = array_merge($rcmail->config->get('report_junk_'.$service.'_config'),$data);
	
		$datastring = array();
		foreach ( $thedata as $key => $value ) {
			$datastring[] = $key . '=' . $value;
		}
		$datastring = implode('&',$datastring);
	
		$curlHandle = curl_init();
	
		curl_setopt($curlHandle,CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curlHandle,CURLOPT_URL,$rcmail->config->get('report_junk_'.$service.'_url'));
		curl_setopt($curlHandle,CURLOPT_POST,count($data));
		curl_setopt($curlHandle,CURLOPT_POSTFIELDS,$datastring);
	
		curl_exec($curlHandle);
		$http_status = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
		curl_close($curlHandle);
		$retval[$service]=$http_status;
	}
	return $retval;
  }
}
