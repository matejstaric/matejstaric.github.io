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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background: transparent;
        }
        .chart-container {
            position: relative;
            height: 400px;
            margin: 20px 0;
        }
    </style>
    <!-- Alpine Plugins -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/persist@3.x.x/dist/cdn.min.js"></script>
    <!-- Alpine Core -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="">
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
                <!-- Location Selection -->
                <div>
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

                <!-- Variable Selection -->
                <div>
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

                <!-- Date Range -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                        <canvas :id="'chart' + chart.id"></canvas>
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
                <p>• Arhivski podatki so na voljo od leta 1991 naprej</p>
                <p>• Podatki se osvežujejo dnevno ob 7:00</p>
                <p>• Maksimalen časovni razpon je 5 let</p>
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
            selectedLocations: [],
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
            chartInstances: [],

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

                // Validation
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

                this.loading = true;
                this.clearCharts();

                try {
                    // Fetch data for each location (can fetch multiple variables per request)
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
                    
                    // Process results and create individual charts for each variable
                    let chartId = 0;
                    for (const result of results) {
                        if (result.success && result.data) {
                            this.processLocationData(result.data, result.location, chartId);
                            chartId += this.selectedVariables.length;
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
                const canvas = document.getElementById(`chart${chart.id}`);
                if (!canvas) return;

                const ctx = canvas.getContext('2d');
                
                // Destroy existing chart if it exists
                if (this.chartInstances[chart.id]) {
                    this.chartInstances[chart.id].destroy();
                }

                // Determine chart type - use bar for rainfall and sunshine hours, otherwise use selected type
                const isRainfall = chart.unit === 'mm';
                const isSunshine = chart.unit === 'h';
                const useBarChart = isRainfall || isSunshine;
                const chartType = useBarChart ? 'bar' : (this.chartType === 'area' ? 'line' : this.chartType);

                const chartInstance = new Chart(ctx, {
                    type: chartType,
                    data: {
                        labels: chart.data.map(d => d.date),
                        datasets: [{
                            label: `${chart.title} (${chart.unit})`,
                            data: chart.data.map(d => d.value),
                            borderColor: this.getChartColor(chart.id),
                            backgroundColor: this.getChartColor(chart.id, useBarChart ? 0.7 : 0.1),
                            borderWidth: useBarChart ? 1 : 2,
                            fill: this.chartType === 'area' && !useBarChart,
                            tension: 0.1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: chart.unit
                                },
                                // For bar charts, add some padding at the top
                                grace: useBarChart ? '5%' : '0%'
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Datum'
                                },
                                // For bar charts, don't show all labels if there are too many
                                ticks: useBarChart && chart.data.length > 30 ? {
                                    maxTicksLimit: 10,
                                    maxRotation: 45
                                } : {}
                            }
                        },
                        plugins: {
                            title: {
                                display: true,
                                text: chart.title
                            },
                            legend: {
                                display: false
                            }
                        }
                    }
                });

                this.chartInstances[chart.id] = chartInstance;
            },

            renderCombinedChart(chart) {
                const canvas = document.getElementById(`chart${chart.id}`);
                if (!canvas) return;

                const ctx = canvas.getContext('2d');
                
                // Destroy existing chart if it exists
                if (this.chartInstances[chart.id]) {
                    this.chartInstances[chart.id].destroy();
                }

                const chartInstance = new Chart(ctx, {
                    type: 'line', // Always use line chart for temperature comparison
                    data: {
                        labels: chart.data.map(d => d.date),
                        datasets: chart.datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: false, // Temperature doesn't need to start at zero
                                title: {
                                    display: true,
                                    text: chart.unit
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Datum'
                                },
                                ticks: chart.data.length > 30 ? {
                                    maxTicksLimit: 10,
                                    maxRotation: 45
                                } : {}
                            }
                        },
                        plugins: {
                            title: {
                                display: true,
                                text: chart.title
                            },
                            legend: {
                                display: true, // Show legend for multiple temperature lines
                                position: 'top'
                            }
                        }
                    }
                });

                this.chartInstances[chart.id] = chartInstance;
            },

            getChartColor(index, alpha = 1) {
                const colors = [
                    `rgba(59, 130, 246, ${alpha})`, // Blue
                    `rgba(16, 185, 129, ${alpha})`, // Green
                    `rgba(245, 101, 101, ${alpha})`, // Red
                    `rgba(251, 191, 36, ${alpha})`, // Yellow
                    `rgba(139, 92, 246, ${alpha})`, // Purple
                    `rgba(236, 72, 153, ${alpha})`, // Pink
                ];
                return colors[index % colors.length];
            },

            clearCharts() {
                // Destroy all chart instances
                this.chartInstances.forEach(chart => {
                    if (chart) {
                        chart.destroy();
                    }
                });
                this.chartInstances = [];
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