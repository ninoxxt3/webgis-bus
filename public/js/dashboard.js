/**
 * Transit Operations Dashboard - Frontend Logic
 * Fleet: K-05 (Bus Kota Tangerang – Jakarta)
 * Pure Vanilla JavaScript (No React / Vue)
 */

// Global State
let map = null;
const tripRouteLayers = {};
const tripMarkerLayers = {};
let allRouteBounds = null;
let currentFilter = 'all';
let activeHighlightedTrip = null;

const tripColorPalette = {
    1: '#0ea5e9', // Trip 1: Sky Blue
    2: '#f59e0b', // Trip 2: Amber
    3: '#f43f5e', // Trip 3: Rose/Crimson
    4: '#8b5cf6'  // Trip 4: Violet/Purple
};

// -----------------------------------------------------------------------------
// Helper Formatting Functions
// -----------------------------------------------------------------------------
function formatRupiah(val) {
    return 'Rp' + Number(val || 0).toLocaleString('id-ID');
}

function formatDecimal(val, digits = 2) {
    return Number(val || 0).toLocaleString('id-ID', {
        minimumFractionDigits: digits,
        maximumFractionDigits: digits
    });
}

// -----------------------------------------------------------------------------
// Normalisasi Data GeoJSON (BUG 4)
// -----------------------------------------------------------------------------
function normalizeTripProperties(props) {
    if (!props) props = {};
    const tripId = Number(props.trip_id || props.tripId || props.id || 1);
    const jarak = Number(props.jarak_km !== undefined ? props.jarak_km : (props.distance_km || 0));
    const durasi = Number(props.durasi_menit !== undefined ? props.durasi_menit : (props.duration_minutes || 0));
    const kecRata = Number(props.kecepatan_rata !== undefined ? props.kecepatan_rata : (props.kecepatan_rata_rata || props.avg_speed || 0));
    const kecMaks = Number(props.kecepatan_maks !== undefined ? props.kecepatan_maks : (props.kecepatan_maksimum || props.max_speed || 0));
    const bbmTotal = Number(props.liter_total !== undefined ? props.liter_total : (props.bbm_total || props.fuel_total || 0));
    const bbmJalan = Number(props.liter_jalan !== undefined ? props.liter_jalan : (bbmTotal - (props.liter_idle || 0)));
    const bbmIdle = Number(props.liter_idle !== undefined ? props.liter_idle : 0);
    const menitIdle = Number(props.menit_idle !== undefined ? props.menit_idle : 0);
    const kmPerLiter = Number(props.km_per_liter !== undefined ? props.km_per_liter : (bbmTotal > 0 ? (jarak / bbmTotal) : 0));
    const biaya = Number(props.biaya_rp !== undefined ? props.biaya_rp : (props.biaya || (bbmTotal * 6800)));
    const literBoros = Number(props.liter_boros !== undefined ? props.liter_boros : (props.wasted_fuel || 0));
    const biayaBoros = Number(props.biaya_boros_rp !== undefined ? props.biaya_boros_rp : (literBoros * 6800));

    // Arah panah representatif
    let arahDesc = props.arah || '';
    if (!arahDesc) {
        arahDesc = (tripId % 2 === 1) ? 'Jakarta → Tangerang' : 'Tangerang → Jakarta';
    } else {
        arahDesc = arahDesc.replace('ke', '→');
    }

    return {
        tripId,
        nama: props.nama || `TRIP ${tripId}`,
        kendaraan: props.kendaraan || 'Bus kota',
        kodeKendaraan: props.kode_kendaraan || 'K-05',
        trayek: props.trayek || 'Tangerang – Jakarta',
        arah: arahDesc,
        hari: props.hari || 'Senin',
        tanggal: props.tanggal || '03-03-2025',
        jamMulai: props.jam_mulai || '',
        jamSelesai: props.jam_selesai || '',
        durasiMenit: durasi,
        jarakKm: jarak,
        kecepatanRata: kecRata,
        kecepatanMaks: kecMaks,
        literTotal: bbmTotal,
        literJalan: bbmJalan,
        literIdle: bbmIdle,
        menitIdle: menitIdle,
        kmPerLiter: kmPerLiter,
        biayaRp: biaya,
        literBoros: literBoros,
        biayaBorosRp: biayaBoros,
        jenisBbm: props.jenis_bbm || 'Biosolar'
    };
}

// -----------------------------------------------------------------------------
// Inisialisasi Utama Saat Halaman Siap
// -----------------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
    initSidebarToggle();
    initFilterButtonsEventDelegation();
    initTransitApp();
});

window.addEventListener('load', () => {
    if (!efficiencyChartInstance && window.transitTripsData && typeof Chart !== 'undefined') {
        renderCharts(window.transitTripsData);
    }
});

// -----------------------------------------------------------------------------
// Sidebar Mobile Navigation
// -----------------------------------------------------------------------------
function initSidebarToggle() {
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');

    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            sidebar.classList.toggle('open');
        });

        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 900 && !sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        });
    }

    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href && href.startsWith('#')) {
                e.preventDefault();
                document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                this.classList.add('active');
                const targetEl = document.querySelector(href);
                if (targetEl) {
                    targetEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
                if (window.innerWidth <= 900 && sidebar) {
                    sidebar.classList.remove('open');
                }
            }
        });
    });
}

// -----------------------------------------------------------------------------
// Event Delegation Filter Tombol (BUG 1 - Langsung Aktif Tanpa Menunggu Fetch)
// -----------------------------------------------------------------------------
function initFilterButtonsEventDelegation() {
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.trip-filter-btn');
        if (btn) {
            e.preventDefault();
            const trip = btn.getAttribute('data-trip');
            applyTripFilter(trip);
        }
    });
}

function updateFilterButtonStyles(selectedTrip) {
    const buttons = document.querySelectorAll('.trip-filter-btn');
    buttons.forEach(btn => {
        const tripVal = btn.getAttribute('data-trip');
        if (tripVal === String(selectedTrip)) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    const statusText = document.getElementById('mapFilterStatus');
    if (statusText) {
        if (selectedTrip === 'all') {
            statusText.textContent = 'Menampilkan Seluruh Rute (4 Trip)';
        } else {
            statusText.textContent = `Menampilkan Trip ${selectedTrip}`;
        }
    }
}

function applyTripFilter(trip) {
    currentFilter = trip;
    updateFilterButtonStyles(trip);

    if (!map) return;

    if (trip === 'all') {
        activeHighlightedTrip = null;
        // Tampilkan seluruh layer route & marker
        Object.keys(tripRouteLayers).forEach(id => {
            const layer = tripRouteLayers[id];
            const color = tripColorPalette[id] || '#0ea5e9';
            if (!map.hasLayer(layer)) {
                map.addLayer(layer);
            }
            // Reset style normal
            layer.setStyle({
                color: color,
                weight: 5,
                opacity: 0.88
            });
        });

        Object.values(tripMarkerLayers).forEach(markers => {
            markers.forEach(m => {
                if (!map.hasLayer(m)) {
                    map.addLayer(m);
                }
            });
        });

        if (allRouteBounds && allRouteBounds.isValid()) {
            map.fitBounds(allRouteBounds, { padding: [40, 40] });
        }
    } else {
        const selectedTripId = parseInt(trip, 10);
        activeHighlightedTrip = selectedTripId;

        // Hanya tampilkan route trip yang dipilih
        Object.keys(tripRouteLayers).forEach(id => {
            const layer = tripRouteLayers[id];
            const color = tripColorPalette[id] || '#0ea5e9';
            if (parseInt(id, 10) === selectedTripId) {
                if (!map.hasLayer(layer)) {
                    map.addLayer(layer);
                }
                // Highlight route yang dipilih
                layer.setStyle({
                    color: color,
                    weight: 8,
                    opacity: 1
                });
                try {
                    map.fitBounds(layer.getBounds(), { padding: [50, 50] });
                } catch (err) {
                    console.warn('Gagal fitBounds layer:', err);
                }
            } else {
                if (map.hasLayer(layer)) {
                    map.removeLayer(layer);
                }
            }
        });

        // Filter marker titik ujung: hanya tampilkan marker milik trip ini
        Object.keys(tripMarkerLayers).forEach(id => {
            const markers = tripMarkerLayers[id];
            if (parseInt(id, 10) === selectedTripId) {
                markers.forEach(m => {
                    if (!map.hasLayer(m)) map.addLayer(m);
                });
            } else {
                markers.forEach(m => {
                    if (map.hasLayer(m)) map.removeLayer(m);
                });
            }
        });
    }
}

// -----------------------------------------------------------------------------
// Inisialisasi Peta & Muat Data (BUG 1, 2, 3, 5, 6)
// -----------------------------------------------------------------------------
async function initTransitApp() {
    const mapContainer = document.getElementById('map');
    if (!mapContainer) return;

    try {
        // Inisialisasi Peta Leaflet
        map = L.map('map', {
            center: [-6.165, 106.795],
            zoom: 11,
            zoomControl: true,
            attributionControl: true
        });

        // Basemap OpenStreetMap Standard (Tanpa API Key, Tanpa Watermark)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(map);

    } catch (e) {
        console.error('Inisialisasi Leaflet gagal:', e);
        return;
    }

    // Ambil data dari API atau Fallback Data Blade
    let ruteData = window.INITIAL_RUTE_DATA || null;
    let titikData = window.INITIAL_TITIK_DATA || null;

    try {
        const [ruteRes, titikRes] = await Promise.all([
            fetch('/api/geojson/rute').catch(() => null),
            fetch('/api/geojson/titik-ujung').catch(() => null)
        ]);

        if (ruteRes && ruteRes.ok) {
            ruteData = await ruteRes.json();
        }
        if (titikRes && titikRes.ok) {
            titikData = await titikRes.json();
        }
    } catch (fetchErr) {
        console.warn('Menggunakan data inisial lokal:', fetchErr);
    }

    // 1. Render Rute & Marker ke Peta
    if (ruteData) {
        renderRoutes(ruteData);
    } else {
        console.error('Data rute.geojson tidak tersedia.');
    }

    if (titikData) {
        renderEndPoints(titikData);
    }

    // 2. Render Tabel Perjalanan
    if (ruteData) {
        renderTable(ruteData);
    }

    // 3. Render Grafik Chart.js (LANGKAH 4 & 5)
    if (ruteData && ruteData.features) {
        try {
            const trips = ruteData.features
                .map(feat => {
                    const p = normalizeTripProperties(feat.properties);
                    return {
                        trip: `Trip ${p.tripId}`,
                        tripId: p.tripId,
                        efficiency: Number(p.kmPerLiter.toFixed(2)),
                        fuel: Number(p.literJalan.toFixed(2)),
                        fuelTotal: Number(p.literTotal.toFixed(2)),
                        fuelIdle: Number(p.literIdle.toFixed(2)),
                        fuelWaste: Number(p.literBoros.toFixed(2)),
                        cost: Math.round(p.biayaRp),
                        wasteCost: Math.round(p.biayaBorosRp)
                    };
                })
                .sort((a, b) => a.tripId - b.tripId);

            window.transitTripsData = trips;
            renderCharts(trips);
        } catch (err) {
            console.error('Gagal memproses data rute untuk grafik:', err);
        }
    }

    // Pastikan filter aktif sesuai default
    updateFilterButtonStyles(currentFilter);
}

// -----------------------------------------------------------------------------
// Render Rute Perjalanan & Event Popup (BUG 2 & BUG 7)
// -----------------------------------------------------------------------------
function renderRoutes(geojsonData) {
    if (!geojsonData || !geojsonData.features) return;

    allRouteBounds = L.latLngBounds();

    geojsonData.features.forEach(feature => {
        const p = normalizeTripProperties(feature.properties);
        const tripId = p.tripId;
        const color = tripColorPalette[tripId] || '#0ea5e9';

        const layer = L.geoJSON(feature, {
            style: {
                color: color,
                weight: 5,
                opacity: 0.88,
                lineCap: 'round',
                lineJoin: 'round'
            },
            onEachFeature: (feat, lay) => {
                // Hover effect (BUG 7)
                lay.on('mouseover', function () {
                    if (activeHighlightedTrip !== tripId) {
                        this.setStyle({ weight: 7, opacity: 1 });
                    }
                });

                lay.on('mouseout', function () {
                    if (activeHighlightedTrip !== tripId) {
                        this.setStyle({ weight: 5, opacity: 0.88 });
                    }
                });

                // Format Popup Informatif Rapi (BUG 2)
                const popupContent = `
                    <div class="popup-header" style="border-top: 4px solid ${color};">
                        <div>
                            <div class="popup-title">TRIP ${tripId}</div>
                            <div style="font-size:0.75rem; color:#94a3b8; font-weight:600;">${p.arah}</div>
                        </div>
                        <span class="trip-pill" style="background:${color}22; color:${color}; font-size:0.75rem;">
                            ${p.kodeKendaraan}
                        </span>
                    </div>
                    <div class="popup-body">
                        <div class="popup-row">
                            <span class="popup-label">Tanggal:</span>
                            <span class="popup-val">${p.hari}, ${p.tanggal}</span>
                        </div>
                        <div class="popup-row">
                            <span class="popup-label">Waktu:</span>
                            <span class="popup-val">${p.jamMulai} – ${p.jamSelesai} WIB</span>
                        </div>
                        <div class="popup-row">
                            <span class="popup-label">Durasi:</span>
                            <span class="popup-val">${formatDecimal(p.durasiMenit, 1)} menit</span>
                        </div>
                        <div class="popup-row">
                            <span class="popup-label">Jarak:</span>
                            <span class="popup-val highlight">${formatDecimal(p.jarakKm, 2)} km</span>
                        </div>
                        <div class="popup-row">
                            <span class="popup-label">Kecepatan rata-rata:</span>
                            <span class="popup-val">${formatDecimal(p.kecepatanRata, 1)} km/j</span>
                        </div>
                        <div class="popup-row">
                            <span class="popup-label">Kecepatan maksimum:</span>
                            <span class="popup-val">${formatDecimal(p.kecepatanMaks, 1)} km/j</span>
                        </div>
                        <div class="popup-row">
                            <span class="popup-label">BBM:</span>
                            <span class="popup-val">${formatDecimal(p.literTotal, 2)} Liter (${p.jenisBbm})</span>
                        </div>
                        <div class="popup-row">
                            <span class="popup-label">Efisiensi:</span>
                            <span class="popup-val ${p.kmPerLiter < 3.3 ? 'danger' : 'highlight'}">
                                ${formatDecimal(p.kmPerLiter, 2)} km/liter
                            </span>
                        </div>
                        <div class="popup-row">
                            <span class="popup-label">Biaya BBM:</span>
                            <span class="popup-val">${formatRupiah(p.biayaRp)}</span>
                        </div>
                        <div class="popup-row">
                            <span class="popup-label">BBM Boros:</span>
                            <span class="popup-val danger">${formatDecimal(p.literBoros, 2)} Liter</span>
                        </div>
                        <div class="popup-row">
                            <span class="popup-label">Biaya Boros:</span>
                            <span class="popup-val danger">${formatRupiah(p.biayaBorosRp)}</span>
                        </div>
                    </div>
                `;

                // Bind popup standar
                lay.bindPopup(popupContent, { maxWidth: 360, className: 'transit-popup' });

                // Event Click pada Route: Buka Popup di lokasi klik & Highlight (BUG 2 & BUG 7)
                lay.on('click', function (e) {
                    this.openPopup(e.latlng);
                    this.setStyle({ weight: 9, opacity: 1 });
                });
            }
        });

        tripRouteLayers[tripId] = layer;
        layer.addTo(map);

        try {
            allRouteBounds.extend(layer.getBounds());
        } catch (err) {
            console.warn('Bounds error:', err);
        }
    });

    if (allRouteBounds && allRouteBounds.isValid()) {
        map.fitBounds(allRouteBounds, { padding: [40, 40] });
    }
}

// -----------------------------------------------------------------------------
// Render Marker Titik Awal & Akhir (BUG 2)
// -----------------------------------------------------------------------------
function renderEndPoints(geojsonData) {
    if (!geojsonData || !geojsonData.features) return;

    geojsonData.features.forEach(feature => {
        const p = feature.properties || {};
        const tripId = Number(p.trip_id || 1);
        const jenis = p.jenis || 'Titik';
        const isStart = jenis.toLowerCase().includes('awal');
        const coords = [feature.geometry.coordinates[1], feature.geometry.coordinates[0]];

        const pinColor = isStart ? '#10b981' : '#ef4444';
        const pinSymbol = isStart ? 'A' : 'B';

        const customIcon = L.divIcon({
            className: 'custom-transit-pin',
            html: `
                <div style="
                    background-color: ${pinColor};
                    color: #ffffff;
                    width: 26px;
                    height: 26px;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 11px;
                    font-weight: 800;
                    border: 2px solid #ffffff;
                    box-shadow: 0 3px 10px rgba(0,0,0,0.5);
                    cursor: pointer;
                ">
                    ${pinSymbol}
                </div>
            `,
            iconSize: [26, 26],
            iconAnchor: [13, 13],
            popupAnchor: [0, -14]
        });

        const marker = L.marker(coords, { icon: customIcon });

        // Popup Titik Awal / Akhir Sesuai Spesifikasi Soal (BUG 2)
        const popupHeaderTitle = `Trip ${tripId} — ${isStart ? 'Titik Awal' : 'Titik Akhir'}`;
        const markerPopupHtml = `
            <div style="padding: 12px 16px; font-size: 0.85rem; min-width: 200px;">
                <div style="font-weight: 700; color: ${pinColor}; font-size: 0.95rem; margin-bottom: 6px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 4px;">
                    ${popupHeaderTitle}
                </div>
                <div style="display:flex; flex-direction:column; gap:4px; color:#cbd5e1; font-size:0.82rem;">
                    <div>Waktu: <strong>${p.waktu || '-'} WIB</strong></div>
                    <div>Hari: <strong>${p.hari || 'Senin'}</strong></div>
                    <div>Jarak: <strong>${formatDecimal(p.jarak_km, 2)} km</strong></div>
                    <div>BBM: <strong>${formatDecimal(p.liter_total, 2)} L</strong></div>
                    <div>Biaya: <strong>${formatRupiah(p.biaya_rp)}</strong></div>
                    <div style="color: #94a3b8; font-size: 0.72rem; margin-top: 4px;">
                        Koordinat: ${coords[0].toFixed(5)}, ${coords[1].toFixed(5)}
                    </div>
                </div>
            </div>
        `;

        marker.bindPopup(markerPopupHtml, { maxWidth: 280, className: 'transit-popup' });

        if (!tripMarkerLayers[tripId]) {
            tripMarkerLayers[tripId] = [];
        }
        tripMarkerLayers[tripId].push(marker);
        marker.addTo(map);
    });
}

// -----------------------------------------------------------------------------
// Chart.js Visualizations (LANGKAH 5 & 6)
// -----------------------------------------------------------------------------
let efficiencyChartInstance = null;
let fuelChartInstance = null;
let costChartInstance = null;

function renderCharts(trips) {
    if (typeof Chart === 'undefined') {
        console.error('Chart.js belum berhasil dimuat');
        return;
    }

    const efficiencyCanvas = document.getElementById('efficiencyChart');
    const fuelCanvas = document.getElementById('fuelChart');
    const costCanvas = document.getElementById('costChart');

    if (!efficiencyCanvas) {
        console.error('Canvas efficiencyChart tidak ditemukan');
    }
    if (!fuelCanvas) {
        console.error('Canvas fuelChart tidak ditemukan');
    }
    if (!costCanvas) {
        console.error('Canvas costChart tidak ditemukan');
    }

    if (!Array.isArray(trips) || trips.length === 0) {
        console.warn('Data trip tidak tersedia untuk renderCharts');
        return;
    }

    // Hindari Error "Canvas is already in use" (LANGKAH 6)
    if (efficiencyChartInstance) {
        efficiencyChartInstance.destroy();
        efficiencyChartInstance = null;
    }
    if (fuelChartInstance) {
        fuelChartInstance.destroy();
        fuelChartInstance = null;
    }
    if (costChartInstance) {
        costChartInstance.destroy();
        costChartInstance = null;
    }

    const tripLabels = trips.map(t => t.trip);
    const efficiencyData = trips.map(t => t.efficiency);
    const fuelRoadData = trips.map(t => t.fuel);
    const fuelIdleData = trips.map(t => t.fuelIdle);
    const fuelWasteData = trips.map(t => t.fuelWaste);
    const costData = trips.map(t => t.cost);
    const wasteCostData = trips.map(t => t.wasteCost);

    // Debugging Wajib (LANGKAH 10)
    console.log('Chart.js:', typeof Chart);
    console.log('Trip data:', trips);
    console.log('Efficiency data:', efficiencyData);
    console.log('Fuel data:', fuelRoadData);
    console.log('Cost data:', costData);

    const chartBaseConfig = {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
            duration: 600
        },
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    color: '#94a3b8',
                    font: { family: 'inherit', size: 11, weight: '600' },
                    boxWidth: 12,
                    boxHeight: 12
                }
            },
            tooltip: {
                backgroundColor: '#1e293b',
                borderColor: '#334155',
                borderWidth: 1,
                titleColor: '#ffffff',
                bodyColor: '#cbd5e1',
                padding: 10
            }
        },
        scales: {
            x: {
                grid: { color: 'rgba(255, 255, 255, 0.05)' },
                ticks: { color: '#94a3b8', font: { size: 11, weight: '600' } }
            },
            y: {
                grid: { color: 'rgba(255, 255, 255, 0.05)' },
                ticks: { color: '#94a3b8', font: { size: 11 } }
            }
        }
    };

    try {
        // GRAFIK 1: Efisiensi BBM per Trip (Canvas: efficiencyChart)
        if (efficiencyCanvas) {
            efficiencyChartInstance = new Chart(efficiencyCanvas, {
                type: 'bar',
                data: {
                    labels: tripLabels,
                    datasets: [
                        {
                            label: 'Efisiensi Aktual (km/L)',
                            data: efficiencyData,
                            backgroundColor: ['#0ea5e9', '#f59e0b', '#f43f5e', '#8b5cf6'],
                            borderRadius: 6,
                            barPercentage: 0.55
                        },
                        {
                            type: 'line',
                            label: 'Target Acuan (3,3 km/L)',
                            data: tripLabels.map(() => 3.3),
                            borderColor: '#10b981',
                            borderWidth: 2.5,
                            borderDash: [6, 4],
                            pointRadius: 5,
                            pointBackgroundColor: '#10b981',
                            fill: false
                        }
                    ]
                },
                options: {
                    ...chartBaseConfig,
                    scales: {
                        ...chartBaseConfig.scales,
                        y: {
                            ...chartBaseConfig.scales.y,
                            suggestedMax: 3.8,
                            title: { display: true, text: 'km / liter', color: '#64748b' }
                        }
                    }
                }
            });
        }

        // GRAFIK 2: Konsumsi BBM per Trip (Canvas: fuelChart)
        if (fuelCanvas) {
            fuelChartInstance = new Chart(fuelCanvas, {
                type: 'bar',
                data: {
                    labels: tripLabels,
                    datasets: [
                        {
                            label: 'BBM Jalan (L)',
                            data: fuelRoadData,
                            backgroundColor: '#0284c7',
                            borderRadius: 4
                        },
                        {
                            label: 'BBM Idle (L)',
                            data: fuelIdleData,
                            backgroundColor: '#fbbf24',
                            borderRadius: 4
                        },
                        {
                            label: 'BBM Boros (L)',
                            data: fuelWasteData,
                            backgroundColor: '#ef4444',
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    ...chartBaseConfig,
                    scales: {
                        ...chartBaseConfig.scales,
                        y: {
                            ...chartBaseConfig.scales.y,
                            title: { display: true, text: 'Liter', color: '#64748b' }
                        }
                    }
                }
            });
        }

        // GRAFIK 3: Biaya Riil vs Biaya Boros (Canvas: costChart)
        if (costCanvas) {
            costChartInstance = new Chart(costCanvas, {
                type: 'bar',
                data: {
                    labels: tripLabels,
                    datasets: [
                        {
                            label: 'Biaya BBM Riil (Rp)',
                            data: costData,
                            backgroundColor: '#38bdf8',
                            borderRadius: 6
                        },
                        {
                            label: 'Biaya Pemborosan (Rp)',
                            data: wasteCostData,
                            backgroundColor: '#f87171',
                            borderRadius: 6
                        }
                    ]
                },
                options: {
                    ...chartBaseConfig,
                    scales: {
                        ...chartBaseConfig.scales,
                        y: {
                            ...chartBaseConfig.scales.y,
                            ticks: {
                                color: '#94a3b8',
                                callback: function (val) {
                                    return 'Rp' + (val / 1000) + 'k';
                                }
                            },
                            title: { display: true, text: 'Rupiah (Rp)', color: '#64748b' }
                        }
                    }
                }
            });
        }
    } catch (error) {
        console.error('Gagal membuat grafik:', error);
    }
}

window.renderCharts = renderCharts;

// -----------------------------------------------------------------------------
// Tabel Detail Perjalanan
// -----------------------------------------------------------------------------
function renderTable(geojsonData) {
    const tableBody = document.getElementById('tripTableBody');
    if (!tableBody || !geojsonData || !geojsonData.features) return;

    const features = [...geojsonData.features].sort((a, b) => {
        const idA = a.properties.trip_id || a.properties.id || 0;
        const idB = b.properties.trip_id || b.properties.id || 0;
        return idA - idB;
    });

    tableBody.innerHTML = '';

    features.forEach(feat => {
        const p = normalizeTripProperties(feat.properties);
        const tripId = p.tripId;

        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <span class="trip-pill trip-${tripId}">
                    Trip ${tripId}
                </span>
            </td>
            <td><strong>${p.arah}</strong></td>
            <td>${p.hari}, ${p.tanggal}</td>
            <td>${p.jamMulai} – ${p.jamSelesai}</td>
            <td>${formatDecimal(p.durasiMenit, 1)} m</td>
            <td><strong>${formatDecimal(p.jarakKm, 2)} km</strong></td>
            <td>${formatDecimal(p.kecepatanRata, 1)} km/j</td>
            <td>${formatDecimal(p.kecepatanMaks, 1)} km/j</td>
            <td>${formatDecimal(p.literTotal, 2)} L</td>
            <td style="color:${p.kmPerLiter < 3.3 ? '#f87171' : '#34d399'}; font-weight:700;">
                ${formatDecimal(p.kmPerLiter, 2)}
            </td>
            <td>${formatRupiah(p.biayaRp)}</td>
            <td style="color:#f87171; font-weight:600;">${formatDecimal(p.literBoros, 2)} L</td>
        `;

        tableBody.appendChild(row);
    });
}
