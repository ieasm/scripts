<?php
//Changed by Gusev M.V.
//Date:20.07.2012
//Sent sms when site vmdaily.ru is not available
//It was created inthe scope of ORD:138613

  $user = '3431_nauka';
  $password = 'yfhujnhjy';
  $response = 'NaukaSvyaz';

  include("packet.class.php");
  $packetsms = new packetclass();

  $message = 'Сайт www.vmdaily.ru не доступен';
  $to = array(79175727800,79852162353,79637162819,79035888748,79160912309,79269126700,79262717319);
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
