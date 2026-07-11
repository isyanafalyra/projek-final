<x-app-layout>
    @push('styles')
        <!-- Leaflet CSS -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <style>
            /* Additional dashboard enhancements */
            .metric-progress {
                height: 8px;
                border-radius: 4px;
                background-color: rgba(255, 255, 255, 0.05);
            }
            .trend-badge {
                font-size: 0.75rem;
                padding: 3px 8px;
                border-radius: 4px;
                font-weight: 600;
            }
            .sentiment-ratio-bar {
                height: 6px;
                border-radius: 3px;
                overflow: hidden;
                display: flex;
            }
            .bg-sentiment-pos { background-color: var(--success-color); }
            .bg-sentiment-neg { background-color: var(--danger-color); }
            .bg-sentiment-neu { background-color: var(--text-secondary); }
            
            .watchlist-item {
                cursor: pointer;
                transition: background-color 0.2s ease;
            }
            .watchlist-item:hover {
                background-color: rgba(255, 255, 255, 0.05);
            }
        </style>
    @endpush

    <!-- Header Section -->
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h2 class="text-white fw-bold mb-0">Global Supply Chain Intelligence</h2>
            <p class="text-secondary mb-0">Platform Monitoring Risiko Rantai Pasok Berbasis Multi-API & Data Science</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <span class="badge bg-secondary-subtle text-secondary border border-secondary px-3 py-2 rounded-pill">
                <i class="fa-solid fa-clock me-2 text-info"></i>Data Terkini Real-time
            </span>
        </div>
    </div>

    <!-- Navigation Tab Pills -->
    <div class="d-flex justify-content-center justify-content-md-start">
        <ul class="nav nav-pills nav-pills-custom" id="dashboardTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="map-tab" data-bs-toggle="pill" data-bs-target="#map-content" type="button" role="tab">
                    <i class="fa-solid fa-map-location-dot me-2"></i>Global Map & Weather
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="analytics-tab" data-bs-toggle="pill" data-bs-target="#analytics-content" type="button" role="tab">
                    <i class="fa-solid fa-chart-pie me-2"></i>Country Intelligence
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="currency-tab" data-bs-toggle="pill" data-bs-target="#currency-content" type="button" role="tab">
                    <i class="fa-solid fa-coins me-2"></i>Valuta & Kurs
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="compare-tab" data-bs-toggle="pill" data-bs-target="#compare-content" type="button" role="tab">
                    <i class="fa-solid fa-scale-balanced me-2"></i>Perbandingan Negara
                </button>
            </li>
        </ul>
    </div>

    <!-- Tab Contents -->
    <div class="tab-content" id="dashboardTabsContent">
        
        <!-- TAB 1: Map Global Monitoring -->
        <div class="tab-pane fade show active" id="map-content" role="tabpanel" aria-labelledby="map-tab">
            <div class="row">
                <div class="col-lg-9">
                    <div class="glass-card p-3 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="text-white fw-bold mb-0">
                                <i class="fa-solid fa-earth-americas text-info me-2"></i>Peta Logistik & Cuaca Dunia
                            </h5>
                            <div class="d-flex gap-2">
                                <select id="mapCountrySelect" class="form-select form-select-sm form-control-custom" style="max-width: 200px;">
                                    <option value="" disabled selected>Pilih Negara...</option>
                                </select>
                                <input type="text" id="mapSearchInput" class="form-control form-control-sm form-control-custom" placeholder="Cari Pelabuhan..." style="max-width: 220px;">
                                <button id="mapSearchBtn" class="btn btn-sm btn-primary-custom"><i class="fa-solid fa-magnifying-glass"></i></button>
                            </div>
                        </div>
                        
                        <!-- Peta Leaflet Container -->
                        <div id="map"></div>
                    </div>
                </div>
                
                <div class="col-lg-3">
                    <!-- Quick Info Panel -->
                    <div class="glass-card p-4 mb-4">
                        <h5 class="text-white fw-bold mb-3"><i class="fa-solid fa-circle-info text-info me-2"></i>Legenda & Status</h5>
                        <p class="text-secondary small mb-3">Peta ini menampilkan lokasi pelabuhan kargo utama dunia. Klik pin pelabuhan untuk memuat cuaca real-time langsung melalui satelit Open-Meteo.</p>
                        
                        <hr class="border-secondary my-3">
                        
                        <div class="mb-3">
                            <label class="text-secondary d-block small fw-bold mb-2">RISIKO NEGARA (RISK RANGE)</label>
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge-low-risk py-1 px-2 rounded me-2 small" style="font-size: 0.75rem;">Low Risk</span>
                                <span class="text-secondary small">&lt; 30 (Aman & Stabil)</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge-medium-risk py-1 px-2 rounded me-2 small" style="font-size: 0.75rem;">Medium Risk</span>
                                <span class="text-secondary small">30 - 60 (Volatilitas Sedang)</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge-high-risk py-1 px-2 rounded me-2 small" style="font-size: 0.75rem;">High Risk</span>
                                <span class="text-secondary small">&gt; 60 (Kerawanan Tinggi)</span>
                            </div>
                        </div>
                        
                        <hr class="border-secondary my-3">
                        
                        <div>
                            <label class="text-secondary d-block small fw-bold mb-2">METEOROLOGI LOGISTIK</label>
                            <div class="d-flex align-items-center mb-2">
                                <i class="fa-solid fa-umbrella text-primary me-3 fs-5"></i>
                                <span class="text-secondary small">Precipitation &gt; 10mm (Hujan Lebat)</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="fa-solid fa-wind text-warning me-3 fs-5"></i>
                                <span class="text-secondary small">Wind Speed &gt; 40 km/h (Badai Angin)</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="fa-solid fa-temperature-empty text-danger me-3 fs-5"></i>
                                <span class="text-secondary small">Suhu Ekstrem (&lt;5°C / &gt;38°C)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: Country Intelligence -->
        <div class="tab-pane fade" id="analytics-content" role="tabpanel" aria-labelledby="analytics-tab">
            <!-- Country Selector Control -->
            <div class="glass-card p-4 mb-4">
                <div class="row align-items-center">
                    <div class="col-md-5">
                        <label class="form-label form-label-custom mb-2">PILIH NEGARA MONITORING</label>
                        <div class="d-flex">
                            <select id="countrySelect" class="form-select form-control-custom">
                                <option value="" disabled selected>Memuat daftar negara...</option>
                            </select>
                            <button id="watchlistBtn" class="btn btn-secondary-custom ms-2 d-flex align-items-center justify-content-center" style="width: 48px;" title="Tambahkan ke Watchlist">
                                <i class="fa-regular fa-heart text-danger fs-5" id="watchlistIcon"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-7 mt-3 mt-md-0">
                        <div class="d-flex gap-2 flex-wrap" id="watchlistContainer">
                            <!-- Watchlisted Quick Buttons -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loader -->
            <div id="analyticsLoader" class="text-center py-5 d-none">
                <div class="spinner-border text-info" style="width: 3rem; height: 3rem;" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5 class="text-secondary mt-3">Mengkalkulasi Analitik Risiko Negara...</h5>
            </div>

            <!-- Main Country Grid -->
            <div id="countryDashboardGrid" class="d-none">
                <div class="row">
                    <!-- Column Left: Country Profile -->
                    <div class="col-md-4 mb-4">
                        <div class="glass-card p-4 h-100">
                            <div class="d-flex align-items-center mb-4">
                                <img id="countryFlag" src="" alt="Flag" class="rounded me-3 border border-secondary" style="width: 70px; height: 45px; object-fit: cover;">
                                <div>
                                    <h4 class="text-white fw-bold mb-0" id="countryNameDisplay">Negara</h4>
                                    <span class="text-secondary small fw-bold" id="countryRegionDisplay">ASIA</span>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="text-secondary small fw-bold d-block mb-1">IBUKOTA</label>
                                <h6 class="text-white fw-medium mb-0" id="countryCapital">Jakarta</h6>
                            </div>
                            <div class="mb-3">
                                <label class="text-secondary small fw-bold d-block mb-1">MATA UANG</label>
                                <h6 class="text-white fw-medium mb-0" id="countryCurrency">Rupiah (IDR)</h6>
                            </div>
                            <div class="mb-3">
                                <label class="text-secondary small fw-bold d-block mb-1">KELOMPOK PENDAPATAN (WORLD BANK)</label>
                                <h6 class="text-white fw-medium mb-0" id="countryIncomeLevel">Upper Middle Income</h6>
                            </div>
                            <div class="mb-0">
                                <label class="text-secondary small fw-bold d-block mb-1">BAHASA UTAMA</label>
                                <h6 class="text-white fw-medium mb-0" id="countryLanguages">Indonesian</h6>
                            </div>
                        </div>
                    </div>

                    <!-- Column Middle: Risk Scoring Engine -->
                    <div class="col-md-4 mb-4">
                        <div class="glass-card p-4 h-100 text-center">
                            <h5 class="text-white fw-bold mb-4 text-start">Risk Scoring Engine</h5>
                            
                            <div class="risk-gauge-wrapper mb-4">
                                <div class="risk-gauge-circle">
                                    <span class="risk-gauge-value text-white" id="riskTotalValue">0</span>
                                    <span class="risk-gauge-label" id="riskLevelDisplay">N/A</span>
                                </div>
                            </div>
                            
                            <div class="text-start">
                                <label class="text-secondary small fw-bold d-block mb-3 text-center">RINCIAN MATRIKS BOBOT RISIKO</label>
                                
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-secondary small"><i class="fa-solid fa-cloud-bolt text-info me-2"></i>Cuaca (Weight: 30%)</span>
                                        <span class="text-white small fw-bold" id="riskWeatherVal">0%</span>
                                    </div>
                                    <div class="progress metric-progress">
                                        <div class="progress-bar bg-info" id="riskWeatherBar" role="progressbar" style="width: 0%"></div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-secondary small"><i class="fa-solid fa-money-bill-trend-up text-warning me-2"></i>Inflasi (Weight: 20%)</span>
                                        <span class="text-white small fw-bold" id="riskInflationVal">0%</span>
                                    </div>
                                    <div class="progress metric-progress">
                                        <div class="progress-bar bg-warning" id="riskInflationBar" role="progressbar" style="width: 0%"></div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-secondary small"><i class="fa-solid fa-newspaper text-danger me-2"></i>Berita & Politik (Weight: 40%)</span>
                                        <span class="text-white small fw-bold" id="riskPoliticalVal">0%</span>
                                    </div>
                                    <div class="progress metric-progress">
                                        <div class="progress-bar bg-danger" id="riskPoliticalBar" role="progressbar" style="width: 0%"></div>
                                    </div>
                                </div>
                                
                                <div class="mb-0">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-secondary small"><i class="fa-solid fa-scale-unbalanced text-success me-2"></i>Volatilitas Kurs (Weight: 10%)</span>
                                        <span class="text-white small fw-bold" id="riskCurrencyVal">0%</span>
                                    </div>
                                    <div class="progress metric-progress">
                                        <div class="progress-bar bg-success" id="riskCurrencyBar" role="progressbar" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Column Right: Historical Risk scores -->
                    <div class="col-md-4 mb-4">
                        <div class="glass-card p-4 h-100">
                            <h5 class="text-white fw-bold mb-3"><i class="fa-solid fa-timeline text-info me-2"></i>Riwayat Kalkulasi Risiko</h5>
                            <p class="text-secondary small mb-3">Tren skor risiko negara berdasarkan log kalkulasi logistik 5 sesi terakhir.</p>
                            <div style="height: 240px; position: relative;">
                                <canvas id="historicalRiskChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Bottom Left: Macroeconomic visualization -->
                    <div class="col-lg-6 mb-4">
                        <div class="glass-card p-4">
                            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                                <h5 class="text-white fw-bold mb-0"><i class="fa-solid fa-chart-line text-info me-2"></i>Visualisasi Ekonomi Makro (World Bank)</h5>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-info active" onclick="switchMacroMetric('gdp')">GDP</button>
                                    <button type="button" class="btn btn-outline-info" onclick="switchMacroMetric('inflation')">Inflasi</button>
                                    <button type="button" class="btn btn-outline-info" onclick="switchMacroMetric('trade')">Ekspor/Impor</button>
                                    <button type="button" class="btn btn-outline-info" onclick="switchMacroMetric('population')">Populasi</button>
                                </div>
                            </div>
                            <div style="height: 260px; position: relative;">
                                <canvas id="macroeconomicsChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Right: Sentiment & News Intelligence -->
                    <div class="col-lg-6 mb-4">
                        <div class="glass-card p-4">
                            <h5 class="text-white fw-bold mb-2"><i class="fa-solid fa-brain text-info me-2"></i>News Intelligence (GNews API)</h5>
                            <p class="text-secondary small mb-3">Analisis sentimen leksikon (positif/negatif) berita terkait rantai pasok global.</p>
                            
                            <!-- Sentiment global stats -->
                            <div class="d-flex gap-4 p-3 bg-secondary-subtle rounded-3 mb-3 border border-secondary align-items-center">
                                <div>
                                    <h2 class="text-white fw-bold mb-0" id="avgSentimentScore">0</h2>
                                    <span class="text-secondary small">Average Sentiment Score</span>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between small text-secondary mb-1">
                                        <span>Rasio Sentimen Berita</span>
                                        <span class="text-white fw-bold" id="sentimentRatiosText">Pos: 0% | Neg: 0%</span>
                                    </div>
                                    <div class="sentiment-ratio-bar">
                                        <div class="bg-sentiment-pos" id="ratioBarPos" style="width: 0%"></div>
                                        <div class="bg-sentiment-neu" id="ratioBarNeu" style="width: 100%"></div>
                                        <div class="bg-sentiment-neg" id="ratioBarNeg" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- List news -->
                            <div class="news-scroll-container" id="newsListContainer">
                                <!-- Dynamic list -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 3: Markets & Currency -->
        <div class="tab-pane fade" id="currency-content" role="tabpanel" aria-labelledby="currency-tab">
            <div class="row">
                <!-- Conversion Rate Table -->
                <div class="col-lg-5 mb-4">
                    <div class="glass-card p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="text-white fw-bold mb-0"><i class="fa-solid fa-table text-info me-2"></i>Daftar Kurs Valuta Asing</h5>
                            <div>
                                <select id="baseCurrencySelect" class="form-select form-select-sm form-control-custom" style="min-width: 100px;">
                                    <option value="USD">Base: USD</option>
                                    <option value="EUR">Base: EUR</option>
                                    <option value="IDR">Base: IDR</option>
                                    <option value="SGD">Base: SGD</option>
                                </select>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-dark table-hover border-secondary align-middle">
                                <thead>
                                    <tr>
                                        <th>Mata Uang</th>
                                        <th>Kode</th>
                                        <th class="text-end">Nilai Kurs</th>
                                        <th class="text-end">Tren Hari Ini</th>
                                    </tr>
                                </thead>
                                <tbody id="currencyTableBody">
                                    <!-- Dynamic rows -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Currency Chart -->
                <div class="col-lg-7 mb-4">
                    <div class="glass-card p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="text-white fw-bold mb-0"><i class="fa-solid fa-chart-line text-info me-2"></i>Tren Volatilitas Nilai Tukar (7 Hari)</h5>
                            <div>
                                <select id="chartCurrencySelect" class="form-select form-select-sm form-control-custom" style="min-width: 120px;">
                                    <option value="IDR">IDR (Rupiah)</option>
                                    <option value="SGD">SGD (Dollar SG)</option>
                                    <option value="EUR">EUR (Euro)</option>
                                    <option value="CNY">CNY (Yuan)</option>
                                    <option value="JPY">JPY (Yen)</option>
                                    <option value="GBP">GBP (Poundsterling)</option>
                                </select>
                            </div>
                        </div>
                        <p class="text-secondary small mb-3">Tren nilai tukar harian terhadap mata uang dasar (base) yang dipilih.</p>
                        <div style="height: 320px; position: relative;">
                            <canvas id="currencyTrendChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 4: Country Comparison Engine -->
        <div class="tab-pane fade" id="compare-content" role="tabpanel" aria-labelledby="compare-tab">
            <div class="glass-card p-4 mb-4">
                <h5 class="text-white fw-bold mb-4 text-center text-md-start"><i class="fa-solid fa-arrows-left-right text-info me-2"></i>Country Comparison Engine</h5>
                <div class="row align-items-end justify-content-center">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <label class="form-label form-label-custom">NEGARA A (BASE)</label>
                        <select id="compareSelectA" class="form-select form-control-custom">
                            <!-- Populated dynamically -->
                        </select>
                    </div>
                    <div class="col-md-1 mb-3 mb-md-0 text-center comparison-divider">
                        <span>VS</span>
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0">
                        <label class="form-label form-label-custom">NEGARA B (KOMPARATOR)</label>
                        <select id="compareSelectB" class="form-select form-control-custom">
                            <!-- Populated dynamically -->
                        </select>
                    </div>
                    <div class="col-md-3 mt-3 mt-md-0">
                        <button id="compareBtn" class="btn btn-primary-custom w-100 py-3"><i class="fa-solid fa-scale-balanced me-2"></i>Bandingkan Sekarang</button>
                    </div>
                </div>
            </div>

            <!-- Loader Comparison -->
            <div id="compareLoader" class="text-center py-5 d-none">
                <div class="spinner-border text-info" style="width: 3rem; height: 3rem;" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5 class="text-secondary mt-3">Mengambil dan Menganalisis Perbandingan Data...</h5>
            </div>

            <!-- Comparison Dashboard Display -->
            <div id="comparisonDashboard" class="d-none">
                <!-- Risk Meter row -->
                <div class="row mb-4">
                    <div class="col-md-5 mb-3 mb-md-0">
                        <div class="comparison-card text-center">
                            <img id="compareFlagA" src="" class="rounded mb-2" style="width: 60px; height: 38px; object-fit: cover;">
                            <h4 class="text-white fw-bold mb-1" id="compareNameA">Negara A</h4>
                            <span class="badge" id="compareLevelBadgeA">Low Risk</span>
                            <div class="fs-1 fw-bold text-white mt-3 mb-2" id="compareTotalRiskA">0</div>
                            <span class="text-secondary small">Total Risk Score</span>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex flex-column align-items-center justify-content-center">
                        <h4 class="text-info fw-bold mb-0">RISIKO</h4>
                        <span class="text-secondary small text-center mt-2 d-block">Komparator Selisih</span>
                        <div class="fs-4 fw-bold mt-2" id="compareRiskDiff">0</div>
                    </div>
                    <div class="col-md-5">
                        <div class="comparison-card text-center">
                            <img id="compareFlagB" src="" class="rounded mb-2" style="width: 60px; height: 38px; object-fit: cover;">
                            <h4 class="text-white fw-bold mb-1" id="compareNameB">Negara B</h4>
                            <span class="badge" id="compareLevelBadgeB">High Risk</span>
                            <div class="fs-1 fw-bold text-white mt-3 mb-2" id="compareTotalRiskB">0</div>
                            <span class="text-secondary small">Total Risk Score</span>
                        </div>
                    </div>
                </div>

                <!-- Metrics Comparison details chart -->
                <div class="row">
                    <!-- Column Left: Detailed sub-risk comparisons -->
                    <div class="col-md-6 mb-4">
                        <div class="glass-card p-4 h-100">
                            <h5 class="text-white fw-bold mb-4">Komparasi Rincian Sub-Risiko</h5>
                            <div style="height: 300px; position: relative;">
                                <canvas id="compareMetricsChart"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Column Right: Economic Indicators Comparative Table -->
                    <div class="col-md-6 mb-4">
                        <div class="glass-card p-4 h-100">
                            <h5 class="text-white fw-bold mb-4">Perbandingan Statistik Ekonomi Makro</h5>
                            <div class="table-responsive">
                                <table class="table table-dark table-hover border-secondary align-middle">
                                    <thead>
                                        <tr>
                                            <th>Metrik Makro (World Bank)</th>
                                            <th class="text-center text-info" id="compareTableHeadA">Negara A</th>
                                            <th class="text-center text-info" id="compareTableHeadB">Negara B</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Kelompok Pendapatan</td>
                                            <td class="text-center text-white" id="compareMacroIncomeA">-</td>
                                            <td class="text-center text-white" id="compareMacroIncomeB">-</td>
                                        </tr>
                                        <tr>
                                            <td>GDP Terkini (Miliar USD)</td>
                                            <td class="text-center text-white" id="compareMacroGdpA">-</td>
                                            <td class="text-center text-white" id="compareMacroGdpB">-</td>
                                        </tr>
                                        <tr>
                                            <td>Laju Inflasi Tahunan (%)</td>
                                            <td class="text-center text-white" id="compareMacroInflationA">-</td>
                                            <td class="text-center text-white" id="compareMacroInflationB">-</td>
                                        </tr>
                                        <tr>
                                            <td>Ekspor (% GDP)</td>
                                            <td class="text-center text-white" id="compareMacroExportsA">-</td>
                                            <td class="text-center text-white" id="compareMacroExportsB">-</td>
                                        </tr>
                                        <tr>
                                            <td>Impor (% GDP)</td>
                                            <td class="text-center text-white" id="compareMacroImportsA">-</td>
                                            <td class="text-center text-white" id="compareMacroImportsB">-</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
        <!-- Leaflet JS & Chart.js -->
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        
        <script>
            // State Global Dashboard
            let countriesList = [];
            let currentCountryCode = '';
            let currentCountryData = null;
            let currentMacroMetric = 'gdp'; // Default macro display
            
            // Chart references
            let historicalRiskChart = null;
            let macroeconomicsChart = null;
            let currencyTrendChart = null;
            let compareMetricsChart = null;
            
            // Map reference
            let leafletMap = null;
            let portMarkersGroup = null;

            // CSRF Token header for POST requests
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // --- WINDOW ONLOAD SETUP ---
            window.addEventListener('DOMContentLoaded', () => {
                initLeafletMap();
                fetchCountries();
                fetchPorts();
                fetchCurrencyRates('USD');

                // Bind Event Listeners
                document.getElementById('countrySelect').addEventListener('change', (e) => {
                    const countryCode = e.target.value;
                    loadCountryAnalytics(countryCode);
                });

                document.getElementById('watchlistBtn').addEventListener('click', toggleWatchlist);
                
                document.getElementById('baseCurrencySelect').addEventListener('change', (e) => {
                    fetchCurrencyRates(e.target.value);
                });

                document.getElementById('chartCurrencySelect').addEventListener('change', (e) => {
                    updateCurrencyTrendChart();
                });

                document.getElementById('compareBtn').addEventListener('click', processComparison);

                document.getElementById('mapSearchBtn').addEventListener('click', handleMapSearch);
                document.getElementById('mapSearchInput').addEventListener('keyup', (e) => {
                    if (e.key === 'Enter') handleMapSearch();
                });
                document.getElementById('mapCountrySelect').addEventListener('change', (e) => {
                    handleMapCountryChange(e.target.value);
                });
            });

            // --- TABS RE-RENDER MAP BUG FIX ---
            // Leaflet maps can bug out and render blank grey when initialized in hidden tabs.
            // We listen to tab change events to invalidateMapSize.
            const mapTabEl = document.getElementById('map-tab');
            mapTabEl.addEventListener('shown.bs.tab', () => {
                if (leafletMap) {
                    setTimeout(() => {
                        leafletMap.invalidateSize();
                    }, 200);
                }
            });

            // --- INITS ---
            
            function initLeafletMap() {
                // Center map on Southeast Asia/Global view
                leafletMap = L.map('map', {
                    attributionControl: false,
                    zoomControl: true
                }).setView([15, 105], 3);

                // Add dark styling tile using OpenStreetMap
                const mapTiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19
                }).addTo(leafletMap);
                
                // Hack to force Leaflet Tiles to Dark Mode style using CSS class injection
                mapTiles.getContainer().classList.add('dark-map-tiles');

                portMarkersGroup = L.layerGroup().addTo(leafletMap);
            }

            // --- METODE FETCH DATA ---

            function fetchCountries() {
                fetch('/api/countries')
                    .then(res => res.json())
                    .then(response => {
                        if (response.status === 'success') {
                            countriesList = response.data;
                            populateCountryDropdowns();
                            renderWatchlistButtons();
                            
                            // Load first country as default in Analytics (e.g. Indonesia / ID)
                            if (countriesList.length > 0) {
                                const idIndex = countriesList.findIndex(c => c.iso_code === 'ID');
                                const defaultCountry = idIndex !== -1 ? countriesList[idIndex] : countriesList[0];
                                document.getElementById('countrySelect').value = defaultCountry.iso_code;
                                loadCountryAnalytics(defaultCountry.iso_code);
                            }
                        }
                    })
                    .catch(err => console.error("Error loading countries:", err));
            }

            function fetchPorts() {
                fetch('/api/ports')
                    .then(res => res.json())
                    .then(response => {
                        if (response.status === 'success') {
                            renderPortMarkers(response.data);
                        }
                    })
                    .catch(err => console.error("Error loading ports:", err));
            }

            let globalCurrencyRates = {};
            let globalCurrencyTrends = {};

            function fetchCurrencyRates(baseCurrency) {
                fetch(`/api/currency?base=${baseCurrency}`)
                    .then(res => res.json())
                    .then(response => {
                        if (response.status === 'success') {
                            globalCurrencyRates = response.rates;
                            globalCurrencyTrends = response.trends;
                            renderCurrencyTable(baseCurrency);
                            updateCurrencyTrendChart();
                        }
                    })
                    .catch(err => console.error("Error loading currencies:", err));
            }

            // --- FRONTEND RENDERERS ---

            function populateCountryDropdowns() {
                const select = document.getElementById('countrySelect');
                const selectA = document.getElementById('compareSelectA');
                const selectB = document.getElementById('compareSelectB');
                const mapSelect = document.getElementById('mapCountrySelect');

                select.innerHTML = '';
                selectA.innerHTML = '<option value="" disabled selected>Pilih Negara A</option>';
                selectB.innerHTML = '<option value="" disabled selected>Pilih Negara B</option>';
                if (mapSelect) {
                    mapSelect.innerHTML = '<option value="" disabled selected>Pilih Negara...</option>';
                }

                countriesList.forEach(country => {
                    const optionHtml = `<option value="${country.iso_code}">${country.name} (${country.iso_code})</option>`;
                    select.insertAdjacentHTML('beforeend', optionHtml);
                    selectA.insertAdjacentHTML('beforeend', optionHtml);
                    selectB.insertAdjacentHTML('beforeend', optionHtml);
                    if (mapSelect) {
                        mapSelect.insertAdjacentHTML('beforeend', optionHtml);
                    }
                });
            }

            function renderWatchlistButtons() {
                const container = document.getElementById('watchlistContainer');
                container.innerHTML = '<span class="text-secondary small fw-bold d-block w-100 mb-1">WATCHLIST CEPAT:</span>';
                
                const watchlisted = countriesList.filter(c => c.is_watchlist);
                if (watchlisted.length === 0) {
                    container.insertAdjacentHTML('beforeend', '<span class="text-secondary small italic">Tidak ada watchlist saat ini. Tandai negara menggunakan tombol hati.</span>');
                    return;
                }

                watchlisted.forEach(country => {
                    const btn = `
                        <button class="btn btn-sm btn-outline-info rounded-pill px-3 py-1 text-start watchlist-item" onclick="loadCountryAnalytics('${country.iso_code}')">
                            <i class="fa-solid fa-star text-warning me-1"></i> ${country.name}
                        </button>
                    `;
                    container.insertAdjacentHTML('beforeend', btn);
                });
            }

            // Render ports on Leaflet map
            function renderPortMarkers(ports) {
                portMarkersGroup.clearLayers();
                
                ports.forEach(port => {
                    // Create visual custom pin
                    const marker = L.circleMarker([port.lat, port.lng], {
                        radius: 8,
                        fillColor: '#38bdf8',
                        color: '#0f172a',
                        weight: 2,
                        opacity: 1,
                        fillOpacity: 0.8
                    });

                    // Build tooltip content dynamically
                    const popupContent = `
                        <div class="p-2">
                            <span class="text-info fw-bold d-block" style="font-size: 1.05rem;"><i class="fa-solid fa-anchor me-1"></i> ${port.name}</span>
                            <span class="text-secondary small d-block mb-2"><i class="fa-solid fa-map-pin me-1"></i> ${port.country_name} (${port.country_code})</span>
                            <div class="border-top border-secondary pt-2" id="port-weather-${port.id}">
                                <div class="spinner-border spinner-border-sm text-info" role="status"></div>
                                <span class="text-secondary small ms-2">Menghubungkan satelit cuaca...</span>
                            </div>
                        </div>
                    `;

                    marker.bindPopup(popupContent);
                    
                    // Click listener to load weather from Open-Meteo API (via Client-side)
                    marker.on('click', () => {
                        fetchPortWeather(port);
                    });

                    portMarkersGroup.addLayer(marker);
                });
            }

            function fetchPortWeather(port) {
                // Call Open-Meteo API directly on client side for super fast and light responses
                fetch(`https://api.open-meteo.com/v1/forecast?latitude=${port.lat}&longitude=${port.lng}&current=temperature_2m,relative_humidity_2m,apparent_temperature,precipitation,weather_code,wind_speed_10m`)
                    .then(res => res.json())
                    .then(data => {
                        const container = document.getElementById(`port-weather-${port.id}`);
                        if (!container) return;

                        if (data && data.current) {
                            const cur = data.current;
                            
                            // Map weather code (WMO standard)
                            let wmoDesc = 'Cerah';
                            let wmoIcon = 'fa-sun text-warning';
                            if (cur.weather_code >= 95) {
                                wmoDesc = 'Badai Petir';
                                wmoIcon = 'fa-cloud-bolt text-danger';
                            } else if (cur.weather_code >= 71) {
                                wmoDesc = 'Salju';
                                wmoIcon = 'fa-snowflake text-primary';
                            } else if (cur.weather_code >= 51) {
                                wmoDesc = 'Hujan / Gerimis';
                                wmoIcon = 'fa-cloud-showers-heavy text-info';
                            } else if (cur.weather_code >= 1) {
                                wmoDesc = 'Berawan';
                                wmoIcon = 'fa-cloud text-secondary';
                            }

                            // Highlighting weather warnings
                            let precipWarning = cur.precipitation > 10.0 ? '<span class="text-danger fw-bold">(Hujan Ekstrem)</span>' : '';
                            let windWarning = cur.wind_speed_10m > 40.0 ? '<span class="text-danger fw-bold">(Badai Angin)</span>' : '';

                            container.innerHTML = `
                                <div class="d-flex align-items-center mb-1">
                                    <i class="fa-solid ${wmoIcon} me-2"></i>
                                    <span class="text-white fw-medium">${wmoDesc}</span>
                                </div>
                                <span class="text-secondary small d-block">Suhu: <strong class="text-white">${cur.temperature_2m}°C</strong> (Feels like ${cur.apparent_temperature}°C)</span>
                                <span class="text-secondary small d-block">Kec. Angin: <strong class="text-white">${cur.wind_speed_10m} km/h</strong> ${windWarning}</span>
                                <span class="text-secondary small d-block">Curah Hujan: <strong class="text-white">${cur.precipitation} mm</strong> ${precipWarning}</span>
                            `;
                        } else {
                            container.innerHTML = `<span class="text-danger small">Gagal memuat cuaca.</span>`;
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        const container = document.getElementById(`port-weather-${port.id}`);
                        if (container) {
                            container.innerHTML = `<span class="text-danger small">Koneksi cuaca terputus.</span>`;
                        }
                    });
            }

            // Map Search function
            function handleMapSearch() {
                const query = document.getElementById('mapSearchInput').value.toLowerCase().trim();
                if (!query) return;

                let foundMarker = null;
                let foundPort = null;

                portMarkersGroup.eachLayer(layer => {
                    const popup = layer.getPopup();
                    const popupText = popup.getContent().toLowerCase();
                    if (popupText.includes(query)) {
                        foundMarker = layer;
                    }
                });

                if (foundMarker) {
                    leafletMap.setView(foundMarker.getLatLng(), 6);
                    foundMarker.openPopup();
                    
                    // Trigger manual click to load weather
                    const latlng = foundMarker.getLatLng();
                    // We need to re-find port details from marker coordinates
                    fetch('/api/ports')
                        .then(res => res.json())
                        .then(response => {
                            if (response.status === 'success') {
                                const p = response.data.find(x => Math.abs(x.lat - latlng.lat) < 0.01 && Math.abs(x.lng - latlng.lng) < 0.01);
                                if (p) fetchPortWeather(p);
                            }
                        });
                } else {
                    alert(`Pelabuhan matching "${query}" tidak ditemukan.`);
                }
            }

            // Handle Country Change in Map View
            function handleMapCountryChange(isoCode) {
                if (!isoCode) return;
                
                // Fetch ports to find the one matching the country code
                fetch('/api/ports')
                    .then(res => res.json())
                    .then(response => {
                        if (response.status === 'success') {
                            const port = response.data.find(p => p.country_code === isoCode);
                            if (port) {
                                // Find the Leaflet marker corresponding to this port
                                let foundMarker = null;
                                portMarkersGroup.eachLayer(layer => {
                                    const latlng = layer.getLatLng();
                                    if (Math.abs(latlng.lat - port.lat) < 0.01 && Math.abs(latlng.lng - port.lng) < 0.01) {
                                        foundMarker = layer;
                                    }
                                });

                                if (foundMarker) {
                                    leafletMap.setView(foundMarker.getLatLng(), 6);
                                    foundMarker.openPopup();
                                    fetchPortWeather(port);
                                } else {
                                    const country = countriesList.find(c => c.iso_code === isoCode);
                                    if (country) {
                                        const capitalCoords = {
                                            'ID': [-6.2088, 106.8456],
                                            'SG': [1.3521, 103.8198],
                                            'CN': [39.9042, 116.4074],
                                            'US': [38.9072, -77.0369],
                                            'NL': [52.3676, 4.9041],
                                            'JP': [35.6762, 139.6503],
                                            'DE': [52.5200, 13.4050],
                                            'AU': [-35.2809, 149.1300],
                                            'GB': [51.5074, -0.1278],
                                            'IN': [28.6139, 77.2090],
                                            'MY': [3.1390, 101.6869],
                                            'KR': [37.5665, 126.9780],
                                        };
                                        const coords = capitalCoords[isoCode];
                                        if (coords) {
                                            leafletMap.setView(coords, 5);
                                        }
                                    }
                                }
                            } else {
                                const country = countriesList.find(c => c.iso_code === isoCode);
                                if (country) {
                                    const capitalCoords = {
                                        'ID': [-6.2088, 106.8456],
                                        'SG': [1.3521, 103.8198],
                                        'CN': [39.9042, 116.4074],
                                        'US': [38.9072, -77.0369],
                                        'NL': [52.3676, 4.9041],
                                        'JP': [35.6762, 139.6503],
                                        'DE': [52.5200, 13.4050],
                                        'AU': [-35.2809, 149.1300],
                                        'GB': [51.5074, -0.1278],
                                        'IN': [28.6139, 77.2090],
                                        'MY': [3.1390, 101.6869],
                                        'KR': [37.5665, 126.9780],
                                    };
                                    const coords = capitalCoords[isoCode];
                                    if (coords) {
                                        leafletMap.setView(coords, 5);
                                    }
                                }
                            }
                        }
                    });
            }

            // --- COUNTRY ANALYTICS LOGIC (TAB 2) ---

            function loadCountryAnalytics(countryCode) {
                // Ensure dropdown matches selection
                document.getElementById('countrySelect').value = countryCode;
                currentCountryCode = countryCode;

                const grid = document.getElementById('countryDashboardGrid');
                const loader = document.getElementById('analyticsLoader');

                grid.classList.add('d-none');
                loader.classList.remove('d-none');

                // Get watchlist status
                const country = countriesList.find(c => c.iso_code === countryCode);
                if (country) {
                    const icon = document.getElementById('watchlistIcon');
                    if (country.is_watchlist) {
                        icon.className = 'fa-solid fa-heart text-danger fs-5';
                    } else {
                        icon.className = 'fa-regular fa-heart text-danger fs-5';
                    }
                }

                // Call internal REST APIs in parallel
                Promise.all([
                    fetch(`/api/risk?country_code=${countryCode}`).then(res => res.json()),
                    fetch(`/api/news?q=${country ? country.name : ''}`).then(res => res.json())
                ])
                .then(([riskRes, newsRes]) => {
                    if (riskRes.status === 'success') {
                        currentCountryData = riskRes.data;
                        renderRiskEngine(riskRes.data);
                        renderMacroeconomicsChart();
                    }
                    if (newsRes.status === 'success') {
                        renderNewsIntelligence(newsRes);
                    }

                    // Render Basic Profile info
                    if (country) {
                        document.getElementById('countryFlag').src = country.flag_url || 'https://flagcdn.com/w320/id.png';
                        document.getElementById('countryNameDisplay').textContent = country.name;
                        document.getElementById('countryRegionDisplay').textContent = (country.region + ' / ' + country.subregion).toUpperCase();
                        document.getElementById('countryCapital').textContent = country.capital || 'N/A';
                        document.getElementById('countryCurrency').textContent = `${country.currency_name} (${country.currency_code})`;
                        document.getElementById('countryIncomeLevel').textContent = country.income_level || 'N/A';
                        document.getElementById('countryLanguages').textContent = country.languages.join(', ') || 'N/A';
                    }

                    loader.classList.add('d-none');
                    grid.classList.remove('d-none');
                })
                .catch(err => {
                    console.error("Error loading analytics:", err);
                    loader.classList.add('d-none');
                });
            }

            function renderRiskEngine(data) {
                // Update total gauge
                const total = Math.round(data.scores.total);
                document.getElementById('riskTotalValue').textContent = total;
                
                const levelDisplay = document.getElementById('riskLevelDisplay');
                levelDisplay.textContent = data.risk_level;
                
                // Colorize level Display
                if (data.risk_level === 'Low Risk') {
                    levelDisplay.className = 'risk-gauge-label text-success';
                } else if (data.risk_level === 'Medium Risk') {
                    levelDisplay.className = 'risk-gauge-label text-warning';
                } else {
                    levelDisplay.className = 'risk-gauge-label text-danger';
                }

                // Update sub-metrics
                document.getElementById('riskWeatherVal').textContent = `${Math.round(data.scores.weather)}%`;
                document.getElementById('riskWeatherBar').style.width = `${data.scores.weather}%`;
                
                document.getElementById('riskInflationVal').textContent = `${Math.round(data.scores.inflation)}%`;
                document.getElementById('riskInflationBar').style.width = `${data.scores.inflation}%`;
                
                document.getElementById('riskPoliticalVal').textContent = `${Math.round(data.scores.political)}%`;
                document.getElementById('riskPoliticalBar').style.width = `${data.scores.political}%`;
                
                document.getElementById('riskCurrencyVal').textContent = `${Math.round(data.scores.currency)}%`;
                document.getElementById('riskCurrencyBar').style.width = `${data.scores.currency}%`;

                // Update Chart.js Historical Risk Scores
                renderHistoricalRiskChart(data.history);
            }

            function renderHistoricalRiskChart(history) {
                const ctx = document.getElementById('historicalRiskChart').getContext('2d');
                
                const labels = history.map(h => {
                    const date = new Date(h.calculated_at);
                    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                });
                const dataValues = history.map(h => h.total_score);

                if (historicalRiskChart) {
                    historicalRiskChart.destroy();
                }

                historicalRiskChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Total Risk Score',
                            data: dataValues,
                            borderColor: '#38bdf8',
                            backgroundColor: 'rgba(56, 189, 248, 0.15)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.3,
                            pointBackgroundColor: '#38bdf8',
                            pointRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                min: 0,
                                max: 100,
                                grid: { color: 'rgba(255, 255, 255, 0.05)' },
                                ticks: { color: '#94a3b8' }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { color: '#94a3b8' }
                            }
                        }
                    }
                });
            }

            // Switching macroeconomics indicators display
            function switchMacroMetric(metric) {
                currentMacroMetric = metric;
                
                // Toggle active class in button group
                const btnGroup = document.querySelector('.btn-group');
                const buttons = btnGroup.querySelectorAll('button');
                buttons.forEach(btn => btn.classList.remove('active'));
                
                // Set active target
                const activeIndex = metric === 'gdp' ? 0 : (metric === 'inflation' ? 1 : (metric === 'trade' ? 2 : 3));
                buttons[activeIndex].classList.add('active');

                renderMacroeconomicsChart();
            }

            function renderMacroeconomicsChart() {
                if (!currentCountryData || !currentCountryData.macro) return;
                
                const ctx = document.getElementById('macroeconomicsChart').getContext('2d');
                const macro = currentCountryData.macro;
                let chartType = 'line';
                let datasets = [];
                let labels = [];

                if (currentMacroMetric === 'gdp') {
                    labels = macro.gdp.map(d => d.year);
                    datasets = [{
                        label: 'Nominal GDP (Miliar USD)',
                        data: macro.gdp.map(d => d.value / 1e9),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.2
                    }];
                } else if (currentMacroMetric === 'inflation') {
                    labels = macro.inflation.map(d => d.year);
                    datasets = [{
                        label: 'Laju Inflasi Tahunan (%)',
                        data: macro.inflation.map(d => d.value),
                        borderColor: '#fbbf24',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.3
                    }];
                } else if (currentMacroMetric === 'trade') {
                    labels = macro.exports_gdp.map(d => d.year);
                    datasets = [
                        {
                            label: 'Ekspor (% GDP)',
                            data: macro.exports_gdp.map(d => d.value),
                            borderColor: '#38bdf8',
                            backgroundColor: 'transparent',
                            borderWidth: 2,
                            tension: 0.1
                        },
                        {
                            label: 'Impor (% GDP)',
                            data: macro.imports_gdp.map(d => d.value),
                            borderColor: '#ef4444',
                            backgroundColor: 'transparent',
                            borderWidth: 2,
                            tension: 0.1
                        }
                    ];
                } else if (currentMacroMetric === 'population') {
                    labels = macro.population.map(d => d.year);
                    datasets = [{
                        label: 'Total Populasi (Juta)',
                        data: macro.population.map(d => d.value / 1e6),
                        borderColor: '#a855f7',
                        backgroundColor: 'rgba(168, 85, 247, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.1
                    }];
                }

                if (macroeconomicsChart) {
                    macroeconomicsChart.destroy();
                }

                macroeconomicsChart = new Chart(ctx, {
                    type: chartType,
                    data: {
                        labels: labels,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                labels: { color: '#f8fafc' }
                            }
                        },
                        scales: {
                            y: {
                                grid: { color: 'rgba(255, 255, 255, 0.05)' },
                                ticks: { color: '#94a3b8' }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { color: '#94a3b8' }
                            }
                        }
                    }
                });
            }

            function renderNewsIntelligence(newsRes) {
                // Update average sentiment & stats
                const stats = newsRes.stats;
                document.getElementById('avgSentimentScore').textContent = stats.average_sentiment_score;
                
                const ratios = stats.ratios;
                document.getElementById('sentimentRatiosText').textContent = `Pos: ${ratios.positive}% | Neu: ${ratios.neutral}% | Neg: ${ratios.negative}%`;
                
                document.getElementById('ratioBarPos').style.width = `${ratios.positive}%`;
                document.getElementById('ratioBarNeu').style.width = `${ratios.neutral}%`;
                document.getElementById('ratioBarNeg').style.width = `${ratios.negative}%`;

                // Render article items
                const list = document.getElementById('newsListContainer');
                list.innerHTML = '';

                if (newsRes.data.length === 0) {
                    list.innerHTML = `<span class="text-secondary small italic text-center d-block py-4">Tidak ada berita logistik relevan untuk negara ini saat ini.</span>`;
                    return;
                }

                newsRes.data.forEach(item => {
                    let sentimentBadgeClass = 'bg-sentiment-neu';
                    if (item.sentiment === 'Positive') sentimentBadgeClass = 'bg-success';
                    if (item.sentiment === 'Negative') sentimentBadgeClass = 'bg-danger';

                    const newsHtml = `
                        <div class="p-3 mb-3 bg-secondary-subtle border border-secondary rounded-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge ${sentimentBadgeClass} text-white px-2 py-1 rounded" style="font-size:0.7rem;">Sentimen: ${item.sentiment} (${item.sentiment_score})</span>
                                <span class="text-secondary small" style="font-size:0.7rem;">${item.source} • ${new Date(item.published_at).toLocaleDateString()}</span>
                            </div>
                            <h6 class="text-white fw-bold mb-1"><a href="${item.url}" target="_blank" class="hover:text-info text-decoration-none">${item.title}</a></h6>
                            <p class="text-secondary mb-0 small" style="line-height: 1.4;">${item.description}</p>
                        </div>
                    `;
                    list.insertAdjacentHTML('beforeend', newsHtml);
                });
            }

            // Watchlist toggler
            function toggleWatchlist() {
                const country = countriesList.find(c => c.iso_code === currentCountryCode);
                if (!country) return;

                fetch('/api/watchlist/toggle', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ country_id: country.id })
                })
                .then(res => res.json())
                .then(response => {
                    if (response.status === 'success') {
                        // Mutate state in JS memory
                        country.is_watchlist = response.action === 'added';
                        
                        // Update watchlist buttons & toggle heart icon
                        renderWatchlistButtons();
                        const icon = document.getElementById('watchlistIcon');
                        if (country.is_watchlist) {
                            icon.className = 'fa-solid fa-heart text-danger fs-5';
                        } else {
                            icon.className = 'fa-regular fa-heart text-danger fs-5';
                        }
                    } else if (response.status === 'error') {
                        alert(response.message);
                    }
                })
                .catch(err => console.error("Error toggling watchlist:", err));
            }

            // --- CURRENCY TAB LOGIC (TAB 3) ---

            function renderCurrencyTable(baseCurrency) {
                const tbody = document.getElementById('currencyTableBody');
                tbody.innerHTML = '';

                const targetCurrencies = [
                    { name: 'Rupiah Indonesia', code: 'IDR', icon: 'fa-rupiah-sign' },
                    { name: 'Euro', code: 'EUR', icon: 'fa-euro-sign' },
                    { name: 'Yuan Tiongkok', code: 'CNY', icon: 'fa-yen-sign' },
                    { name: 'Yen Jepang', code: 'JPY', icon: 'fa-yen-sign' },
                    { name: 'Poundsterling Inggris', code: 'GBP', icon: 'fa-sterling-sign' },
                    { name: 'Dollar Singapura', code: 'SGD', icon: 'fa-dollar-sign' },
                    { name: 'Dollar Australia', code: 'AUD', icon: 'fa-dollar-sign' }
                ];

                targetCurrencies.forEach(curr => {
                    // Skip if target is equal to base currency
                    if (curr.code === baseCurrency) return;

                    const rate = globalCurrencyRates[curr.code];
                    if (!rate) return;

                    // Simulate slight drift arrows depending on rate value
                    const driftDir = (rate % 2 > 1.0) ? 'up' : 'down';
                    const driftHtml = driftDir === 'up' 
                        ? `<span class="text-success"><i class="fa-solid fa-caret-up me-1"></i>+0.12%</span>`
                        : `<span class="text-danger"><i class="fa-solid fa-caret-down me-1"></i>-0.08%</span>`;

                    const row = `
                        <tr>
                            <td><i class="fa-solid ${curr.icon} me-2 text-info"></i>${curr.name}</td>
                            <td class="fw-bold">${curr.code}</td>
                            <td class="text-end fw-bold text-white">${rate.toLocaleString(undefined, { maximumFractionDigits: 2 })}</td>
                            <td class="text-end">${driftHtml}</td>
                        </tr>
                    `;
                    tbody.insertAdjacentHTML('beforeend', row);
                });
            }

            function updateCurrencyTrendChart() {
                const selectedCurrency = document.getElementById('chartCurrencySelect').value;
                const trendData = globalCurrencyTrends[selectedCurrency];
                if (!trendData) return;

                const ctx = document.getElementById('currencyTrendChart').getContext('2d');
                const labels = trendData.map(d => d.date);
                const values = trendData.map(d => d.rate);

                // Add styling gradient background
                const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                gradient.addColorStop(0, 'rgba(56, 189, 248, 0.3)');
                gradient.addColorStop(1, 'rgba(56, 189, 248, 0.0)');

                if (currencyTrendChart) {
                    currencyTrendChart.destroy();
                }

                currencyTrendChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: `Nilai Tukar 1 USD ke ${selectedCurrency}`,
                            data: values,
                            borderColor: '#38bdf8',
                            backgroundColor: gradient,
                            borderWidth: 2,
                            fill: true,
                            tension: 0.15,
                            pointRadius: 3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                grid: { color: 'rgba(255, 255, 255, 0.05)' },
                                ticks: { color: '#94a3b8' }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { color: '#94a3b8' }
                            }
                        }
                    }
                });
            }

            // --- TAB 4: COMPARISON ENGINE ---

            function processComparison() {
                const codeA = document.getElementById('compareSelectA').value;
                const codeB = document.getElementById('compareSelectB').value;

                if (!codeA || !codeB) {
                    alert('Mohon pilih kedua negara terlebih dahulu!');
                    return;
                }
                if (codeA === codeB) {
                    alert('Tidak bisa membandingkan negara yang sama!');
                    return;
                }

                const loader = document.getElementById('compareLoader');
                const dash = document.getElementById('comparisonDashboard');

                dash.classList.add('d-none');
                loader.classList.remove('d-none');

                Promise.all([
                    fetch(`/api/risk?country_code=${codeA}`).then(res => res.json()),
                    fetch(`/api/risk?country_code=${codeB}`).then(res => res.json())
                ])
                .then(([resA, resB]) => {
                    if (resA.status === 'success' && resB.status === 'success') {
                        renderComparisonDashboard(resA.data, resB.data);
                    }
                    loader.classList.add('d-none');
                    dash.classList.remove('d-none');
                })
                .catch(err => {
                    console.error("Comparison failed:", err);
                    loader.classList.add('d-none');
                });
            }

            function renderComparisonDashboard(dataA, dataB) {
                const countryA = countriesList.find(c => c.iso_code === dataA.country_code);
                const countryB = countriesList.find(c => c.iso_code === dataB.country_code);

                // Update Header Table names
                document.getElementById('compareTableHeadA').textContent = dataA.country_name;
                document.getElementById('compareTableHeadB').textContent = dataB.country_name;

                // Flags & profiles
                document.getElementById('compareFlagA').src = countryA ? countryA.flag_url : '';
                document.getElementById('compareFlagB').src = countryB ? countryB.flag_url : '';

                document.getElementById('compareNameA').textContent = dataA.country_name;
                document.getElementById('compareNameB').textContent = dataB.country_name;

                // Risk level badges
                const badgeA = document.getElementById('compareLevelBadgeA');
                badgeA.textContent = dataA.risk_level;
                badgeA.className = `badge ${dataA.risk_level === 'Low Risk' ? 'bg-success' : (dataA.risk_level === 'Medium Risk' ? 'bg-warning' : 'bg-danger')}`;
                
                const badgeB = document.getElementById('compareLevelBadgeB');
                badgeB.textContent = dataB.risk_level;
                badgeB.className = `badge ${dataB.risk_level === 'Low Risk' ? 'bg-success' : (dataB.risk_level === 'Medium Risk' ? 'bg-warning' : 'bg-danger')}`;

                // Risk total value
                document.getElementById('compareTotalRiskA').textContent = Math.round(dataA.scores.total);
                document.getElementById('compareTotalRiskB').textContent = Math.round(dataB.scores.total);

                // Calculated diff
                const diff = Math.round(dataA.scores.total - dataB.scores.total);
                const diffVal = document.getElementById('compareRiskDiff');
                if (diff > 0) {
                    diffVal.innerHTML = `<span class="text-danger"><i class="fa-solid fa-arrow-up-long me-1"></i>+${diff} (A lebih rentan)</span>`;
                } else if (diff < 0) {
                    diffVal.innerHTML = `<span class="text-success"><i class="fa-solid fa-arrow-down-long me-1"></i>${diff} (B lebih rentan)</span>`;
                } else {
                    diffVal.innerHTML = `<span class="text-secondary">0 (Setara)</span>`;
                }

                // Table macro stats
                document.getElementById('compareMacroIncomeA').textContent = countryA ? countryA.income_level : '-';
                document.getElementById('compareMacroIncomeB').textContent = countryB ? countryB.income_level : '-';

                // Handle World Bank indicators
                if (dataA.macro && dataB.macro) {
                    const latestGdpA = dataA.macro.gdp && dataA.macro.gdp.length ? endVal(dataA.macro.gdp) / 1e9 : null;
                    const latestGdpB = dataB.macro.gdp && dataB.macro.gdp.length ? endVal(dataB.macro.gdp) / 1e9 : null;
                    document.getElementById('compareMacroGdpA').textContent = latestGdpA ? `$${latestGdpA.toLocaleString(undefined, {maximumFractionDigits:1})} B` : '-';
                    document.getElementById('compareMacroGdpB').textContent = latestGdpB ? `$${latestGdpB.toLocaleString(undefined, {maximumFractionDigits:1})} B` : '-';

                    const latestInflationA = dataA.macro.inflation && dataA.macro.inflation.length ? endVal(dataA.macro.inflation) : null;
                    const latestInflationB = dataB.macro.inflation && dataB.macro.inflation.length ? endVal(dataB.macro.inflation) : null;
                    document.getElementById('compareMacroInflationA').textContent = latestInflationA ? `${latestInflationA.toFixed(2)}%` : '-';
                    document.getElementById('compareMacroInflationB').textContent = latestInflationB ? `${latestInflationB.toFixed(2)}%` : '-';

                    const expA = dataA.macro.exports_gdp && dataA.macro.exports_gdp.length ? endVal(dataA.macro.exports_gdp) : null;
                    const expB = dataB.macro.exports_gdp && dataB.macro.exports_gdp.length ? endVal(dataB.macro.exports_gdp) : null;
                    document.getElementById('compareMacroExportsA').textContent = expA ? `${expA.toFixed(1)}%` : '-';
                    document.getElementById('compareMacroExportsB').textContent = expB ? `${expB.toFixed(1)}%` : '-';

                    const impA = dataA.macro.imports_gdp && dataA.macro.imports_gdp.length ? endVal(dataA.macro.imports_gdp) : null;
                    const impB = dataB.macro.imports_gdp && dataB.macro.imports_gdp.length ? endVal(dataB.macro.imports_gdp) : null;
                    document.getElementById('compareMacroImportsA').textContent = impA ? `${impA.toFixed(1)}%` : '-';
                    document.getElementById('compareMacroImportsB').textContent = impB ? `${impB.toFixed(1)}%` : '-';
                }

                // Sub-risk radar/bar comparisons chart
                renderCompareMetricsChart(dataA, dataB);
            }

            function endVal(arr) {
                return arr[arr.length - 1].value;
            }

            function renderCompareMetricsChart(dataA, dataB) {
                const ctx = document.getElementById('compareMetricsChart').getContext('2d');

                const categories = ['Weather', 'Inflation', 'Political/News', 'Currency'];
                const scoresA = [dataA.scores.weather, dataA.scores.inflation, dataA.scores.political, dataA.scores.currency];
                const scoresB = [dataB.scores.weather, dataB.scores.inflation, dataB.scores.political, dataB.scores.currency];

                if (compareMetricsChart) {
                    compareMetricsChart.destroy();
                }

                compareMetricsChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: categories,
                        datasets: [
                            {
                                label: dataA.country_name,
                                data: scoresA,
                                backgroundColor: 'rgba(56, 189, 248, 0.8)',
                                borderColor: '#38bdf8',
                                borderWidth: 1
                            },
                            {
                                label: dataB.country_name,
                                data: scoresB,
                                backgroundColor: 'rgba(239, 68, 68, 0.8)',
                                borderColor: '#ef4444',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { labels: { color: '#f8fafc' } }
                        },
                        scales: {
                            y: {
                                min: 0,
                                max: 100,
                                grid: { color: 'rgba(255, 255, 255, 0.05)' },
                                ticks: { color: '#94a3b8' }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { color: '#94a3b8' }
                            }
                        }
                    }
                });
            }
        </script>
    @endpush
</x-app-layout>
