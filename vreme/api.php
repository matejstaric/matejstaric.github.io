<?php
/**
 * API endpoint for fetching ARSO archive data
 * This fetches real data from ARSO servers
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle OPTIONS request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON input']);
    exit();
}

// Check if this is a last30days request
$isLast30Days = isset($input['last_30_days']) && $input['last_30_days'] === true;

if ($isLast30Days) {
    // For last 30 days, only location_code is required
    $required = ['location_code'];
} else {
    // For regular requests, all parameters are required
    $required = ['location_code', 'variable_ids', 'date_from', 'date_to'];
}

foreach ($required as $param) {
    if (!isset($input[$param])) {
        http_response_code(400);
        echo json_encode(['error' => "Missing required parameter: $param"]);
        exit();
    }
}

$locationCode = $input['location_code'];

if ($isLast30Days) {
    // For last 30 days, we don't need these parameters
    $variableIds = [];
    $dateFrom = null;
    $dateTo = null;
} else {
    $variableIds = $input['variable_ids']; // Array of variable IDs
    $dateFrom = $input['date_from'];
    $dateTo = $input['date_to'];
    
    // Validate dates
    if (!strtotime($dateFrom) || !strtotime($dateTo)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid date format']);
        exit();
    }
    
    if (strtotime($dateFrom) > strtotime($dateTo)) {
        http_response_code(400);
        echo json_encode(['error' => 'Date from must be before date to']);
        exit();
    }
}

    // Location ID mapping (ARSO station IDs with required structure)
    $locations = [
        'LJ' => ['id' => 1895, 'group' => 'dailyData0', 'slug' => 'ljubljana'],
        'NG' => ['id' => 3001, 'group' => 'dailyData0', 'slug' => 'doblice'],
        'MB' => ['id' => 1902, 'group' => 'dailyData0', 'slug' => 'maribor'],  
        'NM' => ['id' => 1893, 'group' => 'dailyData0', 'slug' => 'novo-mesto'],
        'CE' => ['id' => 2482, 'group' => 'dailyData0', 'slug' => 'celje'],
        'KP' => ['id' => 1896, 'group' => 'dailyData0', 'slug' => 'koper'],
        'LI' => ['id' => 1900, 'group' => 'dailyData0', 'slug' => 'lisca'],
        'BR' => ['id' => 3049, 'group' => 'dailyData0', 'slug' => 'brnik'],
        'KR' => ['id' => 1890, 'group' => 'dailyData0', 'slug' => 'kredarica'],
        'SG' => ['id' => 1897, 'group' => 'dailyData0', 'slug' => 'slovenj-gradec'],
        'MS' => ['id' => 1894, 'group' => 'dailyData0', 'slug' => 'murska-sobota'],
        'KG' => ['id' => 1086, 'group' => 'dailyData0', 'slug' => 'kranj']
    ];

// Variable mapping to ARSO variable IDs (direct mapping only)
$variableMapping = [
    '35' => '35',  // Average temperature (t2m_klima)
    '36' => '36',  // Min temperature (tmin)
    '38' => '38',  // Max temperature (tmax)
    '41' => '41',  // Sunshine duration (trajanje_so)
    '46' => '46',  // Cloudiness (oblacnost)
    '85' => '85',  // Rainfall (količina padavin)
];

// Get location info
$locationInfo = $locations[$locationCode] ?? null;
if (!$locationInfo) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid location code']);
    exit();
}

if ($isLast30Days) {
    // Handle Last 30 Days request
    try {
        $slug = $locationInfo['slug'];
        $textUrl = "https://meteo.arso.gov.si/uploads/probase/www/climate/graph/sl/by_location/{$slug}/last30days_{$slug}.txt";
        
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => [
                    'User-Agent: Mozilla/5.0 (compatible; WeatherApp/1.0)',
                    'Accept: text/plain'
                ],
                'timeout' => 30
            ]
        ]);
        
        error_log('Fetching ARSO Last 30 Days URL: ' . $textUrl);
        $textContent = file_get_contents($textUrl, false, $context);
        
        if ($textContent === false) {
            throw new Exception('Failed to fetch last 30 days data from ARSO');
        }
        
        // Parse the text format
        $parsedData = parseLast30DaysText($textContent);
        
        if (!$parsedData) {
            throw new Exception('Failed to parse last 30 days text data');
        }
        
        // Return the response
        echo json_encode([
            'success' => true,
            'data' => $parsedData,
            'is_last_30_days' => true,
            'meta' => [
                'location_code' => $locationCode,
                'location_id' => $locationInfo['id'],
                'slug' => $slug,
                'source_url' => $textUrl,
                'data_type' => 'last_30_days'
            ]
        ]);
        
    } catch (Exception $e) {
        error_log('ARSO Last 30 Days Error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'error' => 'Failed to fetch last 30 days data: ' . $e->getMessage(),
            'debug_url' => $textUrl ?? 'URL not constructed'
        ]);
    }
    
} else {
    // Handle regular API request
    
    // Map variable IDs to ARSO IDs
    $arsoVariableIds = [];
    foreach ($variableIds as $varId) {
        if (isset($variableMapping[$varId])) {
            $arsoVariableIds[] = $variableMapping[$varId];
        }
    }
    
    if (empty($arsoVariableIds)) {
        http_response_code(400);
        echo json_encode(['error' => 'No valid variable IDs provided']);
        exit();
    }
    
    // Build ARSO API URL
    $varsParam = implode(',', $arsoVariableIds);
    $nocache = 'mg' . uniqid();
    $arsoUrl = "https://meteo.arso.gov.si/webmet/archive/data.xml?" . http_build_query([
        'lang' => 'si',
        'vars' => $varsParam,
        'group' => $locationInfo['group'],
        'type' => 'daily',
        'id' => $locationInfo['id'],
        'd1' => $dateFrom,
        'd2' => $dateTo,
        'nocache' => $nocache
    ]);
    
    // Fetch data from ARSO
    try {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => [
                    'User-Agent: Mozilla/5.0 (compatible; WeatherApp/1.0)',
                    'Accept: application/xml, text/xml'
                ],
                'timeout' => 30
            ]
        ]);
        
        error_log('Fetching ARSO URL: ' . $arsoUrl);
        $xmlContent = file_get_contents($arsoUrl, false, $context);
        
        if ($xmlContent === false) {
            throw new Exception('Failed to fetch data from ARSO');
        }
    
        // Parse the PUJS format
        $arsoData = parseArsoXml($xmlContent);
        
        if (!$arsoData) {
            throw new Exception('Failed to parse ARSO data - check error logs for details');
        }
    
        // Transform points to include human-readable dates
        if (isset($arsoData['points'])) {
            $arsoData['points'] = transformPointsWithDates($arsoData['points']);
        }
    
        // Return the response
        echo json_encode([
            'success' => true,
            'data' => $arsoData,
            'is_last_30_days' => false,
            'meta' => [
                'location_code' => $locationCode,
                'location_id' => $locationInfo['id'],
                'variable_ids' => $variableIds,
                'arso_variable_ids' => $arsoVariableIds,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'arso_url' => $arsoUrl
            ]
        ]);
    
    } catch (Exception $e) {
        error_log('ARSO API Error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'error' => 'Failed to fetch data: ' . $e->getMessage(),
            'debug_url' => $arsoUrl ?? 'URL not constructed'
        ]);
    }
}

/**
 * Parse ARSO XML format (PUJS)
 */
function parseArsoXml($xmlContent) {
    // Extract the JavaScript object from CDATA
    if (!preg_match('/AcademaPUJS\.set\(\s*(\{.*\})\s*\)/s', $xmlContent, $matches)) {
        error_log('PUJS pattern not found in XML content');
        return false;
    }
    
    $jsObject = trim($matches[1]);
    
    // Log the original JS object for debugging
    error_log('Original JS Object (first 500 chars): ' . substr($jsObject, 0, 500));
    
    // More robust JavaScript to PHP conversion
    try {
        // Handle JavaScript object keys (make them quoted)
        $jsObject = preg_replace('/([{,]\s*)([a-zA-Z_][a-zA-Z0-9_]*)\s*:/', '$1"$2":', $jsObject);
        
        // Handle unquoted string values (but not numbers or objects/arrays)
        $jsObject = preg_replace('/:\s*([a-zA-Z_][a-zA-Z0-9_\-\.]*)\s*([,}])/', ': "$1"$2', $jsObject);
        
        // Handle JavaScript single quotes
        $jsObject = str_replace("'", '"', $jsObject);
        
        // Clean up trailing commas before } or ]
        $jsObject = preg_replace('/,(\s*[}\]])/', '$1', $jsObject);
        
        // Try to decode JSON
        $data = json_decode($jsObject, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            return $data;
        }
        
        // If JSON parsing fails, try alternative approach
        error_log('JSON decode failed: ' . json_last_error_msg());
        error_log('Processed JS Object (first 500 chars): ' . substr($jsObject, 0, 500));
        
        // Alternative: try to manually parse the structure
        return parseArsoManually($matches[1]);
        
    } catch (Exception $e) {
        error_log('Exception in parseArsoXml: ' . $e->getMessage());
        return false;
    }
}

/**
 * Manual parser for ARSO PUJS format as fallback
 */
function parseArsoManually($jsContent) {
    try {
        // Initialize result array
        $result = [];
        
        // Extract baseurl
        if (preg_match('/baseurl\s*:\s*["\']([^"\']*)["\']/', $jsContent, $matches)) {
            $result['baseurl'] = $matches[1];
        }
        
        // Extract gen
        if (preg_match('/gen\s*:\s*["\']([^"\']*)["\']/', $jsContent, $matches)) {
            $result['gen'] = $matches[1];
        }
        
        // Extract datatype
        if (preg_match('/datatype\s*:\s*["\']([^"\']*)["\']/', $jsContent, $matches)) {
            $result['datatype'] = $matches[1];
        }
        
        // Extract o array
        if (preg_match('/o\s*:\s*\[\s*([^\]]*)\s*\]/', $jsContent, $matches)) {
            $oArray = explode(',', $matches[1]);
            $result['o'] = array_map(function($item) {
                return trim(str_replace(['"', "'"], '', $item));
            }, $oArray);
        }
        
        // Extract params
        $result['params'] = [];
        if (preg_match('/params\s*:\s*\{([^{}]*(?:\{[^{}]*\}[^{}]*)*)\}/', $jsContent, $matches)) {
            $paramsContent = $matches[1];
            // Parse each parameter
            preg_match_all('/([a-zA-Z0-9_]+)\s*:\s*\{\s*([^}]+)\s*\}/', $paramsContent, $paramMatches, PREG_SET_ORDER);
            
            foreach ($paramMatches as $paramMatch) {
                $paramKey = $paramMatch[1];
                $paramContent = $paramMatch[2];
                $param = [];
                
                // Extract pid, name, s, l, unit
                if (preg_match('/pid\s*:\s*["\']?([^"\',:]*)["\']?/', $paramContent, $pidMatch)) {
                    $param['pid'] = trim($pidMatch[1]);
                }
                if (preg_match('/name\s*:\s*["\']([^"\']*)["\']/', $paramContent, $nameMatch)) {
                    $param['name'] = $nameMatch[1];
                }
                if (preg_match('/s\s*:\s*["\']([^"\']*)["\']/', $paramContent, $sMatch)) {
                    $param['s'] = $sMatch[1];
                }
                if (preg_match('/l\s*:\s*["\']([^"\']*)["\']/', $paramContent, $lMatch)) {
                    $param['l'] = $lMatch[1];
                }
                if (preg_match('/unit\s*:\s*["\']([^"\']*)["\']/', $paramContent, $unitMatch)) {
                    $param['unit'] = $unitMatch[1];
                }
                
                $result['params'][$paramKey] = $param;
            }
        }
        
        // Extract points using a bracket-counting approach
        $result['points'] = [];
        if (preg_match('/points\s*:\s*(\{.*\})/', $jsContent, $matches)) {
            $pointsStr = $matches[1];
            $result['points'] = parseNestedPoints($pointsStr);
        }
        
        return $result;
        
    } catch (Exception $e) {
        error_log('Exception in parseArsoManually: ' . $e->getMessage());
        return false;
    }
}

/**
 * Parse nested points structure using bracket counting
 */
function parseNestedPoints($pointsStr) {
    $points = [];
    
    // Remove ONLY the outermost braces, not all braces
    $content = $pointsStr;
    if (substr($content, 0, 1) === '{' && substr($content, -1) === '}') {
        $content = substr($content, 1, -1);
    }
    
    // Use regex to find location patterns (4 digits like _2482) - more specific than before
    preg_match_all('/(_\d{4})\s*:\s*\{/', $content, $locationStarts, PREG_OFFSET_CAPTURE);
    
    for ($loc = 0; $loc < count($locationStarts[0]); $loc++) {
        $locationId = $locationStarts[1][$loc][0];  
        $matchPos = $locationStarts[0][$loc][1];
        $matchLen = strlen($locationStarts[0][$loc][0]);
        $startPos = $matchPos + $matchLen;
        
        // Find the closing brace for this location using bracket counting
        $braceCount = 1;
        $i = $startPos;
        $len = strlen($content);
        
        while ($i < $len && $braceCount > 0) {
            if ($content[$i] === '{') {
                $braceCount++;
            } elseif ($content[$i] === '}') {
                $braceCount--;
            }
            $i++;
        }
        
        if ($braceCount === 0) {
            $locationContent = substr($content, $startPos, $i - $startPos - 1);
            $points[$locationId] = parseLocationData($locationContent);
        }
    }
    
    return $points;
}

/**
 * Parse data points within a location
 */
function parseLocationData($locationData) {
    $dataPoints = [];
    
    // Use regex to find all timestamp patterns like _118681920:{...}
    preg_match_all('/(_\d+)\s*:\s*\{([^{}]*(?:\{[^{}]*\}[^{}]*)*)\}/', $locationData, $timeMatches, PREG_SET_ORDER);
    
    foreach ($timeMatches as $timeMatch) {
        $timestamp = $timeMatch[1];
        $paramData = $timeMatch[2];
        
        $params = parseParameterData($paramData);
        if (!empty($params)) {
            $dataPoints[$timestamp] = $params;
        }
    }
    
    return $dataPoints;
}

/**
 * Parse parameter data like p0:"29.7", p1:"12.1", p2:"0"
 */
function parseParameterData($paramData) {
    $params = [];
    
    if (trim($paramData) === '') {
        return $params;
    }
    
    // Extract parameter values like p0:"29.7", p1:"12.1", p2:"0"
    preg_match_all('/(p\d+)\s*:\s*"([^"]*)"/', $paramData, $matches, PREG_SET_ORDER);
    
    foreach ($matches as $match) {
        $paramKey = $match[1];
        $paramValue = $match[2];
        $params[$paramKey] = $paramValue;
    }
    
    return $params;
}

/**
 * Convert ARSO timestamp ID to date
 * Pattern: Each day = 1440 minutes (24 hours * 60 minutes)
 * Reference: _118689120 = 2025-09-01
 */
function timestampToDate($timestamp) {
    // Remove underscore and convert to integer
    $value = intval(substr($timestamp, 1));
    
    // Reference point: _118689120 corresponds to 2025-09-01
    $reference = 118689120; 
    $referenceDate = new DateTime('2025-09-01');
    
    // Calculate difference in minutes, then convert to days
    $minutesDiff = $value - $reference;
    $daysDiff = intval($minutesDiff / 1440); // 1440 minutes per day
    
    $resultDate = clone $referenceDate;
    if ($daysDiff >= 0) {
        $resultDate->add(new DateInterval("P{$daysDiff}D"));
    } else {
        $resultDate->sub(new DateInterval("P" . abs($daysDiff) . "D"));
    }
    
    return $resultDate->format('Y-m-d');
}

/**
 * Transform points data to include human-readable dates
 */
function transformPointsWithDates($points) {
    $result = [];
    
    foreach ($points as $locationId => $locationData) {
        $result[$locationId] = [
            'by_timestamp' => [],
            'by_date' => []
        ];
        
        foreach ($locationData as $timestamp => $data) {
            // Convert timestamp to date
            $date = timestampToDate($timestamp);
            
            // Create Unix timestamp (midnight in Slovenia timezone - Europe/Ljubljana)
            $dateTime = new DateTime($date . ' 00:00:00', new DateTimeZone('Europe/Ljubljana'));
            $unixTimestamp = $dateTime->getTimestamp();
            
            // Also provide a UTC midnight timestamp for consistency
            $dateTimeUtc = new DateTime($date . ' 00:00:00', new DateTimeZone('UTC'));
            $unixTimestampUtc = $dateTimeUtc->getTimestamp();
            
            // Store by timestamp (original format)
            $result[$locationId]['by_timestamp'][$timestamp] = array_merge($data, [
                'date' => $date,
                'timestamp_id' => $timestamp,
                'unix_timestamp' => $unixTimestamp,
                'unix_timestamp_utc' => $unixTimestampUtc
            ]);
            
            // Store by date (user-friendly format)
            $result[$locationId]['by_date'][$date] = array_merge($data, [
                'date' => $date,
                'timestamp_id' => $timestamp,
                'unix_timestamp' => $unixTimestamp,
                'unix_timestamp_utc' => $unixTimestampUtc
            ]);
        }
        
        // Sort by date for consistent ordering
        ksort($result[$locationId]['by_date']);
    }
    
    return $result;
}

/**
 * Parse Last 30 Days text format from ARSO
 */
function parseLast30DaysText($textContent) {
    $lines = explode("\n", $textContent);
    $data = [];
    $variables = [];
    $inDataSection = false;
    
    // Define variable mappings based on the text format
    $variableMapping = [
        'Datum' => ['id' => 'date', 'name' => 'Datum', 'unit' => ''],
        'Tpovp' => ['id' => 'temp_avg', 'name' => 'Povprečna temperatura', 'unit' => '°C'],
        'Tpovp_ref' => ['id' => 'temp_avg_ref', 'name' => 'Povprečna temperatura (ref)', 'unit' => '°C'],
        'Tmin' => ['id' => 'temp_min', 'name' => 'Najnižja temperatura', 'unit' => '°C'],
        'Tmin_ref' => ['id' => 'temp_min_ref', 'name' => 'Najnižja temperatura (ref)', 'unit' => '°C'],
        'Tmaks' => ['id' => 'temp_max', 'name' => 'Najvišja temperatura', 'unit' => '°C'],
        'Tmaks_ref' => ['id' => 'temp_max_ref', 'name' => 'Najvišja temperatura (ref)', 'unit' => '°C'],
        'Pad' => ['id' => 'precipitation', 'name' => 'Padavine', 'unit' => 'mm'],
        'Sneg' => ['id' => 'snow', 'name' => 'Snežna odeja', 'unit' => 'cm'],
        'Sref' => ['id' => 'snow_ref', 'name' => 'Snežna odeja (ref)', 'unit' => 'cm'],
        'SOTr' => ['id' => 'sunshine', 'name' => 'Sončno obsevanje', 'unit' => 'h'],
        'SORf' => ['id' => 'sunshine_ref', 'name' => 'Sončno obsevanje (ref)', 'unit' => 'h'],
        'Glob' => ['id' => 'global_radiation', 'name' => 'Globalni obsev', 'unit' => 'kWh/m²']
    ];
    
    $headerFound = false;
    $columnNames = [];
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        if (empty($line)) continue;
        
        // Look for header line with column names
        if (!$headerFound && preg_match('/^\s*Datum\s+Tpovp/', $line)) {
            $headerFound = true;
            // Extract column names
            $columnNames = preg_split('/\s+/', trim($line));
            $inDataSection = true;
            
            // Map variables
            foreach ($columnNames as $index => $colName) {
                if (isset($variableMapping[$colName])) {
                    $variables[$index] = $variableMapping[$colName];
                }
            }
            continue;
        }
        
        // Parse data lines
        if ($inDataSection && preg_match('/^\s*\d+\.\d+\.\d+/', $line)) {
            $values = preg_split('/\s+/', trim($line));
            
            if (count($values) >= count($columnNames)) {
                $row = [];
                
                foreach ($columnNames as $index => $colName) {
                    $value = isset($values[$index]) ? trim($values[$index]) : '';
                    
                    if ($colName === 'Datum') {
                        // Convert date from DD.MM.YYYY to YYYY-MM-DD
                        if (preg_match('/(\d+)\.(\d+)\.(\d+)/', $value, $matches)) {
                            $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                            $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
                            $year = $matches[3];
                            $value = "$year-$month-$day";
                        }
                        $row['date'] = $value;
                    } else {
                        // Convert numeric values
                        if ($value !== '' && is_numeric($value)) {
                            $row[$variables[$index]['id']] = (float)$value;
                        } else {
                            $row[$variables[$index]['id']] = null;
                        }
                    }
                }
                
                if (isset($row['date'])) {
                    $data[] = $row;
                }
            }
        }
        
        // Stop parsing when we hit the legend
        if (strpos($line, 'Legenda:') !== false) {
            break;
        }
    }
    
    // Sort data by date
    usort($data, function($a, $b) {
        return strcmp($a['date'], $b['date']);
    });
    
    return [
        'variables' => $variables,
        'data' => $data,
        'count' => count($data)
    ];
}

?>