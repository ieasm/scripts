<?php

/*******************************************************************************

������� ������������ XML-POST-�������� �� ������ gw1.streamsms.ru

��� ������������ �������� ������������ ���������� cURL �� PHP.
(�� PHP ������� ������ ���� ��������� ��������� ������� � ����������
������ cURL).

������ ������������� - � �����

*******************************************************************************/

Class STREAMSMS 
   {
    /**
    * GetCommandStatus - ���������� ����� �� �������
    *
    * @param $status string - ������ �������� �� �������
    *
    * @return string ����������� - ������ �������� �� �������
    */
    function GetCommandStatus($status)
     {
      switch($status)
       {
        case 'OK_Operation_Completed':
          return '�������� ���������';
        break;

        case 'Error_Not_Enough_Credits':
          return '������: ������������ ��������';
        break;

        case 'Error_Message_Rejected':
          return '������: ��������� ���������';
        break;

        case 'Error_Invalid_Destination_Address':
          return '������: ������������ ����� ���������� ���������';
        break;

        case 'Error_Invalid_Source_Address':
          return '������: ������������ ����� ����������� ���������';
        break;

        case 'Error_SMS_User_Disabled':
          return '������: ���-������������ ������������';
        break;

        case 'Error_Invalid_MessageID':
          return '������: ������������ ������������� ���������';
        break;

        case 'Error_Invalid_Login':
          return '������: ������������ �����';
        break;

        case 'Error_Invalid_Password':
          return '������: ������������ ������';
        break;

        case 'Error_Unauthorised_IP_Address':
          return '������: ���������������� IP-�����';
        break;

        case 'Error_Message_Queue_Full':
          return '������: ������� ��������� �����';
        break;

        case 'Error_Gateway_Offline':
          return '������: ������ ����������';
        break;

        case 'Error_Gateway_Busy':
          return '������: ������ ����� ������ ��������';
        break;

        case 'Error_Database_Offline':
          return '������: ������ ���� ������ ����������';
        break;

        default:
          return '����� �� ���������';
        break;
      } // switch($status)

    } // GetCommandStatus

    /**
    * GetMessageStatus - ����������� ������� ���������
    *
    * @param $status string ������ ���������
    *
    * @return string ����������� ������� ���������
    */
    function GetMessageStatus($status)
     {
      switch($status)
       {
        case 'Enqueued':
          return '��������� ������� ��������';
        break;

        case 'Delivered_To_Gateway':
          return '��������� ���������� �� ������';
        break;

        case 'Sent':
          return '��������� �������� � ��������� ����';
        break;

        case 'Delivered_To_Recipient':
          return '��������� ���������� ����������';
        break;

        case 'Error_Invalid_Destination_Address':
          return '������: ������������ ����� ���������� ���������';
        break;

        case 'Error_Invalid_Source_Address':
          return '������: ������������ ����� ����������� ���������';
        break;

        case 'Error_Rejected':
          return '������: ��������� ���������';
        break;

        case 'Error_Expired':
          return '������: ����� ���� ����� ���������';
        break;

        default:
          return '������ �� ���������';
        break;
      } // switch($status)

    } // GetMessageStatus

    /**
    * SendToServer - �������� ������� �� ������ ����� cURL
    *
    * @param $xml_data string XML-������ � ������� (SOAP)
    * @param $headers string ��������� ������� � ������� (SOAP)
    *
    * @return string XML-����� �� ������� (SOAP)
    */
    function SendToServer($xml_data,$headers)
       {
        $ch = curl_init(); // ���������������� ���������� cURL
        curl_setopt($ch, CURLOPT_URL,"http://gw1.streamsms.ru/WebService.asmx");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // ������ ���� ����� (�������� ������) �� �������
        curl_setopt($ch, CURLOPT_TIMEOUT, 60); // ������ ����-��� ������ � ��������
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); // ������ ��������� HTTP �������
        curl_setopt($ch, CURLOPT_POST, 1); // ����� POST ������
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_data); // ������ ���� POST
        $data = curl_exec($ch); // ��������� HTTP �����

        if(curl_errno($ch)) 
           {
            die("Error: ".curl_error($ch));
            }
        else
           {
            curl_close($ch);
            return $data;
            }

        } // SendToServer

    /**
    * GetCreditBalance � ������ �� ��������� ������� ������������
    *
    * @param $login string ����� ������������
    * @param $password string ������ ������������
    *
    * @return array("����� �������" => (string), "�������" => (decimal)) ����� ������� � ���� ������� ������
    */
    function GetCreditBalance($login,$password)
       {
        $xml_data = '<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:xsd="http://www.w3.org/2001/XMLSchema"
xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
<soap:Body>
<GetCreditBalance xmlns="http://gw1.devinosms.com/WebService.asmx">
<smsUser>'.$login.'</smsUser>
<password>'.$password.'</password>
</GetCreditBalance>
</soap:Body>
</soap:Envelope>';

        $headers = array(
            "POST /WebService.asmx HTTP/1.1",
            "HOST gw1.streamsms.ru",
            "Content-Type: text/xml; charset=utf-8",
            "Content-length: ".strlen($xml_data),
            "SOAPAction: http://gw1.devinosms.com/WebService.asmx/GetCreditBalance"
            );

        $data = $this->SendToServer($xml_data,$headers);
        // Show me the result
        $p = xml_parser_create();
        xml_parse_into_struct($p,$data,$results);
        xml_parser_free($p);
        return array(
            "����� �������" => $this->GetCommandStatus($results[3]['value']),
            "�������" => $results[4]['value']
            );
        } // GetCreditBalance

    /**
    * SendTextMessage - �������� �������� ���������� SMS-���������
    *
    * @param $login string ����� ������������
    * @param $password string ������ ������������
    * @param $destinationAddress string ��������� ���������� ����� ���������� ���������, � ������������� �������: ��� ������ + ��� ���� + ����� ��������. ������: 7903123456
    * @param $messageData string ����� ���������, �������������� ��������� IA5 � UCS2
    * @param $sourceAddress string ����� ����������� ���������. �� 11 ��������� �������� ��� �� 15 ��������
    * @param $deliveryReport boolean ����������� ����� � ������� ������� ���������
    * @param $flashMessage boolean �������� Flash-SMS
    * @param $validityPeriod integer ����� ����� ���������, ��������������� � �������
    *
    * @return array("����� �������" => (string), "ID ���������" => (decimal)) ����� ������� � ���� ������� ������
    */
    function SendTextMessage($login,$password,$destinationAddress,$messageData,$sourceAddress,$deliveryReport,$flashMessage,$validityPeriod)
       {

        $xml_data = '<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:xsd="http://www.w3.org/2001/XMLSchema"
xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
<soap:Body>
<SendTextMessage xmlns="http://gw1.devinosms.com/WebService.asmx">
<smsUser>'.$login.'</smsUser>
<password>'.$password.'</password>
<destinationAddress>'.$destinationAddress.'</destinationAddress>
<messageData>'.$messageData.'</messageData>
<sourceAddress>'.$sourceAddress.'</sourceAddress>
<deliveryReport>'.$deliveryReport.'</deliveryReport>
<flashMessage>'.$flashMessage.'</flashMessage>
<validityPeriod>'.$validityPeriod.'</validityPeriod>
</SendTextMessage>
</soap:Body>
</soap:Envelope>';

        $headers = array(
            "POST /WebService.asmx HTTP/1.1",
            "HOST gw1.streamsms.ru",
            "Content-Type: text/xml; charset=utf-8",
            "Content-length: ".strlen($xml_data),
            "SOAPAction: http://gw1.devinosms.com/WebService.asmx/SendTextMessage"
            );

        $data = $this->SendToServer($xml_data,$headers);
        // Show me the result
        $p = xml_parser_create();
        xml_parse_into_struct($p,$data,$results);
        xml_parser_free($p);
        return array(
            "����� �������" => $this->GetCommandStatus($results[3]['value']),
            "ID ���������" => $results[5]['value']
            );
        } // SendTextMessage

    /**
    * GetMessageState � ������ �� ��������� ������ ������������� SMS-���������
    *
    * @param $login string ����� ������������
    * @param $password string ������ ������������
    * @param $messageId string ������������� ���������
    *
    * @return array("����� �������" => (string), "����� �������" => (string), "������ ���������" => (string)) ����� ������� � ���� ������� ������
    */
    function GetMessageState($login,$password,$messageId)
       {
        $xml_data = '<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:xsd="http://www.w3.org/2001/XMLSchema"
xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
<soap:Body>
<GetMessageState xmlns="http://gw1.devinosms.com/WebService.asmx">
<smsUser>'.$login.'</smsUser>
<password>'.$password.'</password>
<messageId>'.$messageId.'</messageId>
</GetMessageState>
</soap:Body>
</soap:Envelope>';

        $headers = array(
            "POST /WebService.asmx HTTP/1.1",
            "HOST gw1.streamsms.ru",
            "Content-Type: text/xml; charset=utf-8",
            "Content-length: ".strlen($xml_data),
            "SOAPAction: http://gw1.devinosms.com/WebService.asmx/GetMessageState"
            );

        $data = $this->SendToServer($xml_data,$headers);
        // Show me the result
        $p = xml_parser_create();
        xml_parse_into_struct($p,$data,$results);
        xml_parser_free($p);
        return array(
            "����� �������" => $this->GetCommandStatus($results[3]['value']),
            "����� �������" => join(' ',split('T',$results[4]['value'])),
            "������ ���������" => $this->GetMessageStatus($results[5]['value'])
            );
    } // GetMessageState

    /**
    * GetMessageState2 � ������ �� ��������� ������ ������������� SMS-��������� (���� � ������� �������� ��������� ������ ����� 2� �����)
    *
    * @param $login string ����� ������������
    * @param $password string ������ ������������
    * @param $messageId string ������������� ���������
    *
    * @return array("����� �������" => (string), "����� �������" => (string), "������ ���������" => (string)) ����� ������� � ���� ������� ������
    */
    function GetMessageState2($login,$password,$messageId)
       {
        $xml_data = '<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:xsd="http://www.w3.org/2001/XMLSchema"
xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
<soap:Body>
<GetMessageState xmlns="http://gw1.devinosms.com/WebService.asmx">
<smsUser>'.$login.'</smsUser>
<password>'.$password.'</password>
<messageId>'.$messageId.'</messageId>
</GetMessageState>
</soap:Body>
</soap:Envelope>';

        $headers = array(
            "POST /WebService.asmx HTTP/1.1",
            "HOST gw1.streamsms.ru",
            "Content-Type: text/xml; charset=utf-8",
            "Content-length: ".strlen($xml_data),
            "SOAPAction: http://gw1.devinosms.com/WebService.asmx/GetMessageState2"
        );

        $data = $this->SendToServer($xml_data,$headers);
        // Show me the result
        $p = xml_parser_create();
        xml_parse_into_struct($p,$data,$results);
        xml_parser_free($p);
        return array(
          "����� �������" => $this->GetCommandStatus($results[3]['value']),
          "����� �������" => join(' ',split('T',$results[4]['value'])),
          "������ ���������" => $this->GetMessageStatus($results[5]['value'])
        );
    } // GetMessageState
}

/*******************************************************************************
������ �������������:

  # ������������� �����, ������ � �����������
  $user = 'username';
  $password = 'password';
  $response = 'SMS-Notify'; // ��� ����������� ������ ������ ��������������� ������

  # ��������� �����, ������� ������
  include("STREAMSMS.Class.php");
  $streamsms = new STREAMSMS();

  # �������� �������
  print_r($streamsms->GetCreditBalance($user,$password));
  echo "<br />"; flush();

  # ���������� ��������� �� ����� 79118887766
  $message = '��������!';
  $to = '79118887766';
  $status = 1;
  $flash = 0;
  $time = 10;

  $message = htmlspecialchars($message); // ��� �������������� �������� (�������� ' <> & ) � XML �������

  $result = $streamsms->SendTextMessage($user,$password,$to,iconv("WINDOWS-1251","UTF-8",$message),$response,$status,$flash,$time);
  print_r($result);
  echo "<br />"; flush();

  # ��� 5 ������ ���� ��������� ����������
  sleep(5);

  # ��������� ������ ���������� ���������
  print_r($streamsms->GetMessageState($user,$password,$result['ID ���������']));
  echo "<br />"; flush();

  # �������� ������� ����� ��������
  print_r($streamsms->GetCreditBalance($user,$password));
  echo "<br />"; flush();
*******************************************************************************/
?>