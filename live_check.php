<?php
// +---------------------------------------------+
// |         Copyright (c) 2024 Paeddy           |
// +---------------------------------------------+
$check_time = array(00, 15, 30, 45);
if (in_array(date("i"), $check_time)) {
  // Config laden
  include 'config.php';
  
  $multiCurl = array();
  $mh = curl_multi_init();
  foreach ($stations as $i => $station) {
    $multiCurl[$i] = curl_init();
    curl_setopt($multiCurl[$i], CURLOPT_RETURNTRANSFER, true);
    curl_setopt($multiCurl[$i], CURLOPT_HEADER, false);
    curl_setopt($multiCurl[$i], CURLOPT_TIMEOUT, 10);
    curl_setopt($multiCurl[$i], CURLOPT_URL, 'https://api.laut.fm/station/'.$station['name'].'/last_songs');
    curl_multi_add_handle($mh, $multiCurl[$i]);
  }
  $index = null;
  do {
    curl_multi_exec($mh,$index);
  } while($index > 0);
  foreach($multiCurl as $k => $ch) {
    $statuscode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($statuscode === 200) {
      $obsolete = false;
      $live = false;
      $json_data = json_decode(curl_multi_getcontent($ch));
      $res = $json_data[0];
      
      // Prüfen ob letzter Eintrag älter als 30 Minuten ist
      $compare = time() - strtotime($res->started_at);
      if ($compare > 30*60){ $obsolete = true; }     

      // Prüfen ob live gesendet wird
      if (isset($res->live)) { $live = true; }

      if ($obsolete === true OR $live === false) {
        
        // Hostname anpassen
        $host = str_replace('_', '', trim(strtolower($stations[$index]['name']), ' -'));

        // Wenn Station nicht als live in der API steht oder
        // die Metadaten älter als 30 Minuten sind, wird automatisch gekickt  
        $get_lautcast_kill = curl_init();
        curl_setopt($get_lautcast_kill, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($get_lautcast_kill, CURLOPT_TIMEOUT, 10);
        curl_setopt($get_lautcast_kill, CURLOPT_USERAGENT, 'Metadata monitoring');
        curl_setopt($get_lautcast_kill, CURLOPT_URL, "https://source:{$stations[$index]['password']}@{$host}.stream.laut.fm:8443/admin/killsource?mount=/{$stations[$index]['name']}" );
        $result_xml = curl_exec($get_lautcast_kill);
        $header = curl_getinfo($get_lautcast_kill, CURLINFO_HTTP_CODE);  
        curl_close($get_lautcast_kill);

        if ($header === 200) {
          $result = new SimpleXMLElement($result_xml);
          $output = $result->message;
          echo "=> {$stations[$index]['name']} - {$output}\n";
          $time = date('d.m.Y - H:i:s', strtotime($res->started_at));
          // Mail schicken
          $empfaenger = $stations[$index]['mail'];
          $betreff = "Eine Live-Verbindung wurde gekickt";
          $from = "From: Metadaten Überwachung <monitoring@{$_SERVER['SERVER_NAME']}>";
          $text = "Dies ist eine automatisierte E-Mail. \n\n Eine Live-Verbindung wurde gekickt. \n\n Details: \n Station: {$stations[$index]['name']} \n Letztes Titelupdate: {$time} Uhr \n lautcast meldet: {$output} ({$header})";
          mail($empfaenger, $betreff, $text, $from);
        } else {
          echo "Kicken nicht m&ouml;glich. lautcast meldet: {$header}";
        }
      // Warten
      usleep(200*1000);
      } else {
        echo "Kicken bei /{$stations[$index]['name']} nicht notwendig<br/>";
      }
    } else {
      echo "Die API konnte den n&ouml;tigen API-Endpunkt bei der Station {$stations[$index]} nicht erreichen ({$statuscode}). ";
    }
    $index++;
  }
} else {
  echo "N&auml;chste Pr&uuml;fung erfolgt bald";
}
?>