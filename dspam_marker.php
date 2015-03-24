<?php 
header('Status: 200 OK');

if (($_SERVER['REMOTE_ADDR']!='127.0.0.1') AND ($_SERVER['REMOTE_ADDR']!='::1')){
  echo "Denied!";
  error_log("DENIED ".$_SERVER['REMOTE_ADDR']);
  exit;
}

$header=$_POST['mail'];
$recipient=$_POST['address'];

$headerarray=split("\n",$header);
$signature="";
$recipient="";
foreach ($headerarray as $line){
    $linearray=split(":",$line);
    if (($signature=="") AND ($linearray[0]=='X-DSPAM-Signature')){
        $signature=trim($linearray[1]);
        continue;
    }
    if (($recipient=="") AND ($linearray[0]=='Delivered-To')){
        $recipient=trim(preg_replace('/[<>]/','',$linearray[1]));
        continue;
    }
}

if ($signature!=""){
    $signature=preg_replace('/[^a-z0-9]/', '', $signature);
    if (strlen($signature)==22){
        $cmd="dspam --client --source=error --class='spam' --signature='".$signature."' --user '"..escapeshellcmd($recipient)."'";
        exec($cmd,$retarr,$retval);
        error_log($cmd);
        error_log($retval);
        error_log(join("\n",$retarr));
    }
}
?>

