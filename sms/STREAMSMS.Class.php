<?php

/*******************************************************************************

Функции формирования XML-POST-запросов на сервер gw1.streamsms.ru

Для формирования запросов используется библиотека cURL из PHP.
(на PHP серевре должны быть разрешены исходящие запросы и установлен
модуль cURL).

Пример использования - в конце

*******************************************************************************/

Class STREAMSMS 
   {
    /**
    * GetCommandStatus - декодирует ответ от сервера
    *
    * @param $status string - Статус комманды от сервера
    *
    * @return string Расшифровка - статус комманды от сервера
    */
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

    /**
    * GetMessageStatus - Расшифровка статуса сообщения
    *
    * @param $status string Статус сообщения
    *
    * @return string Расшифровка статуса сообщения
    */
    function GetMessageStatus($status)
     {
      switch($status)
       {
        case 'Enqueued':
          return 'Сообщение ожидает отправки';
        break;

        case 'Delivered_To_Gateway':
          return 'Сообщение доставлено на сервер';
        break;

        case 'Sent':
          return 'Сообщение передано в мобильную сеть';
        break;

        case 'Delivered_To_Recipient':
          return 'Сообщение доставлено получателю';
        break;

        case 'Error_Invalid_Destination_Address':
          return 'Ошибка: некорректный номер получателя сообщения';
        break;

        case 'Error_Invalid_Source_Address':
          return 'Ошибка: некорректный адрес отправителя сообщения';
        break;

        case 'Error_Rejected':
          return 'Ошибка: сообщение отклонено';
        break;

        case 'Error_Expired':
          return 'Ошибка: истек срок жизни сообщения';
        break;

        default:
          return 'Статус не распознан';
        break;
      } // switch($status)

    } // GetMessageStatus

    /**
    * SendToServer - отправка запроса на сервер через cURL
    *
    * @param $xml_data string XML-запрос к серверу (SOAP)
    * @param $headers string Заголовки запроса к серверу (SOAP)
    *
    * @return string XML-ответ от сервера (SOAP)
    */
    function SendToServer($xml_data,$headers)
       {
        $ch = curl_init(); // Инициализировать библиотеку cURL
        curl_setopt($ch, CURLOPT_URL,"http://gw1.streamsms.ru/WebService.asmx");
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

    /**
    * GetCreditBalance – запрос на получение баланса пользователя
    *
    * @param $login string Логин пользователя
    * @param $password string Пароль пользователя
    *
    * @return array("Ответ сервера" => (string), "Балланс" => (decimal)) Ответ сервера в виде массива данных
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
            "Ответ сервера" => $this->GetCommandStatus($results[3]['value']),
            "Балланс" => $results[4]['value']
            );
        } // GetCreditBalance

    /**
    * SendTextMessage - передача простого текстового SMS-сообщения
    *
    * @param $login string Логин пользователя
    * @param $password string Пароль пользователя
    * @param $destinationAddress string Мобильный телефонный номер получателя сообщения, в международном формате: код страны + код сети + номер телефона. Пример: 7903123456
    * @param $messageData string Текст сообщения, поддерживаемые кодировки IA5 и UCS2
    * @param $sourceAddress string Адрес отправителя сообщения. До 11 латинских символов или до 15 цифровых
    * @param $deliveryReport boolean Запрашивать отчет о статусе данного сообщения
    * @param $flashMessage boolean Отправка Flash-SMS
    * @param $validityPeriod integer Время жизни сообщения, устанавливается в минутах
    *
    * @return array("Ответ сервера" => (string), "ID сообщения" => (decimal)) Ответ сервера в виде массива данных
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
            "Ответ сервера" => $this->GetCommandStatus($results[3]['value']),
            "ID сообщения" => $results[5]['value']
            );
        } // SendTextMessage

    /**
    * GetMessageState – запрос на получение статус отправленного SMS-сообщения
    *
    * @param $login string Логин пользователя
    * @param $password string Пароль пользователя
    * @param $messageId string Идентификатор сообщения
    *
    * @return array("Ответ сервера" => (string), "Отчёт получен" => (string), "Статус сообщения" => (string)) Ответ сервера в виде массива данных
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
            "Ответ сервера" => $this->GetCommandStatus($results[3]['value']),
            "Отчёт получен" => join(' ',split('T',$results[4]['value'])),
            "Статус сообщения" => $this->GetMessageStatus($results[5]['value'])
            );
    } // GetMessageState

    /**
    * GetMessageState2 – запрос на получение статус отправленного SMS-сообщения (если с момента отправки ссобщения прошло более 2х суток)
    *
    * @param $login string Логин пользователя
    * @param $password string Пароль пользователя
    * @param $messageId string Идентификатор сообщения
    *
    * @return array("Ответ сервера" => (string), "Отчёт получен" => (string), "Статус сообщения" => (string)) Ответ сервера в виде массива данных
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
          "Ответ сервера" => $this->GetCommandStatus($results[3]['value']),
          "Отчёт получен" => join(' ',split('T',$results[4]['value'])),
          "Статус сообщения" => $this->GetMessageStatus($results[5]['value'])
        );
    } // GetMessageState
}

/*******************************************************************************
Пример использования:

  # Устанавливаем логин, пароль и отправителя
  $user = 'username';
  $password = 'password';
  $response = 'SMS-Notify'; // Имя отправителя должно жестко соответствовать логину

  # Объявляем класс, создаем объект
  include("STREAMSMS.Class.php");
  $streamsms = new STREAMSMS();

  # Получаем балланс
  print_r($streamsms->GetCreditBalance($user,$password));
  echo "<br />"; flush();

  # Отправляем сообщение на номер 79118887766
  $message = 'Проверка!';
  $to = '79118887766';
  $status = 1;
  $flash = 0;
  $time = 10;

  $message = htmlspecialchars($message); // Для преобразования символов (например ' <> & ) к XML формату

  $result = $streamsms->SendTextMessage($user,$password,$to,iconv("WINDOWS-1251","UTF-8",$message),$response,$status,$flash,$time);
  print_r($result);
  echo "<br />"; flush();

  # Ждём 5 секунд пока сообщение отсылается
  sleep(5);

  # Проверяем статус отосланого сообщения
  print_r($streamsms->GetMessageState($user,$password,$result['ID сообщения']));
  echo "<br />"; flush();

  # Получаем балланс после отправки
  print_r($streamsms->GetCreditBalance($user,$password));
  echo "<br />"; flush();
*******************************************************************************/
?>