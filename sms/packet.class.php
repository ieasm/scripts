<?php
class packetclass
{
    function GetCommandStatus($status)
     {
      switch($status)
       {
        case 'OK_Operation_Completed':
          return 'Операция выполнена';
        break;

        case 'Error_Not_Enough_Credits':
          return 'Ошибка: недостаточно кредитов';
        break;

        case 'Error_Message_Rejected':
          return 'Ошибка: сообщение отклонено';
        break;

        case 'Error_Invalid_Destination_Address':
          return 'Ошибка: некорректный номер получателя сообщения';
        break;

        case 'Error_Invalid_Source_Address':
          return 'Ошибка: некорректный адрес отправителя сообщения';
        break;

        case 'Error_SMS_User_Disabled':
          return 'Ошибка: СМС-пользователь заблокирован';
        break;

        case 'Error_Invalid_MessageID':
          return 'Ошибка: некорректный идентификатор сообщения';
        break;

        case 'Error_Invalid_Login':
          return 'Ошибка: неправильный логин';
        break;

        case 'Error_Invalid_Password':
          return 'Ошибка: неправильный пароль';
        break;

        case 'Error_Unauthorised_IP_Address':
          return 'Ошибка: неавторизованный IP-адрес';
        break;

        case 'Error_Message_Queue_Full':
          return 'Ошибка: очередь сообщений полна';
        break;

        case 'Error_Gateway_Offline':
          return 'Ошибка: сервер недоступен';
        break;

        case 'Error_Gateway_Busy':
          return 'Ошибка: сервер занят другим запросом';
        break;

        case 'Error_Database_Offline':
          return 'Ошибка: сервер базы данных недоступен';
        break;

        default:
          return 'Ответ не распознан';
        break;
      } // switch($status)

    } // GetCommandStatus

    function SendToServer($xml_data,$headers)
       {
        $ch = curl_init(); // Инициализировать библиотеку cURL
        curl_setopt($ch, CURLOPT_URL,"http://gw1.streamsms.ru/SendPacketSMS.asmx");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Должен быть ответ (ожидание ответа) от сервера
        curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Задать тайм-аут работы с сокетами
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); // Задать заголовки HTTP запроса
        curl_setopt($ch, CURLOPT_POST, 1); // Будет POST запрос
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_data); // Задать тело POST
        $data = curl_exec($ch); // Выполнить HTTP обмен

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
