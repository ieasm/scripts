<?php
//Changed by Gusev M.V.
//Date:20.07.2012
//Sends SMS when at least one of 11 triggered
//It was created inthe scope of ORD:138613
//SMS notification for luxoft channels:Msk-Croydon,Msk-Kiev,Kiev-Watford
  $user = '3431_nauka';
  $password = 'yfhujnhjy';
  $response = 'NaukaSvyaz';

  include("packet.class.php");
  $packetsms = new packetclass();

  $name = str_replace("Недоступен", "", $argv[1]);
  
  $stat = "недоступен";
  if ($argv[2] == "OK")
     $stat = "снова доступен";

  $message = "$name $stat";
  $to = array(79175727800,79250817757,79262717319,79261150125);
  $message = htmlspecialchars($message);
//  $message = iconv("WINDOWS-1251","UTF-8",$message);
  $messageData = array();

  foreach ($to as $phone){
      $wmsg = '<WMsg><ID>0</ID><DestinationAddress>'.$phone.'</DestinationAddress><Data>'.$message.'</Data><SourceAddress>'.$response.'</SourceAddress><SmscMsgID><string>String</string></SmscMsgID><Status>0</Status></WMsg>';
      array_push($messageData, $wmsg);
  }

  $messageData = implode(' ', $messageData);

  $result = $packetsms->SendPacket($user,$password,$messageData);

  foreach ($result as $r){
      if ($r[level] == 7){
          echo $r[value].'<br/>';
      }
  }

?>

