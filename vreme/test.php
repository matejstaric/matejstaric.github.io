<?php

$xml_forecast = file_get_contents('https://meteo.arso.gov.si/uploads/probase/www/fproduct/text/en/fcast_si-subregion_latest.xml');
$xml_forecast_5days = file_get_contents('https://meteo.arso.gov.si/uploads/probase/www/fproduct/text/sl/forecast_si_latest.xml');

$xml_manned = file_get_contents('https://meteo.arso.gov.si/uploads/probase/www/observ/surface/text/sl/observation_si_latest.xml');
$xml_auto = file_get_contents('https://meteo.arso.gov.si/uploads/probase/www/observ/surface/text/sl/observationAms_si_latest.xml');

$xml = simplexml_load_string($xml_manned);
$json = json_encode($xml);

$data = json_decode($json, true);

$data_additional =
    json_decode(
        json_encode(
            simplexml_load_string(
                file_get_contents('https://meteo.arso.gov.si/uploads/probase/www/observ/surface/text/sl/observationAms_si_latest.xml')
            )
        ),
        true
    );

// var_dump($data_additional)

$allowedLocations = [
    [
        'code' => 'LJ',
        'display_name' => 'Ljubljana',
        'location' => 'LJUBL-ANA_BRNIK_',
    ],
    [
        'code' => 'MB',
        'display_name' => 'Maribor',
        'location' => 'MARIBOR_SLIVNICA_',
    ],
    [
        'code' => 'NM',
        'display_name' => 'Novo mesto',
        'location' => 'NOVO-MES_',
    ],
    [
        'code' => 'JE',
        'display_name' => 'Jesenice',
        'location' => 'RATECE_',
    ],
    [
        'code' => 'KP',
        'display_name' => 'Koper',
        'location' => 'PORTOROZ_SECOVLJE_',
    ],
    [
        'code' => 'CR',
        'display_name' => 'Črnomelj',
        'location' => 'CRNOMELJ_',
    ],
    [
        'code' => 'KO',
        'display_name' => 'Kočevje',
        'location' => 'KOCEVJE_',
    ],
    [
        'code' => 'CE',
        'display_name' => 'Celje',
        'location' => 'CELJE_',
    ],
    [
        'code' => 'MS',
        'display_name' => 'Murska Sobota',
        'location' => 'MURSK-SOB_',
    ],
    [
        'code' => 'SG',
        'display_name' => 'Slovenj Gradec',
        'location' => 'SLOVE-GRA_',
    ],
    [
        'code' => 'NG',
        'display_name' => 'Nova Gorica',
        'location' => 'NOVA-GOR_',
    ],
    [
        'code' => 'PO',
        'display_name' => 'Postojna',
        'location' => 'POSTOJNA_',
    ],
    [
        'code' => 'KR',
        'display_name' => 'Kranj',
        'location' => 'KRANJ_',
    ],
    [
        'code' => 'BO',
        'display_name' => 'Bohinj',
        'location' => 'BOHIN-CES_',
    ],
];

$filteredData = [
    'id' => $data['@attributes']['id'],
    'html_url' => $data['meteosi_url'],
    'xml_url' => 'https://meteo.arso.gov.si/uploads/probase/www/observ/surface/text/sl/observation_si_latest.xml',
    'data' => []
];

foreach ($data['metData'] as $item) {
    foreach ($allowedLocations as $location) {
        if ($item['domain_meteosiId'] === $location['location']) {
            $filteredData['data'][] = [
                'location' => $item['domain_longTitle'],
                'location_display' => $location['display_name'],
                'location_short' => $location['code'],
                'lat' => (float)$item['domain_lat'],
                'lon' => (float)$item['domain_lon'],
                'alt' => (float)$item['domain_altitude'],
                'sunrise' => $item['sunrise'],
                'sunset' => $item['sunset'],
                'tempC' => (int)$item['t_degreesC'],
                'icon' => match($item['nn_icon']) {
                    'clear'         => 'sunny',
                    'mostClear'     => 'sunny',
                    'slightCloudy'  => 'partly cloudy',
                    'partCloudy'    => 'partly cloudy',
                    'modCloudy'     => 'partly cloudy',
                    'prevCloudy'    => 'cloudy',
                    'overcast'      => 'cloudy',
                    'FG'            => 'fog',
                    default => ''
                },
                'icon_url' => ($item['nn_icon'] ? $data['icon_url_base'] . $item['nn_icon'] . '.' . $data['icon_format'] : null),
                'condition' => ($item['nn_shortText'] ? $item['nn_shortText'] : null),
                'valid' => $item['valid'],
            ];
        }
    }
}

// Initialize Alpine.js data
$xData = [
    'rows' => $filteredData['data'],
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update data based on form inputs
    $xData['rows'][$_POST['index']]['tempC'] = $_POST['tempC'];
    $xData['rows'][$_POST['index']]['icon'] = $_POST['icon'];

    // Write data to CSV file
    buildCSV($xData['rows']);
}

function buildCSV($rows) {
    $fp = fopen('data.csv', 'w');
    fputcsv($fp, ['site', 'location', 'latitude', 'longitude', 'temperature', 'icon', 'city']);
    $i = 1;
    foreach ($rows as $row) {
        fputcsv($fp, [
            $i++,
            $row['location_display'],
            $row['lat'],
            $row['lon'],
            $row['tempC'],
            $row['icon'] ?? '',
            $row['location'],
        ]);
    }
    fclose($fp);
}
?>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<div x-data="<?= htmlspecialchars(json_encode($xData)) ?>">
    <table>
        <thead>
            <tr>
                <th>Site</th>
                <th>Location</th>
                <th>Latitude</th>
                <th>Longitude</th>
                <th>Temperature</th>
                <th>Icon</th>
                <th>City</th>
            </tr>
        </thead>
        <tbody>
            <template x-for="(row, index) in rows" :key="row.location">
                <tr>
                    <td x-text="index + 1"></td>
                    <td x-text="row.location_display"></td>
                    <td x-text="row.lat"></td>
                    <td x-text="row.lon"></td>
                    <td>
                        <input name="tempC" type="number" x-model.number="row.tempC">
                    </td>
                    <td>
                        <select name="icon" x-model="row.icon">
                            <option value="" disabled>Select an icon</option>
                            <option value="sunny">Sunny</option>
                            <option value="partly cloudy">Partly cloudy</option>
                            <option value="cloudy">Cloudy</option>
                            <option value="rain">Rainy</option>
                            <option value="storm">Storm</option>
                            <option value="fog">Fog</option>
                        </select>
                    </td>
                    <td x-text="row.location"></td>
                </tr>
            </template>
        </tbody>
    </table>
    <form method="post" x-show="rows.length">
        <button type="submit">Save to CSV</button>
    </form>
</div>