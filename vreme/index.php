<?php

    $xml_string = file_get_contents('https://meteo.arso.gov.si/uploads/probase/www/observ/surface/text/sl/observation_si_latest.xml');
    $xml = simplexml_load_string($xml_string);
    $json = json_encode($xml);

    $data = json_decode($json, true);
  
    $filteredData = [
        'id' => $data['@attributes']['id'],
        'html_url' => $data['meteosi_url'],
        'xml_url' => 'https://meteo.arso.gov.si/uploads/probase/www/observ/surface/text/sl/observation_si_latest.xml',
        'data' => []
    ];
  
    foreach ($data['metData'] as $item) {
        $filteredData['data'][] = [
            'location' => $item['domain_longTitle'],
            'lat' => (float)$item['domain_lat'],
            'lon' => (float)$item['domain_lon'],
            'alt' => (float)$item['domain_altitude'],
            'sunrise' => $item['sunrise'],
            'sunset' => $item['sunset'],
            'tempC' => (int)$item['t_degreesC'],
            'icon' => ($item['nn_icon'] ? $item['nn_icon'] : null),
            'icon_url' => ($item['nn_icon'] ? $data['icon_url_base'] . $item['nn_icon'] . '.' . $data['icon_format'] : null),
            'condition' => ($item['nn_shortText'] ? $item['nn_shortText'] : null),
            'valid' => $item['valid'],
        ];
    }
  
    $filteredJson = json_encode($filteredData);
    
    header('Content-Type: application/json');
    echo $filteredJson;
  
?>