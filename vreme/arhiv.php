<?php

// Define available locations for archive data (matching backend API)
$allowedLocations = [
    [
        'code' => 'LJ',
        'display_name' => 'Ljubljana',
        'arso_id' => '1895',
    ],
    [
        'code' => 'NG',
        'display_name' => 'Nova Gorica', 
        'arso_id' => '3001',
    ],
    [
        'code' => 'MB',
        'display_name' => 'Maribor',
        'arso_id' => '1902',
    ],
    [
        'code' => 'NM',
        'display_name' => 'Novo mesto',
        'arso_id' => '1893',
    ],
    [
        'code' => 'CE',
        'display_name' => 'Celje',
        'arso_id' => '2482',
    ],
    [
        'code' => 'KP',
        'display_name' => 'Koper',
        'arso_id' => '1896',
    ],
    [
        'code' => 'LI',
        'display_name' => 'Lisca',
        'arso_id' => '1900',
    ],
    [
        'code' => 'BR',
        'display_name' => 'Brnik',
        'arso_id' => '3049',
    ],
    [
        'code' => 'KR',
        'display_name' => 'Kredarica',
        'arso_id' => '1890',
    ],
    [
        'code' => 'SG',
        'display_name' => 'Slovenj Gradec',
        'arso_id' => '1897',
    ],
    [
        'code' => 'MS',
        'display_name' => 'Murska Sobota',
        'arso_id' => '1894',
    ],
    [
        'code' => 'KG',
        'display_name' => 'Kranj',
        'arso_id' => '1086',
    ]
];

// Define available variables for archive data (using ARSO IDs directly)
$availableVariables = [
    [
        'id' => '85',
        'name' => 'padavine_klima',
        'display_name' => 'Količina padavin',
        'description' => '24-urna količina padavin ob 7 h (mm)',
        'unit' => 'mm'
    ],
    [
        'id' => '38',
        'name' => 'tmax',
        'display_name' => 'Najvišja temperatura',
        'description' => 'Maksimalna temperatura zraka na 2 m (°C)',
        'unit' => '°C'
    ],
    [
        'id' => '36',
        'name' => 'tmin',
        'display_name' => 'Najnižja temperatura',
        'description' => 'Minimalna temperatura zraka na 2 m (°C)', 
        'unit' => '°C'
    ],
    [
        'id' => '35',
        'name' => 't2m_klima',
        'display_name' => 'Povprečna temperatura',
        'description' => 'Povprečna temperatura zraka na 2 m (°C)',
        'unit' => '°C'
    ],
    [
        'id' => '41',
        'name' => 'trajanje_so',
        'display_name' => 'Sončno obsevanje',
        'description' => 'Trajanje sončnega obsevanja (h)',
        'unit' => 'h'
    ],
    [
        'id' => '46',
        'name' => 'oblacnost',
        'display_name' => 'Oblačnost',
        'description' => 'Povprečna oblačnost (pokritost neba) (%)',
        'unit' => '%'
    ]
];

?>

<html>

<head>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Hind', sans-serif !important;
            font-size: 36px !important;
        }
        body {
            background: transparent;
            font-family: 'Hind', sans-serif !important;
        }
        .weekend-bg {
            background: rgba(255, 255, 255, 0.3) !important;
        }
        .chart-container {
            position: relative;
            height: 700px;
            width: 1700px;
            margin: 20px auto;
            background: #1a1a1a;
            border-radius: 8px;
            padding: 20px;
        }
        /* Override specific font sizes where needed */
        .text-xl { font-size: 36px !important; }
        .text-lg { font-size: 36px !important; }
        .text-2xl { font-size: 42px !important; }
        .text-sm { font-size: 36px !important; }
        .text-xs { font-size: 36px !important; }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Hind', sans-serif !important;
        }
        input, select, button, label {
            font-family: 'Hind', sans-serif !important;
            font-size: 36px !important;
        }
    </style>
    <!-- Alpine Plugins -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/persist@3.x.x/dist/cdn.min.js"></script>
    <!-- Alpine Core -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="" :class="{ 'weekend-bg': isWeekend() }">
    <div class="bg-gray-100 border-b border-gray-300 py-6 sticky top-0 z-50">
        <div class="container mx-auto flex items-baseline gap-8">
            <h1 class="text-xl font-bold text-gray-800">Vreme</h1> 
            <nav class="flex items-center gap-4">
                <a href="/vreme" class="text-lg hover:underline text-gray-500 font-bold">Trenutno</a>
                <a href="/vreme/fcast.php" class="text-lg hover:underline text-gray-500 font-bold">Napoved</a>
                <a href="/vreme" class="text-lg hover:underline text-gray-500 font-bold">Obeti</a>
                <a href="/vreme/arhiv.php" class="text-lg underline text-blue-700 font-bold">Arhiv</a>
            </nav>
        </div>
    </div>

    <div class="container mx-auto py-8" x-data="archiveData()">
        
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Arhivski podatki vremena</h2>
            
            <form @submit.prevent="fetchData()" class="space-y-6">
                <!-- Data Type Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Tip podatkov</label>
                    <div class="flex space-x-4">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="radio" value="custom" name="data_type" 
                                   class="text-blue-600 focus:ring-blue-500"
                                   x-model="dataType">
                            <span class="text-sm text-gray-700">Prilagojeno obdobje</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="radio" value="last_30_days" name="data_type" 
                                   class="text-blue-600 focus:ring-blue-500"
                                   x-model="dataType">
                            <span class="text-sm text-gray-700">Zadnjih 30 dni (z referenco)</span>
                        </label>
                    </div>
                    <p class="text-xs text-gray-500 mt-1" x-show="dataType === 'last_30_days'">
                        Zadnjih 30 dni z vsemi spremenljivkami in referenčnimi podatki za obdobje 1991-2020
                    </p>
                </div>
                <!-- Location Selection -->
                <div x-show="dataType === 'custom'">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Lokacije</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                        <?php foreach ($allowedLocations as $location): ?>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" value="<?= $location['code'] ?>" 
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                   x-model="selectedLocations">
                            <span class="text-sm text-gray-700"><?= $location['display_name'] ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Location Selection for Last 30 Days (single selection) -->
                <div x-show="dataType === 'last_30_days'">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Lokacija</label>
                    <select x-model="last30DaysLocation" 
                            class="block w-full md:w-1/2 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Izberite lokacijo...</option>
                        <option value="LJ">Ljubljana</option>
                        <option value="NG">Nova Gorica (Doblice)</option>
                        <option value="NM">Novo mesto</option>
                        <option value="CE">Celje</option>
                        <option value="MB">Maribor</option>
                        <option value="KP">Koper</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">
                        Podatki zadnjih 30 dni so na voljo le za izbrane lokacije
                    </p>
                </div>

                <!-- Variable Selection -->
                <div x-show="dataType === 'custom'">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Spremenljivke</label>
                    <div class="space-y-2">
                        <?php foreach ($availableVariables as $variable): ?>
                        <label class="flex items-start space-x-2 cursor-pointer">
                            <input type="checkbox" value="<?= $variable['id'] ?>"
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mt-1"
                                   x-model="selectedVariables">
                            <div class="flex-1">
                                <span class="text-sm font-medium text-gray-900"><?= $variable['display_name'] ?></span>
                                <p class="text-xs text-gray-500"><?= $variable['description'] ?> (<?= $variable['unit'] ?>)</p>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Info for Last 30 Days Variables -->
                <div x-show="dataType === 'last_30_days'" class="bg-blue-50 border border-blue-200 rounded-md p-4">
                    <h4 class="text-sm font-semibold text-blue-800 mb-2">Vključene spremenljivke (zadnjih 30 dni):</h4>
                    <div class="text-xs text-blue-700 grid grid-cols-1 md:grid-cols-2 gap-1">
                        <div>• Povprečna temperatura (+ referenca)</div>
                        <div>• Najnižja temperatura (+ referenca)</div>
                        <div>• Najvišja temperatura (+ referenca)</div>
                        <div>• Količina padavin</div>
                        <div>• Snežna odeja (+ referenca)</div>
                        <div>• Sončno obsevanje (+ referenca)</div>
                        <div>• Globalni obsev</div>
                    </div>
                </div>

                <!-- Date Range -->
                <div x-show="dataType === 'custom'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Datum od</label>
                        <input type="date" required
                               x-model="dateFrom"
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Datum do</label>
                        <input type="date" required
                               x-model="dateTo"
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div x-show="errorMessage" class="bg-red-50 border border-red-200 rounded-md p-4">
                    <p class="text-red-600 text-sm" x-text="errorMessage"></p>
                </div>

                <div class="flex items-center space-x-4">
                    <button type="submit" 
                            :disabled="loading"
                            class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white font-medium py-2 px-4 rounded-md transition-colors flex items-center">
                        <span x-show="!loading">Prikaži podatke</span>
                        <span x-show="loading" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Nalagam...
                        </span>
                    </button>
                    <button type="button" @click="showAdvanced = !showAdvanced"
                            class="text-blue-600 hover:text-blue-800 font-medium py-2 px-4 rounded-md border border-blue-600 hover:border-blue-800 transition-colors">
                        Napredne nastavitve
                    </button>
                </div>

                <!-- Advanced Settings -->
                <div x-show="showAdvanced" x-transition class="border-t pt-6 space-y-4">
                    <h3 class="text-lg font-medium text-gray-800">Napredne nastavitve</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tip grafa</label>
                            <select x-model="chartType" class="block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="line">Črtni graf</option>
                                <option value="bar">Stolpčni graf</option>
                                <option value="area">Ploščinski graf</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Agregacija</label>
                            <select x-model="aggregation" class="block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="daily">Dnevno</option>
                                <option value="weekly">Tedensko</option>
                                <option value="monthly">Mesečno</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <input type="checkbox" x-model="showAverage" id="show_average" 
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <label for="show_average" class="text-sm text-gray-700">Prikaži povprečje</label>
                    </div>
                </div>
            </form>
        </div>

        <!-- Charts Display -->
        <div x-show="charts.length > 0" class="space-y-8">
            <h2 class="text-2xl font-bold text-gray-800">Rezultati</h2>
            
            <template x-for="(chart, index) in charts" :key="chart.id">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4" x-text="chart.title"></h3>
                    <p class="text-sm text-gray-600 mb-4" x-text="chart.subtitle"></p>
                    
                    <div class="chart-container">
                        <div :id="'chart' + chart.id"></div>
                    </div>
                    
                    <!-- Export Button -->
                    <div class="mt-4 text-right">
                        <button @click="exportChart(chart.id)" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                            📊 Izvozi SVG
                        </button>
                    </div>
                    
                    <!-- Chart Statistics -->
                    <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                        <div class="bg-gray-50 rounded-md p-3">
                            <p class="text-xs text-gray-500">Minimum</p>
                            <p class="text-lg font-semibold text-gray-800" x-text="chart.stats.min.toFixed(1) + ' ' + chart.unit"></p>
                        </div>
                        <div class="bg-gray-50 rounded-md p-3">
                            <p class="text-xs text-gray-500">Maksimum</p>
                            <p class="text-lg font-semibold text-gray-800" x-text="chart.stats.max.toFixed(1) + ' ' + chart.unit"></p>
                        </div>
                        <div class="bg-gray-50 rounded-md p-3">
                            <p class="text-xs text-gray-500">Povprečje</p>
                            <p class="text-lg font-semibold text-gray-800" x-text="chart.stats.avg.toFixed(1) + ' ' + chart.unit"></p>
                        </div>
                        <div class="bg-gray-50 rounded-md p-3">
                            <p class="text-xs text-gray-500" x-text="chart.unit === 'mm' ? 'Skupaj' : 'Razpon'"></p>
                            <p class="text-lg font-semibold text-gray-800" x-text="(chart.unit === 'mm' ? chart.stats.sum : chart.stats.range).toFixed(1) + ' ' + chart.unit"></p>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Information Section -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mt-8">
            <h3 class="text-lg font-semibold text-blue-800 mb-3">Informacije o podatkih</h3>
            <div class="text-sm text-blue-700 space-y-2">
                <p>• <strong>Prilagojeno obdobje:</strong> Arhivski podatki so na voljo od leta 1991 naprej</p>
                <p>• <strong>Zadnjih 30 dni:</strong> Vključuje referenčne podatke za obdobje 1991-2020</p>
                <p>• Podatki se osvežujejo dnevno ob 7:00</p>
                <p>• Maksimalen časovni razpon za prilagojeno obdobje je 5 let</p>
                <p>• Podatki so pridobljeni iz ARSO meteoroloških postaj</p>
                <p>• Za večje količine podatkov uporabite funkcijo izvoza</p>
            </div>
        </div>
    </div>

    <script>
    // Location and variable data for JavaScript
    const locations = <?= json_encode($allowedLocations) ?>;
    const variables = <?= json_encode($availableVariables) ?>;

    // Alpine.js component
    function archiveData() {
        return {
            dataType: 'custom', // 'custom' or 'last_30_days'
            selectedLocations: [],
            last30DaysLocation: '',
            selectedVariables: [],
            dateFrom: '',
            dateTo: '',
            showAdvanced: false,
            chartType: 'line',
            aggregation: 'daily',
            showAverage: false,
            loading: false,
            errorMessage: '',
            charts: [],
            chartInstances: {},

            isWeekend() {
                const today = new Date();
                const dayOfWeek = today.getDay();
                return dayOfWeek === 0 || dayOfWeek === 6; // Sunday = 0, Saturday = 6
            },

            init() {
                // Set default dates to last 30 days
                const thirtyDaysAgo = new Date();
                thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
                this.dateFrom = thirtyDaysAgo.toISOString().split('T')[0];
                
                const today = new Date();
                this.dateTo = today.toISOString().split('T')[0];
            },

            async fetchData() {
                this.errorMessage = '';

                // Validation for Last 30 Days
                if (this.dataType === 'last_30_days') {
                    if (!this.last30DaysLocation) {
                        this.errorMessage = 'Prosim izberite lokacijo.';
                        return;
                    }
                } else {
                    // Validation for custom period
                    if (this.selectedLocations.length === 0) {
                        this.errorMessage = 'Prosim izberite vsaj eno lokacijo.';
                        return;
                    }

                    if (this.selectedVariables.length === 0) {
                        this.errorMessage = 'Prosim izberite vsaj eno spremenljivko.';
                        return;
                    }

                    if (!this.dateFrom || !this.dateTo) {
                        this.errorMessage = 'Prosim izberite časovni razpon.';
                        return;
                    }

                    if (new Date(this.dateFrom) > new Date(this.dateTo)) {
                        this.errorMessage = 'Datum "od" mora biti pred datumom "do".';
                        return;
                    }
                }

                this.loading = true;
                this.clearCharts();

                try {
                    if (this.dataType === 'last_30_days') {
                        // Handle Last 30 Days request
                        const result = await this.fetchLast30DaysData(this.last30DaysLocation);
                        if (result.success && result.data) {
                            this.processLast30DaysData(result.data, result.meta);
                        }
                    } else {
                        // Handle custom period request (existing logic)
                        const promises = [];
                        
                        for (const locationCode of this.selectedLocations) {
                            const location = locations.find(l => l.code === locationCode);
                            if (location) {
                                promises.push(
                                    this.fetchLocationData(location, this.selectedVariables)
                                );
                            }
                        }

                        const results = await Promise.all(promises);
                        
                        let chartId = 0;
                        for (const result of results) {
                            if (result.success && result.data) {
                                this.processLocationData(result.data, result.location, chartId);
                                chartId += this.selectedVariables.length;
                            }
                        }
                    }

                } catch (error) {
                    console.error('Error fetching data:', error);
                    this.errorMessage = 'Napaka pri pridobivanju podatkov. Prosim poskusite znova.';
                } finally {
                    this.loading = false;
                }
            },

            async fetchLocationData(location, variableIds) {
                const response = await fetch('/vreme/api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        location_code: location.code,
                        variable_ids: variableIds,
                        date_from: this.dateFrom,
                        date_to: this.dateTo
                    })
                });

                const data = await response.json();
                
                return {
                    success: data.success,
                    data: data.data,
                    location: location,
                    error: data.error
                };
            },

            async fetchLast30DaysData(locationCode) {
                const response = await fetch('/vreme/api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        location_code: locationCode,
                        last_30_days: true
                    })
                });

                const data = await response.json();
                
                return {
                    success: data.success,
                    data: data.data,
                    meta: data.meta,
                    error: data.error
                };
            },

            processLast30DaysData(parsedData, meta) {
                if (!parsedData.data || parsedData.data.length === 0) {
                    console.warn('No Last 30 Days data available');
                    return;
                }

                const locationName = this.getLocationName(meta.location_code);
                
                // Create charts for each variable with reference data
                this.createTemperatureChartsWithRef(parsedData.data, locationName);
                this.createPrecipitationChart(parsedData.data, locationName);
                this.createSunshineChartWithRef(parsedData.data, locationName);
                this.createSnowChartWithRef(parsedData.data, locationName);
                this.createGlobalRadiationChart(parsedData.data, locationName);
            },

            getLocationName(code) {
                const locationMap = {
                    'LJ': 'Ljubljana',
                    'NG': 'Nova Gorica (Doblice)', 
                    'NM': 'Novo mesto',
                    'CE': 'Celje',
                    'MB': 'Maribor',
                    'KP': 'Koper'
                };
                return locationMap[code] || code;
            },

            createTemperatureChartsWithRef(data, locationName) {
                // Combined temperature chart with reference data
                const chartId = this.charts.length;
                const datasets = [];
                
                // Average temperature
                datasets.push({
                    label: 'Povprečna temperatura',
                    data: data.map(d => d.temp_avg).filter(v => v !== null),
                    borderColor: 'rgba(59, 130, 246, 1)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: false
                });
                
                datasets.push({
                    label: 'Povprečna temperatura (ref)',
                    data: data.map(d => d.temp_avg_ref).filter(v => v !== null),
                    borderColor: 'rgba(59, 130, 246, 0.5)',
                    backgroundColor: 'rgba(59, 130, 246, 0.05)',
                    borderWidth: 1,
                    borderDash: [5, 5],
                    fill: false
                });
                
                // Min temperature
                datasets.push({
                    label: 'Najnižja temperatura',
                    data: data.map(d => d.temp_min).filter(v => v !== null),
                    borderColor: 'rgba(16, 185, 129, 1)',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 2,
                    fill: false
                });
                
                datasets.push({
                    label: 'Najnižja temperatura (ref)',
                    data: data.map(d => d.temp_min_ref).filter(v => v !== null),
                    borderColor: 'rgba(16, 185, 129, 0.5)',
                    backgroundColor: 'rgba(16, 185, 129, 0.05)',
                    borderWidth: 1,
                    borderDash: [5, 5],
                    fill: false
                });
                
                // Max temperature
                datasets.push({
                    label: 'Najvišja temperatura',
                    data: data.map(d => d.temp_max).filter(v => v !== null),
                    borderColor: 'rgba(245, 101, 101, 1)',
                    backgroundColor: 'rgba(245, 101, 101, 0.1)',
                    borderWidth: 2,
                    fill: false
                });
                
                datasets.push({
                    label: 'Najvišja temperatura (ref)',
                    data: data.map(d => d.temp_max_ref).filter(v => v !== null),
                    borderColor: 'rgba(245, 101, 101, 0.5)',
                    backgroundColor: 'rgba(245, 101, 101, 0.05)',
                    borderWidth: 1,
                    borderDash: [5, 5],
                    fill: false
                });
                
                // Calculate temperature statistics
                const allTemps = [...data.map(d => d.temp_avg), ...data.map(d => d.temp_min), ...data.map(d => d.temp_max)].filter(v => v !== null);
                
                const chart = {
                    id: chartId,
                    title: `Temperature - ${locationName}`,
                    subtitle: `Zadnjih 30 dni s primerjavo z referenčnim obdobjem (1991-2020)`,
                    unit: '°C',
                    data: data,
                    datasets: datasets,
                    stats: {
                        min: Math.min(...allTemps),
                        max: Math.max(...allTemps),
                        avg: allTemps.reduce((a, b) => a + b, 0) / allTemps.length,
                        sum: allTemps.reduce((a, b) => a + b, 0),
                        range: Math.max(...allTemps) - Math.min(...allTemps)
                    }
                };
                
                this.charts.push(chart);
                this.$nextTick(() => {
                    this.renderLast30DaysChart(chart);
                });
            },

            createPrecipitationChart(data, locationName) {
                const chartId = this.charts.length;
                const precipData = data.map(d => d.precipitation).filter(v => v !== null);
                
                const chart = {
                    id: chartId,
                    title: `Padavine - ${locationName}`,
                    subtitle: `Zadnjih 30 dni - 24-urna količina padavin`,
                    unit: 'mm',
                    data: data,
                    datasets: [{
                        label: 'Padavine (mm)',
                        data: data.map(d => d.precipitation),
                        borderColor: 'rgba(59, 130, 246, 1)',
                        backgroundColor: 'rgba(59, 130, 246, 0.7)',
                        borderWidth: 1
                    }],
                    stats: {
                        min: Math.min(...precipData),
                        max: Math.max(...precipData),
                        avg: precipData.reduce((a, b) => a + b, 0) / precipData.length,
                        sum: precipData.reduce((a, b) => a + b, 0),
                        range: Math.max(...precipData) - Math.min(...precipData)
                    }
                };
                
                this.charts.push(chart);
                this.$nextTick(() => {
                    this.renderLast30DaysChart(chart, 'bar');
                });
            },

            createSunshineChartWithRef(data, locationName) {
                const chartId = this.charts.length;
                const sunshineData = data.map(d => d.sunshine).filter(v => v !== null);
                const sunshineRefData = data.map(d => d.sunshine_ref).filter(v => v !== null);
                const allSunshineData = [...sunshineData, ...sunshineRefData];
                
                const chart = {
                    id: chartId,
                    title: `Sončno obsevanje - ${locationName}`,
                    subtitle: `Zadnjih 30 dni s primerjavo z referenčnim obdobjem (1991-2020)`,
                    unit: 'h',
                    data: data,
                    datasets: [
                        {
                            label: 'Sončno obsevanje',
                            data: data.map(d => d.sunshine),
                            borderColor: 'rgba(251, 191, 36, 1)',
                            backgroundColor: 'rgba(251, 191, 36, 0.7)',
                            borderWidth: 1
                        },
                        {
                            label: 'Sončno obsevanje (ref)',
                            data: data.map(d => d.sunshine_ref),
                            borderColor: 'rgba(251, 191, 36, 0.5)',
                            backgroundColor: 'rgba(251, 191, 36, 0.3)',
                            borderWidth: 1
                        }
                    ],
                    stats: {
                        min: Math.min(...allSunshineData),
                        max: Math.max(...allSunshineData),
                        avg: allSunshineData.reduce((a, b) => a + b, 0) / allSunshineData.length,
                        sum: allSunshineData.reduce((a, b) => a + b, 0),
                        range: Math.max(...allSunshineData) - Math.min(...allSunshineData)
                    }
                };
                
                this.charts.push(chart);
                this.$nextTick(() => {
                    this.renderLast30DaysChart(chart, 'bar');
                });
            },

            createSnowChartWithRef(data, locationName) {
                // Only create snow chart if there's snow data
                const hasSnowData = data.some(d => d.snow !== null && d.snow > 0);
                if (!hasSnowData) return;
                
                const chartId = this.charts.length;
                const snowData = data.map(d => d.snow).filter(v => v !== null);
                const snowRefData = data.map(d => d.snow_ref).filter(v => v !== null);
                const allSnowData = [...snowData, ...snowRefData];
                
                const chart = {
                    id: chartId,
                    title: `Snežena odeja - ${locationName}`,
                    subtitle: `Zadnjih 30 dni s primerjavo z referenčnim obdobjem (1991-2020)`,
                    unit: 'cm',
                    data: data,
                    datasets: [
                        {
                            label: 'Snežena odeja',
                            data: data.map(d => d.snow),
                            borderColor: 'rgba(219, 234, 254, 1)',
                            backgroundColor: 'rgba(219, 234, 254, 0.7)',
                            borderWidth: 1
                        },
                        {
                            label: 'Snežena odeja (ref)',
                            data: data.map(d => d.snow_ref),
                            borderColor: 'rgba(219, 234, 254, 0.5)',
                            backgroundColor: 'rgba(219, 234, 254, 0.3)',
                            borderWidth: 1
                        }
                    ],
                    stats: {
                        min: Math.min(...allSnowData),
                        max: Math.max(...allSnowData),
                        avg: allSnowData.reduce((a, b) => a + b, 0) / allSnowData.length,
                        sum: allSnowData.reduce((a, b) => a + b, 0),
                        range: Math.max(...allSnowData) - Math.min(...allSnowData)
                    }
                };
                
                this.charts.push(chart);
                this.$nextTick(() => {
                    this.renderLast30DaysChart(chart, 'bar');
                });
            },

            createGlobalRadiationChart(data, locationName) {
                // Only create radiation chart if there's data
                const hasRadiationData = data.some(d => d.global_radiation !== null && d.global_radiation > 0);
                if (!hasRadiationData) return;
                
                const chartId = this.charts.length;
                const radiationData = data.map(d => d.global_radiation).filter(v => v !== null);
                
                const chart = {
                    id: chartId,
                    title: `Globalni obsev - ${locationName}`,
                    subtitle: `Zadnjih 30 dni - dnevni globalni obsev`,
                    unit: 'kWh/m²',
                    data: data,
                    datasets: [{
                        label: 'Globalni obsev (kWh/m²)',
                        data: data.map(d => d.global_radiation),
                        borderColor: 'rgba(251, 146, 60, 1)',
                        backgroundColor: 'rgba(251, 146, 60, 0.7)',
                        borderWidth: 1
                    }],
                    stats: {
                        min: Math.min(...radiationData),
                        max: Math.max(...radiationData),
                        avg: radiationData.reduce((a, b) => a + b, 0) / radiationData.length,
                        sum: radiationData.reduce((a, b) => a + b, 0),
                        range: Math.max(...radiationData) - Math.min(...radiationData)
                    }
                };
                
                this.charts.push(chart);
                this.$nextTick(() => {
                    this.renderLast30DaysChart(chart, 'bar');
                });
            },

            renderLast30DaysChart(chart, chartType = 'line') {
                // Use setTimeout to ensure DOM element is available
                setTimeout(() => {
                    const element = document.getElementById(`chart${chart.id}`);
                    if (!element) {
                        console.error(`Chart element with ID chart${chart.id} not found`);
                        return;
                    }

                    // Validate chart data
                    if (!chart || !chart.data || !Array.isArray(chart.data) || chart.data.length === 0) {
                        console.error('Invalid chart data:', chart);
                        return;
                    }

                    if (!chart.datasets || !Array.isArray(chart.datasets) || chart.datasets.length === 0) {
                        console.error('Invalid chart datasets:', chart.datasets);
                        return;
                    }

                    // Destroy existing chart if it exists
                    if (this.chartInstances[chart.id]) {
                        try {
                            this.chartInstances[chart.id].destroy();
                        } catch (e) {
                            console.warn('Error destroying existing chart:', e);
                        }
                    }

                    const series = chart.datasets.map(dataset => ({
                        name: dataset.label || 'Data',
                        data: chart.data.map((item, index) => {
                            const dataValue = dataset.data && dataset.data[index] !== undefined ? dataset.data[index] : null;
                            return {
                                x: new Date(item.date).getTime(),
                                y: dataValue
                            };
                        }).filter(point => point.y !== null)
                    }));

                    // Extract categories for x-axis
                    const categories = chart.data.map(d => d.date);

                    const options = {
                        series: series,
                        chart: {
                            type: chartType === 'bar' ? 'column' : 'line',
                            height: 700,
                            width: 1700,
                            background: '#1a1a1a',
                            foreColor: '#ffffff',
                            fontFamily: 'Hind, sans-serif',
                            toolbar: {
                                show: true,
                                tools: {
                                    download: true,
                                    selection: false,
                                    zoom: false,
                                    zoomin: false,
                                    zoomout: false,
                                    pan: false,
                                    reset: false
                                },
                                export: {
                                    svg: {
                                        filename: `${chart.title}_chart`
                                    },
                                    png: {
                                        filename: `${chart.title}_chart`
                                    }
                                }
                            }
                        },
                        colors: ['#00BFFF', '#FF6B35', '#32CD32', '#FFD700', '#FF69B4', '#9370DB'],
                        title: {
                            text: chart.title,
                            align: 'center',
                            style: {
                                fontSize: '36px',
                                fontWeight: 'bold',
                                color: '#ffffff',
                                fontFamily: 'Hind, sans-serif'
                            }
                        },
                        xaxis: {
                            type: 'datetime',
                            title: {
                                text: 'Datum',
                                style: {
                                    fontSize: '36px',
                                    color: '#ffffff',
                                    fontFamily: 'Hind, sans-serif'
                                }
                            },
                            labels: {
                                style: {
                                    fontSize: '36px',
                                    colors: '#ffffff',
                                    fontFamily: 'Hind, sans-serif'
                                },
                                datetimeUTC: false,
                                format: 'dd/MM'
                            },
                            axisBorder: {
                                color: '#ffffff'
                            },
                            axisTicks: {
                                color: '#ffffff'
                            }
                        },
                        yaxis: {
                            title: {
                                text: chart.unit,
                                style: {
                                    fontSize: '36px',
                                    color: '#ffffff',
                                    fontFamily: 'Hind, sans-serif'
                                }
                            },
                            labels: {
                                style: {
                                    fontSize: '36px',
                                    colors: '#ffffff',
                                    fontFamily: 'Hind, sans-serif'
                                }
                            },
                            min: chartType === 'bar' ? 0 : undefined,
                            axisBorder: {
                                color: '#ffffff'
                            }
                        },
                        grid: {
                            borderColor: '#444444',
                            strokeDashArray: 3
                        },
                        legend: {
                            show: chart.datasets.length > 1,
                            position: 'top',
                            labels: {
                                colors: '#ffffff',
                                useSeriesColors: false,
                                fontFamily: 'Hind, sans-serif'
                            },
                            fontSize: '36px'
                        },
                        dataLabels: {
                            enabled: chartType === 'bar' || chartType === 'column',
                            style: {
                                fontSize: '24px',
                                fontFamily: 'Hind, sans-serif',
                                colors: ['#ffffff']
                            },
                            formatter: function(val) {
                                return val + ' ' + chart.unit;
                            }
                        },
                        stroke: {
                            width: chartType === 'line' ? 4 : 1
                        },
                        fill: {
                            opacity: chartType === 'area' ? 0.4 : 1
                        },
                        plotOptions: {
                            bar: {
                                borderRadius: 4,
                                columnWidth: '70%'
                            }
                        },
                        responsive: [{
                            breakpoint: 768,
                            options: {
                                chart: {
                                    height: 300
                                }
                            }
                        }]
                    };

                    try {
                        const chartInstance = new ApexCharts(element, options);
                        chartInstance.render();
                        this.chartInstances[chart.id] = chartInstance;
                    } catch (error) {
                        console.error('Error creating ApexChart:', error);
                        element.innerHTML = '<div class="text-center text-red-500 p-4">Error loading chart</div>';
                    }
                }, 100); // 100ms delay to ensure DOM is ready
            },

            processLocationData(arsoData, location, startChartId) {
                if (!arsoData.points || !arsoData.points['_' + location.arso_id]) {
                    console.warn('No data found for location:', location.display_name);
                    return;
                }

                // Use the new by_timestamp structure
                const locationPoints = arsoData.points['_' + location.arso_id].by_timestamp || arsoData.points['_' + location.arso_id];
                
                // Check if we have temperature variables to combine
                const tempVariableIds = ['35', '36', '38']; // max, min, avg temperature
                const selectedTempVars = this.selectedVariables.filter(id => tempVariableIds.includes(id));
                
                if (selectedTempVars.length > 1) {
                    // Create combined temperature chart
                    this.createCombinedTemperatureChart(locationPoints, arsoData.params, location, selectedTempVars, startChartId);
                    startChartId++;
                    
                    // Process remaining non-temperature variables
                    let chartId = startChartId;
                    for (let i = 0; i < this.selectedVariables.length; i++) {
                        const variableId = this.selectedVariables[i];
                        if (!tempVariableIds.includes(variableId)) {
                            const variable = variables.find(v => v.id === variableId);
                            const paramKey = 'p' + i; // ARSO uses p0, p1, p2, etc.
                            
                            if (variable && arsoData.params[paramKey]) {
                                const chartData = this.convertArsoDataForVariable(locationPoints, paramKey);
                                
                                if (chartData.length > 0) {
                                    this.createChart(chartData, location, variable, chartId++);
                                }
                            }
                        }
                    }
                } else {
                    // Process each variable separately (original logic)
                    let chartId = startChartId;
                    for (let i = 0; i < this.selectedVariables.length; i++) {
                        const variableId = this.selectedVariables[i];
                        const variable = variables.find(v => v.id === variableId);
                        const paramKey = 'p' + i; // ARSO uses p0, p1, p2, etc.
                        
                        if (variable && arsoData.params[paramKey]) {
                            const chartData = this.convertArsoDataForVariable(locationPoints, paramKey);
                            
                            if (chartData.length > 0) {
                                this.createChart(chartData, location, variable, chartId++);
                            }
                        }
                    }
                }
            },

            createCombinedTemperatureChart(locationPoints, params, location, tempVariableIds, chartId) {
                // Map variable IDs to their parameter keys and details
                const tempMappings = [];
                for (let i = 0; i < this.selectedVariables.length; i++) {
                    const varId = this.selectedVariables[i];
                    if (tempVariableIds.includes(varId)) {
                        const variable = variables.find(v => v.id === varId);
                        if (variable && params['p' + i]) {
                            tempMappings.push({
                                paramKey: 'p' + i,
                                variable: variable,
                                color: this.getChartColor(tempMappings.length)
                            });
                        }
                    }
                }

                if (tempMappings.length === 0) return;

                // Convert data for each temperature variable
                const datasets = [];
                const allData = [];
                
                for (const mapping of tempMappings) {
                    const data = this.convertArsoDataForVariable(locationPoints, mapping.paramKey);
                    if (data.length > 0) {
                        datasets.push({
                            label: mapping.variable.display_name,
                            data: data.map(d => d.value),
                            borderColor: mapping.color,
                            backgroundColor: mapping.color.replace('1)', '0.1)'),
                            borderWidth: 2,
                            fill: false,
                            tension: 0.1
                        });
                        allData.push(...data.map(d => d.value));
                    }
                }

                if (datasets.length === 0) return;

                // Use the first dataset for chart structure (dates should be same for all)
                const firstData = this.convertArsoDataForVariable(locationPoints, tempMappings[0].paramKey);
                
                // Calculate combined statistics
                const min = Math.min(...allData);
                const max = Math.max(...allData);
                const avg = allData.reduce((a, b) => a + b, 0) / allData.length;
                const sum = allData.reduce((a, b) => a + b, 0);

                const chart = {
                    id: chartId,
                    title: `Temperatura - ${location.display_name}`,
                    subtitle: `Primerjava temperatur | Obdobje: ${this.formatDate(this.dateFrom)} - ${this.formatDate(this.dateTo)}`,
                    unit: '°C',
                    data: firstData, // For chart structure
                    datasets: datasets, // Multiple datasets
                    stats: {
                        min: min,
                        max: max,
                        avg: avg,
                        sum: sum,
                        range: max - min
                    }
                };

                this.charts.push(chart);

                // Create chart after DOM update
                this.$nextTick(() => {
                    this.renderCombinedChart(chart);
                });
            },

            convertArsoDataForVariable(locationPoints, paramKey) {
                const data = [];

                for (const [timestampKey, values] of Object.entries(locationPoints)) {
                    if (values[paramKey] !== undefined && values[paramKey] !== null && values[paramKey] !== '') {
                        // Use the unix_timestamp provided by the API for better accuracy
                        const unixTimestamp = values.unix_timestamp;
                        const date = values.date; // Already formatted date from API
                        const value = parseFloat(values[paramKey]);

                        if (!isNaN(value) && unixTimestamp) {
                            data.push({
                                date: date,
                                timestamp: unixTimestamp,
                                value: value
                            });
                        }
                    }
                }

                // Sort by timestamp
                data.sort((a, b) => a.timestamp - b.timestamp);

                return data;
            },

            createChart(chartData, location, variable, chartId) {
                if (chartData.length === 0) {
                    return;
                }

                // Calculate statistics
                const values = chartData.map(d => d.value);
                const min = Math.min(...values);
                const max = Math.max(...values);
                const avg = values.reduce((a, b) => a + b, 0) / values.length;
                const sum = values.reduce((a, b) => a + b, 0);

                const chart = {
                    id: chartId,
                    title: `${variable.display_name} - ${location.display_name}`,
                    subtitle: `${variable.description} | Obdobje: ${this.formatDate(this.dateFrom)} - ${this.formatDate(this.dateTo)}`,
                    unit: variable.unit,
                    data: chartData,
                    stats: {
                        min: min,
                        max: max,
                        avg: avg,
                        sum: sum,
                        range: max - min
                    }
                };

                this.charts.push(chart);

                // Create chart after DOM update
                this.$nextTick(() => {
                    this.renderChart(chart);
                });
            },

            renderChart(chart) {
                // Use setTimeout to ensure DOM element is available
                setTimeout(() => {
                    const element = document.getElementById(`chart${chart.id}`);
                    if (!element) {
                        console.error(`Chart element with ID chart${chart.id} not found`);
                        return;
                    }

                    // Validate chart data
                    if (!chart || !chart.data || !Array.isArray(chart.data) || chart.data.length === 0) {
                        console.error('Invalid chart data:', chart);
                        return;
                    }

                    // Destroy existing chart if it exists
                    if (this.chartInstances[chart.id]) {
                        try {
                            this.chartInstances[chart.id].destroy();
                        } catch (e) {
                            console.warn('Error destroying existing chart:', e);
                        }
                    }

                // Determine chart type - use bar for rainfall and sunshine hours, otherwise use selected type
                const isRainfall = chart.unit === 'mm';
                const isSunshine = chart.unit === 'h';
                const useBarChart = isRainfall || isSunshine;
                const apexChartType = useBarChart ? 'bar' : (this.chartType === 'area' ? 'area' : this.chartType === 'line' ? 'line' : 'line');

                const validData = chart.data.filter(d => d.value !== null && d.value !== undefined && !isNaN(d.value));
                
                if (validData.length === 0) {
                    console.error('No valid data points found for chart:', chart);
                    return;
                }

                // Extract data values and categories separately
                const dataValues = validData.map(d => d.value);
                const categories = validData.map(d => d.date);

                const options = {
                    series: [{
                        name: `${chart.title}`,
                        data: dataValues
                    }],
                    chart: {
                        type: apexChartType,
                        height: 700,
                        width: 1700,
                        background: '#1a1a1a',
                        foreColor: '#ffffff',
                        fontFamily: 'Hind, sans-serif',
                        toolbar: {
                            show: true,
                            tools: {
                                download: true,
                                selection: false,
                                zoom: false,
                                zoomin: false,
                                zoomout: false,
                                pan: false,
                                reset: false
                            },
                            export: {
                                svg: {
                                    filename: `${chart.title}_chart`
                                },
                                png: {
                                    filename: `${chart.title}_chart`
                                }
                            }
                        }
                    },
                    colors: ['#00BFFF'],
                    title: {
                        text: `${chart.title} (${chart.unit})`,
                        align: 'center',
                        style: {
                            fontSize: '36px',
                            fontWeight: 'bold',
                            color: '#ffffff',
                            fontFamily: 'Hind, sans-serif'
                        }
                    },
                    xaxis: {
                        type: 'category',
                        categories: categories,
                        title: {
                            text: 'Datum',
                            style: {
                                fontSize: '36px',
                                color: '#ffffff',
                                fontFamily: 'Hind, sans-serif'
                            }
                        },
                        labels: {
                            style: {
                                fontSize: '36px',
                                colors: '#ffffff',
                                fontFamily: 'Hind, sans-serif'
                            },
                            rotate: chart.data.length > 10 ? -45 : 0,
                            formatter: function(value, index) {
                                // Show only dates ending with 5, 10, 15, 20, 25, 30
                                const day = new Date(value).getDate();
                                if (day % 5 === 0) {
                                    return day;
                                }
                                return '';
                            }
                        },
                        axisBorder: {
                            color: '#ffffff'
                        },
                        axisTicks: {
                            color: '#ffffff'
                        }
                    },
                    yaxis: {
                        title: {
                            text: chart.unit,
                            style: {
                                fontSize: '36px',
                                color: '#ffffff',
                                fontFamily: 'Hind, sans-serif'
                            }
                        },
                        labels: {
                            style: {
                                fontSize: '36px',
                                colors: '#ffffff',
                                fontFamily: 'Hind, sans-serif'
                            },
                            formatter: function(value) {
                                // For rainfall (mm), show ticks every 20mm
                                if (chart.unit === 'mm') {
                                    return Math.round(value / 20) * 20 === value ? value + ' mm' : '';
                                }
                                return value + ' ' + chart.unit;
                            }
                        },
                        min: useBarChart ? 0 : undefined,
                        tickAmount: chart.unit === 'mm' ? undefined : 5,
                        axisBorder: {
                            color: '#ffffff'
                        }
                    },
                    grid: {
                        borderColor: '#444444',
                        strokeDashArray: 3
                    },
                    legend: {
                        show: false
                    },
                    dataLabels: {
                        enabled: useBarChart,
                        style: {
                            fontSize: '24px',
                            fontFamily: 'Hind, sans-serif',
                            colors: ['#ffffff']
                        },
                        formatter: function(val) {
                            return val + ' ' + chart.unit;
                        }
                    },
                    stroke: {
                        width: apexChartType === 'line' || apexChartType === 'area' ? 4 : 1
                    },
                    fill: {
                        opacity: apexChartType === 'area' ? 0.4 : 1
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            columnWidth: '70%'
                        }
                    }
                };

                try {
                    const chartInstance = new ApexCharts(element, options);
                    chartInstance.render();
                    this.chartInstances[chart.id] = chartInstance;
                                } catch (error) {
                    console.error('Error creating ApexChart:', error);
                    element.innerHTML = '<div class="text-center text-red-500 p-4">Error loading chart</div>';
                }
                }, 100); // 100ms delay to ensure DOM is ready
            },

            renderCombinedChart(chart) {
                // Use setTimeout to ensure DOM element is available
                setTimeout(() => {
                    const element = document.getElementById(`chart${chart.id}`);
                    if (!element) {
                        console.error(`Chart element with ID chart${chart.id} not found`);
                        return;
                    }

                    // Validate chart data
                    if (!chart || !chart.data || !Array.isArray(chart.data) || chart.data.length === 0) {
                        console.error('Invalid chart data:', chart);
                        return;
                    }

                    if (!chart.datasets || !Array.isArray(chart.datasets) || chart.datasets.length === 0) {
                        console.error('Invalid chart datasets:', chart.datasets);
                        return;
                    }

                    // Destroy existing chart if it exists
                    if (this.chartInstances[chart.id]) {
                        try {
                            this.chartInstances[chart.id].destroy();
                        } catch (e) {
                            console.warn('Error destroying existing chart:', e);
                        }
                    }

                                    // Prepare data in simple array format
                    const series = chart.datasets.map(dataset => ({
                        name: dataset.label || 'Data',
                        data: dataset.data.filter(value => value !== null && value !== undefined)
                    }));

                    // Extract categories (dates) from chart data
                    const categories = chart.data.map(d => d.date);

                    const options = {
                    series: series,
                    chart: {
                        type: 'line',
                        height: 700,
                        width: 1700,
                        background: '#1a1a1a',
                        foreColor: '#ffffff',
                        fontFamily: 'Hind, sans-serif',
                        toolbar: {
                            show: true,
                            tools: {
                                download: true,
                                selection: false,
                                zoom: false,
                                zoomin: false,
                                zoomout: false,
                                pan: false,
                                reset: false
                            },
                            export: {
                                svg: {
                                    filename: `${chart.title}_chart`
                                },
                                png: {
                                    filename: `${chart.title}_chart`
                                }
                            }
                        }
                    },
                    title: {
                        text: chart.title,
                        align: 'center',
                        style: {
                            fontSize: '36px',
                            fontWeight: 'bold',
                            color: '#ffffff',
                            fontFamily: 'Hind, sans-serif'
                        }
                    },
                    xaxis: {
                        type: 'category',
                        categories: categories,
                        title: {
                            text: 'Datum',
                            style: {
                                fontSize: '36px',
                                color: '#ffffff',
                                fontFamily: 'Hind, sans-serif'
                            }
                        },
                        labels: {
                            rotate: chart.data.length > 30 ? -45 : 0,
                            maxHeight: 120,
                            style: {
                                fontSize: '36px',
                                colors: '#ffffff',
                                fontFamily: 'Hind, sans-serif'
                            }
                        },
                        axisBorder: {
                            color: '#ffffff'
                        },
                        axisTicks: {
                            color: '#ffffff'
                        }
                    },
                    yaxis: {
                        title: {
                            text: chart.unit,
                            style: {
                                fontSize: '36px',
                                color: '#ffffff',
                                fontFamily: 'Hind, sans-serif'
                            }
                        },
                        labels: {
                            style: {
                                fontSize: '36px',
                                colors: '#ffffff',
                                fontFamily: 'Hind, sans-serif'
                            }
                        },
                        axisBorder: {
                            color: '#ffffff'
                        }
                    },
                    grid: {
                        borderColor: '#444444',
                        strokeDashArray: 3
                    },
                    stroke: {
                        width: 2
                    },
                    legend: {
                        show: true,
                        position: 'top',
                        labels: {
                            colors: '#ffffff',
                            fontFamily: 'Hind, sans-serif'
                        },
                        fontSize: '36px'
                    },
                    responsive: [{
                        breakpoint: 768,
                        options: {
                            chart: {
                                height: 300
                            }
                        }
                    }]
                };

                try {
                    const chartInstance = new ApexCharts(element, options);
                    chartInstance.render();
                    this.chartInstances[chart.id] = chartInstance;
                                } catch (error) {
                    console.error('Error creating ApexChart:', error);
                    element.innerHTML = '<div class="text-center text-red-500 p-4">Error loading chart</div>';
                }
                }, 100); // 100ms delay to ensure DOM is ready
            },

            exportChart(chartId) {
                const chartInstance = this.chartInstances[chartId];
                if (!chartInstance) {
                    console.error('Chart instance not found for ID:', chartId);
                    alert('Chart not found. Please ensure the chart is fully loaded before exporting.');
                    return;
                }

                try {
                    // Use ApexCharts built-in export functionality
                    chartInstance.exportChart({
                        type: 'svg',
                        filename: `chart_${chartId}`
                    });
                } catch (error) {
                    console.error('Error exporting chart:', error);
                    // Fallback to dataURI method
                    try {
                        if (typeof chartInstance.dataURI === 'function') {
                            chartInstance.dataURI({ format: 'svg' }).then((uri) => {
                                if (uri && uri.imgURI) {
                                    const link = document.createElement('a');
                                    link.href = uri.imgURI;
                                    link.download = `chart_${chartId}.svg`;
                                    document.body.appendChild(link);
                                    link.click();
                                    document.body.removeChild(link);
                                } else {
                                    console.error('Invalid URI received from chart export');
                                    alert('Failed to export chart. The chart data may be invalid.');
                                }
                            }).catch((error) => {
                                console.error('Error in dataURI export:', error);
                                alert('Failed to export chart. Please try again.');
                            });
                        } else {
                            console.error('dataURI method not available on chart instance');
                            alert('Export functionality not available for this chart.');
                        }
                    } catch (fallbackError) {
                        console.error('Error in fallback export method:', fallbackError);
                        alert('Failed to export chart. Please try refreshing the page and generating the chart again.');
                    }
                }
            },

            getChartColor(index, alpha = 1) {
                const colors = [
                    '#3B82F6', // Blue
                    '#10B981', // Green
                    '#F56565', // Red
                    '#FBBF24', // Yellow
                    '#8B5CF6', // Purple
                    '#EC4899', // Pink
                ];
                return colors[index % colors.length];
            },

            clearCharts() {
                // Destroy all chart instances
                Object.values(this.chartInstances).forEach(chart => {
                    if (chart && typeof chart.destroy === 'function') {
                        chart.destroy();
                    }
                });
                this.chartInstances = {};
                this.charts = [];
            },

            formatDate(dateStr) {
                const date = new Date(dateStr);
                return date.toLocaleDateString('sl-SI');
            }
        }
    }
    </script>
</body>

</html>