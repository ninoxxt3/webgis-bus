<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transit GIS | Analisis Operasional Bus Kota K-05</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Leaflet CSS (Local Vendor Asset) -->
    <link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}">

    <!-- Custom Transit Operations Dashboard Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/transit-dashboard.css') }}">
</head>
<body>
    <div class="app-container">
        <!-- ================================================================ -->
        <!-- SIDEBAR KIRI: TRANSIT GIS                                        -->
        <!-- ================================================================ -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <!-- SVG Ikon Bus -->
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M8 6v6"></path>
                        <path d="M16 6v6"></path>
                        <path d="M4 6h16a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z"></path>
                        <path d="M6 17v2a1 1 0 0 0 1 1h1a1 1 0 0 0 1-1v-2"></path>
                        <path d="M15 17v2a1 1 0 0 0 1 1h1a1 1 0 0 0 1-1v-2"></path>
                        <circle cx="7" cy="13" r="1"></circle>
                        <circle cx="17" cy="13" r="1"></circle>
                    </svg>
                </div>
                <div>
                    <div class="sidebar-brand-title">Transit GIS</div>
                    <div class="sidebar-brand-subtitle">Bus Operations</div>
                </div>
            </div>

            <!-- Navigasi Menu Sidebar -->
            <nav class="sidebar-nav">
                <a href="#overview" class="nav-link active">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect width="7" height="9" x="3" y="3" rx="1"></rect><rect width="7" height="5" x="14" y="3" rx="1"></rect><rect width="7" height="9" x="14" y="12" rx="1"></rect><rect width="7" height="5" x="3" y="16" rx="1"></rect></svg>
                    <span>Overview</span>
                </a>
                <a href="#peta" class="nav-link">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"></polygon><line x1="9" x2="9" y1="3" y2="18"></line><line x1="15" x2="15" y1="6" y2="21"></line></svg>
                    <span>Peta Perjalanan</span>
                </a>
                <a href="#kinerja-trip" class="nav-link">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"></path><path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"></path></svg>
                    <span>Detail Trip</span>
                </a>
                <a href="#analisis-bbm" class="nav-link">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18"></path><path d="M15 11h2a2 2 0 0 1 2 2v7a2 2 0 0 0 2 2 2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-1"></path><path d="M3 11h10"></path></svg>
                    <span>Analisis BBM</span>
                </a>
                <a href="#grafik-analisis" class="nav-link">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="18" x2="18" y1="20" y2="10"></line><line x1="12" x2="12" y1="20" y2="4"></line><line x1="6" x2="6" y1="20" y2="14"></line></svg>
                    <span>Grafik Evaluasi</span>
                </a>
                <a href="#tabel-perjalanan" class="nav-link">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 3h18v18H3zM3 9h18M3 15h18M9 3v18M15 3v18"></path></svg>
                    <span>Tabel Detail</span>
                </a>
                <a href="#tentang-data" class="nav-link">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"></circle><path d="M12 16v-4"></path><path d="M12 8h.01"></path></svg>
                    <span>Tentang Data</span>
                </a>
            </nav>

            <!-- Status Armada Footer -->
            <div class="sidebar-footer">
                <div class="fleet-badge">
                    <div>
                        <span class="pulse-indicator"></span>
                        <strong style="color:#ffffff;">K-05 Live</strong>
                    </div>
                    <span style="font-size:0.75rem; color:#38bdf8;">135 km OK</span>
                </div>
            </div>
        </aside>

        <!-- ================================================================ -->
        <!-- MAIN CONTENT WRAPPER                                             -->
        <!-- ================================================================ -->
        <div class="main-wrapper">
            <!-- Top Sticky Header -->
            <header class="top-header">
                <div class="header-left">
                    <button class="menu-toggle" id="menuToggle" aria-label="Toggle Menu">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                    </button>
                    <div class="header-title-block">
                        <h1>
                            Bus Kota Operations
                            <span class="header-route-badge">K-05 | Tangerang – Jakarta</span>
                        </h1>
                    </div>
                </div>

                <!-- Identitas Mahasiswa & Kode CaAs -->
                <div class="header-right">
                    <div class="identity-card">
                        <span class="identity-name">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            {{ $studentName }}
                        </span>
                        <span class="identity-caas">Kode CaAs: {{ $caasCode }}</span>
                    </div>
                </div>
            </header>

            <!-- Dashboard Content Container -->
            <main class="content-body">

                <!-- ============================================================ -->
                <!-- BAGIAN 1: STATUS OPERASIONAL (Metric Cards Ringkas)          -->
                <!-- ============================================================ -->
                <section id="overview">
                    <div class="section-header">
                        <div>
                            <h2 class="section-title">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"></path></svg>
                                Status Operasional
                            </h2>
                            <p class="section-subtitle">Ringkasan telemetri harian Bus Kota armada K-05 koridor Tangerang – Jakarta</p>
                        </div>
                        <div>
                            <span class="header-route-badge" style="background: rgba(16, 185, 129, 0.1); color: #34d399; border-color: rgba(16, 185, 129, 0.3);">
                                {{ $ringkasan['jenis_bbm'] ?? 'Biosolar' }} • Rp{{ number_format($ringkasan['harga_per_liter'] ?? 6800, 0, ',', '.') }}/L
                            </span>
                        </div>
                    </div>

                    <div class="metrics-grid">
                        <!-- Card 1: Total Trip -->
                        <div class="metric-card">
                            <div class="metric-card-top">
                                <span class="metric-label">Jumlah Trip</span>
                                <div class="metric-icon-box">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                </div>
                            </div>
                            <div>
                                <span class="metric-value">{{ $ringkasan['jumlah_trip'] ?? 4 }}</span>
                                <span class="metric-unit">Trip</span>
                            </div>
                            <div class="metric-footer">
                                <span>1 Hari Operasional (Senin)</span>
                            </div>
                        </div>

                        <!-- Card 2: Total Jarak Tempuh -->
                        <div class="metric-card">
                            <div class="metric-card-top">
                                <span class="metric-label">Total Jarak Tempuh</span>
                                <div class="metric-icon-box">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"></polygon></svg>
                                </div>
                            </div>
                            <div>
                                <span class="metric-value">{{ number_format($ringkasan['total_km'] ?? 135.0, 0, ',', '.') }}</span>
                                <span class="metric-unit">km</span>
                            </div>
                            <div class="metric-footer">
                                <span>Rata-rata 33,75 km / trip</span>
                            </div>
                        </div>

                        <!-- Card 3: Total Konsumsi BBM -->
                        <div class="metric-card">
                            <div class="metric-card-top">
                                <span class="metric-label">Konsumsi BBM Total</span>
                                <div class="metric-icon-box">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18"></path><path d="M15 11h2a2 2 0 0 1 2 2v7a2 2 0 0 0 2 2 2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-1"></path></svg>
                                </div>
                            </div>
                            <div>
                                <span class="metric-value">{{ number_format($ringkasan['total_liter'] ?? 50.1, 1, ',', '.') }}</span>
                                <span class="metric-unit">Liter</span>
                            </div>
                            <div class="metric-footer">
                                <span>Termasuk idle {{ number_format($ringkasan['liter_idle'] ?? 0.4, 1, ',', '.') }} L</span>
                            </div>
                        </div>

                        <!-- Card 4: Total Biaya BBM -->
                        <div class="metric-card">
                            <div class="metric-card-top">
                                <span class="metric-label">Total Biaya BBM</span>
                                <div class="metric-icon-box">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                                </div>
                            </div>
                            <div>
                                <span class="metric-value" style="font-size: 1.45rem;">Rp{{ number_format($ringkasan['total_biaya'] ?? 340740, 0, ',', '.') }}</span>
                            </div>
                            <div class="metric-footer">
                                <span class="badge-negative">Pemborosan: Rp{{ number_format($ringkasan['biaya_boros'] ?? 62472, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <!-- Card 5: Efisiensi Aktual -->
                        @php
                            $totalKm = $ringkasan['total_km'] ?? 135.0;
                            $totalLiter = $ringkasan['total_liter'] ?? 50.1;
                            $efisiensiAktual = $totalLiter > 0 ? round($totalKm / $totalLiter, 2) : 0;
                            $efisiensiAcuan = $ringkasan['efisiensi_acuan'] ?? 3.3;
                            $selisih = round($efisiensiAcuan - $efisiensiAktual, 2);
                            $persenBoros = round(($selisih / $efisiensiAcuan) * 100, 1);
                        @endphp
                        <div class="metric-card" style="border-color: rgba(239, 68, 68, 0.35);">
                            <div class="metric-card-top">
                                <span class="metric-label">Efisiensi Aktual</span>
                                <div class="metric-icon-box" style="color: #f87171;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 8 14"></polyline></svg>
                                </div>
                            </div>
                            <div>
                                <span class="metric-value" style="color: #f87171;">{{ number_format($efisiensiAktual, 2, ',', '.') }}</span>
                                <span class="metric-unit">km/L</span>
                            </div>
                            <div class="metric-footer">
                                <span class="badge-negative">-{{ $persenBoros }}% vs Acuan ({{ number_format($efisiensiAcuan, 1, ',', '.') }})</span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- ============================================================ -->
                <!-- BAGIAN 2: PETA PERJALANAN (Leaflet - Elemen Terbesar)        -->
                <!-- ============================================================ -->
                <section id="peta">
                    <div class="section-header">
                        <div>
                            <h2 class="section-title">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"></polygon><line x1="8" y1="2" x2="8" y2="18"></line><line x1="16" y1="6" x2="16" y2="22"></line></svg>
                                Peta Perjalanan Bus K-05
                            </h2>
                            <p class="section-subtitle">Visualisasi spasial 4 trip perjalanan bolak-balik koridor Tangerang – Jakarta</p>
                        </div>
                        <div class="map-status-pill">
                            <span class="pulse-indicator"></span>
                            <span id="mapFilterStatus">Menampilkan Seluruh Rute (4 Trip)</span>
                        </div>
                    </div>

                    <div class="map-card">
                        <!-- Toolbar Filter Tombol Trip -->
                        <div class="map-toolbar">
                            <div class="filter-button-group">
                                <button type="button" class="trip-filter-btn active" data-trip="all">
                                    <span class="btn-dot dot-all"></span>
                                    Semua Trip
                                </button>
                                <button type="button" class="trip-filter-btn" data-trip="1">
                                    <span class="btn-dot dot-trip1"></span>
                                    Trip 1
                                </button>
                                <button type="button" class="trip-filter-btn" data-trip="2">
                                    <span class="btn-dot dot-trip2"></span>
                                    Trip 2
                                </button>
                                <button type="button" class="trip-filter-btn" data-trip="3">
                                    <span class="btn-dot dot-trip3"></span>
                                    Trip 3
                                </button>
                                <button type="button" class="trip-filter-btn" data-trip="4">
                                    <span class="btn-dot dot-trip4"></span>
                                    Trip 4
                                </button>
                            </div>

                            <div style="font-size:0.82rem; color:#94a3b8;">
                                Klik pada garis rute atau marker pin untuk melihat detail operasional & konsumsi BBM
                            </div>
                        </div>

                        <!-- Canvas Peta Leaflet -->
                        <div class="map-container-wrapper">
                            <div id="map"></div>

                            <!-- Legend Peta -->
                            <div class="map-legend">
                                <div class="legend-title">
                                    <span>Legend Rute & Titik</span>
                                    <span style="font-size:0.7rem; color:#38bdf8;">K-05 Bus</span>
                                </div>
                                <div class="legend-item">
                                    <div>
                                        <span class="legend-color-line" style="background-color: var(--trip-1);"></span>
                                        <strong>Trip 1</strong>: JKT → TNG (Pagi)
                                    </div>
                                    <span class="legend-meta">05:22</span>
                                </div>
                                <div class="legend-item">
                                    <div>
                                        <span class="legend-color-line" style="background-color: var(--trip-2);"></span>
                                        <strong>Trip 2</strong>: TNG → JKT (Siang)
                                    </div>
                                    <span class="legend-meta">09:44</span>
                                </div>
                                <div class="legend-item">
                                    <div>
                                        <span class="legend-color-line" style="background-color: var(--trip-3);"></span>
                                        <strong>Trip 3</strong>: TNG → JKT (Sore)
                                    </div>
                                    <span class="legend-meta">14:55</span>
                                </div>
                                <div class="legend-item">
                                    <div>
                                        <span class="legend-color-line" style="background-color: var(--trip-4);"></span>
                                        <strong>Trip 4</strong>: JKT → TNG (Malam)
                                    </div>
                                    <span class="legend-meta">18:52</span>
                                </div>
                                <div style="border-top:1px dashed #334155; margin-top:8px; padding-top:8px;" class="legend-item">
                                    <div>
                                        <span style="display:inline-block; width:12px; height:12px; border-radius:50%; background:#10b981; margin-right:8px;"></span>
                                        Titik Awal (Terminal Keberangkatan)
                                    </div>
                                </div>
                                <div class="legend-item">
                                    <div>
                                        <span style="display:inline-block; width:12px; height:12px; border-radius:50%; background:#ef4444; margin-right:8px;"></span>
                                        Titik Akhir (Terminal Kedatangan)
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- ============================================================ -->
                <!-- BAGIAN 3: DATA TRIP (Panel Trip Performance)                -->
                <!-- ============================================================ -->
                <section id="kinerja-trip">
                    <div class="section-header">
                        <div>
                            <h2 class="section-title">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 3v12"></path><path d="M18 9a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"></path><path d="M6 21a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"></path><path d="M15 6a9 9 0 0 0-9 9"></path></svg>
                                Trip Performance
                            </h2>
                            <p class="section-subtitle">Kinerja setiap trip operasional dihitung langsung dari data aktual <code>rute.geojson</code></p>
                        </div>
                    </div>

                    <div class="trip-cards-grid">
                        @if(isset($ruteGeoJson['features']))
                            @foreach($ruteGeoJson['features'] as $feat)
                                @php
                                    $p = $feat['properties'];
                                    $tId = $p['trip_id'];
                                @endphp
                                <div class="trip-card trip-{{ $tId }}-border">
                                    <div>
                                        <div class="trip-card-header">
                                            <span class="trip-badge-name">Trip {{ $tId }}</span>
                                            <span class="trip-time-tag">{{ $p['jam_mulai'] }} – {{ $p['jam_selesai'] }}</span>
                                        </div>
                                        <div class="trip-route-desc">
                                            @if($p['arah'] === 'Jakarta ke Tangerang')
                                                Jakarta → Tangerang
                                            @else
                                                Tangerang → Jakarta
                                            @endif
                                        </div>
                                        <div class="trip-stats-grid">
                                            <div class="trip-stat-item">
                                                <span class="trip-stat-title">Jarak Tempuh</span>
                                                <span class="trip-stat-val">{{ number_format($p['jarak_km'], 2, ',', '.') }} km</span>
                                            </div>
                                            <div class="trip-stat-item">
                                                <span class="trip-stat-title">Efisiensi</span>
                                                <span class="trip-stat-val" style="color: {{ $p['km_per_liter'] < 3.3 ? '#f87171' : '#34d399' }};">
                                                    {{ number_format($p['km_per_liter'], 2, ',', '.') }} km/L
                                                </span>
                                            </div>
                                            <div class="trip-stat-item">
                                                <span class="trip-stat-title">Konsumsi BBM</span>
                                                <span class="trip-stat-val">{{ number_format($p['liter_total'], 2, ',', '.') }} L</span>
                                            </div>
                                            <div class="trip-stat-item">
                                                <span class="trip-stat-title">Kecepatan Rata</span>
                                                <span class="trip-stat-val">{{ number_format($p['kecepatan_rata'], 1, ',', '.') }} km/j</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="trip-card-footer">
                                        <span>Biaya: <strong>Rp{{ number_format($p['biaya_rp'], 0, ',', '.') }}</strong></span>
                                        <span style="color:#f87171;">Boros: {{ number_format($p['liter_boros'], 2, ',', '.') }} L</span>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </section>

                <!-- ============================================================ -->
                <!-- BAGIAN 4: ANALISIS BBM                                      -->
                <!-- ============================================================ -->
                <section id="analisis-bbm">
                    <div class="section-header">
                        <div>
                            <h2 class="section-title">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                                Analisis Bahan Bakar (BBM)
                            </h2>
                            <p class="section-subtitle">Evaluasi komparatif antara efisiensi acuan standar dengan hasil telemetri aktual kendaraan K-05</p>
                        </div>
                    </div>

                    <div class="analysis-container">
                        <!-- Komparasi Efisiensi & Narasi Evaluatif -->
                        <div class="analysis-main-card">
                            <div>
                                <h3 style="font-size:1.05rem; font-weight:700; color:#ffffff; margin-bottom:4px;">
                                    Komparasi Efisiensi: Acuan vs Aktual
                                </h3>
                                <p style="font-size:0.82rem; color:#94a3b8;">
                                    Perhitungan efisiensi aktual: <code>total_km / total_liter = 135 km / 50,1 liter = {{ number_format($efisiensiAktual, 2, ',', '.') }} km/liter</code>
                                </p>

                                <div class="analysis-comparison-box">
                                    <div class="comp-col">
                                        <div class="comp-label">Efisiensi Acuan Standar</div>
                                        <div class="comp-number target">{{ number_format($efisiensiAcuan, 1, ',', '.') }} <span style="font-size:1rem; font-weight:500; color:#64748b;">km/L</span></div>
                                        <div style="font-size:0.75rem; color:#10b981; margin-top:4px;">Target Benchmark Armada</div>
                                    </div>
                                    <div class="comp-vs">VS</div>
                                    <div class="comp-col">
                                        <div class="comp-label">Efisiensi Aktual Telemetri</div>
                                        <div class="comp-number actual">{{ number_format($efisiensiAktual, 2, ',', '.') }} <span style="font-size:1rem; font-weight:500; color:#64748b;">km/L</span></div>
                                        <div style="font-size:0.75rem; color:#f87171; margin-top:4px;">Selisih -{{ number_format($selisih, 2, ',', '.') }} km/L (-{{ $persenBoros }}%)</div>
                                    </div>
                                </div>

                                <div class="analysis-narrative">
                                    <strong>Kesimpulan Evaluasi Efisiensi:</strong>
                                    Kendaraan bus kota <strong>K-05 beroperasi LEBIH BOROS</strong> dibandingkan acuan standar efisiensi. Bus hanya mencapai <strong>{{ number_format($efisiensiAktual, 2, ',', '.') }} km/liter</strong> dari target <strong>{{ number_format($efisiensiAcuan, 1, ',', '.') }} km/liter</strong> (defisit 18,48%).
                                    Faktor utama penurunan efisiensi terjadi pada <strong>Trip 3 (2,50 km/L)</strong> dan <strong>Trip 4 (2,57 km/L)</strong> akibat perlambatan kecepatan rata-rata (21,9 – 22,4 km/jam) pada jam sibuk sore/malam serta waktu idle mesin yang mencapai 9 menit pada Trip 4. Pemborosan total mencapai <strong>{{ number_format($ringkasan['liter_boros'] ?? 9.2, 1, ',', '.') }} liter Biosolar</strong>.
                                </div>
                            </div>
                        </div>

                        <!-- Kartu Rincian Finansial & Pemborosan BBM -->
                        <div class="fuel-breakdown-grid">
                            <div class="fuel-mini-card">
                                <span class="mini-card-title">Total Konsumsi BBM</span>
                                <div class="mini-card-val">{{ number_format($ringkasan['total_liter'] ?? 50.1, 1, ',', '.') }} <span style="font-size:0.9rem; font-weight:500; color:#64748b;">Liter</span></div>
                                <span class="mini-card-desc">Bahan bakar Biosolar selama 4 trip</span>
                            </div>

                            <div class="fuel-mini-card">
                                <span class="mini-card-title">Konsumsi BBM Idle</span>
                                <div class="mini-card-val" style="color: #fbbf24;">{{ number_format($ringkasan['liter_idle'] ?? 0.4, 1, ',', '.') }} <span style="font-size:0.9rem; font-weight:500; color:#64748b;">Liter</span></div>
                                <span class="mini-card-desc">BBM terpakai saat bus berhenti/macet</span>
                            </div>

                            <div class="fuel-mini-card danger">
                                <span class="mini-card-title">Volume BBM Boros</span>
                                <div class="mini-card-val danger-text">{{ number_format($ringkasan['liter_boros'] ?? 9.2, 1, ',', '.') }} <span style="font-size:0.9rem; font-weight:500; color:#64748b;">Liter</span></div>
                                <span class="mini-card-desc">Kelebihan konsumsi di atas acuan 3,3 km/L</span>
                            </div>

                            <div class="fuel-mini-card">
                                <span class="mini-card-title">Total Biaya BBM</span>
                                <div class="mini-card-val" style="font-size:1.3rem;">Rp{{ number_format($ringkasan['total_biaya'] ?? 340740, 0, ',', '.') }}</div>
                                <span class="mini-card-desc">Dihitung pada tarif Rp6.800/liter</span>
                            </div>

                            <div class="fuel-mini-card danger" style="grid-column: span 2;">
                                <span class="mini-card-title">Biaya Akibat Pemborosan (Loss)</span>
                                <div class="mini-card-val danger-text" style="font-size: 1.6rem;">
                                    Rp{{ number_format($ringkasan['biaya_boros'] ?? 62472, 0, ',', '.') }}
                                </div>
                                <span class="mini-card-desc">Kerugian biaya akibat inefisiensi BBM yang dapat dihemat jika armada beroperasi sesuai acuan</span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- ============================================================ -->
                <!-- BAGIAN 5: GRAFIK (Minimal 3 Grafik Chart.js)                -->
                <!-- ============================================================ -->
                <section id="grafik-analisis">
                    <div class="section-header">
                        <div>
                            <h2 class="section-title">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                                Grafik Visualisasi & Analisis
                            </h2>
                            <p class="section-subtitle">Grafik interaktif Chart.js yang bersumber langsung dari properti telemetri <code>rute.geojson</code></p>
                        </div>
                    </div>

                    <div class="charts-grid">
                        <!-- Grafik 1: Efisiensi km/liter setiap trip -->
                        <div class="chart-card">
                            <div class="chart-header">
                                <span class="chart-title">1. Efisiensi BBM per Trip</span>
                                <span class="chart-badge">Target: 3,3 km/L</span>
                            </div>
                            <div class="chart-container">
                                <canvas id="efficiencyChart"></canvas>
                            </div>
                        </div>

                        <!-- Grafik 2: Konsumsi BBM setiap trip -->
                        <div class="chart-card">
                            <div class="chart-header">
                                <span class="chart-title">2. Konsumsi BBM per Trip</span>
                                <span class="chart-badge">Liter (Jalan, Idle, Boros)</span>
                            </div>
                            <div class="chart-container">
                                <canvas id="fuelChart"></canvas>
                            </div>
                        </div>

                        <!-- Grafik 3: Biaya BBM setiap trip -->
                        <div class="chart-card">
                            <div class="chart-header">
                                <span class="chart-title">3. Biaya Riil vs Biaya Boros</span>
                                <span class="chart-badge">Rupiah (Rp)</span>
                            </div>
                            <div class="chart-container">
                                <canvas id="costChart"></canvas>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- ============================================================ -->
                <!-- BAGIAN 6: TABEL DETAIL PERJALANAN (Responsive)              -->
                <!-- ============================================================ -->
                <section id="tabel-perjalanan">
                    <div class="section-header">
                        <div>
                            <h2 class="section-title">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="3" rx="2"></rect><path d="M3 9h18"></path><path d="M3 15h18"></path><path d="M9 3v18"></path></svg>
                                Detail Perjalanan
                            </h2>
                            <p class="section-subtitle">Data tabular komprehensif telemetri perjalanan bus kota K-05</p>
                        </div>
                    </div>

                    <div class="table-card">
                        <div class="table-header-bar">
                            <span style="font-size:0.85rem; font-weight:600; color:#ffffff;">Log 4 Trip Operasional</span>
                            <span style="font-size:0.78rem; color:#94a3b8;">Total 634 Titik GPS Terverifikasi</span>
                        </div>
                        <div class="table-responsive">
                            <table class="transit-table">
                                <thead>
                                    <tr>
                                        <th>Trip</th>
                                        <th>Arah</th>
                                        <th>Tanggal</th>
                                        <th>Jam</th>
                                        <th>Durasi</th>
                                        <th>Jarak</th>
                                        <th>Kecepatan Rata-rata</th>
                                        <th>Kecepatan Maksimum</th>
                                        <th>BBM</th>
                                        <th>km/liter</th>
                                        <th>Biaya</th>
                                        <th>BBM Boros</th>
                                    </tr>
                                </thead>
                                <tbody id="tripTableBody">
                                    @if(isset($ruteGeoJson['features']))
                                        @foreach($ruteGeoJson['features'] as $feat)
                                            @php
                                                $p = $feat['properties'];
                                                $tId = $p['trip_id'];
                                            @endphp
                                            <tr>
                                                <td>
                                                    <span class="trip-pill trip-{{ $tId }}">
                                                        Trip {{ $tId }}
                                                    </span>
                                                </td>
                                                <td><strong>{{ $p['arah'] }}</strong></td>
                                                <td>{{ $p['hari'] }}, {{ $p['tanggal'] }}</td>
                                                <td>{{ $p['jam_mulai'] }} – {{ $p['jam_selesai'] }}</td>
                                                <td>{{ number_format($p['durasi_menit'], 1, ',', '.') }} m</td>
                                                <td><strong>{{ number_format($p['jarak_km'], 2, ',', '.') }} km</strong></td>
                                                <td>{{ number_format($p['kecepatan_rata'], 1, ',', '.') }} km/j</td>
                                                <td>{{ number_format($p['kecepatan_maks'], 1, ',', '.') }} km/j</td>
                                                <td>{{ number_format($p['liter_total'], 2, ',', '.') }} L</td>
                                                <td style="color: {{ $p['km_per_liter'] < 3.3 ? '#f87171' : '#34d399' }}; font-weight:700;">
                                                    {{ number_format($p['km_per_liter'], 2, ',', '.') }}
                                                </td>
                                                <td>Rp{{ number_format($p['biaya_rp'], 0, ',', '.') }}</td>
                                                <td style="color:#f87171; font-weight:600;">{{ number_format($p['liter_boros'], 2, ',', '.') }} L</td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <!-- ============================================================ -->
                <!-- BAGIAN 7: KETERBATASAN DATA                                 -->
                <!-- ============================================================ -->
                <section id="tentang-data">
                    <div class="limitations-card">
                        <div class="section-header" style="margin-bottom:8px;">
                            <h2 class="section-title" style="color:#fbbf24;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                                Keterbatasan Data
                            </h2>
                        </div>
                        <p style="font-size:0.85rem; color:#94a3b8;">
                            Analisis operasional pada dashboard ini didasarkan pada dataset dengan batasan ruang lingkup sebagai berikut:
                        </p>

                        <ul class="limitations-list">
                            <li class="limitations-item">
                                <span class="limitations-icon">⚠️</span>
                                <div><strong>Data Simulasi:</strong> Data merupakan data simulasi dan bukan merupakan rekaman sensor kendaraan sungguhan di lapangan.</div>
                            </li>
                            <li class="limitations-item">
                                <span class="limitations-icon">🚌</span>
                                <div><strong>Spesifik K-05:</strong> Data hanya mencakup satu unit armada kendaraan bus kota (kode K-05).</div>
                            </li>
                            <li class="limitations-item">
                                <span class="limitations-icon">📅</span>
                                <div><strong>Rentang Waktu 1 Hari:</strong> Dataset hanya mencakup 4 trip perjalanan dalam 1 hari operasional (Senin, 03-03-2025).</div>
                            </li>
                            <li class="limitations-item">
                                <span class="limitations-icon">📊</span>
                                <div><strong>Bukan Generalisasi Armada:</strong> Hasil analisis efisiensi ini tidak dapat digeneralisasikan untuk merepresentasikan seluruh armada bus kota trayek Tangerang – Jakarta.</div>
                            </li>
                            <li class="limitations-item" style="grid-column: span 2;">
                                <span class="limitations-icon">⛽</span>
                                <div><strong>Kondisi Acuan BBM:</strong> Nilai konsumsi BBM dan efisiensi acuan (3,3 km/liter) mengacu pada ketetapan dataset yang tersedia dan dapat berbeda dengan kondisi riil di jalanan padat.</div>
                            </li>
                        </ul>
                    </div>
                </section>

                <!-- ============================================================ -->
                <!-- BAGIAN 8: AUDIT PREPROCESSING GPS & REST API                -->
                <!-- ============================================================ -->
                <section>
                    <div class="audit-card">
                        <div class="section-header" style="margin-bottom:6px;">
                            <h2 class="section-title">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                                Hasil Preprocessing & Integritas GPS Mentah
                            </h2>
                            <span class="badge-positive">Status: {{ $gpsAudit['status_integritas'] ?? 'Clean' }}</span>
                        </div>
                        <p style="font-size:0.82rem; color:#94a3b8;">
                            Dataset <code>public/data/gps_mentah.csv</code> telah diaudit melalui algoritma validasi koordinat (bounding box Tangerang–Jakarta), validasi format timestamp ISO, deteksi missing values, pengecekan duplikasi baris, serta pengurutan kronologis.
                        </p>

                        <div class="audit-grid">
                            <div class="audit-item">
                                <div class="audit-label">Baris Mentah</div>
                                <div class="audit-val">{{ $gpsAudit['total_baris_mentah'] ?? 634 }}</div>
                            </div>
                            <div class="audit-item">
                                <div class="audit-label">Baris Valid</div>
                                <div class="audit-val success">{{ $gpsAudit['total_baris_valid'] ?? 634 }}</div>
                            </div>
                            <div class="audit-item">
                                <div class="audit-label">Missing Value</div>
                                <div class="audit-val success">{{ $gpsAudit['jumlah_missing_values'] ?? 0 }}</div>
                            </div>
                            <div class="audit-item">
                                <div class="audit-label">Duplikat</div>
                                <div class="audit-val success">{{ $gpsAudit['jumlah_duplikat'] ?? 0 }}</div>
                            </div>
                            <div class="audit-item">
                                <div class="audit-label">Koordinat Invalid</div>
                                <div class="audit-val success">{{ $gpsAudit['jumlah_koordinat_invalid'] ?? 0 }}</div>
                            </div>
                            <div class="audit-item">
                                <div class="audit-label">File Bersih Dibuat</div>
                                <div class="audit-val" style="font-size:0.85rem; color:#38bdf8;">gps_clean.csv</div>
                            </div>
                        </div>

                        <!-- REST API Links -->
                        <div style="margin-top:16px; padding-top:14px; border-top:1px solid var(--border-color); display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                            <span style="font-size:0.8rem; color:#94a3b8; font-weight:600;">Endpoint API:</span>
                            <a href="{{ url('/api/ringkasan') }}" target="_blank" class="header-route-badge">/api/ringkasan</a>
                            <a href="{{ url('/api/geojson/rute') }}" target="_blank" class="header-route-badge">/api/geojson/rute</a>
                            <a href="{{ url('/api/geojson/titik-ujung') }}" target="_blank" class="header-route-badge">/api/geojson/titik-ujung</a>
                            <a href="{{ url('/api/gps-mentah') }}" target="_blank" class="header-route-badge">/api/gps-mentah</a>
                            <a href="{{ url('/api/gps-preprocessed') }}" target="_blank" class="header-route-badge">/api/gps-preprocessed</a>
                        </div>
                    </div>
                </section>

            </main>

            <!-- Footer Aplikasi -->
            <footer class="app-footer">
                <div>
                    <strong>WebGIS Analisis Operasional Bus Kota (K-05)</strong> — Tugas Minggu 5
                </div>
                <div>
                    Mahasiswa: <strong>{{ $studentName }}</strong> | Kode CaAs: <strong>{{ $caasCode }}</strong>
                </div>
            </footer>
        </div>
    </div>

    <!-- Embedded Data for Instant & Robust Rendering (BUG 1, BUG 3, BUG 5) -->
    <script>
        window.INITIAL_RUTE_DATA = {!! json_encode($ruteGeoJson) !!};
        window.INITIAL_TITIK_DATA = {!! json_encode($titikUjungGeoJson) !!};
        window.INITIAL_RINGKASAN_DATA = {!! json_encode($ringkasan) !!};
    </script>

    <!-- Leaflet JS (Local Vendor Asset) -->
    <script src="{{ asset('vendor/leaflet/leaflet.js') }}"></script>
    <script>
        if (typeof L === 'undefined') {
            document.write('<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"><\/script>');
        }
    </script>

    <!-- Chart.js (Local Vendor Asset with CDN Fallback) -->
    <script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>
    <script>
        if (typeof Chart === 'undefined') {
            document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"><\/script>');
        }
    </script>

    <!-- Custom Dashboard Script -->
    <script src="{{ asset('js/dashboard.js') }}"></script>
</body>
</html>
