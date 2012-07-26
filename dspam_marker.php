<?php 
header('Status: 200 OK');

if ($_SERVER['REMOTE_ADDR']!='127.0.0.1'){
  echo "Denied!";
  exit;
}

$header=$_POST['mail'];
$recipient=$_POST['address'];

$headerarray=split("\n",$header);
$signature="";
foreach ($headerarray as $line){
    $linearray=split(":",$line);
    if ($linearray[0]=='X-DSPAM-Signature'){
        $signature=trim($linearray[1]);
        break;
    }
}

if ($signature!=""){
    $signature=preg_replace('/[^a-z0-9]/', '', $signature);
    if (strlen($signature)==22){
        exec("dspam --client --source=error --class=spam --signature=".$signature." --user '".escapeshellcmd($recipient)."'");
    }
}
?>
