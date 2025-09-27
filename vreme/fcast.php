<?php
try {
    $xml_forecast_regions = @file_get_contents('https://meteo.arso.gov.si/uploads/probase/www/fproduct/text/en/fcast_si-subregion_latest.xml');
    if ($xml_forecast_regions === FALSE) {
        throw new Exception('Failed to fetch the Slovenia XML file.');

    }
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
    die();
}

$xml = simplexml_load_string($xml_forecast_regions);
// yes, encode and decode are needed!
$json = json_encode($xml);
$dataSlovenia = json_decode($json, true);

try {
    $xml_forecast_regions = @file_get_contents('https://meteo.arso.gov.si/uploads/probase/www/observ/surface/text/sl/observation_si_latest.xml');
    if ($xml_forecast_regions === FALSE) {
        throw new Exception('Failed to fetch the Slovenia XML file.');

    }
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
    die();
}

$xml = simplexml_load_string($xml_forecast_regions);
// yes, encode and decode are needed!
$json = json_encode($xml);
$currentSlovenia = json_decode($json, true);


// Capitals

try {
    $xml_forecast_regions = @file_get_contents('https://meteo.arso.gov.si/uploads/probase/www/fproduct/text/sl/forecast_eu-capital_latest.xml');
    if ($xml_forecast_regions === FALSE) {
        throw new Exception('Failed to fetch the Capitals XML file.');

    }
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
    die();
}

$xml = simplexml_load_string($xml_forecast_regions);
// yes, encode and decode are needed!
$json = json_encode($xml);
$dataEurope = json_decode($json, true);


try {
    $xml_forecast_regions = @file_get_contents('https://meteo.arso.gov.si/uploads/probase/www/observ/surface/text/sl/observation_eu-capital_latest.xml');
    if ($xml_forecast_regions === FALSE) {
        throw new Exception('Failed to fetch the Capitals XML file.');

    }
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
    die();
}

$xml = simplexml_load_string($xml_forecast_regions);
// yes, encode and decode are needed!
$json = json_encode($xml);
$currentEurope = json_decode($json, true);

// Adriatic

try {
    $xml_forecast_regions = @file_get_contents('https://meteo.arso.gov.si/uploads/probase/www/fproduct/text/sl/forecast_adria_latest.xml');
    if ($xml_forecast_regions === FALSE) {
        throw new Exception('Failed to fetch the Capitals XML file.');

    }
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
    die();
}

$xml = simplexml_load_string($xml_forecast_regions);
// yes, encode and decode are needed!
$json = json_encode($xml);
$dataAdriatic = json_decode($json, true);

// Additional locations

try {
    $xml_forecast_regions = @file_get_contents('https://meteo.arso.gov.si/uploads/probase/www/fproduct/text/sl/forecast_eu_latest.xml');
    if ($xml_forecast_regions === FALSE) {
        throw new Exception('Failed to fetch the Capitals XML file.');

    }
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
    die();
}

$xml = simplexml_load_string($xml_forecast_regions);
// yes, encode and decode are needed!
$json = json_encode($xml);
$dataAdditional = json_decode($json, true);

try {
    $xml_forecast_regions = @file_get_contents('https://meteo.arso.gov.si/uploads/probase/www/observ/surface/text/sl/observation_eu_latest.xml');
    if ($xml_forecast_regions === FALSE) {
        throw new Exception('Failed to fetch the Capitals XML file.');

    }
} catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
    die();
}

$xml = simplexml_load_string($xml_forecast_regions);
// yes, encode and decode are needed!
$json = json_encode($xml);
$currentAdditional = json_decode($json, true);






// group multiple days for each location domain_meteosiId
$groupedData = [];

foreach ($dataSlovenia['metData'] as $item) {
    $domain_meteosiId = $item['domain_meteosiId'];
    if (!isset($groupedData[$domain_meteosiId])) {
        $groupedData[$domain_meteosiId] = [];
    }
    $groupedData[$domain_meteosiId][] = $item;
}
foreach ($dataEurope['metData'] as $item) {
    $domain_meteosiId = $item['domain_meteosiId'];
    if (!isset($groupedData[$domain_meteosiId])) {
        $groupedData[$domain_meteosiId] = [];
    }
    $groupedData[$domain_meteosiId][] = $item;
}
foreach ($dataAdriatic['metData'] as $item) {
    $domain_meteosiId = $item['domain_meteosiId'];
    if (!isset($groupedData[$domain_meteosiId])) {
        $groupedData[$domain_meteosiId] = [];
    }
    $groupedData[$domain_meteosiId][] = $item;
}
foreach ($dataAdditional['metData'] as $item) {
    $domain_meteosiId = $item['domain_meteosiId'];
    if (!isset($groupedData[$domain_meteosiId])) {
        $groupedData[$domain_meteosiId] = [];
    }
    $groupedData[$domain_meteosiId][] = $item;
}

$groupedCurrent = [];

foreach ($currentSlovenia['metData'] as $item) {
    $domain_meteosiId = $item['domain_meteosiId'];
    if (!isset($groupedCurrent[$domain_meteosiId])) {
        $groupedCurrent[$domain_meteosiId] = [];
    }
    $groupedCurrent[$domain_meteosiId][] = $item;
}
foreach ($currentEurope['metData'] as $item) {
    $domain_meteosiId = $item['domain_meteosiId'];
    if (!isset($groupedCurrent[$domain_meteosiId])) {
        $groupedCurrent[$domain_meteosiId] = [];
    }
    $groupedCurrent[$domain_meteosiId][] = $item;
}
foreach ($currentAdditional['metData'] as $item) {
    $domain_meteosiId = $item['domain_meteosiId'];
    if (!isset($groupedCurrent[$domain_meteosiId])) {
        $groupedCurrent[$domain_meteosiId] = [];
    }
    $groupedCurrent[$domain_meteosiId][] = $item;
}

// order by date -- NOT needed, as long as the original data is ordered already

// foreach ($groupedData as $domain_meteosiId => $items) {
//     usort($items, function ($a, $b) {
//         $dateA = DateTime::createFromFormat('d.m.Y H:i e', $a['valid']);
//         $dateB = DateTime::createFromFormat('d.m.Y H:i e', $b['valid']);
//         return $dateA <=> $dateB;
//     });
//     $groupedData[$domain_meteosiId] = $items;
// }

// Check locations order, if that is important!

$allowedLocations = [
    1 => [
        'code' => 'LJ',
        'display_name' => 'Ljubljana',
        'location' => 'SI_OSREDNJESLOVENSKA_',
        'currentLocation' => 'LJUBL-ANA_BEZIGRAD_'
    ],
    2 => [
        'code' => 'MB',
        'display_name' => 'Maribor',
        'location' => 'SI_PODRAVSKA_',
        'currentLocation' => 'MARIBOR_SLIVNICA_'
    ],
    3 => [
        'code' => 'CE',
        'display_name' => 'Celje',
        'location' => 'SI_SAVINJSKA_',
        'currentLocation' => 'CELJE_'
    ],
    4 => [
        'code' => 'SG',
        'display_name' => 'Slovenj Gradec',
        'location' => 'SI_KOROSKA_',
        'currentLocation' => 'SLOVE-GRA_'
    ],
    5 => [
        'code' => 'MS',
        'display_name' => 'Murska Sobota',
        'location' => 'SI_POMURSKA_',
        'currentLocation' => 'MURSK-SOB_'
    ],
    6 => [
        'code' => 'NM',
        'display_name' => 'Novo mesto',
        'location' => 'SI_DOLENJSKA_',
        'currentLocation' => 'NOVO-MES_'
    ],
    7 => [
        'code' => 'CR',
        'display_name' => 'Črnomelj',
        'location' => 'SI_BELOKRANJSKA_',
        'currentLocation' => 'CRNOMELJ_'
    ],
    8 => [
        'code' => 'KO',
        'display_name' => 'Kočevje',
        'location' => 'SI_KOCEVSKA_',
        'currentLocation' => 'KOCEVJE_'
    ],
    9 => [
        'code' => 'PO',
        'display_name' => 'Postojna',
        'location' => 'SI_NOTRANJSKO-KRASKA_',
        'currentLocation' => 'POSTOJNA_'
    ],
    10 => [
        'code' => 'KP',
        'display_name' => 'Koper',
        'location' => 'SI_OBALNO-KRASKA_',
        'currentLocation' => 'PORTOROZ_SECOVLJE_'
    ],
    11 => [
        'code' => 'NG',
        'display_name' => 'Nova Gorica',
        'location' => 'SI_GORISKA_',
        'currentLocation' => 'NOVA-GOR_'
    ],
    12 => [
        'code' => 'JE',
        'display_name' => 'Jesenice',
        'location' => 'SI_ZGORNJESAVSKA_',
        'currentLocation' => 'RATECE_'
    ],
    // 0 => [
    //     'code' => 'KK',
    //     'display_name' => 'Krško',
    //     'location' => 'SI_SPODNJEPOSAVSKA_',
    //     'currentLocation' => 'CERKLJE_LETAL-SCE_'
    // ],
    // 0 => [
    //     'code' => 'KR',
    //     'display_name' => 'Kranj',
    //     'location' => 'SI_GORENJSKA_',
    //     'currentLocation' => 'LJUBL-ANA_BRNIK_'
    // ],
    // 0 => [
    //     'code' => 'BO',
    //     'display_name' => 'Bovec',
    //     'location' => 'SI_BOVSKA_',
    // ],
    13 => [
        'code' => 'SAR',
        'display_name' => 'Sarajevo',
        'location' => 'SARAJEVO_BJELAVE_',
        'currentLocation' => 'SARAJEVO_BJELAVE_',
    ],
    14 => [
        'code' => 'BUD',
        'display_name' => 'Budimpešta',
        'location' => 'BUDAPEST_PESTS-INC_',
        'currentLocation' => 'BUDAPEST_PESTS-INC_',
    ],
    15 => [
        'code' => 'VCE',
        'display_name' => 'Benetke',
        'location' => 'VENEZ-TES_',
    ],
    16 => [
        'code' => 'SZG',
        'display_name' => 'Salzburg',
        'location' => 'KLAGE-URT_FLUGH-FEN_',
        'currentLocation' => 'KLAGE-URT_FLUGH-FEN_',
    ],
    17 => [
        'code' => 'ZRH',
        'display_name' => 'Zürich',
        'location' => 'ZUERICH_KLOTEN_',
        'currentLocation' => 'ZUERICH_KLOTEN_',
    ],
    18 => [
        'code' => 'JE',
        'display_name' => 'Milano',
        'location' => 'MILANO_LINATE_',
        'currentLocation' => 'MILANO_LINATE_',
    ],
    19 => [
        'code' => 'JE',
        'display_name' => 'Zadar',
        'location' => 'ZADAR-PUN_',
    ],
];

// List of icons and their IDs

$icons = [
    1 => 'sunny',
    2 => 'mostly sunny',
    3 => 'partly cloudy',
    4 => 'mostly cloudy',
    5 => 'cloudy',
    6 => 'fog',
    7 => 'light rain',
    8 => 'rain',
    9 => 'heavy rain',
    10 => 'light snow',
    11 => 'snow',
    12 => 'heavy snow',
    13 => 'mix',
    14 => 'shower',
    15 => 'storm',
    16 => 'heavy storm',
    17 => 'partly rain',
    18 => 'partly snow',
    19 => 'partly mix',
    20 => 'night clear',
    21 => 'night partly cloudy',
    22 => 'night cloudy',
    23 => 'night rain',
    24 => 'night snow',
    25 => 'night mix',
    26 => 'night shower',
    27 => 'night storm',
    28 => 'night heavy storm'
];

$mapping = [
    'clear' => 'sunny',

    'mostClear'=> 'mostly sunny',

    'slightCloudy' => 'partly cloudy',
    'partCloudy' => 'partly cloudy',

    'partCloudy_SHRA' => 'light rain',
    'partCloudy_RA' => 'rain',
    'partCloudy_heavyRA' => 'heavy rain',

    'modCloudy' => 'mostly cloudy',
    'prevCloudy' => 'mostly cloudy',

    'prevCloudy_SHRA' => 'shower',
    'prevCloudy_RA' => 'rain',

    'overcast' => 'cloudy',
    'overcast_SHRA' => 'light rain',
    'overcast_RA' => 'rain',
    'overcast_RASN' => 'mix',

    'overcast_lightSN' => 'snow',
    'overcast_SN' => 'snow',
    'overcast_heavySN' => 'snow',

    'FG' => 'fog',

    // add additional mappings here

];

// match functions for icons
function getIconName($arsoNnIcon, $arsoWwsynIcon) {
    global $mapping;

    if($arsoWwsynIcon !== []) {
        $arsoIcon = $arsoNnIcon . '_' . $arsoWwsynIcon;
    } else {
        $arsoIcon = $arsoNnIcon;
    }

    $mappedName = $mapping[$arsoIcon] ?: null;

    return $mappedName;
}

// match functions for icons
function getIconId($arsoNnIcon, $arsoWwsynIcon) {
    global $icons;

    $mappedName = getIconName($arsoNnIcon, $arsoWwsynIcon);

    // If the mapped name was found, find its key in the icons array
    if ($mappedName) {
        $iconKey = array_search($mappedName, $icons);
        if ($iconKey !== false) {
            return $iconKey;
        }
    } else {
        return 'Icon not found';
    }

    // If no match was found, return a default value
    return null;
}

// here we match data (from multiple sources) with a list of allowed locations and map icons

$filteredData = [];

foreach ($groupedData as $arsoLocation) {
    foreach ($allowedLocations as $key => $allowedLocation) {
        if ($arsoLocation[0]['domain_meteosiId'] === $allowedLocation['location']) {
            $filteredData[$key] = [
                'location' => $arsoLocation[0]['domain_longTitle'],
                'location_display' => $allowedLocation['display_name'],
                'location_short' => $allowedLocation['code'],
                'currentT' => isset($allowedLocation['currentLocation']) && isset($groupedCurrent[$allowedLocation['currentLocation']]) && !is_array($groupedCurrent[$allowedLocation['currentLocation']][0]['t_degreesC']) ? $groupedCurrent[$allowedLocation['currentLocation']][0]['t_degreesC'] : '-',
                // 'currentT' => isset($allowedLocation['location']) ? $groupedCurrent[$allowedLocation['locationCurrent']] : 'n/a',
                'todayT' => (int)$arsoLocation[0]['t_degreesC'],
                'tonightT' => 0,
                'day1amT' => (int)$arsoLocation[1]['t_degreesC'],
                'day1pmT' => (int)$arsoLocation[2]['t_degreesC'],
                'currentIcon' => '1',
                'todayIconId' => getIconId($arsoLocation[0]['nn_icon'], $arsoLocation[0]['wwsyn_icon']),
                'todayIcon' => getIconName($arsoLocation[0]['nn_icon'], $arsoLocation[0]['wwsyn_icon']),
                'tonightIcon' => '1',
                'day1amIconId' => getIconId($arsoLocation[1]['nn_icon'], $arsoLocation[1]['wwsyn_icon']),
                'day1amIcon' => getIconName($arsoLocation[1]['nn_icon'], $arsoLocation[1]['wwsyn_icon']),
                'day1pmIconId' => getIconId($arsoLocation[2]['nn_icon'], $arsoLocation[2]['wwsyn_icon']),
                'day1pmIcon' => getIconName($arsoLocation[2]['nn_icon'], $arsoLocation[2]['wwsyn_icon']),
            ];
        }
    }
}

ksort($filteredData);

// Handle sumbit button --> run csv function

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    buildCSV($filteredData);
}

function buildCSV($rows) {
    // Output headers so that the file is downloaded rather than displayed
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=vreme_all-mks.csv');

    $fp = fopen('php://output', 'w');
    fputcsv($fp, [
        'index', 
        'town', 
        'currentT', 
        'todayT', 
        'tonightT', 
        'day1amT', 
        'day1pmT', 
        'currentIcon', 
        'todayIcon', 
        'tonightIcon', 
        'day1amIcon', 
        'day1pmIcon'
    ]);
    fputcsv($fp, [
        '0', 
        'Town', 
        '0', 
        '0', 
        '0', 
        '0', 
        '0', 
        '1', 
        '1', 
        '1', 
        '1', 
        '1'
    ]);

    foreach ($rows as $key => $row) {
        fputcsv($fp, [
            $key,
            $row['location_display'],
            $row['currentT'],
            $row['todayT'],
            $row['tonightT'],
            $row['day1amT'],
            $row['day1pmT'],
            $row['currentIcon'],
            $row['todayIconId'],
            $row['tonightIcon'],
            $row['day1amIconId'],
            $row['day1pmIconId'],
        ]);
    }

    // "Save" the file and open it for download
    fclose($fp);
    exit;
}

?>

<html>

<head>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="dark:bg-gray-900">
    <div class="bg-gray-100 border-b border-gray-300 py-6 sticky top-0 z-50">
        <div class="container mx-auto flex items-baseline gap-8">
            <h1 class="text-xl font-bold text-gray-800">Vreme</h1> 
            <nav class="flex items-center gap-4">
                <a href="/vreme" class="text-lg hover:underline text-gray-500 font-bold">Trenutno</a>
                <a href="/vreme/fcast.php" class="text-lg underline text-blue-700 font-bold">Napoved</a>
                <a href="/vreme" class="text-lg hover:underline text-gray-500 font-bold">Obeti</a>
                <a href="/vreme/arhiv.php" class="text-lg hover:underline text-gray-500 font-bold">Arhiv</a>
            </nav>
        </div>
    </div>
    <div class="container mx-auto grid gap-8 grid-cols-3">
        <table class="col-span-3 min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead>
                <tr>
                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-400 tracking-wider">
                        index
                    </th>
                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-400 tracking-wider">
                        town
                    </th>
                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-400 tracking-wider">
                        currentT	
                    </th>
                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-400 tracking-wider">
                        todayT
                    </th>
                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-400 tracking-wider">
                        tonightT
                    </th>
                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-400 tracking-wider">
                        day1amT
                    </th>
                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-400 tracking-wider">
                        day1pmT
                    </th>
                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-400 tracking-wider">
                        currentIcon
                    </th>
                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-400 tracking-wider">
                        todayIcon
                    </th>
                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-400 tracking-wider">
                        tonightIcon
                    </th>
                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-400 tracking-wider">
                        day1amIcon
                    </th>
                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-400 tracking-wider">
                        day1pmIcon
                    </th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($filteredData as $index => $row): ?>
                <tr class="bg-white dark:bg-gray-900" id="row<?= $index ?>">
                    <td class="px-6 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100"><?= $index ?></td>
                    <td class="whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?= $row['location_display'] ?></td>
                    <td class="whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center"><?= $row['currentT'] ?></td>
                    <td class="whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center"><?= $row['todayT'] ?></td>
                    <td class="whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center"><?= $row['tonightT'] ?></td>
                    <td class="whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center"><?= $row['day1amT'] ?></td>
                    <td class="whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center"><?= $row['day1pmT'] ?></td>
                    <td class="whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?= $row['currentIcon'] ?></td>
                    <td class="whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?= $row['todayIconId'] ?> <span class="text-xs text-gray-400">(<?= $row['todayIcon'] ?>)</span></td>
                    <td class="whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?= $row['tonightIcon'] ?></td>
                    <td class="whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?= $row['day1amIconId'] ?> <span class="text-xs text-gray-400">(<?= $row['day1amIcon'] ?>)</span></td>
                    <td class="whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?= $row['day1pmIconId'] ?> <span class="text-xs text-gray-400">(<?= $row['day1pmIcon'] ?>)</span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <form method="post">
            <button type="submit" class="bg-indigo-500 hover:bg-indigo-700 py-2 px-4 text-white rounded-md">Download CSV</button>
        </form>
    </div>
    
</body>

</html>