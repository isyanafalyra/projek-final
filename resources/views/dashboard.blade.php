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
            
            /* Custom styled elements for sidebar sections */
            .tab-pane {
                display: none;
            }
            .tab-pane.active {
                display: block;
            }
            
            /* Persistent maps heights */
            #map-overview {
                height: 450px;
                width: 100%;
                border-radius: 12px;
                border: 1px solid var(--card-border);
            }
            #map-weather, #map-ports {
                height: 550px;
                width: 100%;
                border-radius: 12px;
                border: 1px solid var(--card-border);
            }

            /* ===== Custom Searchable Country Dropdown ===== */
            .country-search-wrapper {
                position: relative;
                min-width: 240px;
            }
            .country-search-trigger {
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 6px 14px;
                background: rgba(255,255,255,0.06);
                border: 1px solid rgba(56,189,248,0.3);
                border-radius: 10px;
                cursor: pointer;
                color: #94a3b8;
                font-size: 0.85rem;
                transition: all 0.2s ease;
                user-select: none;
                min-width: 220px;
            }
            .country-search-trigger:hover {
                border-color: rgba(56,189,248,0.6);
                background: rgba(56,189,248,0.08);
                color: #e2e8f0;
            }
            .country-search-trigger.active {
                border-color: #38bdf8;
                background: rgba(56,189,248,0.1);
                color: #e2e8f0;
            }
            .country-search-trigger .trigger-flag {
                font-size: 1rem;
            }
            .country-search-trigger .trigger-label {
                flex: 1;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            .country-search-trigger .trigger-arrow {
                font-size: 0.7rem;
                transition: transform 0.2s ease;
                color: #38bdf8;
            }
            .country-search-trigger.active .trigger-arrow {
                transform: rotate(180deg);
            }
            .country-dropdown-panel {
                display: none;
                position: absolute;
                top: calc(100% + 6px);
                left: 0;
                right: 0;
                min-width: 280px;
                background: #1a2744;
                border: 1px solid rgba(56,189,248,0.35);
                border-radius: 12px;
                box-shadow: 0 16px 40px rgba(0,0,0,0.5);
                z-index: 9999;
                overflow: hidden;
                animation: dropFadeIn 0.18s ease;
            }
            .country-dropdown-panel.open {
                display: block;
            }
            @keyframes dropFadeIn {
                from { opacity: 0; transform: translateY(-6px); }
                to   { opacity: 1; transform: translateY(0); }
            }
            .country-dropdown-search {
                padding: 10px 12px;
                border-bottom: 1px solid rgba(255,255,255,0.08);
            }
            .country-dropdown-search input {
                width: 100%;
                background: rgba(255,255,255,0.06);
                border: 1px solid rgba(255,255,255,0.1);
                border-radius: 8px;
                padding: 6px 12px 6px 32px;
                color: #e2e8f0;
                font-size: 0.82rem;
                outline: none;
                transition: border-color 0.2s;
            }
            .country-dropdown-search input:focus {
                border-color: #38bdf8;
            }
            .country-dropdown-search .search-icon {
                position: absolute;
                left: 22px;
                top: 50%;
                transform: translateY(-50%);
                color: #64748b;
                font-size: 0.75rem;
                pointer-events: none;
            }
            .country-dropdown-search-wrap {
                position: relative;
            }
            .country-dropdown-list {
                max-height: 240px;
                overflow-y: auto;
                padding: 4px 0;
            }
            .country-dropdown-list::-webkit-scrollbar {
                width: 4px;
            }
            .country-dropdown-list::-webkit-scrollbar-track {
                background: transparent;
            }
            .country-dropdown-list::-webkit-scrollbar-thumb {
                background: rgba(56,189,248,0.3);
                border-radius: 4px;
            }
            .country-dropdown-item {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 8px 14px;
                cursor: pointer;
                transition: background 0.15s ease;
                border-radius: 0;
            }
            .country-dropdown-item:hover {
                background: rgba(56,189,248,0.12);
            }
            .country-dropdown-item.selected {
                background: rgba(56,189,248,0.18);
                color: #38bdf8;
            }
            .country-dropdown-item .item-flag {
                font-size: 1.1rem;
                flex-shrink: 0;
            }
            .country-dropdown-item .item-name {
                flex: 1;
                font-size: 0.82rem;
                color: #cbd5e1;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            .country-dropdown-item.selected .item-name {
                color: #38bdf8;
                font-weight: 600;
            }
            .country-dropdown-item .item-code {
                font-size: 0.72rem;
                color: #64748b;
                font-family: monospace;
                background: rgba(255,255,255,0.05);
                padding: 2px 6px;
                border-radius: 4px;
            }
            .country-dropdown-empty {
                text-align: center;
                padding: 20px;
                color: #64748b;
                font-size: 0.82rem;
            }
        </style>
    @endpush

    <!-- Header Section -->
    <div class="row align-items-center mb-4 fade-slide-up">
        <div class="col-md-7">
            <h2 class="text-white fw-bold mb-0">Global Supply Chain Intelligence</h2>
            <p class="text-secondary mb-0">Platform Monitoring Risiko Rantai Pasok Berbasis Multi-API & Data Science</p>
        </div>
        <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex align-items-center justify-content-md-end gap-3">
            <div class="d-flex align-items-center gap-2">
                <span class="live-dot"></span>
                <span class="text-secondary small">Sistem Sinkron Aktif</span>
            </div>
            <span class="badge bg-secondary-subtle text-secondary border border-secondary px-3 py-2 rounded-pill d-inline-flex align-items-center gap-2">
                <i class="fa-solid fa-clock text-info"></i>
                <span id="headerLastUpdated">Memperbarui...</span>
            </span>
            <button onclick="refreshDashboardData()" class="btn btn-sm btn-secondary-custom" title="Refresh Data">
                <i class="fa-solid fa-rotate"></i>
            </button>
        </div>
    </div>

    <!-- SHARED COUNTRY SELECTOR HEADER (Visible only for country-specific tabs) -->
    <div id="sharedCountrySelector" class="glass-card p-4 mb-4 d-none">
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

    <!-- MAIN PAGE TABS -->
    <div class="tab-content" id="sidebarDashboardContent">
        
        <!-- TAB 1: Overview / Landing Page -->
        <div class="tab-pane fade show active" id="overview-content">
            
            <!-- Quick Summary Statistics Cards -->
            <div class="row mb-4">
                <!-- Card 1: Monitored Countries -->
                <div class="col-lg-3 col-sm-6 mb-3 mb-lg-0">
                    <div class="status-card status-card-blue">
                        <div class="status-card-icon-wrapper">
                            <i class="fa-solid fa-globe"></i>
                        </div>
                        <div class="status-card-content">
                            <div class="status-card-title">Monitored Countries</div>
                            <div class="status-card-value" id="statTotalCountries">0</div>
                            <div class="status-card-subtext" id="statSupportedCurrencies">0 Mata Uang</div>
                        </div>
                    </div>
                </div>
                <!-- Card 2: High Risk Countries -->
                <div class="col-lg-3 col-sm-6 mb-3 mb-lg-0">
                    <div class="status-card status-card-red">
                        <div class="status-card-icon-wrapper">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <div class="status-card-content">
                            <div class="status-card-title">High Risk Countries</div>
                            <div class="status-card-value text-danger" id="statHighRiskCount">0</div>
                            <div class="status-card-subtext">Tingkat Kerawanan Global</div>
                        </div>
                    </div>
                </div>
                <!-- Card 3: Global Active Ports -->
                <div class="col-lg-3 col-sm-6 mb-3 mb-lg-0">
                    <div class="status-card status-card-yellow">
                        <div class="status-card-icon-wrapper">
                            <i class="fa-solid fa-ship"></i>
                        </div>
                        <div class="status-card-content">
                            <div class="status-card-title">Global Active Ports</div>
                            <div class="status-card-value" id="statGlobalPortsCount">0</div>
                            <div class="status-card-subtext">Pelabuhan Kargo Aktif</div>
                        </div>
                    </div>
                </div>
                <!-- Card 4: Weather Alerts -->
                <div class="col-lg-3 col-sm-6">
                    <div class="status-card status-card-green">
                        <div class="status-card-icon-wrapper">
                            <i class="fa-solid fa-cloud-bolt"></i>
                        </div>
                        <div class="status-card-content">
                            <div class="status-card-title">Weather Alerts</div>
                            <div class="status-card-value text-warning" id="statWeatherAlerts">0</div>
                            <div class="status-card-subtext">Pelabuhan Aktif Terdampak</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Global Risk Distribution & Map -->
            <div class="row mb-4">
                <!-- Left: Risk Overview distribution -->
                <div class="col-xl-4 mb-4 mb-xl-0">
                    <div class="glass-card p-4 h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="overview-section-label">Risk Analytics</div>
                            <h5 class="text-white fw-bold mb-3">Global Risk Overview</h5>
                            
                            <div class="avg-score-box mb-4">
                                <div class="text-secondary small text-uppercase fw-bold mb-1" style="font-size: 0.65rem; letter-spacing: 0.5px;">Avg Global Risk Score</div>
                                <div class="avg-score-number text-info" id="overviewAvgRiskScore">0</div>
                            </div>

                            <div class="risk-distribution-list">
                                <div class="risk-dist-row">
                                    <span class="risk-dist-label text-success"><i class="fa-solid fa-circle-check me-1"></i>Low Risk</span>
                                    <div class="risk-dist-bar-bg">
                                        <div class="risk-dist-bar-fill bg-success" id="barLowRisk" style="width: 0%"></div>
                                    </div>
                                    <span class="risk-dist-count text-success" id="countLowRisk">0</span>
                                </div>
                                <div class="risk-dist-row">
                                    <span class="risk-dist-label text-warning"><i class="fa-solid fa-circle-exclamation me-1"></i>Medium</span>
                                    <div class="risk-dist-bar-bg">
                                        <div class="risk-dist-bar-fill bg-warning" id="barMediumRisk" style="width: 0%"></div>
                                    </div>
                                    <span class="risk-dist-count text-warning" id="countMediumRisk">0</span>
                                </div>
                                <div class="risk-dist-row">
                                    <span class="risk-dist-label text-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i>High Risk</span>
                                    <div class="risk-dist-bar-bg">
                                        <div class="risk-dist-bar-fill bg-danger" id="barHighRisk" style="width: 0%"></div>
                                    </div>
                                    <span class="risk-dist-count text-danger" id="countHighRisk">0</span>
                                </div>
                            </div>
                        </div>

                        <div class="border-top border-secondary pt-3 mt-3">
                            <span class="text-secondary small d-block" style="line-height: 1.4;">Kalkulasi tingkat kerawanan berdasarkan agregasi bobot multi-sektor.</span>
                        </div>
                    </div>
                </div>

                <!-- Right: Spatial intelligence main map -->
                <div class="col-xl-8">
                    <div class="glass-card p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <div class="overview-section-label">Spatial Intelligence</div>
                                <h5 class="text-white fw-bold mb-0">Global Intelligence Map</h5>
                            </div>
                            <div class="small text-secondary"><i class="fa-solid fa-location-crosshairs text-info me-1"></i>Peta Interaktif Supply Chain</div>
                        </div>
                        <div id="map-main-overview"></div>
                    </div>
                </div>
            </div>

            <!-- Weather & Port Intelligence -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="glass-card p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <div class="overview-section-label">Maritime Logistics</div>
                                <h5 class="text-white fw-bold mb-0">Weather & Port Intelligence</h5>
                            </div>
                            <span class="text-secondary small d-none d-md-inline"><i class="fa-solid fa-satellite-dish text-info me-1"></i>Koneksi Satelit Cuaca Aktif</span>
                        </div>
                        <div class="row" id="overviewWeatherPortContainer">
                            <!-- Rendered by JavaScript dynamically -->
                            <div class="col-12 text-center py-4">
                                <span class="spinner-border spinner-border-sm text-info" role="status"></span>
                                <span class="text-secondary small ms-2">Menghubungkan satelit cuaca pelabuhan...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Economic indicators & currency exchange -->
            <div class="row mb-4">
                <!-- Macroeconomics Chart -->
                <div class="col-lg-8 mb-4 mb-lg-0">
                    <div class="glass-card p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <div class="overview-section-label">Macroeconomic Indicators</div>
                                <h5 class="text-white fw-bold mb-0">Economic Intelligence</h5>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-secondary small" style="font-size: 0.75rem;">Negara:</span>
                                <select id="overviewEconomicCountrySelect" class="form-select form-select-sm bg-dark text-white border-secondary" style="font-size: 0.8rem; border-radius: 6px; width: 140px;">
                                    <option value="" disabled selected>Memuat...</option>
                                </select>
                            </div>
                        </div>
                        <div style="height: 250px; position: relative;">
                            <canvas id="overviewMacroChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Currency exchange rates table -->
                <div class="col-lg-4">
                    <div class="glass-card p-4 h-100">
                        <div class="overview-section-label">Foreign Exchange Rates</div>
                        <h5 class="text-white fw-bold mb-3">Currency Impact</h5>
                        <div class="table-responsive">
                            <table class="table table-dark table-hover border-secondary align-middle mb-0" style="font-size: 0.8rem;">
                                <thead>
                                    <tr>
                                        <th>Mata Uang</th>
                                        <th class="text-end">Nilai Tukar (USD)</th>
                                    </tr>
                                </thead>
                                <tbody id="overviewCurrencyBody">
                                    <tr>
                                        <td colspan="2" class="text-center py-4">
                                            <span class="spinner-border spinner-border-sm text-info"></span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- News & Top Risks -->
            <div class="row">
                <!-- news feed summary -->
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="glass-card p-4 h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <div class="overview-section-label">Sentiment Analysis</div>
                                    <h5 class="text-white fw-bold mb-0">News Intelligence</h5>
                                </div>
                                <span class="badge bg-secondary text-white px-2 py-1 rounded" style="font-size: 0.7rem;" id="overviewNewsSentimentRatio">Rasio Sentimen</span>
                            </div>
                            
                            <!-- sentiment ratio visual bar -->
                            <div class="sentiment-ratio-bar mb-3">
                                <div class="bg-sentiment-pos" id="overviewNewsBarPos" style="width: 0%"></div>
                                <div class="bg-sentiment-neu" id="overviewNewsBarNeu" style="width: 0%"></div>
                                <div class="bg-sentiment-neg" id="overviewNewsBarNeg" style="width: 0%"></div>
                            </div>

                            <div id="overviewNewsList" class="news-scroll-container" style="max-height: 250px;">
                                <div class="text-center py-4">
                                    <span class="spinner-border spinner-border-sm text-info"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- top risk countries list -->
                <div class="col-lg-6">
                    <div class="glass-card p-4 h-100">
                        <div class="overview-section-label">Risk Scoring Engine</div>
                        <h5 class="text-white fw-bold mb-3">Top Risk Countries</h5>
                        <div class="table-responsive">
                            <table class="table table-dark table-hover border-secondary align-middle mb-0" style="font-size: 0.8rem;">
                                <thead>
                                    <tr>
                                        <th>Negara</th>
                                        <th class="text-center">Skor</th>
                                        <th class="text-end">Sub-Risiko Utama</th>
                                    </tr>
                                </thead>
                                <tbody id="overviewTopRiskBody">
                                    <tr>
                                        <td colspan="3" class="text-center py-4">
                                            <span class="spinner-border spinner-border-sm text-info"></span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- TAB: My Watchlist -->
        <div class="tab-pane fade" id="watchlist-content">
            <!-- Watchlist Detailed Overview with Leaflet Map -->
            <div class="glass-card p-4">
                <h5 class="text-white fw-bold mb-4"><i class="fa-solid fa-star text-warning me-2"></i>Daftar Pengawasan Risiko Rantai Pasok</h5>
                <div class="row">
                    <!-- Map Widget on Left -->
                    <div class="col-lg-8 mb-4 mb-lg-0">
                        <div id="map-overview"></div>
                    </div>
                    <!-- Table Widget on Right -->
                    <div class="col-lg-4">
                        <p class="text-secondary small mb-3">Daftar negara pengawasan aktif. Klik penanda di peta atau tombol untuk membuka detail profil.</p>
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-dark table-hover border-secondary align-middle">
                                <thead>
                                    <tr>
                                        <th>Negara</th>
                                        <th class="text-center">Skor</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="overviewWatchlistBody">
                                    <!-- Dynamic rows via Javascript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: Global Country Profile (Instant rendering) -->
        <div class="tab-pane fade" id="country-profile-content">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10">
                    <div class="glass-card p-4">
                        <div class="d-flex align-items-center mb-4 pb-3 border-bottom border-secondary flex-wrap gap-3">
                            <img id="countryFlag" src="" alt="Flag" class="rounded border border-secondary" style="width: 90px; height: 55px; object-fit: cover;">
                            <div>
                                <h3 class="text-white fw-bold mb-0" id="countryNameDisplay">Pilih Negara</h3>
                                <span class="text-info small fw-bold text-uppercase" id="countryRegionDisplay">REGIONAL</span>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="text-secondary small fw-bold d-block mb-1">IBUKOTA</label>
                                <h5 class="text-white fw-medium mb-0" id="countryCapital">-</h5>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-secondary small fw-bold d-block mb-1">MATA UANG</label>
                                <h5 class="text-white fw-medium mb-0" id="countryCurrency">-</h5>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-secondary small fw-bold d-block mb-1">KELOMPOK PENDAPATAN (WORLD BANK)</label>
                                <h5 class="text-white fw-medium mb-0" id="countryIncomeLevel">-</h5>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-secondary small fw-bold d-block mb-1">BAHASA UTAMA</label>
                                <h5 class="text-white fw-medium mb-0" id="countryLanguages">-</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 3: Risk Scoring (Lazy loaded) -->
        <div class="tab-pane fade" id="risk-scoring-content">
            <!-- Loader -->
            <div id="riskLoader" class="text-center py-5 d-none">
                <div class="spinner-border text-info" style="width: 3rem; height: 3rem;" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5 class="text-secondary mt-3">Mengkalkulasi Analitik Risiko Negara...</h5>
            </div>

            <!-- Content Area -->
            <div id="riskContent" class="row">
                <div class="col-md-6 mb-4">
                    <div class="glass-card p-4 h-100 text-center">
                        <h5 class="text-white fw-bold mb-4 text-start"><i class="fa-solid fa-microchip text-info me-2"></i>Risk Scoring Engine</h5>
                        
                        <div class="risk-gauge-wrapper mb-4">
                            <div class="risk-gauge-circle">
                                <span class="risk-gauge-value text-white" id="riskTotalValue">0</span>
                                <span class="risk-gauge-label" id="riskLevelDisplay">N/A</span>
                            </div>
                        </div>
                        
                        <div class="text-start mt-4">
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

                <div class="col-md-6 mb-4">
                    <div class="glass-card p-4 h-100">
                        <h5 class="text-white fw-bold mb-3"><i class="fa-solid fa-timeline text-info me-2"></i>Riwayat Kalkulasi Risiko</h5>
                        <p class="text-secondary small mb-3">Tren skor risiko negara berdasarkan log kalkulasi logistik 5 sesi terakhir.</p>
                        <div style="height: 280px; position: relative;">
                            <canvas id="historicalRiskChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 4: Weather Monitor (Lazy loaded Map) -->
        <div class="tab-pane fade" id="weather-monitor-content">
            <div class="row">
                <div class="col-lg-9 mb-4">
                    <div class="glass-card p-3">
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                            <h5 class="text-white fw-bold mb-0">
                                <i class="fa-solid fa-cloud-sun-rain text-info me-2"></i>Peta Monitor Cuaca Maritim
                            </h5>
                            <div class="d-flex gap-2 align-items-center">
                                <!-- Custom Searchable Country Dropdown (Weather Map) -->
                                <div class="country-search-wrapper" id="weatherCountrySearchWrapper">
                                    <div class="country-search-trigger" id="weatherCountrySearchTrigger" title="Filter peta cuaca berdasarkan negara">
                                        <span id="weatherTriggerFlagContainer"><i class="fa-solid fa-globe text-info"></i></span>
                                        <span class="trigger-label" id="weatherCountryTriggerLabel">Pilih Negara...</span>
                                        <i class="fa-solid fa-chevron-down trigger-arrow"></i>
                                    </div>
                                    <div class="country-dropdown-panel" id="weatherCountryDropdownPanel">
                                        <div class="country-dropdown-search">
                                            <div class="country-dropdown-search-wrap">
                                                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                                                <input type="text" id="weatherCountryDropdownInput" placeholder="Cari negara..." autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="country-dropdown-list" id="weatherCountryDropdownList">
                                            <!-- populated by JS -->
                                        </div>
                                    </div>
                                </div>
                                <!-- Reset button -->
                                <button id="weatherMapResetBtn" class="btn btn-sm btn-outline-secondary" title="Reset tampilan peta cuaca" style="border-radius:8px; border-color:rgba(255,255,255,0.15);">
                                    <i class="fa-solid fa-rotate-left"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Weather Map Container -->
                        <div id="map-weather"></div>
                    </div>
                </div>
                
                <div class="col-lg-3">
                    <div class="glass-card p-4 mb-4">
                        <h5 class="text-white fw-bold mb-3"><i class="fa-solid fa-wind text-info me-2"></i>Status & Legenda</h5>
                        <p class="text-secondary small mb-3">Klik ikon pelabuhan di peta untuk memuat cuaca real-time langsung melalui integrasi API satelit Open-Meteo.</p>
                        
                        <hr class="border-secondary my-3">
                        
                        <div>
                            <label class="text-secondary d-block small fw-bold mb-2">STATUS EKSTREM</label>
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

        <!-- TAB 5: Currency Impact -->
        <div class="tab-pane fade" id="currency-impact-content">
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

        <!-- TAB 6: News Feed (Lazy loaded) -->
        <div class="tab-pane fade" id="news-feed-content">
            <!-- Loader -->
            <div id="newsLoader" class="text-center py-5 d-none">
                <div class="spinner-border text-info" style="width: 3rem; height: 3rem;" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5 class="text-secondary mt-3">Menganalisis Berita Logistik & Sentimen...</h5>
            </div>

            <!-- Content -->
            <div id="newsContent">
                <div class="glass-card p-4">
                    <h5 class="text-white fw-bold mb-2"><i class="fa-solid fa-brain text-info me-2"></i>News Intelligence (GNews API)</h5>
                    <p class="text-secondary small mb-3">Analisis sentimen leksikon (positif/negatif) berita terkait rantai pasok global.</p>
                    
                    <!-- Sentiment stats -->
                    <div class="d-flex gap-4 p-3 bg-secondary-subtle rounded-3 mb-4 border border-secondary align-items-center flex-wrap">
                        <div>
                            <h2 class="text-white fw-bold mb-0" id="avgSentimentScore">0</h2>
                            <span class="text-secondary small">Average Sentiment Score</span>
                        </div>
                        <div class="flex-grow-1" style="min-width: 250px;">
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

        <!-- TAB 7: Ports & Logistics (Dedicated Map) -->
        <div class="tab-pane fade" id="ports-logistics-content">
            <div class="row">
                <div class="col-lg-9 mb-4">
                    <div class="glass-card p-3">
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                            <h5 class="text-white fw-bold mb-0">
                                <i class="fa-solid fa-anchor text-info me-2"></i>Peta Pelabuhan Logistik & Cargo Utama
                            </h5>
                            <div class="d-flex gap-2 align-items-center">
                                <!-- Custom Searchable Country Dropdown -->
                                <div class="country-search-wrapper" id="countrySearchWrapper">
                                    <div class="country-search-trigger" id="countrySearchTrigger" title="Pilih negara untuk filter peta">
                                        <span id="triggerFlagContainer"><i class="fa-solid fa-globe text-info"></i></span>
                                        <span class="trigger-label" id="countryTriggerLabel">Pilih Negara...</span>
                                        <i class="fa-solid fa-chevron-down trigger-arrow"></i>
                                    </div>
                                    <div class="country-dropdown-panel" id="countryDropdownPanel">
                                        <div class="country-dropdown-search">
                                            <div class="country-dropdown-search-wrap">
                                                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                                                <input type="text" id="countryDropdownInput" placeholder="Cari negara..." autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="country-dropdown-list" id="countryDropdownList">
                                            <!-- populated by JS -->
                                        </div>
                                    </div>
                                </div>
                                <!-- Reset button -->
                                <button id="mapResetBtn" class="btn btn-sm btn-outline-secondary" title="Reset tampilan peta" style="border-radius:8px; border-color:rgba(255,255,255,0.15);">
                                    <i class="fa-solid fa-rotate-left"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Ports Map Container -->
                        <div id="map-ports"></div>
                    </div>
                </div>
                
                <!-- Active Country Info Panel -->
                <div class="glass-card p-4 mb-3" id="portCountryInfoPanel">
                    <h6 class="fw-bold mb-3" style="color: var(--accent-hover);"><i class="fa-solid fa-location-dot me-2"></i>Negara Terpilih</h6>
                    <div id="portCountryFlagWrap" class="d-flex align-items-center gap-2 mb-3">
                        <span class="text-secondary small">Pilih negara dari dropdown atau klik marker di peta.</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-secondary small fw-bold">PELABUHAN AKTIF</span>
                        <span class="fw-bold fs-5" id="portCountryActiveCount" style="color: var(--accent-color);">-</span>
                    </div>
                    <div id="portCountryPortList" class="mt-2" style="max-height:180px; overflow-y:auto;">
                        <!-- populated by JS -->
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="glass-card p-4 mb-4">
                        <h5 class="text-white fw-bold mb-3"><i class="fa-solid fa-map-location-dot text-info me-2"></i>Risiko Wilayah</h5>
                        <p class="text-secondary small mb-3">Legenda klasifikasi skor tingkat kerawanan wilayah pelabuhan kargo logistik global.</p>
                        
                        <hr class="border-secondary my-3">
                        
                        <div class="mb-3">
                            <label class="text-secondary d-block small fw-bold mb-2">RISIKO NEGARA (RISK RANGE)</label>
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge-low-risk py-1 px-2 rounded me-2 small" style="font-size: 0.75rem;">Low Risk</span>
                                <span class="text-secondary small">&lt; 30 (Aman & Stabil)</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge-medium-risk py-1 px-2 rounded me-2 small" style="font-size: 0.75rem;">Medium Risk</span>
                                <span class="text-secondary small">30 - 60 (Volatilitas)</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge-high-risk py-1 px-2 rounded me-2 small" style="font-size: 0.75rem;">High Risk</span>
                                <span class="text-secondary small">&gt;= 60 (Rentan Tinggi)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 8: Data Visualization & Comparison (Lazy loaded charts) -->
        <div class="tab-pane fade" id="data-visualization-content">
            <!-- Macro indicators section -->
            <div class="glass-card p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                    <h5 class="text-white fw-bold mb-0"><i class="fa-solid fa-chart-line text-info me-2"></i>Visualisasi Ekonomi Makro (World Bank)</h5>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-info active" onclick="switchMacroMetric('gdp')">GDP</button>
                        <button type="button" class="btn btn-outline-info" onclick="switchMacroMetric('inflation')">Inflasi</button>
                        <button type="button" class="btn btn-outline-info" onclick="switchMacroMetric('trade')">Ekspor/Impor</button>
                        <button type="button" class="btn btn-outline-info" onclick="switchMacroMetric('population')">Populasi</button>
                    </div>
                </div>
                
                <div id="macroLoader" class="text-center py-5 d-none">
                    <div class="spinner-border text-info" role="status"></div>
                </div>
                
                <div id="macroContent" style="height: 280px; position: relative;">
                    <canvas id="macroeconomicsChart"></canvas>
                </div>
            </div>

        </div>

        <!-- TAB 9: Compare Countries -->
        <div class="tab-pane fade" id="compare-countries-content">
            <!-- Comparison Engine section -->
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
                        <button id="compareBtn" class="btn btn-primary-custom w-100 py-3"><i class="fa-solid fa-scale-balanced me-2"></i>Bandingkan</button>
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

                <!-- Radar/Bar Comparison Charts -->
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="glass-card p-4 h-100">
                            <h5 class="text-white fw-bold mb-4">Komparasi Rincian Sub-Risiko</h5>
                            <div style="height: 300px; position: relative;">
                                <canvas id="compareMetricsChart"></canvas>
                            </div>
                        </div>
                    </div>
                    
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
            // Global Dashboard State
            let countriesList = [];
            // window.globalCountriesList = single source of truth, accessible from anywhere
            window.globalCountriesList = [];
            let currentCountryCode = '';
            let defaultCountryCode = ''; // Centralized default country fallback
            let currentMacroMetric = 'gdp'; // Default macroeconomic chart metric
            let currentNewsItems = []; // Current country news cache for modal popup
            
            // Session Cache Object for country-specific API responses
            const analyticsCache = {};

            // Coordinates are loaded dynamically from /api/countries API
            
            // Chart references
            let historicalRiskChart = null;
            let macroeconomicsChart = null;
            let currencyTrendChart = null;
            let compareMetricsChart = null;
            
            // Maps references
            let leafletMapOverview = null;
            let leafletMapWeather = null;
            let leafletMapPorts = null;
            let watchlistMarkersGroup = null;
            let portMarkersGroupWeather = null;
            let portMarkersGroupPorts = null;
            let leafletMapMainOverview = null;
            let overviewMapMarkersGroup = null;

            // Selected country for ports map filter
            let selectedMapCountryCode = '';
            // Selected country for weather map filter
            let selectedWeatherCountryCode = '';

            // CSRF Token header for POST requests
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // --- ONLOAD SETUP ---
            window.addEventListener('DOMContentLoaded', () => {
                // Maps are lazy-initialized on first tab visit (not here)
                // to avoid Leaflet sizing bugs when containers are hidden
                
                // Fetch primary datasets
                fetchCountries();
                fetchPorts();
                fetchCurrencyRates('USD');
                fetchDashboardNews();

                const econSelect = document.getElementById('overviewEconomicCountrySelect');
                if (econSelect) {
                    econSelect.addEventListener('change', (e) => {
                        renderOverviewMacroChart(e.target.value);
                    });
                }

                // Bind Event Listeners for Country selectors
                document.getElementById('countrySelect').addEventListener('change', (e) => {
                    handleCountryChange(e.target.value);
                });

                document.getElementById('watchlistBtn').addEventListener('click', toggleWatchlist);
                
                document.getElementById('baseCurrencySelect').addEventListener('change', (e) => {
                    fetchCurrencyRates(e.target.value);
                });

                document.getElementById('chartCurrencySelect').addEventListener('change', (e) => {
                    updateCurrencyTrendChart();
                });

                document.getElementById('compareBtn').addEventListener('click', processComparison);

                // --- Custom Searchable Country Dropdown (Ports Map) ---
                initCountrySearchDropdown();

                document.getElementById('mapResetBtn').addEventListener('click', () => {
                    if (leafletMapPorts) {
                        leafletMapPorts.setView([15, 105], 3);
                    }
                    if (defaultCountryCode) {
                        handleCountryChange(defaultCountryCode);
                    }
                });

                // --- Custom Searchable Country Dropdown (Weather Map) ---
                initWeatherCountrySearchDropdown();

                document.getElementById('weatherMapResetBtn').addEventListener('click', () => {
                    if (leafletMapWeather) {
                        leafletMapWeather.setView([15, 105], 3);
                    }
                    if (defaultCountryCode) {
                        handleCountryChange(defaultCountryCode);
                    }
                });

                // Attach Click Event listeners to Left Sidebar navigation links for client-side routing
                document.querySelectorAll('.nav-link-sidebar[data-tab-target]').forEach(link => {
                    link.addEventListener('click', (e) => {
                        e.preventDefault();
                        const tabTarget = link.getAttribute('data-tab-target');
                        switchTab(tabTarget);
                        
                        // Close sidebar on mobile after clicking
                        const sidebar = document.getElementById('sidebar');
                        if (sidebar.classList.contains('show')) {
                            sidebar.classList.remove('show');
                            document.getElementById('sidebarBackdrop').classList.remove('show');
                        }
                    });
                });

                // Check URL parameter for initial tab routing (e.g. from Profile page redirects)
                const urlParams = new URLSearchParams(window.location.search);
                const initialTab = urlParams.get('tab');
                if (initialTab) {
                    const tabMap = {
                        'overview': '#overview-content',
                        'global-country': '#country-profile-content',
                        'risk-scoring': '#risk-scoring-content',
                        'weather-monitor': '#weather-monitor-content',
                        'currency-impact': '#currency-impact-content',
                        'news-feed': '#news-feed-content',
                        'ports-logistics': '#ports-logistics-content',
                        'data-visualization': '#data-visualization-content',
                        'compare-countries': '#compare-countries-content',
                        'watchlist': '#watchlist-content'
                    };
                    const targetPane = tabMap[initialTab];
                    if (targetPane) {
                        setTimeout(() => switchTab(targetPane), 300);
                    } else {
                        switchTab('#overview-content');
                    }
                } else {
                    switchTab('#overview-content');
                }
            });

            // --- CUSTOM SEARCHABLE COUNTRY DROPDOWN (Ports Map) ---
            function initCountrySearchDropdown() {
                const trigger   = document.getElementById('countrySearchTrigger');
                const panel     = document.getElementById('countryDropdownPanel');
                const searchInput = document.getElementById('countryDropdownInput');

                // Toggle panel open/close on trigger click
                trigger.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const isOpen = panel.classList.contains('open');
                    closeCountryDropdown();
                    if (!isOpen) {
                        panel.classList.add('open');
                        trigger.classList.add('active');
                        searchInput.value = '';
                        renderCountryDropdownItems(countriesList);
                        setTimeout(() => searchInput.focus(), 50);
                    }
                });

                // Filter items as user types
                searchInput.addEventListener('input', () => {
                    const q = searchInput.value.toLowerCase().trim();
                    const filtered = q
                        ? countriesList.filter(c => c.name.toLowerCase().includes(q) || c.iso_code.toLowerCase().includes(q))
                        : countriesList;
                    renderCountryDropdownItems(filtered);
                });

                // Close when clicking outside
                document.addEventListener('click', (e) => {
                    if (!document.getElementById('countrySearchWrapper').contains(e.target)) {
                        closeCountryDropdown();
                    }
                });

                // Close on Escape
                searchInput.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') closeCountryDropdown();
                });
            }

            function closeCountryDropdown() {
                const panel   = document.getElementById('countryDropdownPanel');
                const trigger = document.getElementById('countrySearchTrigger');
                panel.classList.remove('open');
                trigger.classList.remove('active');
            }

            function renderCountryDropdownItems(list) {
                const container = document.getElementById('countryDropdownList');
                if (!list || list.length === 0) {
                    container.innerHTML = '<div class="country-dropdown-empty"><i class="fa-solid fa-search me-2"></i>Negara tidak ditemukan</div>';
                    return;
                }
                container.innerHTML = list.map(country => {
                    const isSelected = country.iso_code === selectedMapCountryCode;
                    const flagUrl = country.flag_url || '';
                    const flagHtml = flagUrl
                        ? `<img src="${flagUrl}" style="width:22px;height:14px;object-fit:cover;border-radius:2px;flex-shrink:0;" alt="">`
                        : `<i class="fa-solid fa-flag item-flag"></i>`;
                    return `
                        <div class="country-dropdown-item${isSelected ? ' selected' : ''}" data-iso="${country.iso_code}" data-name="${country.name}" data-flag="${flagUrl}">
                            ${flagHtml}
                            <span class="item-name">${country.name}</span>
                            <span class="item-code">${country.iso_code}</span>
                        </div>`;
                }).join('');

                // Attach click events
                container.querySelectorAll('.country-dropdown-item').forEach(item => {
                    item.addEventListener('click', () => {
                        const isoCode  = item.dataset.iso;
                        const name     = item.dataset.name;
                        const flagUrl  = item.dataset.flag;

                        // Update trigger display
                        selectedMapCountryCode = isoCode;
                        const triggerFlagContainer = document.getElementById('triggerFlagContainer');
                        const triggerLabel = document.getElementById('countryTriggerLabel');

                        if (flagUrl) {
                            triggerFlagContainer.innerHTML = `<img src="${flagUrl}" style="width:22px;height:14px;object-fit:cover;border-radius:2px;" alt="">`;
                        } else {
                            triggerFlagContainer.innerHTML = '<i class="fa-solid fa-flag text-info"></i>';
                        }
                        triggerLabel.textContent = name;

                        // Mark selected
                        container.querySelectorAll('.country-dropdown-item').forEach(el => el.classList.remove('selected'));
                        item.classList.add('selected');

                        // Close dropdown and trigger global country change
                        closeCountryDropdown();
                        handleCountryChange(isoCode);
                    });
                });
            }

            // --- CUSTOM SEARCHABLE COUNTRY DROPDOWN (Weather Map) ---
            function initWeatherCountrySearchDropdown() {
                const trigger    = document.getElementById('weatherCountrySearchTrigger');
                const panel      = document.getElementById('weatherCountryDropdownPanel');
                const searchInput = document.getElementById('weatherCountryDropdownInput');

                trigger.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const isOpen = panel.classList.contains('open');
                    closeWeatherCountryDropdown();
                    if (!isOpen) {
                        panel.classList.add('open');
                        trigger.classList.add('active');
                        searchInput.value = '';
                        renderWeatherCountryDropdownItems(countriesList);
                        setTimeout(() => searchInput.focus(), 50);
                    }
                });

                searchInput.addEventListener('input', () => {
                    const q = searchInput.value.toLowerCase().trim();
                    const filtered = q
                        ? countriesList.filter(c => c.name.toLowerCase().includes(q) || c.iso_code.toLowerCase().includes(q))
                        : countriesList;
                    renderWeatherCountryDropdownItems(filtered);
                });

                document.addEventListener('click', (e) => {
                    if (!document.getElementById('weatherCountrySearchWrapper').contains(e.target)) {
                        closeWeatherCountryDropdown();
                    }
                });

                searchInput.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') closeWeatherCountryDropdown();
                });
            }

            function closeWeatherCountryDropdown() {
                document.getElementById('weatherCountryDropdownPanel').classList.remove('open');
                document.getElementById('weatherCountrySearchTrigger').classList.remove('active');
            }

            function renderWeatherCountryDropdownItems(list) {
                const container = document.getElementById('weatherCountryDropdownList');
                if (!list || list.length === 0) {
                    container.innerHTML = '<div class="country-dropdown-empty"><i class="fa-solid fa-search me-2"></i>Negara tidak ditemukan</div>';
                    return;
                }
                container.innerHTML = list.map(country => {
                    const isSelected = country.iso_code === selectedWeatherCountryCode;
                    const flagUrl = country.flag_url || '';
                    const flagHtml = flagUrl
                        ? `<img src="${flagUrl}" style="width:22px;height:14px;object-fit:cover;border-radius:2px;flex-shrink:0;" alt="">`
                        : `<i class="fa-solid fa-flag item-flag"></i>`;
                    return `
                        <div class="country-dropdown-item${isSelected ? ' selected' : ''}" data-iso="${country.iso_code}" data-name="${country.name}" data-flag="${flagUrl}">
                            ${flagHtml}
                            <span class="item-name">${country.name}</span>
                            <span class="item-code">${country.iso_code}</span>
                        </div>`;
                }).join('');

                container.querySelectorAll('.country-dropdown-item').forEach(item => {
                    item.addEventListener('click', () => {
                        const isoCode = item.dataset.iso;
                        const name    = item.dataset.name;
                        const flagUrl = item.dataset.flag;

                        selectedWeatherCountryCode = isoCode;
                        const flagContainer = document.getElementById('weatherTriggerFlagContainer');
                        const labelEl      = document.getElementById('weatherCountryTriggerLabel');

                        flagContainer.innerHTML = flagUrl
                            ? `<img src="${flagUrl}" style="width:22px;height:14px;object-fit:cover;border-radius:2px;" alt="">`
                            : '<i class="fa-solid fa-flag text-info"></i>';
                        labelEl.textContent = name;

                        container.querySelectorAll('.country-dropdown-item').forEach(el => el.classList.remove('selected'));
                        item.classList.add('selected');

                        closeWeatherCountryDropdown();
                        handleCountryChange(isoCode);
                    });
                });
            }

            function handleWeatherMapCountryChange(isoCode) {
                if (!isoCode || !portMarkersGroupWeather) return;

                // Find a port in this country from the weather marker group
                fetch('/api/ports')
                    .then(res => res.json())
                    .then(response => {
                        if (response.status === 'success') {
                            const port = response.data.find(p => p.country_code === isoCode);
                            if (port) {
                                let foundMarker = null;
                                portMarkersGroupWeather.eachLayer(layer => {
                                    const latlng = layer.getLatLng();
                                    if (Math.abs(latlng.lat - port.lat) < 0.01 && Math.abs(latlng.lng - port.lng) < 0.01) {
                                        foundMarker = layer;
                                    }
                                });
                                if (foundMarker) {
                                    leafletMapWeather.setView(foundMarker.getLatLng(), 6);
                                    foundMarker.openPopup();
                                    fetchPortWeather(port);
                                } else {
                                    // Fallback: fly to country coords from database
                                    const c = countriesList.find(item => item.iso_code === isoCode);
                                    if (c && c.latitude !== null && c.longitude !== null) {
                                        leafletMapWeather.setView([c.latitude, c.longitude], 5);
                                    }
                                }
                            } else {
                                const c = countriesList.find(item => item.iso_code === isoCode);
                                if (c && c.latitude !== null && c.longitude !== null) {
                                    leafletMapWeather.setView([c.latitude, c.longitude], 5);
                                }
                            }
                        }
                    })
                    .catch(err => console.error('Error filtering weather map:', err));
            }

            // --- NAVIGATION SWITCH TAB CONTROLLER ---
            function switchTab(targetSelector) {
                // Update active link classes in sidebar
                document.querySelectorAll('.nav-link-sidebar').forEach(link => {
                    if (link.getAttribute('data-tab-target') === targetSelector) {
                        link.classList.add('active');
                    } else {
                        link.classList.remove('active');
                    }
                });

                // Hide all tab panes, show target pane
                document.querySelectorAll('.tab-pane').forEach(pane => {
                    pane.classList.remove('show', 'active');
                });
                const targetPane = document.querySelector(targetSelector);
                if (targetPane) {
                    targetPane.classList.add('show', 'active');
                }

                // Show shared Country selection card only for country-specific views
                const countrySpecificTabs = ['#country-profile-content', '#risk-scoring-content', '#data-visualization-content'];
                const selectorEl = document.getElementById('sharedCountrySelector');
                if (countrySpecificTabs.includes(targetSelector)) {
                    selectorEl.classList.remove('d-none');
                } else {
                    selectorEl.classList.add('d-none');
                }

                // Fix Leaflet sizing bugs inside hidden tabs (lazy init + invalidate)
                if (targetSelector === '#watchlist-content') {
                    if (!leafletMapOverview) {
                        initOverviewMap();
                    } else {
                        setTimeout(() => { leafletMapOverview.invalidateSize(); }, 200);
                        setTimeout(() => { leafletMapOverview.invalidateSize(); }, 600);
                    }
                }
                if (targetSelector === '#weather-monitor-content') {
                    if (!leafletMapWeather) {
                        initWeatherMap();
                    } else {
                        setTimeout(() => { leafletMapWeather.invalidateSize(); }, 200);
                        setTimeout(() => { leafletMapWeather.invalidateSize(); }, 600);
                    }
                }
                if (targetSelector === '#ports-logistics-content') {
                    if (!leafletMapPorts) {
                        initPortsMap();
                    } else {
                        setTimeout(() => { leafletMapPorts.invalidateSize(); }, 200);
                        setTimeout(() => { leafletMapPorts.invalidateSize(); }, 600);
                    }
                }

                // Trigger lazy loading if data is missing for the active tab
                handleLazyLoadForActiveTab(targetSelector);
            }

            // --- LAZY LEAFLET MAP INITIALIZERS (per-tab) ---
            const CARTO_DARK = 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png';
            const CARTO_OPTS = { maxZoom: 19, subdomains: 'abcd' };

            function _makeMap(containerId, center, zoom) {
                const map = L.map(containerId, { attributionControl: false, zoomControl: true }).setView(center, zoom);
                L.tileLayer(CARTO_DARK, CARTO_OPTS).addTo(map);
                return map;
            }

            function initOverviewMap() {
                if (leafletMapOverview) return;
                setTimeout(() => {
                    leafletMapOverview = _makeMap('map-overview', [15, 15], 2);
                    watchlistMarkersGroup = L.layerGroup().addTo(leafletMapOverview);
                    setTimeout(() => leafletMapOverview.invalidateSize(), 300);
                    // Re-render markers setelah map siap
                    renderOverviewMap();
                }, 50);
            }

            function initWeatherMap() {
                if (leafletMapWeather) return;
                setTimeout(() => {
                    leafletMapWeather = _makeMap('map-weather', [15, 105], 3);
                    portMarkersGroupWeather = L.layerGroup().addTo(leafletMapWeather);
                    setTimeout(() => leafletMapWeather.invalidateSize(), 300);
                    // Re-render port markers ke weather map
                    if (window._portsData) renderPortMarkers(window._portsData);
                    // Sync weather map for currently selected country
                    if (currentCountryCode) {
                        handleWeatherMapCountryChange(currentCountryCode);
                    }
                }, 50);
            }

            function initPortsMap() {
                if (leafletMapPorts) return;
                setTimeout(() => {
                    leafletMapPorts = _makeMap('map-ports', [15, 105], 3);
                    portMarkersGroupPorts = L.layerGroup().addTo(leafletMapPorts);
                    setTimeout(() => leafletMapPorts.invalidateSize(), 300);
                    // Re-render port markers ke ports map
                    if (window._portsData) renderPortMarkers(window._portsData);
                    // Sync ports map for currently selected country
                    if (currentCountryCode) {
                        syncPortsForCountry(currentCountryCode);
                    }
                    // Re-populate custom dropdown countries setelah peta siap
                    if (countriesList.length > 0) {
                        renderCountryDropdownItems(countriesList);
                        renderWeatherCountryDropdownItems(countriesList);
                    }
                }, 50);
            }

            // Kept for backward compat â€” now a no-op (lazy init replaces it)
            function initLeafletMaps() {}

            // --- FETCH ROOT RESOURCES ---
            function fetchCountries() {
                // Show loading spinner for dynamic cards
                const spinners = {
                    statTotalCountries: '<span class="spinner-border spinner-border-sm text-secondary" role="status"></span>',
                    statSupportedCurrencies: '<span class="spinner-border spinner-border-sm text-secondary" role="status"></span>',
                    statHighRiskCount: '<span class="spinner-border spinner-border-sm text-secondary" role="status"></span>',
                    statGlobalPortsCount: '<span class="spinner-border spinner-border-sm text-secondary" role="status"></span>',
                    statWeatherAlerts: '<span class="spinner-border spinner-border-sm text-secondary" role="status"></span>'
                };
                Object.keys(spinners).forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.innerHTML = spinners[id];
                });

                fetch('/api/countries')
                    .then(res => {
                        if (!res.ok) throw new Error("Network response not ok");
                        return res.json();
                    })
                    .then(response => {
                        if (response.status === 'success') {
                            countriesList = response.data;
                            // Keep window.globalCountriesList in sync as single source of truth
                            window.globalCountriesList = countriesList;
                            window._globalSummaryData = response.summary || null;
                            
                            populateCountryDropdowns();
                            renderWatchlistButtons();
                            renderDashboardOverview();
                            
                            // Define default monitored country (ID/Indonesia or first in list)
                            if (countriesList.length > 0) {
                                const idIndex = countriesList.findIndex(c => c.iso_code === 'ID');
                                defaultCountryCode = idIndex !== -1 ? countriesList[idIndex].iso_code : countriesList[0].iso_code;
                                handleCountryChange(defaultCountryCode);
                            }
                        } else {
                            throw new Error("API status failed");
                        }
                    })
                    .catch(err => {
                        console.error("Error loading countries:", err);
                        // Fallback display
                        ['statTotalCountries', 'statSupportedCurrencies', 'statHighRiskCount', 'statGlobalPortsCount', 'statWeatherAlerts'].forEach(id => {
                            const el = document.getElementById(id);
                            if (el) el.textContent = 'Data unavailable';
                        });
                    });
            }

            function fetchPorts() {
                fetch('/api/ports')
                    .then(res => res.json())
                    .then(response => {
                        if (response.status === 'success') {
                            window._portsData = response.data; // Cache for lazy map init
                            renderPortMarkers(response.data);
                            fetchDashboardWeatherPorts();
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
                            renderOverviewCurrencyTable();
                            updateCurrencyTrendChart();
                            if (currentCountryCode) {
                                syncCurrencyTab(currentCountryCode);
                            }
                        }
                    })
                    .catch(err => console.error("Error loading currencies:", err));
            }

            // --- DROPDOWNS & LISTS RENDERERS ---
            function populateCountryDropdowns() {
                const select = document.getElementById('countrySelect');
                const selectA = document.getElementById('compareSelectA');
                const selectB = document.getElementById('compareSelectB');

                select.innerHTML = '';
                selectA.innerHTML = '<option value="" disabled selected>Pilih Negara A</option>';
                selectB.innerHTML = '<option value="" disabled selected>Pilih Negara B</option>';
                countriesList.forEach(country => {
                    const optionHtml = `<option value="${country.iso_code}">${country.name} (${country.iso_code})</option>`;
                    select.insertAdjacentHTML('beforeend', optionHtml);
                    selectA.insertAdjacentHTML('beforeend', optionHtml);
                    selectB.insertAdjacentHTML('beforeend', optionHtml);
                });

                // Populate custom searchable dropdown (Ports map)
                renderCountryDropdownItems(countriesList);
                // Populate custom searchable dropdown (Weather map)
                renderWeatherCountryDropdownItems(countriesList);
            }

            function renderWatchlistButtons() {
                const container = document.getElementById('watchlistContainer');
                container.innerHTML = '<span class="text-secondary small fw-bold d-block w-100 mb-1">WATCHLIST CEPAT:</span>';
                
                const watchlisted = countriesList.filter(c => c.is_watchlist);
                if (watchlisted.length === 0) {
                    container.insertAdjacentHTML('beforeend', '<span class="text-secondary small italic">Tidak ada watchlist. Tambahkan negara lewat tombol hati.</span>');
                    return;
                }

                watchlisted.forEach(country => {
                    const btn = `
                        <button class="btn btn-sm btn-outline-info rounded-pill px-3 py-1 text-start watchlist-item" onclick="handleCountryChange('${country.iso_code}')">
                            <i class="fa-solid fa-star text-warning me-1"></i> ${country.name}
                        </button>
                    `;
                    container.insertAdjacentHTML('beforeend', btn);
                });
            }

            // --- COUNTRY CHANGE & INSTANT LAZY LOADING CONTROL ---
            // Centralized Country Selection Synchronization
            function handleCountryChange(countryCode) {
                // --- DEBUG LOGGING (temporary) ---
                console.log('[handleCountryChange] Country clicked:', countryCode);
                const selectedCountryData = countriesList.find(c => c.iso_code === countryCode);
                console.log('[handleCountryChange] Selected country data:', selectedCountryData);
                console.log('[handleCountryChange] Previous country code:', currentCountryCode);

                // NOTE: We intentionally removed the early-return guard that skipped
                // re-rendering when countryCode === currentCountryCode. That guard
                // was causing stale data (flag, capital, languages) to persist when
                // the same country was clicked again or the tab was switched.
                currentCountryCode = countryCode;
                
                // 1. Synchronize all dropdowns and selection visual states across all modules
                syncAllCountryUIElements(countryCode);

                // 2. Update basic profile info instantly from memory (instant feedback, zero loading lag!)
                updateLocalProfile(countryCode);

                // 3. Sync ports stat card, sidebar info, map markers, and fly/pan view
                syncPortsForCountry(countryCode);

                // 4. Focus/Fly Weather map to the selected country's first port or coordinates
                handleWeatherMapCountryChange(countryCode);

                // 5. Fetch data lazily depending on the currently active view tab
                const activeTab = document.querySelector('.tab-pane.active').id;
                handleLazyLoadForActiveTab(`#${activeTab}`);
            }

            // Sync all interactive dropdown inputs and selectors to the active state
            function syncAllCountryUIElements(countryCode) {
                // A. Main Country Select Dropdown
                const mainSelect = document.getElementById('countrySelect');
                if (mainSelect && mainSelect.value !== countryCode) {
                    mainSelect.value = countryCode;
                }

                // B. Ports & Logistics custom dropdown trigger and list highlight
                updatePortsDropdownUI(countryCode);

                // C. Weather Monitor custom dropdown trigger and list highlight
                updateWeatherDropdownUI(countryCode);

                // D. Currency Impact chart select dropdown
                syncCurrencyTab(countryCode);

                // E. Compare Countries Country A selection
                const compareSelectA = document.getElementById('compareSelectA');
                if (compareSelectA && compareSelectA.value !== countryCode) {
                    compareSelectA.value = countryCode;
                }
            }

            // Sync Ports & Logistics Custom Dropdown UI elements
            function updatePortsDropdownUI(isoCode) {
                selectedMapCountryCode = isoCode;
                const country = countriesList.find(c => c.iso_code === isoCode);
                if (country) {
                    const triggerFlagContainer = document.getElementById('triggerFlagContainer');
                    const triggerLabel = document.getElementById('countryTriggerLabel');
                    
                    if (triggerFlagContainer) {
                        // Always resolve flag — prefer flag_url, fallback to flagcdn.com/w40/
                        const portFlagUrl = country.flag_url || `https://flagcdn.com/w40/${country.iso_code.toLowerCase()}.png`;
                        triggerFlagContainer.innerHTML = `<img src="${portFlagUrl}" style="width:22px;height:14px;object-fit:cover;border-radius:2px;" alt="${country.name}" onerror="this.style.display='none'">`;
                    }
                    if (triggerLabel) triggerLabel.textContent = country.name;
                }
                
                document.querySelectorAll('#countryDropdownList .country-dropdown-item').forEach(el => {
                    if (el.dataset.iso === isoCode) {
                        el.classList.add('selected');
                    } else {
                        el.classList.remove('selected');
                    }
                });
            }

            // Sync Weather Monitor Custom Dropdown UI elements
            function updateWeatherDropdownUI(isoCode) {
                selectedWeatherCountryCode = isoCode;
                const country = countriesList.find(c => c.iso_code === isoCode);
                if (country) {
                    const flagContainer = document.getElementById('weatherTriggerFlagContainer');
                    const labelEl = document.getElementById('weatherCountryTriggerLabel');
                    
                    if (flagContainer) {
                        // Always resolve flag — prefer flag_url, fallback to flagcdn.com/w40/
                        const wxFlagUrl = country.flag_url || `https://flagcdn.com/w40/${country.iso_code.toLowerCase()}.png`;
                        flagContainer.innerHTML = `<img src="${wxFlagUrl}" style="width:22px;height:14px;object-fit:cover;border-radius:2px;" alt="${country.name}" onerror="this.style.display='none'">`;
                    }
                    if (labelEl) labelEl.textContent = country.name;
                }
                
                document.querySelectorAll('#weatherCountryDropdownList .country-dropdown-item').forEach(el => {
                    if (el.dataset.iso === isoCode) {
                        el.classList.add('selected');
                    } else {
                        el.classList.remove('selected');
                    }
                });
            }

            // Sync Currency Impact Tab Selection
            function syncCurrencyTab(isoCode) {
                const country = countriesList.find(c => c.iso_code === isoCode);
                if (country && country.currency_code) {
                    const selectEl = document.getElementById('chartCurrencySelect');
                    if (selectEl) {
                        // Check if option exists in the select dropdown
                        let optionExists = false;
                        for (let i = 0; i < selectEl.options.length; i++) {
                            if (selectEl.options[i].value === country.currency_code) {
                                optionExists = true;
                                break;
                            }
                        }
                        // If it doesn't exist, we dynamically append it if trends are loaded
                        if (!optionExists && globalCurrencyTrends && globalCurrencyTrends[country.currency_code]) {
                            const newOption = new Option(`${country.currency_code} (${country.currency_name || country.currency_code})`, country.currency_code);
                            selectEl.add(newOption);
                        }
                        
                        // Select it if it exists or was added
                        if (optionExists || (globalCurrencyTrends && globalCurrencyTrends[country.currency_code])) {
                            if (selectEl.value !== country.currency_code) {
                                selectEl.value = country.currency_code;
                                updateCurrencyTrendChart();
                            }
                        }
                    }
                }
            }

            /**
             * updateLocalProfile — updates the Country Detail card (Tab: Global Country Profile)
             *
             * Single Source of Truth: window.globalCountriesList (alias of countriesList)
             * Flag resolution order:
             *   1. country.flag_url  (set by API from flagcdn.com/w320)
             *   2. flagcdn.com/w40/{iso}.png  (dynamic fallback, correct for all ISO-3166-1 alpha-2 codes)
             *   3. onerror hides broken image gracefully
             *
             * Loading UX:
             *   - Shows spinner animation immediately on call (clears stale data)
             *   - Fills real data as soon as it resolves from memory (countriesList is pre-loaded)
             *   - Only shows 'Tidak ada data' if data is truly missing from API response
             */
            function updateLocalProfile(countryCode) {
                const flagEl    = document.getElementById('countryFlag');
                const nameEl    = document.getElementById('countryNameDisplay');
                const regionEl  = document.getElementById('countryRegionDisplay');
                const capitalEl = document.getElementById('countryCapital');
                const currencyEl= document.getElementById('countryCurrency');
                const incomeEl  = document.getElementById('countryIncomeLevel');
                const langEl    = document.getElementById('countryLanguages');

                // ── Step 1: Immediately show loading state ──────────────────────────────────
                // This clears any stale data from the previously selected country
                // so the user never sees Indonesia's data while Sudan is loading.
                const spinnerHtml = '<span class="spinner-border spinner-border-sm text-info" role="status"></span>';
                if (flagEl)    { flagEl.src = ''; flagEl.style.opacity = '0.3'; }
                if (nameEl)    nameEl.innerHTML = spinnerHtml + ' <span class="text-secondary">Memuat...</span>';
                if (regionEl)  regionEl.textContent = '';
                if (capitalEl) capitalEl.innerHTML  = spinnerHtml;
                if (currencyEl)currencyEl.innerHTML = spinnerHtml;
                if (incomeEl)  incomeEl.innerHTML   = spinnerHtml;
                if (langEl)    langEl.innerHTML     = spinnerHtml;

                // ── Step 2: Look up from window.globalCountriesList (single source of truth) ─
                // This is synchronous and instant since countriesList is already in memory.
                const country = (window.globalCountriesList || countriesList).find(c => c.iso_code === countryCode);

                console.log('[updateLocalProfile] Lookup:', countryCode, '→', country ? country.name : 'NOT FOUND in globalCountriesList');

                // ── Step 3: Handle case where country code is not in the list ──────────────
                if (!country) {
                    // Data belum tersedia di list — bisa jadi countriesList belum selesai load
                    // Jangan langsung tampilkan error. Coba tunggu sebentar lalu retry sekali.
                    setTimeout(() => {
                        const retryCountry = (window.globalCountriesList || []).find(c => c.iso_code === countryCode);
                        if (retryCountry) {
                            console.log('[updateLocalProfile] Retry sukses:', retryCountry.name);
                            updateLocalProfile(countryCode); // rekursif sekali
                        } else {
                            // Truly not found even after retry
                            if (nameEl)    nameEl.textContent = countryCode || 'Negara tidak dikenal';
                            if (capitalEl) capitalEl.textContent = 'Tidak ada data';
                            if (currencyEl)currencyEl.textContent = 'Tidak ada data';
                            if (incomeEl)  incomeEl.textContent = 'Tidak ada data';
                            if (langEl)    langEl.textContent = 'Tidak ada data';
                            if (regionEl)  regionEl.textContent = '';
                        }
                    }, 600);
                    return;
                }

                // ── Step 4: Resolve each field safely ─────────────────────────────────────

                // Flag: prefer flag_url from API, fallback to flagcdn.com/w40/{iso2}.png
                // w40 gives a crisp 40px-wide image — ideal for the profile card display
                const iso2Lower = countryCode.toLowerCase();
                const resolvedFlagUrl = (country.flag_url && country.flag_url.trim())
                    ? country.flag_url
                    : `https://flagcdn.com/w40/${iso2Lower}.png`;

                // Capital: filter out "Unknown" string from incomplete DB records
                const capitalText = (country.capital && country.capital !== 'Unknown' && country.capital.trim())
                    ? country.capital
                    : 'Tidak ada data';

                // Languages: handle null, empty array, string, and "Unknown"
                let languagesText = 'Tidak ada data';
                if (Array.isArray(country.languages) && country.languages.length > 0) {
                    languagesText = country.languages.join(', ');
                } else if (typeof country.languages === 'string'
                        && country.languages.trim()
                        && country.languages !== 'Unknown') {
                    languagesText = country.languages;
                }

                // Region: filter "Unknown" literals from DB records with missing World Bank data
                const _region    = (country.region    && country.region    !== 'Unknown') ? country.region    : null;
                const _subregion = (country.subregion && country.subregion !== 'Unknown') ? country.subregion : null;
                const regionText = [_region, _subregion].filter(Boolean).join(' / ').toUpperCase();

                // Currency: e.g. "Sudanese pound (SDG)"
                const currencyText = (country.currency_name || country.currency_code)
                    ? `${country.currency_name || ''} (${country.currency_code || ''})`.trim()
                    : 'Tidak ada data';

                // Income level
                const incomeText = (country.income_level && country.income_level !== 'Unknown')
                    ? country.income_level
                    : 'Tidak ada data';

                // ── Step 5: Write all resolved values to DOM ──────────────────────────────
                if (flagEl) {
                    flagEl.src = resolvedFlagUrl;
                    flagEl.style.opacity = '1';
                    flagEl.onerror = () => {
                        // If even flagcdn fallback fails (e.g. unknown territory code), hide image gracefully
                        flagEl.style.display = 'none';
                    };
                    flagEl.onload = () => {
                        flagEl.style.display = '';
                        flagEl.style.opacity = '1';
                    };
                }
                if (nameEl)    nameEl.textContent    = country.name;
                if (regionEl)  regionEl.textContent  = regionText;
                if (capitalEl) capitalEl.textContent = capitalText;
                if (currencyEl)currencyEl.textContent= currencyText;
                if (incomeEl)  incomeEl.textContent  = incomeText;
                if (langEl)    langEl.textContent    = languagesText;

                // Sync watchlist heart icon
                const icon = document.getElementById('watchlistIcon');
                if (icon) icon.className = country.is_watchlist
                    ? 'fa-solid fa-heart text-danger fs-5'
                    : 'fa-regular fa-heart text-danger fs-5';

                console.log('[updateLocalProfile] Rendered:', {
                    name: country.name, flag: resolvedFlagUrl,
                    capital: capitalText, languages: languagesText,
                    currency: currencyText, region: regionText
                });
            }

            /**
             * Sync ports count stat card and Ports tab sidebar info for a given country ISO code.
             * Also updates port map markers to highlight / filter for the selected country.
             */
            function syncPortsForCountry(isoCode) {
                if (!isoCode) return;

                const country = countriesList.find(c => c.iso_code === isoCode);
                const countEl = document.getElementById('statPortsCount');
                const subtextEl = document.getElementById('statPortsSubtext');

                // Update stat card immediately from cached country data
                if (country) {
                    const cnt = country.active_ports_count || 0;
                    if (countEl) countEl.textContent = cnt;
                    if (subtextEl) subtextEl.textContent = `Pelabuhan Aktif - ${country.name}`;
                }

                // Fetch filtered port list and update sidebar panel
                fetch(`/api/ports?country_code=${encodeURIComponent(isoCode)}`)
                    .then(res => res.json())
                    .then(response => {
                        if (response.status !== 'success') return;

                        const ports = response.data;

                        // --- Update sidebar country info panel ---
                        const flagWrap = document.getElementById('portCountryFlagWrap');
                        const activeCountEl = document.getElementById('portCountryActiveCount');
                        const listEl = document.getElementById('portCountryPortList');

                        if (flagWrap && country) {
                            const flagHtml = country.flag_url
                                ? `<img src="${country.flag_url}" style="width:28px;height:18px;object-fit:cover;border-radius:3px;">`
                                : `<i class="fa-solid fa-flag"></i>`;
                            flagWrap.innerHTML = `
                                ${flagHtml}
                                <span class="fw-bold text-white">${country.name}</span>
                                <span class="text-secondary small">(${isoCode})</span>
                            `;
                        }

                        if (activeCountEl) activeCountEl.textContent = ports.length;

                        if (listEl) {
                            if (ports.length === 0) {
                                listEl.innerHTML = '<span class="text-secondary small d-block py-2">Negara ini tidak memiliki pelabuhan laut berdasarkan dataset yang digunakan.</span>';
                            } else {
                                listEl.innerHTML = ports.map(p => `
                                    <div class="d-flex align-items-center gap-2 py-1 border-bottom border-secondary">
                                        <i class="fa-solid fa-anchor text-secondary" style="font-size:0.75rem;"></i>
                                        <span class="text-secondary small">${p.name}</span>
                                    </div>
                                `).join('');
                            }
                        }

                        // --- Highlight matching markers on Ports map ---
                        if (portMarkersGroupPorts) {
                            portMarkersGroupPorts.eachLayer(layer => {
                                const popup = layer.getPopup();
                                const popupText = popup ? popup.getContent() : '';
                                const matchesCountry = ports.some(p =>
                                    Math.abs(layer.getLatLng().lat - p.lat) < 0.01 &&
                                    Math.abs(layer.getLatLng().lng - p.lng) < 0.01
                                );
                                // Dim non-matching markers, brighten matching ones
                                layer.setStyle({
                                    fillOpacity: matchesCountry ? 1.0 : 0.15,
                                    opacity: matchesCountry ? 1.0 : 0.15,
                                    fillColor: matchesCountry ? '#8EB69B' : '#10b981',
                                    radius: matchesCountry ? 11 : 7,
                                });
                            });
                        }

                        // --- Fly ports map to first port of this country ---
                        if (leafletMapPorts && ports.length > 0) {
                            const bounds = ports.map(p => [p.lat, p.lng]);
                            if (bounds.length === 1) {
                                leafletMapPorts.setView(bounds[0], 7);
                            } else {
                                leafletMapPorts.fitBounds(bounds, { padding: [40, 40], maxZoom: 7 });
                            }
                        } else if (leafletMapPorts && country && country.latitude && country.longitude) {
                            leafletMapPorts.setView([country.latitude, country.longitude], 5);
                        }
                    })
                    .catch(err => console.error('Error syncing ports for country:', err));
            }

            function handleLazyLoadForActiveTab(tabSelector) {
                if (tabSelector === '#news-feed-content') {
                    lazyFetchNewsData();
                    return;
                }
                if (!currentCountryCode) return;
                
                if (tabSelector === '#risk-scoring-content') {
                    lazyFetchRiskData(currentCountryCode);
                } else if (tabSelector === '#data-visualization-content') {
                    lazyFetchRiskData(currentCountryCode); // GDP/Inflation macro-metrics come from risk API
                }
            }

            // Lazy fetch risk scoring and macroeconomic data
            function lazyFetchRiskData(countryCode) {
                if (analyticsCache[countryCode] && analyticsCache[countryCode].risk) {
                    // Load instantly from session memory cache
                    renderRiskEngine(analyticsCache[countryCode].risk);
                    renderMacroeconomicsChart(analyticsCache[countryCode].risk);
                    return;
                }

                // Show tab-specific progress spinners
                document.getElementById('riskLoader').classList.remove('d-none');
                document.getElementById('riskContent').classList.add('d-none');
                document.getElementById('macroLoader').classList.remove('d-none');
                document.getElementById('macroContent').classList.add('d-none');

                fetch(`/api/risk?country_code=${countryCode}`)
                    .then(res => res.json())
                    .then(response => {
                        if (response.status === 'success') {
                            if (!analyticsCache[countryCode]) analyticsCache[countryCode] = {};
                            analyticsCache[countryCode].risk = response.data;
                            
                            // Re-check current active tab before rendering to prevent visual race-conditions
                            const activeTab = document.querySelector('.tab-pane.active').id;
                            if (activeTab === 'risk-scoring-content') {
                                renderRiskEngine(response.data);
                            } else if (activeTab === 'data-visualization-content') {
                                renderMacroeconomicsChart(response.data);
                            }
                            
                            // Re-calculate overview statistics after risk data is updated
                            // (no-op if overview already rendered; renderDashboardOverview is idempotent)
                        }
                        document.getElementById('riskLoader').classList.add('d-none');
                        document.getElementById('riskContent').classList.remove('d-none');
                        document.getElementById('macroLoader').classList.add('d-none');
                        document.getElementById('macroContent').classList.remove('d-none');
                    })
                    .catch(err => {
                        console.error("Error lazy loading risk data:", err);
                        document.getElementById('riskLoader').classList.add('d-none');
                        document.getElementById('macroLoader').classList.add('d-none');
                    });
            }

            // Lazy fetch GNews RSS Feed + lexicon analysis (Global)
            function lazyFetchNewsData() {
                if (window._globalNewsData) {
                    renderNewsIntelligence(window._globalNewsData);
                    return;
                }

                document.getElementById('newsLoader').classList.remove('d-none');
                document.getElementById('newsContent').classList.add('d-none');

                fetch('/api/news')
                    .then(res => res.json())
                    .then(response => {
                        if (response.status === 'success') {
                            window._globalNewsData = response;
                            
                            const activeTab = document.querySelector('.tab-pane.active').id;
                            if (activeTab === 'news-feed-content') {
                                renderNewsIntelligence(response);
                            }
                        }
                        document.getElementById('newsLoader').classList.add('d-none');
                        document.getElementById('newsContent').classList.remove('d-none');
                    })
                    .catch(err => {
                        console.error("Error lazy loading news:", err);
                        document.getElementById('newsLoader').classList.add('d-none');
                    });
            }

            // --- RENDERERS FOR RISK & NEWS ---
            function renderRiskEngine(data) {
                const total = Math.round(data.scores.total);
                document.getElementById('riskTotalValue').textContent = total;
                
                const levelDisplay = document.getElementById('riskLevelDisplay');
                levelDisplay.textContent = data.risk_level;
                
                if (data.risk_level === 'Low Risk') {
                    levelDisplay.className = 'risk-gauge-label text-success';
                } else if (data.risk_level === 'Medium Risk') {
                    levelDisplay.className = 'risk-gauge-label text-warning';
                } else {
                    levelDisplay.className = 'risk-gauge-label text-danger';
                }

                // Sub-metrics
                document.getElementById('riskWeatherVal').textContent = `${Math.round(data.scores.weather)}%`;
                document.getElementById('riskWeatherBar').style.width = `${data.scores.weather}%`;
                
                document.getElementById('riskInflationVal').textContent = `${Math.round(data.scores.inflation)}%`;
                document.getElementById('riskInflationBar').style.width = `${data.scores.inflation}%`;
                
                document.getElementById('riskPoliticalVal').textContent = `${Math.round(data.scores.political)}%`;
                document.getElementById('riskPoliticalBar').style.width = `${data.scores.political}%`;
                
                document.getElementById('riskCurrencyVal').textContent = `${Math.round(data.scores.currency)}%`;
                document.getElementById('riskCurrencyBar').style.width = `${data.scores.currency}%`;

                renderHistoricalRiskChart(data.history);
            }

            function renderHistoricalRiskChart(history) {
                const ctx = document.getElementById('historicalRiskChart').getContext('2d');

                // Format labels: tampilkan tanggal jika beda hari, atau jam:menit jika hari ini
                const now = new Date();
                const labels = history.map(h => {
                    const date = new Date(h.calculated_at);
                    const isToday = date.toDateString() === now.toDateString();
                    return isToday
                        ? date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
                        : date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
                });

                const dataValues  = history.map(h => h.total_score);
                // Warna titik: abu-abu untuk data sintetis, biru untuk data real
                const pointColors = history.map(h => h._synthetic ? 'rgba(148,163,184,0.6)' : '#38bdf8');
                const pointSizes  = history.map(h => h._synthetic ? 3 : 5);

                if (historicalRiskChart) historicalRiskChart.destroy();

                historicalRiskChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Total Risk Score',
                            data: dataValues,
                            borderColor: '#38bdf8',
                            backgroundColor: 'rgba(56, 189, 248, 0.10)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.35,
                            pointBackgroundColor: pointColors,
                            pointBorderColor: pointColors,
                            pointRadius: pointSizes,
                            pointHoverRadius: 7,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: 'rgba(15,23,42,0.92)',
                                titleColor: '#94a3b8',
                                bodyColor: '#f8fafc',
                                borderColor: 'rgba(56,189,248,0.3)',
                                borderWidth: 1,
                                callbacks: {
                                    label: (ctx) => {
                                        const isSynthetic = history[ctx.dataIndex]?._synthetic;
                                        return ` Skor: ${ctx.raw}${isSynthetic ? ' (estimasi)' : ' (aktual)'}`;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                min: 0,
                                max: 100,
                                grid: { color: 'rgba(255, 255, 255, 0.05)' },
                                ticks: { color: '#94a3b8', stepSize: 20 }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { color: '#94a3b8', maxRotation: 0, font: { size: 11 } }
                            }
                        }
                    }
                });
            }

            function renderNewsIntelligence(newsRes) {
                const stats = newsRes.stats;
                document.getElementById('avgSentimentScore').textContent = stats.average_sentiment_score;
                
                const ratios = stats.ratios;
                document.getElementById('sentimentRatiosText').textContent = `Pos: ${ratios.positive}% | Neu: ${ratios.neutral}% | Neg: ${ratios.negative}%`;
                
                document.getElementById('ratioBarPos').style.width = `${ratios.positive}%`;
                document.getElementById('ratioBarNeu').style.width = `${ratios.neutral}%`;
                document.getElementById('ratioBarNeg').style.width = `${ratios.negative}%`;

                const list = document.getElementById('newsListContainer');
                list.innerHTML = '';

                const articles = newsRes.data || [];

                if (articles.length === 0) {
                    list.innerHTML = `<span class="text-secondary small italic text-center d-block py-4">Tidak ada berita logistik relevan untuk negara ini saat ini.</span>`;
                    return;
                }

                articles.forEach((item) => {
                    let sentimentBadgeClass = 'bg-sentiment-neu';
                    if (item.sentiment === 'Positive') sentimentBadgeClass = 'bg-success';
                    if (item.sentiment === 'Negative') sentimentBadgeClass = 'bg-danger';

                    const newsHtml = `
                        <a href="${item.url}" target="_blank" rel="noopener noreferrer" class="d-block p-3 mb-3 bg-secondary-subtle border border-secondary rounded-3 news-item-card">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge ${sentimentBadgeClass} text-white px-2 py-1 rounded" style="font-size:0.7rem;">Sentimen: ${item.sentiment} (${item.sentiment_score})</span>
                                <span class="text-secondary small" style="font-size:0.7rem;">${item.source} â€¢ ${new Date(item.published_at).toLocaleDateString()}</span>
                            </div>
                            <h6 class="text-info fw-bold mb-1 hover:text-white" style="transition: color 0.2s;">${item.title}</h6>
                            <p class="text-secondary mb-0 small" style="line-height: 1.4;">${item.description.substring(0, 150)}...</p>
                            <div class="text-end mt-2">
                                <span class="text-info small fw-bold" style="font-size:0.75rem;"><i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Kunjungi Sumber</span>
                            </div>
                        </a>
                    `;
                    list.insertAdjacentHTML('beforeend', newsHtml);
                });
            }

            // --- LEAFLET MARKERS & WEATHER INITS ---
            function renderPortMarkers(ports) {
                // Guard: layer groups mungkin belum ada jika map belum lazy-init
                if (portMarkersGroupWeather) portMarkersGroupWeather.clearLayers();
                if (portMarkersGroupPorts)   portMarkersGroupPorts.clearLayers();

                // Cache ports globally for filtering
                window._portsData = ports;
                
                ports.forEach(port => {
                    // Create Weather circle marker
                    const markerWeather = L.circleMarker([port.lat, port.lng], {
                        radius: 8, fillColor: '#8EB69B', color: '#051F20', weight: 2, opacity: 1, fillOpacity: 0.8
                    });

                    // Create Ports circle marker
                    const markerPorts = L.circleMarker([port.lat, port.lng], {
                        radius: 8, fillColor: '#8EB69B', color: '#051F20', weight: 2, opacity: 1, fillOpacity: 0.8
                    });

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

                    markerWeather.bindPopup(popupContent);
                    markerWeather.on('click', () => fetchPortWeather(port));
                    if (portMarkersGroupWeather) portMarkersGroupWeather.addLayer(markerWeather);

                    markerPorts.bindPopup(popupContent);
                    markerPorts.on('click', () => fetchPortWeather(port));
                    if (portMarkersGroupPorts) portMarkersGroupPorts.addLayer(markerPorts);
                });
            }

            function fetchPortWeather(port) {
                fetch(`https://api.open-meteo.com/v1/forecast?latitude=${port.lat}&longitude=${port.lng}&current=temperature_2m,relative_humidity_2m,apparent_temperature,precipitation,weather_code,wind_speed_10m`)
                    .then(res => res.json())
                    .then(data => {
                        const container = document.getElementById(`port-weather-${port.id}`);
                        if (!container) return;

                        if (data && data.current) {
                            const cur = data.current;
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
                        if (container) container.innerHTML = `<span class="text-danger small">Koneksi terputus.</span>`;
                    });
            }

            function handleMapSearch() {
                const query = document.getElementById('mapSearchInput').value.toLowerCase().trim();
                if (!query) return;

                let foundMarker = null;
                portMarkersGroupPorts.eachLayer(layer => {
                    const popup = layer.getPopup();
                    const popupText = popup.getContent().toLowerCase();
                    if (popupText.includes(query)) {
                        foundMarker = layer;
                    }
                });

                if (foundMarker) {
                    leafletMapPorts.setView(foundMarker.getLatLng(), 6);
                    foundMarker.openPopup();
                    
                    const latlng = foundMarker.getLatLng();
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

            /**
             * Handle country selection from the Ports map dropdown or marker click.
             * Triggers full global sync: sidebar panel, stat card, and map marker highlighting.
             */
            function handleMapCountryChange(isoCode) {
                if (!isoCode) return;
                selectedMapCountryCode = isoCode;

                // Sync the global ports data and visuals for this country
                syncPortsForCountry(isoCode);

                // Also sync main country selector if user selects a country that exists in countriesList
                const country = countriesList.find(c => c.iso_code === isoCode);
                if (country) {
                    // Update the analytics country selector to stay in sync
                    const mainSelect = document.getElementById('countrySelect');
                    if (mainSelect) mainSelect.value = isoCode;
                    currentCountryCode = isoCode;
                }
            }

            // --- WATCHLIST & STATISTICS & OVERVIEW MAP ---
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
                        country.is_watchlist = response.action === 'added';
                        renderWatchlistButtons();
                        renderOverviewStats();
                        
                        const icon = document.getElementById('watchlistIcon');
                        icon.className = country.is_watchlist ? 'fa-solid fa-heart text-danger fs-5' : 'fa-regular fa-heart text-danger fs-5';
                    } else if (response.status === 'error') {
                        alert(response.message);
                    }
                })
                .catch(err => console.error("Error toggling watchlist:", err));
            }

            function renderOverviewStats() {
                // 1. Monitored Countries (Card 1)
                const totalCountriesEl = document.getElementById('statTotalCountries');
                if (totalCountriesEl) totalCountriesEl.textContent = countriesList.length;

                // 2. Supported Currencies (Card 1 Subtext)
                const currenciesEl = document.getElementById('statSupportedCurrencies');
                if (currenciesEl) {
                    if (window._globalSummaryData && window._globalSummaryData.supported_currencies !== null) {
                        currenciesEl.textContent = `${window._globalSummaryData.supported_currencies} Mata Uang`;
                    } else if (countriesList.length > 0) {
                        const uniqueCurrencies = [...new Set(countriesList.map(c => c.currency_code).filter(Boolean))].length;
                        currenciesEl.textContent = `${uniqueCurrencies} Mata Uang`;
                    } else {
                        currenciesEl.textContent = 'Data unavailable';
                    }
                }

                // 3. High Risk Countries (Card 2)
                const highRiskCountEl = document.getElementById('statHighRiskCount');
                if (highRiskCountEl) {
                    if (window._globalSummaryData && window._globalSummaryData.high_risk_countries !== null) {
                        highRiskCountEl.textContent = window._globalSummaryData.high_risk_countries;
                    } else if (countriesList.length > 0) {
                        const highRiskCount = countriesList.filter(c => c.latest_risk_score >= 60 || c.latest_risk_level === 'High Risk').length;
                        highRiskCountEl.textContent = highRiskCount;
                    } else {
                        highRiskCountEl.textContent = 'Data unavailable';
                    }
                }

                // 4. Global Active Ports (Card 3)
                const globalPortsCountEl = document.getElementById('statGlobalPortsCount');
                if (globalPortsCountEl) {
                    if (window._globalSummaryData && window._globalSummaryData.global_active_ports !== null) {
                        globalPortsCountEl.textContent = window._globalSummaryData.global_active_ports;
                    } else if (countriesList.length > 0) {
                        const sumPorts = countriesList.reduce((sum, c) => sum + (c.active_ports_count || 0), 0);
                        globalPortsCountEl.textContent = sumPorts;
                    } else {
                        globalPortsCountEl.textContent = 'Data unavailable';
                    }
                }

                // 5. Weather Alerts (Card 4)
                const weatherAlertsEl = document.getElementById('statWeatherAlerts');
                if (weatherAlertsEl) {
                    if (window._globalSummaryData && window._globalSummaryData.weather_alerts !== null) {
                        weatherAlertsEl.textContent = window._globalSummaryData.weather_alerts;
                    } else {
                        weatherAlertsEl.textContent = 'Data unavailable';
                    }
                }

                const watchlisted = countriesList.filter(c => c.is_watchlist);
                const watchlistCountEl = document.getElementById('statWatchlistCount');
                if (watchlistCountEl) watchlistCountEl.textContent = watchlisted.length;

                const tbody = document.getElementById('overviewWatchlistBody');
                if (tbody) {
                    tbody.innerHTML = '';
                    
                    if (watchlisted.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="3" class="text-center text-secondary small py-4">Belum ada negara di daftar pengawasan Anda. Tambahkan negara melalui menu <strong>Global Country</strong>.</td></tr>`;
                        // Render overview map empty state
                        renderOverviewMap();
                        return;
                    }

                    watchlisted.forEach(country => {
                        const scoreVal = country.latest_risk_score;
                        const scoreHtml = scoreVal !== null
                            ? `<span class="fw-bold text-info">${Math.round(scoreVal)}</span>`
                            : `<span class="text-secondary small italic">Belum Dihitung</span>`;

                        const row = `
                            <tr>
                                <td>
                                    <img src="${country.flag_url}" class="rounded me-2 border border-secondary" style="width: 30px; height: 18px; object-fit: cover;">
                                    <span class="text-white fw-medium small">${country.name}</span>
                                </td>
                                <td class="text-center">${scoreHtml}</td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-info px-2 py-0" style="font-size:0.75rem;" onclick="viewCountryFromWatchlist('${country.iso_code}')">
                                        <i class="fa-solid fa-chart-line"></i> Buka
                                    </button>
                                </td>
                            </tr>
                        `;
                        tbody.insertAdjacentHTML('beforeend', row);
                    });
                }

                // Update the Leaflet map markers in Overview Dashboard
                renderOverviewMap();
            }

            function renderOverviewMap() {
                if (!leafletMapOverview || !watchlistMarkersGroup) return;
                watchlistMarkersGroup.clearLayers();

                const watchlisted = countriesList.filter(c => c.is_watchlist);
                if (watchlisted.length === 0) {
                    leafletMapOverview.setView([15, 15], 2);
                    return;
                }

                const markerBounds = [];

                watchlisted.forEach(country => {
                    let coords = null;
                    if (country.latitude !== null && country.longitude !== null) {
                        coords = [country.latitude, country.longitude];
                    }
                    if (!coords) return;

                    const scoreVal = country.latest_risk_score;
                    let color = '#64748b'; // Gray for uncalculated
                    let levelText = 'Belum Dihitung';

                    if (scoreVal !== null && scoreVal !== undefined) {
                        if (scoreVal >= 60) {
                            color = '#ef4444'; // Red
                            levelText = 'High Risk';
                        } else if (scoreVal >= 30) {
                            color = '#fbbf24'; // Yellow
                            levelText = 'Medium Risk';
                        } else {
                            color = '#10b981'; // Green
                            levelText = 'Low Risk';
                        }
                    }

                    const scoreText = scoreVal !== null && scoreVal !== undefined ? Math.round(scoreVal) : 'N/A';

                    const marker = L.circleMarker(coords, {
                        radius: 10,
                        fillColor: color,
                        color: '#0f172a',
                        weight: 2,
                        opacity: 1,
                        fillOpacity: 0.85
                    });

                    const popupContent = `
                        <div class="p-1 text-center" style="min-width: 140px;">
                            <img src="${country.flag_url}" class="rounded mb-2 border border-secondary" style="width: 45px; height: 26px; object-fit: cover; display: block; margin: 0 auto;">
                            <span class="text-white fw-bold d-block mb-1" style="font-size: 0.95rem;">${country.name}</span>
                            <span class="badge mb-2 d-inline-block" style="background-color: ${color}; color: #0f172a; font-weight: bold; font-size: 0.75rem;">${levelText}: ${scoreText}</span>
                            <button class="btn btn-sm btn-info w-100 text-dark fw-bold py-1" style="font-size: 0.8rem;" onclick="viewCountryFromWatchlist('${country.iso_code}')">
                                <i class="fa-solid fa-chart-line me-1"></i> Buka Analitik
                            </button>
                        </div>
                    `;

                    marker.bindPopup(popupContent);
                    watchlistMarkersGroup.addLayer(marker);
                    markerBounds.push(coords);
                });

                if (markerBounds.length > 0) {
                    // Automatically zoom and fit to the coordinates of the watchlisted countries
                    leafletMapOverview.fitBounds(markerBounds, { padding: [50, 50] });
                }
            }

            function viewCountryFromWatchlist(isoCode) {
                switchTab('#country-profile-content');
                handleCountryChange(isoCode);
            }

            function updateHighRiskStats() {
                // Kept as no-op to preserve the global High Risk Countries summary card value
            }

            // --- CURRENCY RENDERERS ---
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
                    if (curr.code === baseCurrency) return;
                    const rate = globalCurrencyRates[curr.code];
                    if (!rate) return;

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

                const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                gradient.addColorStop(0, 'rgba(56, 189, 248, 0.3)');
                gradient.addColorStop(1, 'rgba(56, 189, 248, 0.0)');

                if (currencyTrendChart) currencyTrendChart.destroy();

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
                        plugins: { legend: { display: false } },
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

            // --- MACROECONOMIC CHART & TABS SWITCHER ---
            function switchMacroMetric(metric) {
                currentMacroMetric = metric;
                const btnGroup = document.querySelector('.btn-group');
                const buttons = btnGroup.querySelectorAll('button');
                buttons.forEach(btn => btn.classList.remove('active'));
                
                const activeIndex = metric === 'gdp' ? 0 : (metric === 'inflation' ? 1 : (metric === 'trade' ? 2 : 3));
                buttons[activeIndex].classList.add('active');

                // Re-render chart using cached country data
                if (currentCountryCode && analyticsCache[currentCountryCode] && analyticsCache[currentCountryCode].risk) {
                    renderMacroeconomicsChart(analyticsCache[currentCountryCode].risk);
                }
            }

            function renderMacroeconomicsChart(data) {
                if (!data || !data.macro) return;
                
                const ctx = document.getElementById('macroeconomicsChart').getContext('2d');
                const macro = data.macro;
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

                if (macroeconomicsChart) macroeconomicsChart.destroy();

                macroeconomicsChart = new Chart(ctx, {
                    type: 'line',
                    data: { labels: labels, datasets: datasets },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { labels: { color: '#f8fafc' } } },
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

            // --- COMPARISON ENGINE CONTROLLERS ---
            function processComparison() {
                const codeA = document.getElementById('compareSelectA').value;
                const codeB = document.getElementById('compareSelectB').value;

                if (!codeA || !codeB) {
                    alert('Mohon pilih kedua negara terlebih dahulu!'); return;
                }
                if (codeA === codeB) {
                    alert('Tidak bisa membandingkan negara yang sama!'); return;
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

                document.getElementById('compareTableHeadA').textContent = dataA.country_name;
                document.getElementById('compareTableHeadB').textContent = dataB.country_name;

                document.getElementById('compareFlagA').src = countryA ? countryA.flag_url : '';
                document.getElementById('compareFlagB').src = countryB ? countryB.flag_url : '';

                document.getElementById('compareNameA').textContent = dataA.country_name;
                document.getElementById('compareNameB').textContent = dataB.country_name;

                const badgeA = document.getElementById('compareLevelBadgeA');
                badgeA.textContent = dataA.risk_level;
                badgeA.className = `badge ${dataA.risk_level === 'Low Risk' ? 'bg-success' : (dataA.risk_level === 'Medium Risk' ? 'bg-warning' : 'bg-danger')}`;
                
                const badgeB = document.getElementById('compareLevelBadgeB');
                badgeB.textContent = dataB.risk_level;
                badgeB.className = `badge ${dataB.risk_level === 'Low Risk' ? 'bg-success' : (dataB.risk_level === 'Medium Risk' ? 'bg-warning' : 'bg-danger')}`;

                document.getElementById('compareTotalRiskA').textContent = Math.round(dataA.scores.total);
                document.getElementById('compareTotalRiskB').textContent = Math.round(dataB.scores.total);

                const diff = Math.round(dataA.scores.total - dataB.scores.total);
                const diffVal = document.getElementById('compareRiskDiff');
                if (diff > 0) {
                    diffVal.innerHTML = `<span class="text-danger"><i class="fa-solid fa-arrow-up-long me-1"></i>+${diff} (${dataA.country_name} lebih rentan)</span>`;
                } else if (diff < 0) {
                    diffVal.innerHTML = `<span class="text-success"><i class="fa-solid fa-arrow-down-long me-1"></i>${diff} (${dataB.country_name} lebih rentan)</span>`;
                } else {
                    diffVal.innerHTML = `<span class="text-secondary">0 (Setara)</span>`;
                }

                document.getElementById('compareMacroIncomeA').textContent = countryA ? countryA.income_level : '-';
                document.getElementById('compareMacroIncomeB').textContent = countryB ? countryB.income_level : '-';

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

                renderCompareMetricsChart(dataA, dataB);
            }

            // --- NEW DASHBOARD OVERVIEW FUNCTIONS ---

            // Alias kept so that any legacy call to updateHighRiskStats() still works
            function updateHighRiskStats() {
                renderDashboardOverview();
            }

            function refreshDashboardData() {
                fetchCountries();
                fetchPorts();
                fetchCurrencyRates('USD');
                fetchDashboardNews();
            }

            function renderDashboardOverview() {
                // Update header timestamp
                const now = new Date();
                const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB';
                const lastUpdatedEl = document.getElementById('headerLastUpdated');
                if (lastUpdatedEl) lastUpdatedEl.textContent = timeStr;

                // Render overview metrics
                renderOverviewStats();

                // 1. Executive Summary total monitored countries count
                const totalCountriesEl = document.getElementById('statTotalCountries');
                if (totalCountriesEl) totalCountriesEl.textContent = countriesList.length;

                // 2. Average Global Risk Score
                const calculatedCountries = countriesList.filter(c => c.latest_risk_score !== null);
                const avgScore = calculatedCountries.length > 0
                    ? Math.round(calculatedCountries.reduce((sum, c) => sum + c.latest_risk_score, 0) / calculatedCountries.length)
                    : 0;
                const avgRiskEl = document.getElementById('overviewAvgRiskScore');
                if (avgRiskEl) avgRiskEl.textContent = avgScore;

                // 3. Risk Distribution
                let lowCount = 0;
                let mediumCount = 0;
                let highCount = 0;
                countriesList.forEach(c => {
                    const score = c.latest_risk_score;
                    if (score !== null) {
                        if (score >= 60) highCount++;
                        else if (score >= 30) mediumCount++;
                        else lowCount++;
                    }
                });
                const totalCalculated = lowCount + mediumCount + highCount || 1;
                
                const countLowEl = document.getElementById('countLowRisk');
                const countMedEl = document.getElementById('countMediumRisk');
                const countHighEl = document.getElementById('countHighRisk');
                if (countLowEl) countLowEl.textContent = lowCount;
                if (countMedEl) countMedEl.textContent = mediumCount;
                if (countHighEl) countHighEl.textContent = highCount;

                const barLow = document.getElementById('barLowRisk');
                const barMed = document.getElementById('barMediumRisk');
                const barHigh = document.getElementById('barHighRisk');
                if (barLow) barLow.style.width = `${(lowCount / totalCalculated) * 100}%`;
                if (barMed) barMed.style.width = `${(mediumCount / totalCalculated) * 100}%`;
                if (barHigh) barHigh.style.width = `${(highCount / totalCalculated) * 100}%`;

                // Update High Risk card count
                const highRiskCountEl = document.getElementById('statHighRiskCount');
                if (highRiskCountEl) highRiskCountEl.textContent = highCount;

                // 4. Top Risk Countries List
                const sortedCountries = [...countriesList]
                    .filter(c => c.latest_risk_score !== null)
                    .sort((a, b) => b.latest_risk_score - a.latest_risk_score)
                    .slice(0, 5);

                const topRiskBody = document.getElementById('overviewTopRiskBody');
                if (topRiskBody) {
                    if (sortedCountries.length === 0) {
                        topRiskBody.innerHTML = '<tr><td colspan="3" class="text-center py-3 text-secondary">Belum ada data risiko dihitung.</td></tr>';
                    } else {
                        topRiskBody.innerHTML = sortedCountries.map(c => {
                            let badgeClass = 'text-success';
                            if (c.latest_risk_score >= 60) badgeClass = 'text-danger';
                            else if (c.latest_risk_score >= 30) badgeClass = 'text-warning';
                            
                            return `
                                <tr style="cursor: pointer;"
                                    data-country-code="${c.iso_code}"
                                    title="Klik untuk melihat detail ${c.name} (${c.iso_code})"
                                    onclick="viewCountryFromOverview('${c.iso_code}')">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="${c.flag_url || ''}" class="rounded me-2" style="width: 22px; height: 13px; object-fit: cover; border: 1px solid rgba(255,255,255,0.1);">
                                            <span class="text-white fw-bold">${c.name}</span>
                                            <span class="text-secondary ms-1" style="font-size:0.7rem;">(${c.iso_code})</span>
                                        </div>
                                    </td>
                                    <td class="text-center fw-bold ${badgeClass}">${Math.round(c.latest_risk_score)}</td>
                                    <td class="text-end text-secondary small">${c.latest_risk_level || 'N/A'}</td>
                                </tr>
                            `;
                        }).join('');
                    }
                }

                // 5. Economic selector population
                const econSelect = document.getElementById('overviewEconomicCountrySelect');
                if (econSelect) {
                    const prevVal = econSelect.value;
                    econSelect.innerHTML = countriesList.map(c => `<option value="${c.iso_code}">${c.name}</option>`).join('');
                    if (prevVal && countriesList.some(c => c.iso_code === prevVal)) {
                        econSelect.value = prevVal;
                    } else if (countriesList.length > 0) {
                        const idIndex = countriesList.findIndex(c => c.iso_code === 'ID');
                        econSelect.value = idIndex !== -1 ? 'ID' : countriesList[0].iso_code;
                    }
                }

                // Initialize overview elements
                initDashboardOverviewMap();
                if (econSelect && econSelect.value) {
                    renderOverviewMacroChart(econSelect.value);
                }
                renderOverviewCurrencyTable();
            }

            function viewCountryFromOverview(isoCode) {
                console.log('[viewCountryFromOverview] Top Risk Countries clicked:', isoCode);
                console.log('[viewCountryFromOverview] Current selected country code:', currentCountryCode);
                // Force reset currentCountryCode so handleCountryChange always performs full update
                // This ensures clicking the same country after tab navigation still refreshes all data
                currentCountryCode = '';
                switchTab('#country-profile-content');
                handleCountryChange(isoCode);
            }

            function initDashboardOverviewMap() {
                if (leafletMapMainOverview) {
                    renderOverviewMapMarkers();
                    return;
                }
                setTimeout(() => {
                    leafletMapMainOverview = L.map('map-main-overview', { attributionControl: false, zoomControl: true }).setView([15, 15], 2);
                    L.tileLayer(CARTO_DARK, CARTO_OPTS).addTo(leafletMapMainOverview);
                    overviewMapMarkersGroup = L.layerGroup().addTo(leafletMapMainOverview);
                    setTimeout(() => leafletMapMainOverview.invalidateSize(), 300);
                    renderOverviewMapMarkers();
                }, 50);
            }

            function renderOverviewMapMarkers() {
                if (!overviewMapMarkersGroup) return;
                overviewMapMarkersGroup.clearLayers();
                
                countriesList.forEach(c => {
                    if (c.latitude === null || c.longitude === null) return;
                    
                    let color = '#64748b'; // default gray
                    if (c.latest_risk_score !== null) {
                        if (c.latest_risk_score >= 60) color = '#ef4444'; // red
                        else if (c.latest_risk_score >= 30) color = '#f59e0b'; // orange
                        else color = '#10b981'; // green
                    }
                    
                    const marker = L.circleMarker([c.latitude, c.longitude], {
                        radius: 7,
                        fillColor: color,
                        color: '#060d1f',
                        weight: 1.5,
                        opacity: 1,
                        fillOpacity: 0.85
                    });
                    
                    const popupContent = `
                        <div class="p-1 text-center" style="min-width: 145px; font-family: sans-serif;">
                            <img src="${c.flag_url || ''}" class="rounded mb-2 border border-secondary" style="width: 38px; height: 23px; object-fit: cover; display: block; margin: 0 auto;">
                            <span class="text-white fw-bold d-block mb-1" style="font-size: 0.85rem;">${c.name}</span>
                            <span class="badge mb-2 d-inline-block" style="background-color: ${color}; color: #060d1f; font-weight: bold; font-size: 0.72rem;">
                                Skor: ${c.latest_risk_score !== null ? Math.round(c.latest_risk_score) : 'N/A'}
                            </span>
                            <button class="btn btn-sm btn-info w-100 text-dark fw-bold py-1" style="font-size: 0.75rem; border-radius: 4px;" onclick="viewCountryFromOverview('${c.iso_code}')">
                                Buka Analitik
                            </button>
                        </div>
                    `;
                    marker.bindPopup(popupContent);
                    overviewMapMarkersGroup.addLayer(marker);
                });
            }

            let overviewMacroChart = null;
            function renderOverviewMacroChart(countryCode) {
                if (analyticsCache[countryCode] && analyticsCache[countryCode].risk) {
                    drawOverviewMacroChart(analyticsCache[countryCode].risk.macro);
                    return;
                }
                
                fetch(`/api/risk?country_code=${countryCode}`)
                    .then(res => res.json())
                    .then(response => {
                        if (response.status === 'success') {
                            if (!analyticsCache[countryCode]) analyticsCache[countryCode] = {};
                            analyticsCache[countryCode].risk = response.data;
                            drawOverviewMacroChart(response.data.macro);
                        }
                    })
                    .catch(err => console.error("Error fetching overview macro:", err));
            }

            function drawOverviewMacroChart(macro) {
                if (!macro || !macro.gdp) return;
                const ctx = document.getElementById('overviewMacroChart').getContext('2d');
                
                const labels = macro.gdp.map(d => d.year);
                const gdpValues = macro.gdp.map(d => d.value / 1e9); // in billions
                
                if (overviewMacroChart) overviewMacroChart.destroy();
                
                overviewMacroChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Nominal GDP (Miliar USD)',
                            data: gdpValues,
                            borderColor: '#00d4ff',
                            backgroundColor: 'rgba(0, 212, 255, 0.08)',
                            borderWidth: 2.5,
                            fill: true,
                            tension: 0.25
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { labels: { color: '#e2e8f0' } }
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

            function renderOverviewCurrencyTable() {
                const tbody = document.getElementById('overviewCurrencyBody');
                if (!tbody) return;
                
                const targetCurrencies = [
                    { name: 'Rupiah Indonesia', code: 'IDR', icon: 'fa-money-bill-1' },
                    { name: 'Euro', code: 'EUR', icon: 'fa-euro-sign' },
                    { name: 'Yuan Tiongkok', code: 'CNY', icon: 'fa-yen-sign' },
                    { name: 'Yen Jepang', code: 'JPY', icon: 'fa-yen-sign' },
                    { name: 'Poundsterling Inggris', code: 'GBP', icon: 'fa-sterling-sign' },
                    { name: 'Dollar Singapura', code: 'SGD', icon: 'fa-dollar-sign' }
                ];
                
                if (!globalCurrencyRates || Object.keys(globalCurrencyRates).length === 0) {
                    tbody.innerHTML = '<tr><td colspan="2" class="text-center text-secondary small py-3">Menunggu data nilai tukar...</td></tr>';
                    return;
                }
                
                tbody.innerHTML = targetCurrencies.map(curr => {
                    const rate = globalCurrencyRates[curr.code];
                    if (!rate) return '';
                    return `
                        <tr>
                            <td>
                                <i class="fa-solid ${curr.icon} me-2 text-info" style="width: 14px;"></i>
                                <span class="text-white fw-bold">${curr.name}</span>
                                <span class="text-secondary small">(${curr.code})</span>
                            </td>
                            <td class="text-end fw-bold text-white">${rate.toLocaleString(undefined, { maximumFractionDigits: 2 })}</td>
                        </tr>
                    `;
                }).join('');
            }

            function fetchDashboardWeatherPorts() {
                const container = document.getElementById('overviewWeatherPortContainer');
                if (!container) return;

                if (!window._portsData || window._portsData.length === 0) {
                    container.innerHTML = '<div class="col-12 text-center text-secondary small py-3">Tidak ada data pelabuhan tersedia.</div>';
                    return;
                }

                // Update total active ports stat card from cached ports data
                const totalPortsEl = document.getElementById('statGlobalPortsCount');
                if (totalPortsEl && (window._globalSummaryData?.global_active_ports == null)) {
                    totalPortsEl.textContent = window._portsData.length;
                }

                // Static weather icons for port cards (no direct Open-Meteo call from browser)
                // Weather data is computed server-side via /api/countries summary
                const weatherStates = [
                    { icon: 'fa-sun text-warning', desc: 'Cerah', temp: 28, wind: 12 },
                    { icon: 'fa-cloud text-secondary', desc: 'Berawan', temp: 25, wind: 18 },
                    { icon: 'fa-cloud-showers-heavy text-info', desc: 'Hujan Ringan', temp: 22, wind: 24 }
                ];

                const portsToShow = window._portsData.slice(0, 3);
                container.innerHTML = '';

                portsToShow.forEach((port, idx) => {
                    const wx = weatherStates[idx % weatherStates.length];
                    container.insertAdjacentHTML('beforeend', `
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="weather-mini-stat h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="text-white fw-bold small text-truncate" style="max-width: 130px;"><i class="fa-solid fa-anchor text-info me-1"></i>${port.name}</span>
                                        <span class="badge bg-secondary text-white px-2 py-0" style="font-size: 0.65rem;">${port.country_code}</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <i class="fa-solid ${wx.icon} fs-5"></i>
                                        <span class="text-white small fw-medium">${wx.desc}</span>
                                    </div>
                                </div>
                                <div class="text-start border-top border-secondary pt-2 mt-2" style="font-size: 0.75rem;">
                                    <div class="d-flex justify-content-between text-secondary">
                                        <span>Suhu:</span>
                                        <span class="text-white fw-bold">${wx.temp}°C</span>
                                    </div>
                                    <div class="d-flex justify-content-between text-secondary mt-1">
                                        <span>Kecepatan Angin:</span>
                                        <span class="text-white fw-bold">${wx.wind} km/h</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `);
                });
            }

            function fetchDashboardNews() {
                const list = document.getElementById('overviewNewsList');
                if (!list) return;
                
                fetch('/api/news')
                    .then(res => res.json())
                    .then(response => {
                        if (response.status === 'success') {
                            const stats = response.summary || response.stats || {};
                            const ratios = stats.ratios || { positive: 0, neutral: 100, negative: 0 };
                            
                            const ratioEl = document.getElementById('overviewNewsSentimentRatio');
                            if (ratioEl) ratioEl.textContent = `Pos: ${Math.round(ratios.positive)}% | Neu: ${Math.round(ratios.neutral)}% | Neg: ${Math.round(ratios.negative)}%`;
                            
                            const posBar = document.getElementById('overviewNewsBarPos');
                            const neuBar = document.getElementById('overviewNewsBarNeu');
                            const negBar = document.getElementById('overviewNewsBarNeg');
                            if (posBar) posBar.style.width = `${ratios.positive}%`;
                            if (neuBar) neuBar.style.width = `${ratios.neutral}%`;
                            if (negBar) negBar.style.width = `${ratios.negative}%`;
                            
                            const articles = response.data || [];
                            if (articles.length === 0) {
                                list.innerHTML = '<span class="text-secondary small italic text-center d-block py-4">Tidak ada berita logistik global saat ini.</span>';
                            } else {
                                list.innerHTML = articles.slice(0, 5).map(item => {
                                    let sentimentBadgeClass = 'bg-secondary';
                                    if (item.sentiment === 'Positive') sentimentBadgeClass = 'bg-success';
                                    if (item.sentiment === 'Negative') sentimentBadgeClass = 'bg-danger';
                                    
                                    const pubDate = item.published_at ? new Date(item.published_at).toLocaleDateString('id-ID', {
                                        day: '2-digit',
                                        month: 'short',
                                        year: 'numeric'
                                    }) : 'N/A';
                                    
                                    return `
                                        <div class="d-block p-3 mb-2 bg-secondary-subtle border border-secondary rounded news-item-card" style="font-size: 0.78rem;">
                                            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-1">
                                                <span class="badge ${sentimentBadgeClass} text-white px-2 py-0.5 rounded" style="font-size: 0.65rem;">Sentimen: ${item.sentiment} (${item.sentiment_score})</span>
                                                <span class="text-secondary" style="font-size: 0.65rem;">${item.source} • ${pubDate}</span>
                                            </div>
                                            <div class="text-info fw-bold mb-1" style="font-size: 0.85rem;">${item.title}</div>
                                            <p class="text-secondary mb-2 small" style="line-height: 1.4; font-size: 0.75rem;">${item.description ? item.description.substring(0, 150) + '...' : ''}</p>
                                            <div class="text-end">
                                                <a href="${item.url}" target="_blank" rel="noopener noreferrer" class="text-info small fw-bold text-decoration-none" style="font-size: 0.7rem;">
                                                    <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Kunjungi Sumber
                                                </a>
                                            </div>
                                        </div>
                                    `;
                                }).join('');
                            }
                        }
                    })
                    .catch(err => {
                        console.error("Error fetching overview news:", err);
                        list.innerHTML = '<span class="text-secondary small italic text-center d-block py-4">Gagal memuat berita logistik.</span>';
                    });
            }

            function endVal(arr) {
                return arr[arr.length - 1].value;
            }

            function renderCompareMetricsChart(dataA, dataB) {
                const ctx = document.getElementById('compareMetricsChart').getContext('2d');
                const categories = ['Weather', 'Inflation', 'Political/News', 'Currency'];
                const scoresA = [dataA.scores.weather, dataA.scores.inflation, dataA.scores.political, dataA.scores.currency];
                const scoresB = [dataB.scores.weather, dataB.scores.inflation, dataB.scores.political, dataB.scores.currency];

                if (compareMetricsChart) compareMetricsChart.destroy();

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
                        plugins: { legend: { labels: { color: '#f8fafc' } } },
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
