<?php
class packetclass
{
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

    function SendToServer($xml_data,$headers)
       {
        $ch = curl_init(); // ���������������� ���������� cURL
        curl_setopt($ch, CURLOPT_URL,"http://gw1.streamsms.ru/SendPacketSMS.asmx");
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

    function SendPacket($login,$password,$messageData)
       {

        $xml_data = '<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:xsd="http://www.w3.org/2001/XMLSchema"
xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
<soap:Body>
<SendPacket xmlns="http://webservice.devinosms.com/SendPacketSMS.asmx">
<userLogin>'.$login.'</userLogin>
<userPassword>'.$password.'</userPassword>
<messageArray>'.$messageData.'</messageArray>
</SendPacket>
</soap:Body>
</soap:Envelope>
';

        $headers = array(
            "POST /WebService.asmx HTTP/1.1",
            "HOST gw1.streamsms.ru",
            "Content-Type: text/xml; charset=utf-8",
            "Content-length: ".strlen($xml_data),
            "SOAPAction: http://webservice.devinosms.com/SendPacketSMS.asmx/SendPacket"
            );

        $data = $this->SendToServer($xml_data,$headers);
        // Show me the result
        $p = xml_parser_create();
        xml_parse_into_struct($p,$data,$results);
        xml_parser_free($p);
        return $results;
        } // SendTextMessage
}
?>
