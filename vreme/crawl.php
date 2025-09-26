<?php

$url = "https://meteo.arso.gov.si/uploads/probase/www/observ/surface/text/sl/observationAms_si_latest.xml";
$xml = simplexml_load_file($url);

// Check if XML is loaded successfully
if ($xml !== false) {
    // Define arrays with locations and labels
    $kraji = [
        'BABNO-POL_' => 'Babno Polje',
        'BORST_GOREN-VAS_' => 'Gorenja vas',
        'NOVA-GOR_BILJE_' => 'Nova Gorica',
        'BOHIN-CES_' => 'Bohinj',
        'BORST_GOREN-VAS_' => 'Gorenja vas',
        'BOVEC_' => 'Bovec',
        'BREGINJ_' => 'Breginj',
        'CELJE_MEDLOG_' => 'Celje',
        'CERKN-JEZ_' => 'Cerknica',
        'DAVCA_' => 'Davča',
        'CRNOMELJ_' => 'Črnomelj',
        'AJDOV-INA_DOLENJE_' => 'Ajdovščina',
        'GORNJ-GRA_' => 'Gornji Grad',
        'GORICKO_KRAJI-PAR_' => 'Goričko',
        'HRASTNIK_' => 'Hrastnik',
        'IDRIJA_CISTI-NAP_' => 'Idrija',
        'ILIRS-BIS_' => 'Ilirska Bistrica',
        'KOCEVJE_' => 'Kočevje',
        'JERONIM_' => 'Vransko',
        'JERUZ-LEM_' => 'Jeruzalem',
        'KAMNI-BIS_' => 'Kamniška Bistrica',
        'KOPER_KAPET-IJA_' => 'Koper',
        'KRSKO_NEK_' => 'Krško',
        'KUBED_' => 'Kubed',
        'LENDAVA_' => 'Lendava',
        'CERKLJE_LETAL-SCE_' => 'Cerklje ob Krki',
        'MARIBOR_SLIVNICA_' => 'Maribor',
        'LJUBL-ANA_BRNIK_' => 'Brnik',
        'LESCE_' => 'Lesce',
        'PORTOROZ_SECOVLJE_' => 'Portorož',
        'LITIJA_GRBIN_' => 'Litija',
        'LJUBL-ANA_BEZIGRAD_' => 'Ljubljana',
        'LOGAR-DOL_' => 'Logarska dolina',
        'LOGATEC_' => 'Logatec',
        'MARIN-VAS_' => 'Marinča vas',
        'METLIKA_' => 'Metlika',
        'MEZICA_' => 'Mežica',
        'MIKLAVZ_NA-GOR_' => 'Miklavž na Gorjancih',
        'MURSK-SOB_' => 'Murska Sobota',
        'NOVA-GOR_' => 'Nova Gorica',
        'NOVA-VAS_BLOKE_' => 'Nova vas',
        'NOVO-MES_' => 'Novo mesto',
        'OSILNICA_' => 'Osilnica',
        'SKOCJAN_' => 'Škocjan',
        'PODNANOS_' => 'Podnanos',
        'POSTOJNA_' => 'Postojna',
        'PTUJ_' => 'Ptuj',
        'RADEG-NDA_' => 'Radegunda',
        'ROGAS-SLA_' => 'Rogaška Slatina',
        'SEVNO_' => 'Sevno',
        'SLOVE-KON_' => 'Slovenska Konjice',
        'SLOVE-GRA_' => 'Slovenj Gradec',
        'TATRE_' => 'Tatre',
        'TOLMIN_VOLCE_' => 'Tolmin',
        'TOPOL_' => 'Topol',
        'TRBOVLJE_' => 'Trbovlje',
        'TREBNJE_' => 'Trebnje',
        'TROJANE_LIMOVCE_' => 'Trojane',
        'VELENJE_' => 'Velenje',
        'VELIK-LAS_' => 'Velike Lašče',
        'VRHNIKA_' => 'Vrhnika',
        'ZADLOG_' => 'Zadlog',
        'ZGORN-KAP_' => 'Zgornja Kapla',
        'ZGORN-RAD_' => 'Zgornji Radovna',
        'ZGORN-SOR_' => 'Zgornja Sorica',
    ];

    // add domain_altitude
    $gore = [
        'BLEGOS_' => 'Blegoš',
        'BUKOV-VRH_' => 'Bukovski vrh',
        'HOCKO-POH_' => 'Hočko Pohorje',
        'JEZERSKO_' => 'Jezersko',
        'KOREN-SED_' => 'Korensko sedlo',
        'KREDA-ICA_' => 'Kredarica',
        'KRN_' => 'Krn',
        'KRVAVEC_' => 'Krvavec',
        'KUM_' => 'Kum',
        'LISCA_' => 'Lisca',
        'NANOS_' => 'Nanos',
        'OTLICA_' => 'Otlica',
        'PASJA-RAV_' => 'Pasja ravan',
        'PAVLI-SED_' => 'Pavličev sedlo',
        'PLANI-POD_' => 'Planina pod Golico',
        'PREDEL_' => 'Predel',
        'RATIT-VEC_' => 'Ratitovec',
        'ROGLA_' => 'Rogla',
        'RUDNO-POL_' => 'Rudno polje',
        'SLAVNIK_' => 'Slavnik',
        'TRIJE-KRA_NA-POH_' => 'Trije kralji',
        'SVISCAKI_' => 'Sviščaki',
        'SEBRE-VRH_' => 'Šebreljski vrh',
        'URSLJ-GOR_' => 'Uršlja gora',
        'VOGEL_' => 'Vogel',
        'VRSIC_' => 'Vršič',
        'ZELENICA_' => 'Zelenica',
    ];

    // Initialize a variable to store CSV content
    $csvContent = "Location,Temperature\n";

    // Loop through each predefined location
    foreach ($kraji as $id => $locationLabel) {
        // Check if the location ID exists in the XML data
        $found = false;
        foreach ($xml->metData as $metData) {
            $locationId = (string) $metData->domain_meteosiId;
            if ($locationId === $id) {
                $temperature = round((float) $metData->t); // Round to the nearest integer
                $csvContent .= "$locationLabel:  $temperature °C\n";
                $found = true;
                break;
            }
        }

        // If the location ID is not found in the XML data, add default entry
        if (!$found) {
            $csvContent .= "$locationLabel,–– °C\n";
        }
    }

     // Loop through each predefined location
     foreach ($gore as $id => $locationLabel) {
        // Check if the location ID exists in the XML data
        $found = false;
        foreach ($xml->metData as $metData) {
            $locationId = (string) $metData->domain_meteosiId;
            if ($locationId === $id) {
                $temperature = round((float) $metData->t); // Round to the nearest integer
                $altitude = number_format($metData->domain_altitude / 1000, 3, '.', '');
                $csvContent .= "$locationLabel ($metData->domain_altitude m):  $temperature °C\n";
                $found = true;
                break;
            }
        }

        // If the location ID is not found in the XML data, add default entry
        if (!$found) {
            $csvContent .= "$locationLabel,–– °C\n";
        }
    }

    // Output the HTML with Tailwind CSS and the CSV content in a styled textarea
    echo '
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <title>CSV Viewer</title>
</head>
<body class="bg-gray-200">
  <div class="container mx-auto mt-8">
    <div class="p-4 bg-white rounded-lg shadow-md">
      <label class="block text-sm font-medium text-gray-700">CSV Content:</label>
      <textarea class="mt-1 p-2 block w-full border rounded-md bg-gray-100" rows="10" readonly>'
      . htmlspecialchars($csvContent) . '</textarea>
    </div>
  </div>
</body>
</html>';

} else {
    echo "Failed to load XML data.";
}
?>
