<?php

$xml_forecast_regions = file_get_contents('https://meteo.arso.gov.si/uploads/probase/www/fproduct/text/en/fcast_si-subregion_latest.xml');
$xml_forecast_slovenia = file_get_contents('https://meteo.arso.gov.si/uploads/probase/www/fproduct/text/sl/forecast_si_latest.xml');

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
                    'mostClear'     => 'mostly-sunny',
                    'slightCloudy'  => 'mostly-sunny',
                    'partCloudy'    => 'partly-cloudy',
                    'modCloudy'     => 'partly-cloudy',
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


// foreach ($data_additional['metData'] as $item) {
//     foreach ($allowedLocations as $location) {
//         if ($item['domain_meteosiId'] === $location['location']) {
//             $filteredData['data'][] = [
//                 'location' => $item['domain_longTitle'],
//                 'location_display' => $location['display_name'],
//                 'location_short' => $location['code'],
//                 'lat' => (float)$item['domain_lat'],
//                 'lon' => (float)$item['domain_lon'],
//                 'alt' => (float)$item['domain_altitude'],
//                 'sunrise' => $item['sunrise'],
//                 'sunset' => $item['sunset'],
//                 'tempC' => (int)$item['t'],
//                 'icon' => null,
//                 'icon_url' => null,
//                 'condition' => null,
//                 'valid' => $item['valid'],
//             ];
//         }
//     }
// }


// Initialize Alpine.js data
$xData = [
    'rows' => $filteredData['data'],
];


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update data based on form inputs
    // $xData['rows'][$_POST['index']]['tempC'] = $_POST['tempC'];
    // $xData['rows'][$_POST['index']]['icon'] = $_POST['icon'];

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


// ORIGINAL
// 
// function buildCurrentCSV() {
//     $rows = [
//         ['site', 'location', 'latitude', 'longitde', 'temperature', 'icon', 'city'],
//     ];
//     $i = 1;
//     foreach ($filteredData['data'] as $item) {
//         $rows[] = [
//             $i,
//             $item['location_display'],
//             $item['lat'],
//             $item['lon'],
//             $item['tempC'],
//             $item['icon'],
//             $item['location']
//         ];
//         $i++;
//     }

//     $path = 'data/slo_map_current.csv';
//     $fp = fopen($path, 'w'); // open in write only mode (write at the start of the file)
//     foreach ($rows as $row) {
//         fputcsv($fp, $row);
//     }
//     fclose($fp);
// }


// razredi: clear, mostClear, slightCloudy, partCloudy, modCloudy, prevCloudy, overcast, FG (za interpretacijo gl. <nn_shortText>)

//     sunny    ?????     partly cloudy, partly cloudy, mostly cloudy, mostly cloudy, cloudy, foggy

// cloudy
// partly cloudy
// mostly cloudy
// rain
// snow

?>

<html>

<head>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: transparent;
        }
        .map {
            /* background: url(/vreme/images/mapbg.png); */
            background-size: auto 107%;
            background-position: -53px -12px;
        }
        .map .location div {
    position: absolute;
    top: 0;
    left: 0;
    z-index: 30;
    opacity: 0; /* Ensure they are hidden initially */
    animation-fill-mode: forwards; /* Keep the final state after animation ends */
}

/* Define custom properties for each location */
.map .location div.NG { --translateX: 60px; --translateY: 270px; }
.map .location div.CR { --translateX: 330px; --translateY: 390px; }
.map .location div.CE { --translateX: 360px; --translateY: 190px; }
.map .location div.KO { --translateX: 260px; --translateY: 310px; }
.map .location div.MB { --translateX: 430px; --translateY: 100px; }
.map .location div.KP { --translateX: 80px; --translateY: 370px; }
.map .location div.LJ { --translateX: 220px; --translateY: 230px; }
.map .location div.MS { --translateX: 530px; --translateY: 70px; }
.map .location div.NM { --translateX: 370px; --translateY: 270px; }
.map .location div.PO { --translateX: 170px; --translateY: 300px; }
.map .location div.JE { --translateX: 130px; --translateY: 150px; }
.map .location div.SG { --translateX: 310px; --translateY: 110px; }
.map .location div.BO { --translateX: 50px; --translateY: 180px; }

/* Fade-in animation */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateX(var(--translateX, 0px)) translateY(var(--translateY, 0px)) scale(0.8);
    }
    to {
        opacity: 1;
        transform: translateX(var(--translateX, 0px)) translateY(var(--translateY, 0px)) scale(1);
    }
}
@keyframes swipeIn {
    from {
        transform: scale(0);
        transform-origin: left bottom;
    }
    to {
        transform: scale(1);
        transform-origin: left bottom;
    }
}

        .map .location div p {
            margin: 0;
            background: orange;
            color: white;
            font-family: sans-serif;
            font-size: 12px;
            padding: 5px 5px 3px;
            text-align: center;
            /* border-radius: 2px; */
        }
        .map .location div p:nth-child(1) {
            display: none;
            position: absolute;
            top: 0;
            left: 0;
            transform: translateY(-100%);
            text-align: left;
            background: floralwhite;
            font-size: 10px;
            color: black;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .map .location div:hover p:nth-child(1) {
            display: block;
        }

        .map .temp,
        .map .clouds {
            width: 624px;
            height: 468px;
            background-position: 62% 42%;
            /* mix-blend-mode: overlay; */
            background-size: 150%;
            z-index: 20;
            position: absolute;
            top: 0;
        }

        .map .rain {
            width: 624px;
            height: 468px;
            /* background-position: 57% 49%; */
            /* mix-blend-mode: multiply; */
            /* background-size: 162%; */
            z-index: 20;
            /* position: absolute;
            top: 0; */
            background-position: 57% 40%;
            background-size: 163%;
        }

        input[type="range"]::-moz-range-progress {
            background: #3f83f8;
        }

        input[type="range"]::-moz-range-thumb {
            appearance: none;
            -moz-appearance: none;
            -webkit-appearance: none;
            background: #1c64f2;
            border: 0;
            border-radius: 9999px;
            cursor: pointer;
            height: 1.25rem;
            width: 1.25rem;
        }
    </style>
    <!-- Alpine Plugins -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/persist@3.x.x/dist/cdn.min.js"></script>
    <!-- Alpine Core -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
document.addEventListener("DOMContentLoaded", () => {
    const locations = document.querySelectorAll(".map .location div");

    // Define the SW corner as the origin point (0, 0)
    const originX = 0;
    const originY = 0;

    // Calculate distances from the SW corner
    const distances = Array.from(locations).map(location => {
        const translateX = parseInt(getComputedStyle(location).getPropertyValue("--translateX"), 10) || 0;
        const translateY = parseInt(getComputedStyle(location).getPropertyValue("--translateY"), 10) || 0;
        const distance = Math.sqrt(Math.pow(translateX - originX, 2) + Math.pow(translateY - originY, 2));
        return { element: location, distance };
    });

    // Determine the min and max distances
    const minDistance = Math.min(...distances.map(d => d.distance));
    const maxDistance = Math.max(...distances.map(d => d.distance));
    const totalDuration = 800; // Total animation duration in milliseconds

    // Distribute animation timings
    distances.forEach(({ element, distance }) => {
        const delay = ((distance - minDistance) / (maxDistance - minDistance)) * totalDuration;
        element.style.animation = `swipeIn 1s ease ${delay}ms forwards`;
    });
});

    </script>
</head>
<!-- dark:bg-gray-900 -->
<body class="">
    <div class="bg-gray-100 border-b border-gray-300 py-6 sticky top-0 z-50">
        <div class="container mx-auto flex items-baseline gap-8">
            <h1 class="text-xl font-bold text-gray-800 ">Vreme</h1> 
            <nav class="flex items-center gap-4">
                <a href="/vreme" class="text-lg underline text-blue-700 font-bold">Trenutno</a>
                <a href="/vreme" class="text-lg hover:underline text-gray-500 font-bold">Napoved</a>
                <a href="/vreme" class="text-lg hover:underline text-gray-500 font-bold">Obeti</a>
            </nav>
        </div>
    </div>
    <div class="container mx-auto grid gap-8 grid-cols-3" x-data="{
                rain: $persist({
                    toggle: false,
                    opacity: 1
                }),
                clouds: $persist({
                    toggle: false,
                    opacity: 1
                }),
                temp: {
                    toggle: false,
                    opacity: 1
                },
                location: {
                    toggle: true,
                },
                timestamp: null,
                time: null,
                rows: <?= htmlspecialchars(json_encode($xData['rows'])) ?>,
                openDropdown: null,
                conditions: [
                    {
                        icon: 'sunny',
                        name: 'jasno',
                    },
                    {
                        icon: 'mostly-sunny',
                        name: 'pretežno jasno',
                    },
                    {
                        icon: 'partly-cloudy',
                        name: 'delno oblačno',
                    },
                    {
                        icon: 'cloudy',
                        name: 'oblačno',
                    },
                    {
                        icon: 'rain',
                        name: 'dež',
                    },
                    {
                        icon: 'heavy-rain',
                        name: 'močan dež',
                    },
                    {
                        icon: 'storm',
                        name: 'nevihta',
                    },
                    {
                        icon: 'heavy-storm',
                        name: 'močna nevihta',
                    },
                    {
                        icon: 'fog',
                        name: 'megla',
                    },
                ],
                updateTimestamp() {
                    this.timestamp = new Date().getTime();
                },
                getTimeFromDate(ts) {
                    var date = new Date(ts);
                    var hours = date.getHours();
                    var minutes = date.getMinutes();
                    var seconds = date.getSeconds();

                    var time = new Date();
                    this.time = time.setHours(hours, minutes, seconds);
                }
            }" x-init="updateTimestamp();getTimeFromDate(timestamp);">
        <div class="col-span-2 min-h-[500px] flex items-center justify-center">
            <div class="map max-w-[830px] aspect-video flex-1 rounded-md" style="position:relative;">
                <!-- <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 280.55 183.98" class="fill-gray-300 dark:fill-gray-700" style="
                        position:absolute;
                        width:600px;
                        height:auto;
                        margin:40px 20px;
                    ">
                    <path d="m276.75 38.56-3.25-5.13-3.12-1.91-1.68-1.23-.67-1.78.78-1.23 1.68-.79 1.23-1.22-.56-2.47-1.35-.89-1.67-.11-1.23-.55v-2.13l-1.22.22-.79-1.01-.78-1.45-1.01-1.23-1.23-3.69-.22-.44.33-1.45 1.12-2.02.56-1.45.56-1.57.23-1.11-.46-.67-1.34-.12-.78-.44-1.79-2.13-.89-.56-12.18 1-3.7-.89-5.47 3.25-4.02 1.01-.56.33-.89.89.11.34.33.33.11.89-.56 4.02-.11 2.24.11 2.24 1.01 3.13 1.9 1.23 1.01 2.01.22 3.02-1.68-2.13-1-.67-3.58-.66-6.03-3.03-2.46-.55-2.46.22-6.04 2.46-2.35.11-6.82-1.01-1.45-.89-.22 2.01v1.68l-.56 1.12-.89.67-1.12.33-1.34-.33h-1.9l-1.9.56-1.34 1-.9 1.79-.34 1.45-.55 1.23-1.68 1.34-2.69.46-2-1.24-2.02-1.79-2.57-1-16.31.78-12.75-1.34-5.14 1.68-3.36 4.69-1.67-2.46-1.23-.22-3.24 2.02-1.9.21-1.23-.1-1.11.44-1.45 2.12-1.01 2.13-1.34 4.02-1.69 3.8-.55.45-2.01.89-2.24.44-.78-.55-1.45.67-.56.67-.45.9-1.01 1.23-.67 1.56-1.23-.22-.44.11-1.79 1.9-.89.56-.9.22-1.89.11-.9.35-1.34 1.89-.79 2.46-.78 2.12-1.57.67-1.23-1.23-1-2.23-1.23-1.68-3.02.67-1.45-.22-2.69-1.23-.55-.56-.22-.67-.46-.56-.88-.22-3.03.66-18.88-.55-1.12-.33-5.03-4.37-1.34-.67-1.46-.45H59.8l-3.02.57-1.45-.23-8.16-3.8-2.68-.45-5.82 1.01h-1.22l-5.93-1.45-1.34-.12v1.01l-.11.89-.33.79-.56.67-.12 3.24-1.01 2.01-1.67.89-2.24-.11-3.01.45-2.24 2.01-1.9 2.68-2.13 2.24-4.13 2.24-2.12 1.56-1.12.56-1.23.11-1 1.12-1.23 2.68-1.68 2.79L0 73.55l.68 1.22.55 1.57.56 3.24 1.23 1.57.34 1.79.78 1.12 2.46-.34-1.23-2.24 1.34.45 2.68.22 1.23.67 2.46.57 1.67 1.23 2.69 2.68 2.23.33 2.57-.22 2.13.44.67 2.35-2.57 4.7-9.95 7.48-2.01 2.69.67.78.23.78-.23.89-.67.9-.45.33-1.34 1.23 1.12 1.34.45.35.11.1.11.12v.22l2.46 2.79 2.68-.22 2.9-1.34 3.03-.67 1.56 2.46-1.34 5.03-3.47 7.93-.33 4.37 1.34 2.67 2.46 1.35 3.13.33 1.45.45 4.36 3.35 6.14 2.8 7.05 11.95.89 1.01 1.34.78h.79l.11.33-.67 1.68-3.47 4.36-4.13.45-3.47-1.9-4.35.44 1.34 1.58 1.34 1.45.9 1.34v.77l-14.54 2.13-1.9-.44.33 1.11.57.78.66.57.9.11v.78l-.56 1.34.22 1.12-.22.56 3.46 3.79 11.63-.55 4.24 2.68 1.23 1.23 1.35.45 4.8.67 1.01-.67.78-1.24 1.34-1.23 4.13-1.78 1.12-1.56-1.89-2.24v-1.34l.33-.9.68-.44 3.79.78 1.23.67 1.23 1.23 2.24 1.56 2.34.79 2.34.11 2.36-.45 4.25-2.01 2.23-.67 2.01.45 3.57 1.89 4.15.79 4.02-.34 3.46-2.01 1.68-1.56 3.46-2.58 1.23-2.12.45-1.79.45-3.34.56-1.69.78-1.23 2.24-2.57.88-1.34 1.12-2.57.68-.78 1.45-.67 1.01.56.22 1.9-.11 2.35.11 1.67.79 1.57 1.11 1.23 3.02 2.45.67.22.56.35.55.66.12 1.01-.34 2.24v.66l1.79 1.46 8.28 3.69 1.33 3.46 3.7.78 3.79-1.34 1.67-2.9.34-2.69h1.57l2.01 1.35 1.57 1.45 2.12.22 1 .45.9.79 4.36.1 7.38 6.26 4.02.57 8.61-3.25h2.9l1.01-.22.78-.68 1.56-2.23.9-.78-4.47-3.02-1.23-2.12-.67-2.68-.56-3.36-.56-1.57-.67-1.33-.11-1 1.12-.57.55-.55.34-1.01.56-.89 2.57-.79 1.12-.56 1.23-.44h1.79l-.45-1.12-1.46-2.46-1.56-1.9-.22-.23-.34.11-1.12.34-.45.34-.11.56-.33.22-1.12-.45-.78-.56-.45-.67-.89-1.68-.9-.89-.66-.34-.23-.56.56-1.44.78-.9 3.58-2.02 11.18-3.69 1.12-.88-.13-1.23-.33-1.35.46-1.23 1-.44 1 .11 1.01.33 1 .23.9-.34 1.68-1.34.89-.44 5.7 1 3.24.23 1.68-.46 1.79-.99.89-1.35v-1.79l-1-2.57-.35-1.56.35-1.57.66-1.57.46-1.56.11-2.12-.23-6.6.67-2.57 1.01-1.57.33-1.34-1.23-1.89-1.12-.79-2.46-1.01-1.11-.66-2.47-2.57-1.22-2.91v-3.24l1.22-3.69 1.69-3.13 1.45-2.13 1.89-1 7.94.56 1.68-1.11 1.79-3.03 1.23-.89 1.34-.67 1.45-.33 4.36-.12 5.81-3.13 3.02-.55 1.45-.45 1.91-.89 1.78-1.23 1.23-1.35.68-1.79v-1.67l-.23-1.79v-2.35l3.24.67 1.01-.22.9-.67.66-1.01.79-.78 1.11-.22.9.45 2.12 2.01 1.23.77 1.45.34h.78l3.25-.78h2l.45-.45-.1-1.45-.46-.56-1.45-1-.55-.68-.22-1.1.11-2.13-.11-1.01-1.01-3.58-.34-1.78.12-1.91 2.56-2.9 2.8-1.12 1.34-.78 1.79-.56 1.23-1.46.67 1.01.56-1.01 1 .79 1.12.11 1.11-.11 1.01.11.79.56 1.45 1.57.78.55.78.11 2.02-.22.88.11.9.56 2.13 1.68-.79-5.26-3.01-2.57z" />
                </svg> -->
                <!-- LINE -->
                <!-- <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 280.55 183.98" class="fill-transparent stroke-gray-500 z-20" style="
                        position:absolute;
                        width:600px;
                        height:auto;
                        margin:40px 20px;
                    ">
                    <path d="m276.75 38.56-3.25-5.13-3.12-1.91-1.68-1.23-.67-1.78.78-1.23 1.68-.79 1.23-1.22-.56-2.47-1.35-.89-1.67-.11-1.23-.55v-2.13l-1.22.22-.79-1.01-.78-1.45-1.01-1.23-1.23-3.69-.22-.44.33-1.45 1.12-2.02.56-1.45.56-1.57.23-1.11-.46-.67-1.34-.12-.78-.44-1.79-2.13-.89-.56-12.18 1-3.7-.89-5.47 3.25-4.02 1.01-.56.33-.89.89.11.34.33.33.11.89-.56 4.02-.11 2.24.11 2.24 1.01 3.13 1.9 1.23 1.01 2.01.22 3.02-1.68-2.13-1-.67-3.58-.66-6.03-3.03-2.46-.55-2.46.22-6.04 2.46-2.35.11-6.82-1.01-1.45-.89-.22 2.01v1.68l-.56 1.12-.89.67-1.12.33-1.34-.33h-1.9l-1.9.56-1.34 1-.9 1.79-.34 1.45-.55 1.23-1.68 1.34-2.69.46-2-1.24-2.02-1.79-2.57-1-16.31.78-12.75-1.34-5.14 1.68-3.36 4.69-1.67-2.46-1.23-.22-3.24 2.02-1.9.21-1.23-.1-1.11.44-1.45 2.12-1.01 2.13-1.34 4.02-1.69 3.8-.55.45-2.01.89-2.24.44-.78-.55-1.45.67-.56.67-.45.9-1.01 1.23-.67 1.56-1.23-.22-.44.11-1.79 1.9-.89.56-.9.22-1.89.11-.9.35-1.34 1.89-.79 2.46-.78 2.12-1.57.67-1.23-1.23-1-2.23-1.23-1.68-3.02.67-1.45-.22-2.69-1.23-.55-.56-.22-.67-.46-.56-.88-.22-3.03.66-18.88-.55-1.12-.33-5.03-4.37-1.34-.67-1.46-.45H59.8l-3.02.57-1.45-.23-8.16-3.8-2.68-.45-5.82 1.01h-1.22l-5.93-1.45-1.34-.12v1.01l-.11.89-.33.79-.56.67-.12 3.24-1.01 2.01-1.67.89-2.24-.11-3.01.45-2.24 2.01-1.9 2.68-2.13 2.24-4.13 2.24-2.12 1.56-1.12.56-1.23.11-1 1.12-1.23 2.68-1.68 2.79L0 73.55l.68 1.22.55 1.57.56 3.24 1.23 1.57.34 1.79.78 1.12 2.46-.34-1.23-2.24 1.34.45 2.68.22 1.23.67 2.46.57 1.67 1.23 2.69 2.68 2.23.33 2.57-.22 2.13.44.67 2.35-2.57 4.7-9.95 7.48-2.01 2.69.67.78.23.78-.23.89-.67.9-.45.33-1.34 1.23 1.12 1.34.45.35.11.1.11.12v.22l2.46 2.79 2.68-.22 2.9-1.34 3.03-.67 1.56 2.46-1.34 5.03-3.47 7.93-.33 4.37 1.34 2.67 2.46 1.35 3.13.33 1.45.45 4.36 3.35 6.14 2.8 7.05 11.95.89 1.01 1.34.78h.79l.11.33-.67 1.68-3.47 4.36-4.13.45-3.47-1.9-4.35.44 1.34 1.58 1.34 1.45.9 1.34v.77l-14.54 2.13-1.9-.44.33 1.11.57.78.66.57.9.11v.78l-.56 1.34.22 1.12-.22.56 3.46 3.79 11.63-.55 4.24 2.68 1.23 1.23 1.35.45 4.8.67 1.01-.67.78-1.24 1.34-1.23 4.13-1.78 1.12-1.56-1.89-2.24v-1.34l.33-.9.68-.44 3.79.78 1.23.67 1.23 1.23 2.24 1.56 2.34.79 2.34.11 2.36-.45 4.25-2.01 2.23-.67 2.01.45 3.57 1.89 4.15.79 4.02-.34 3.46-2.01 1.68-1.56 3.46-2.58 1.23-2.12.45-1.79.45-3.34.56-1.69.78-1.23 2.24-2.57.88-1.34 1.12-2.57.68-.78 1.45-.67 1.01.56.22 1.9-.11 2.35.11 1.67.79 1.57 1.11 1.23 3.02 2.45.67.22.56.35.55.66.12 1.01-.34 2.24v.66l1.79 1.46 8.28 3.69 1.33 3.46 3.7.78 3.79-1.34 1.67-2.9.34-2.69h1.57l2.01 1.35 1.57 1.45 2.12.22 1 .45.9.79 4.36.1 7.38 6.26 4.02.57 8.61-3.25h2.9l1.01-.22.78-.68 1.56-2.23.9-.78-4.47-3.02-1.23-2.12-.67-2.68-.56-3.36-.56-1.57-.67-1.33-.11-1 1.12-.57.55-.55.34-1.01.56-.89 2.57-.79 1.12-.56 1.23-.44h1.79l-.45-1.12-1.46-2.46-1.56-1.9-.22-.23-.34.11-1.12.34-.45.34-.11.56-.33.22-1.12-.45-.78-.56-.45-.67-.89-1.68-.9-.89-.66-.34-.23-.56.56-1.44.78-.9 3.58-2.02 11.18-3.69 1.12-.88-.13-1.23-.33-1.35.46-1.23 1-.44 1 .11 1.01.33 1 .23.9-.34 1.68-1.34.89-.44 5.7 1 3.24.23 1.68-.46 1.79-.99.89-1.35v-1.79l-1-2.57-.35-1.56.35-1.57.66-1.57.46-1.56.11-2.12-.23-6.6.67-2.57 1.01-1.57.33-1.34-1.23-1.89-1.12-.79-2.46-1.01-1.11-.66-2.47-2.57-1.22-2.91v-3.24l1.22-3.69 1.69-3.13 1.45-2.13 1.89-1 7.94.56 1.68-1.11 1.79-3.03 1.23-.89 1.34-.67 1.45-.33 4.36-.12 5.81-3.13 3.02-.55 1.45-.45 1.91-.89 1.78-1.23 1.23-1.35.68-1.79v-1.67l-.23-1.79v-2.35l3.24.67 1.01-.22.9-.67.66-1.01.79-.78 1.11-.22.9.45 2.12 2.01 1.23.77 1.45.34h.78l3.25-.78h2l.45-.45-.1-1.45-.46-.56-1.45-1-.55-.68-.22-1.1.11-2.13-.11-1.01-1.01-3.58-.34-1.78.12-1.91 2.56-2.9 2.8-1.12 1.34-.78 1.79-.56 1.23-1.46.67 1.01.56-1.01 1 .79 1.12.11 1.11-.11 1.01.11.79.56 1.45 1.57.78.55.78.11 2.02-.22.88.11.9.56 2.13 1.68-.79-5.26-3.01-2.57z" />
                </svg> -->
                <div class="location" :class="{hidden: !location.toggle}">
                    <?php
                    // foreach ($filteredData['data'] as $location) {
                    //     echo "<div class=" . $location["location_short"] . ">";
                    //     echo "<p>" . $location["location_display"] . "</p>";
                    //     echo "<p class='shadow-md'>" . $location["tempC"] . "°C</p>";
                    //     if ($location["icon_url"]) {
                    //         echo "<img src='" . $location["icon_url"] . "'>";
                    //     }
                    //     echo "</div>";
                    // }
                    ?>

                    <template x-for="(row, index) in rows" :key="row.location">
                        <div :class="row.location_short">
                            <p x-text="row.location_display"></p>
                            <p class="shadow-md" x-text="row.tempC + ' °C'"></p>
                            <img :src="'/vreme/icons/news/' + row.icon + '.png'" class="h-12 w-12"/>
                        </div>
                    </template>

<!-- 
                    <td class="px-6 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100" x-text="index + 1"></td>
                        <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="row.location_display">
                        <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="row.lat">
                        <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="row.lon">
                        <td class="whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            <input name="tempC" type="number" x-model.number="row.tempC" class="px-6 py-2 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-800 dark:border-gray-600 dark:focus:border-gray-400 dark:focus:ring-gray-500 dark:text-gray-100">
                        </td> -->

                </div>
                <div :class="{hidden: !temp.toggle}" :style="`opacity: ${temp.opacity}; background-image: url('https://meteo.arso.gov.si/uploads/probase/www/nowcast/inca/inca_t2m_latest.png?t=${timestamp}');`" class="temp">
                </div>
                <div :class="{hidden: !clouds.toggle}" :style="`opacity: ${clouds.opacity}; background-image: url('https://meteo.arso.gov.si/uploads/probase/www/nowcast/inca/inca_sp_latest.png?t=${timestamp}');`" class="clouds">
                </div>
                <div :class="{hidden: !rain.toggle}" :style="`opacity: ${rain.opacity}; background-image: url('https://meteo.arso.gov.si/uploads/probase/www/nowcast/inca/inca_si0zm_latest.png?t=${timestamp}');`" class="rain">
                </div>
                
            </div>
        </div>
        <div class="col-span-1 flex flex-col justify-end">

            <p class="text-lg font-semibold leading-none text-gray-800 dark:text-white">Nastavitve pogleda</p>
            <p class="mt-3 text-sm leading-tight text-gray-600 dark:text-gray-400">Izberi plasti za prikaz na zemljevidu.</p>

            <div class="flex flex-col gap-4 border-b dark:border-gray-800 dark:border-gray-800 py-4">
                <label class="relative inline-flex items-center cursor-pointer flex justify-between">
                    <dev class="label flex gap-4 items-center">
                        <svg class="icon h-6 w-6 dark:fill-white">
                            <use xlink:href="#icon-location" />
                        </svg>
                        <span class="text-sm font-medium text-gray-900 dark:text-gray-300">Lokacije</span>
                    </dev>
                    <div class="toggle relative">
                        <input type="checkbox" value="" class="sr-only peer" x-model="location.toggle">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                    </div>
                </label>
            </div>

            <div class="flex flex-col gap-4 border-b dark:border-gray-800 py-4">
                <label class="relative inline-flex items-center cursor-pointer flex justify-between">
                    <dev class="label flex gap-4 items-center">
                        <svg class="icon h-6 w-6 dark:fill-white">
                            <use xlink:href="#icon-rain" />
                        </svg>
                        <span class="text-base font-medium text-gray-900 dark:text-gray-300">Padavine</span>
                    </dev>
                    <div class="toggle relative">
                        <input type="checkbox" value="" class="sr-only peer" x-model="rain.toggle">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                    </div>
                </label>
                <div class="opacity ml-6 pl-4" x-show="rain.toggle">
                    <label for="small-range" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Prosojnost</label>
                    <input id="small-range" type="range" min="0" max="1" step="0.01" x-model="rain.opacity" class="w-full h-1 mb-6 bg-gray-200 rounded-lg appearance-none cursor-pointer range-sm dark:bg-gray-700">
                </div>
            </div>
            <div class="flex flex-col gap-4 border-b dark:border-gray-800 py-4">
                <label class="relative inline-flex items-center cursor-pointer flex justify-between">
                    <dev class="label flex gap-4 items-center">
                        <svg class="icon h-6 w-6 dark:fill-white">
                            <use xlink:href="#icon-clouds" />
                        </svg>
                        <span class="text-base font-medium text-gray-900 dark:text-gray-300">Oblačnost</span>
                    </dev>
                    <div class="toggle relative">
                        <input type="checkbox" value="" class="sr-only peer" x-model="clouds.toggle">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                    </div>
                </label>
                <div class="opacity ml-6 pl-4" x-show="clouds.toggle">
                    <label for="small-range" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Prosojnost</label>
                    <input id="small-range" type="range" min="0" max="1" step="0.01" x-model="clouds.opacity" class="w-full h-1 mb-6 bg-gray-200 rounded-lg appearance-none cursor-pointer range-sm dark:bg-gray-700">
                </div>
            </div>
            <div class="flex flex-col gap-4 border-b dark:border-gray-800 py-4">
                <label class="relative inline-flex items-center cursor-pointer flex justify-between">
                    <dev class="label flex gap-2 items-center">
                        <svg class="icon h-6 w-6 dark:fill-white">
                            <use xlink:href="#icon-temp" />
                        </svg>
                        <span class="ml-3 text-base font-medium text-gray-900 dark:text-gray-300">Temperatura</span>
                    </dev>
                    <div class="toggle relative">
                        <input type="checkbox" value="" class="sr-only peer" x-model="temp.toggle">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                    </div>
                </label>
                <div class="opacity ml-6 pl-4" x-show="temp.toggle">
                    <label for="small-range" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Prosojnost</label>
                    <input id="small-range" type="range" min="0" max="1" step="0.01" x-model="temp.opacity" class="w-full h-1 mb-6 bg-gray-200 rounded-lg appearance-none cursor-pointer range-sm dark:bg-gray-700">
                </div>
            </div>

            <div class="flex justify-between items-center mt-12">
                <p class="ml-3 text-xs font-medium text-gray-500 dark:text-gray-300">Last update:<br><span x-text="timestamp"></span></p>

                <button class="group px-5 py-4 rounded text-base font-medium leading-none text-center text-white bg-blue-700 hover:bg-blue-600 flex" @click="updateTimestamp()">
                    Osveži fotografije
                    <svg class="icon ml-2 h-4 w-4 fill-white group-hover:rotate-180 transition-all duration-300">
                        <use xlink:href="#icon-refresh" />
                    </svg>
                </button>
            </div>

            <div class="lg:px-12 md:px-8 px-4 max-w-[600px] mx-auto mt-12">

                <svg xmlns="http://www.w3.org/2000/svg" class="hidden">
                    <symbol viewBox="0 0 32 32" id="icon-temp">
                        <path d="M21.25 6.008c0-6.904-10.5-6.904-10.5 0v13.048a7.25 7.25 0 1 0 10.496-.002l.003.003zM16 28.75a4.75 4.75 0 0 1-3.17-8.287l.004-.004c.009-.008.013-.02.022-.029.059-.063.112-.133.157-.208l.003-.006a1.28 1.28 0 0 0 .119-.175l.003-.006c.02-.055.037-.122.049-.19l.001-.007c.027-.081.047-.175.056-.272v-.005l.007-.033V6.008a2.752 2.752 0 1 1 5.5.005v-.005 13.52c0 .012.007.023.007.035.009.098.028.188.056.274l-.002-.009c.013.079.031.149.055.217l-.003-.009c.038.068.079.127.123.182l-.002-.002c.048.081.101.151.16.215l-.001-.001c.009.009.012.021.022.029A4.75 4.75 0 0 1 16 28.75zm1.25-7.749v-7.5a1.25 1.25 0 0 0-2.5 0v7.5a3.257 3.257 0 0 0-2 3 3.25 3.25 0 1 0 4.521-2.992l-.021-.008zM26.5 1.75a3.75 3.75 0 1 0 3.75 3.75 3.754 3.754 0 0 0-3.75-3.75zm0 5a1.25 1.25 0 1 1 1.25-1.25 1.252 1.252 0 0 1-1.25 1.25z" />
                    </symbol>
                    <symbol viewBox="0 0 32 32" id="icon-refresh">
                        <path d="M30.445 13.837a1.25 1.25 0 1 0-2.48.332l-.001-.006c.071.505.111 1.089.111 1.682 0 7.044-5.707 12.755-12.749 12.762h-.031a12.598 12.598 0 0 1-9.469-4.273l-.012-.014h4.582a1.25 1.25 0 0 0 0-2.5H3.325c-.035 0-.064.017-.098.02-.08.009-.152.024-.222.046l.009-.002a1.208 1.208 0 0 0-.266.095l.007-.003c-.024.012-.051.012-.075.027-.036.03-.069.06-.1.091a1.277 1.277 0 0 0-.195.178l-.001.001a1.151 1.151 0 0 0-.28.656v.004a1.2 1.2 0 0 0-.027.131l-.001.006v7.072a1.25 1.25 0 0 0 2.5 0v-3.471a15.065 15.065 0 0 0 10.697 4.436h.056-.003c8.418-.009 15.24-6.831 15.25-15.248v-.001c0-.713-.048-1.414-.14-2.102l.009.08zM3.12 19.393c.059 0 .117-.004.173-.012l-.006.001a1.25 1.25 0 0 0 1.073-1.41l.001.006a12.285 12.285 0 0 1-.112-1.689c0-7.043 5.708-12.752 12.75-12.755h.029c3.771 0 7.156 1.653 9.469 4.274l.012.014h-4.581a1.25 1.25 0 0 0 0 2.5H29c.03 0 .056-.015.085-.017.179-.024.342-.071.492-.14l-.011.004c.025-.013.053-.013.078-.028.037-.03.07-.06.101-.092.073-.054.137-.112.194-.177l.001-.001c.093-.116.171-.251.226-.396l.003-.01c.024-.073.042-.158.05-.246v-.005c.011-.038.021-.086.028-.135l.001-.006V2.002a1.25 1.25 0 0 0-2.5 0v3.472a15.068 15.068 0 0 0-10.699-4.439h-.055.003C8.574 1.04 1.747 7.87 1.747 16.294c0 .711.049 1.411.143 2.096l-.009-.079a1.252 1.252 0 0 0 1.237 1.084z" />
                    </symbol>
                    <symbol viewBox="0 0 32 32" id="icon-rain">
                        <path d="M16 .75C8.422.75.75 6.676.75 18a1.25 1.25 0 0 0 2.5 0c0-1.724 2.234-2.179 3.416-2.179s3.417.455 3.417 2.179a1.25 1.25 0 0 0 2.5 0c0-1.724 2.234-2.179 3.417-2.179 1.182 0 3.416.455 3.416 2.179a1.25 1.25 0 0 0 2.5 0c0-1.723 2.234-2.178 3.416-2.178 1.184 0 3.418.455 3.418 2.178a1.25 1.25 0 0 0 2.5 0C31.25 6.676 23.578.75 16 .75zm4.666 14.263a6.712 6.712 0 0 0-4.676-1.691H16a6.72 6.72 0 0 0-4.674 1.697l.007-.006a7.384 7.384 0 0 0-4.728-1.701 7.403 7.403 0 0 0-3.005.633l.047-.019C4.598 7.872 9.758 3.29 15.995 3.249h.004c6.242.042 11.402 4.624 12.344 10.607l.009.071a7.32 7.32 0 0 0-2.959-.615 7.4 7.4 0 0 0-4.741 1.709l.012-.01zM16 16.75c-.69 0-1.25.56-1.25 1.25v8c0 3.789-3.488 3.781-3.489 0a1.25 1.25 0 0 0-2.5 0A5.787 5.787 0 0 0 10 29.819l-.009-.012a3.948 3.948 0 0 0 3.013 1.447h.002c2.108 0 4.244-1.805 4.244-5.254v-8c0-.69-.56-1.25-1.25-1.25z" />
                    </symbol>
                    <symbol viewBox="0 0 32 32" id="icon-clouds">
                        <path d="m5.753 26.352-.102.001a4.674 4.674 0 0 1-3.715-1.832l-.008-.011a6.231 6.231 0 0 1-1.179-3.663l.002-.141v.007a6.373 6.373 0 0 1 3.798-6.14l.041-.016a6.553 6.553 0 0 1 3.512-7.345l.039-.017c3.474-2.106 8.275-2.263 11.021.768a4.708 4.708 0 0 1 2.258-.568c.464 0 .913.066 1.337.19l-.034-.008a4.706 4.706 0 0 1 3.342 3.124l.009.033a3.533 3.533 0 0 1 .035 1.687l.004-.023c2.785.862 4.816 3.307 5.055 6.259l.002.025c.451 3.293-.943 7.094-5.066 7.658zm7.862-18.206a8.355 8.355 0 0 0-4.216 1.208l.037-.021c-1.895 1.149-3.415 3.206-2.004 5.533a1.25 1.25 0 0 1-1.069 1.898c-2.285 0-3.114 2.362-3.114 3.948a3.778 3.778 0 0 0 .675 2.296l-.008-.012a2.153 2.153 0 0 0 1.842.856h-.006 20.179c2.613-.369 2.98-3.211 2.76-4.83-.313-2.291-1.857-4.592-4.473-4.231a1.249 1.249 0 0 1-1.048-2.125c.654-.649.527-1.135.484-1.295a2.246 2.246 0 0 0-1.577-1.378l-.015-.003a2.277 2.277 0 0 0-2.279.601l-.001.001a1.249 1.249 0 0 1-1.946-.234l-.003-.005a4.682 4.682 0 0 0-4.231-2.203l.012-.001z" />
                    </symbol>
                    <symbol viewBox="0 0 32 32" id="icon-location">
                        <path d="M15.961.75C9.493 1.037 4.29 6.081 3.753 12.453l-.003.047c0 4.828 10.104 16.941 11.256 18.307.23.272.571.443.952.443h.004c.38 0 .721-.171.949-.44l.001-.002c1.161-1.365 11.337-13.478 11.337-18.309-.641-6.4-5.83-11.403-12.256-11.749L15.96.749zm.003 27.297C11.86 23.029 6.25 15.234 6.25 12.5c.584-5.013 4.644-8.915 9.68-9.248l.031-.002c5.092.316 9.183 4.22 9.784 9.2l.005.05c0 2.732-5.652 10.527-9.786 15.547zM16 6.429A5.572 5.572 0 1 0 21.572 12 5.577 5.577 0 0 0 16 6.429zm0 8.642A3.07 3.07 0 1 1 19.072 12 3.074 3.074 0 0 1 16 15.071z" />
                    </symbol>
                </svg>
            </div>
        </div>
    
        <table class="col-span-3 min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead>
                <tr>
                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Site
                    </th>
                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Location
                    </th>
                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Latitude
                    </th>
                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Longitude
                    </th>
                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Temperature
                    </th>
                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Icon
                    </th>
                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        City
                    </th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(row, index) in rows" :key="row.location">
                    <tr class="bg-white dark:bg-gray-900" :id="'row' + index">
                        <td class="px-6 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100" x-text="index + 1"></td>
                        <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="row.location_display">
                        <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="row.lat">
                        <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="row.lon">
                        <td class="whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            <input name="tempC" type="number" x-model.number="row.tempC" class="px-6 py-2 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-800 dark:border-gray-600 dark:focus:border-gray-400 dark:focus:ring-gray-500 dark:text-gray-100">
                        </td>
                        <td class="whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            <div>
                                <div class="relative mt-1">
                                    <button type="button" class="relative w-full cursor-default rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 text-left shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm" aria-haspopup="listbox" aria-expanded="true" aria-labelledby="listbox-label" @click="openDropdown === index ? openDropdown = null : openDropdown = index">
                                        <span class="flex items-center">
                                            <img :src="'/vreme/icons/news/' + row.icon + '.png'" alt="" class="h-6 w-6 flex-shrink-0 rounded-full">
                                            <span class="ml-3 block truncate" x-text="row.icon"></span>
                                        </span>
                                        <span class="pointer-events-none absolute inset-y-0 right-0 ml-3 flex items-center pr-2">
                                            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M10 3a.75.75 0 01.55.24l3.25 3.5a.75.75 0 11-1.1 1.02L10 4.852 7.3 7.76a.75.75 0 01-1.1-1.02l3.25-3.5A.75.75 0 0110 3zm-3.76 9.2a.75.75 0 011.06.04l2.7 2.908 2.7-2.908a.75.75 0 111.1 1.02l-3.25 3.5a.75.75 0 01-1.1 0l-3.25-3.5a.75.75 0 01.04-1.06z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </button>

                                    <!--
                                    Select popover, show/hide based on select state.

                                    Entering: ""
                                        From: ""
                                        To: ""
                                    Leaving: "transition ease-in duration-100"
                                        From: "opacity-100"
                                        To: "opacity-0"
                                    -->
                                    <ul x-show="openDropdown === index" class="absolute z-10 mt-1 max-h-56 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm" tabindex="-1" role="listbox" aria-labelledby="listbox-label" aria-activedescendant="listbox-option-3" @click.away="openDropdown = null">
                                    <!--
                                        Select option, manage highlight styles based on mouseenter/mouseleave and keyboard navigation.

                                        Highlighted: "text-white bg-indigo-600", Not Highlighted: "text-gray-900"
                                    -->
                                        <template x-for="(condition, index) in conditions" :key="condition.icon">
                                            <li class="text-gray-900 relative cursor-default select-none py-2 pl-3 pr-9 hover:text-white hover:bg-indigo-600 group" id="listbox-option-0" role="option" @click="row.icon = condition.icon;openDropdown = null">
                                                <div class="flex items-center">
                                                <img :src="'/vreme/icons/news/' + condition.icon + '.png'" alt="" class="h-6 w-6 flex-shrink-0 rounded-full">
                                                <!-- Selected: "font-semibold", Not Selected: "font-normal" -->
                                                <span class="font-normal ml-3 block truncate" x-text="condition.name"></span>
                                                </div>

                                                <!--
                                                Checkmark, only display for selected option.

                                                Highlighted: "text-white", Not Highlighted: "text-indigo-600"
                                                -->
                                                <span class="absolute inset-y-0 right-0 flex items-center pr-4 " :class="condition.icon === row.icon ? 'text-indigo-600 group-hover:text-white' : 'group-hover:text-indigo-600 text-white'">
                                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                                                    </svg>
                                                </span>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-2 whitespace-nowrap text-sm font-medium text-gray-500 dark:text-gray-400" x-text="row.location"></td>
                    </tr>
                </template>
            </tbody>
        </table>
        <form method="post" x-show="rows.length">
            <button type="submit">Save to CSV</button>
            <button type="submit" @click="rows = <?= htmlspecialchars(json_encode($xData['rows'])) ?>">Reset</button>
        </form>
    </div>
    
</body>

</html>