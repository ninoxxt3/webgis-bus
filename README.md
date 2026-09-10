# WebGIS Analisis Operasional Bus Kota (K-05)

Sistem Informasi Geografis berbasis web (WebGIS) interaktif dengan konsep visual **Transit Operations Dashboard** untuk menganalisis performa spasial dan efisiensi konsumsi bahan bakar (BBM) armada Bus Kota koridor **Tangerang – Jakarta**.

---

## Identitas Pengembang
- **Nama Mahasiswa**: Flarino Marco Cristvan Zakaria
- **Kode CaAs**: 2675
- **Tugas**: Praktikum WebGIS Minggu 5

---

## 1. Judul Project
**WebGIS Analisis Operasional & Efisiensi Bahan Bakar Bus Kota (Armada K-05: Tangerang – Jakarta)**

---

## 2. Nama Mahasiswa
**Flarino Marco Cristvan Zakaria**

---

## 3. Kode CaAs
**2675**

---

## 4. Deskripsi
Proyek ini merupakan platform telemetri dan geovisualisasi operasional transportasi publik yang memantau pergerakan armada bus kota dengan kode kendaraan **K-05** pada koridor antarkota **Tangerang – Jakarta**. Sistem menyajikan 4 trip perjalanan dalam 1 hari operasional lengkap dengan metrik jarak tempuh, durasi, kecepatan rata-rata/maksimum, konsumsi BBM Biosolar, serta audit pemborosan operasional akibat kemacetan dan waktu mesin menyala tanpa bergerak (*idle*).

Aplikasi dirancang dengan tata letak horizontal modern, sidebar navigasi responsif, panel metrik ringkas, peta Leaflet.js berukuran besar sebagai pusat perhatian, diagram interaktif Chart.js, serta tabel data tabular yang terstruktur rapi.

---

## 5. Tujuan
1. **Visualisasi Spasial**: Memetakan rute aktual dan titik awal/akhir dari 4 trip perjalanan bus K-05 secara interaktif.
2. **Evaluasi Efisiensi Energi**: Membandingkan efisiensi konsumsi BBM aktual dengan nilai acuan standar industri (3,3 km/liter).
3. **Analisis Inefisiensi & Kerugian**: Mengidentifikasi besaran BBM terbuang (*wasted fuel*) dan kerugian finansial akibat kondisi operasional dan kemacetan lalu lintas.
4. **Penyediaan Layanan Data Terbuka**: Menyediakan RESTful API berbasis Laravel untuk konsumsi data spasial GeoJSON dan tabular.

---

## 6. Dataset
Dataset yang digunakan merupakan data simulasi telemetri bus kota yang disimpan pada direktori `public/data/`:
1. `public/data/gps_mentah.csv`: Rekaman koordinat GPS mentah sebanyak 634 titik (kolom: `trip_id`, `waktu`, `latitude`, `longitude`, `kecepatan_kmh`, `jarak_km`).
2. `public/data/ringkasan.json`: Parameter umum operasional kendaraan K-05 (jarak total 135 km, total BBM 50,1 liter, biaya total Rp340.740, acuan 3,3 km/L).
3. `public/data/rute.geojson`: Layer spasial *LineString* 4 rute perjalanan dengan properti lengkap per trip.
4. `public/data/titik_ujung.geojson`: Layer spasial *Point* 8 titik awal dan akhir terminal keberangkatan/kedatangan.

*Catatan: Seluruh file dataset asli tetap terjaga keasliannya tanpa perubahan isi.*

---

## 7. Teknologi
- **Backend**: Laravel 11.x, PHP 8.3.x
- **Frontend Templating**: Laravel Blade
- **Bahasa**: PHP, JavaScript (Vanilla ES6+), HTML5, CSS3
- **Pemetaan Spasial**: Leaflet.js 1.9.4
- **Tile Layer**: OpenStreetMap Standard (https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png - Bebas API Key)
- **Visualisasi Data**: Chart.js 4.4.1
- **Tipografi**: Plus Jakarta Sans (Google Fonts)
- **Code Styling**: Laravel Pint
- **Testing**: PHPUnit 11.x

*(Proyek ini murni menggunakan Blade dan JavaScript Vanilla tanpa React maupun Vue).*

---

## 8. Struktur Folder
```text
webgis-bus/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── WebGISController.php     # Controller API dan View Dashboard
│   └── Services/
│       └── GpsPreprocessingService.php  # Service Validasi, Sorting, dan Audit GPS
├── public/
│   ├── css/
│   │   └── transit-dashboard.css        # Desain Transit Operations Dashboard
│   ├── js/
│   │   └── dashboard.js                 # Integrasi Leaflet, Filter, dan Chart.js
│   └── data/
│       ├── gps_mentah.csv               # Dataset GPS Mentah Asli (634 baris)
│       ├── ringkasan.json               # Parameter Operasional Asli
│       ├── rute.geojson                 # Geometri Garis 4 Rute Asli
│       ├── titik_ujung.geojson          # Geometri Titik Terminal Asli
│       ├── gps_clean.csv                # Output Preprocessing CSV Bersih
│       └── gps_preprocessed.json        # Output Audit & Agregasi Preprocessing
├── resources/
│   └── views/
│       └── dashboard.blade.php          # View Utama Dashboard WebGIS
├── routes/
│   └── web.php                          # Rute Web dan REST API
├── tests/
│   └── Feature/
│       └── WebGISTest.php               # Feature Tests PHPUnit (6 Pengujian)
└── README.md
```

---

## 9. Preprocessing
Proses preprocessing dilakukan terhadap `gps_mentah.csv` melalui `App\Services\GpsPreprocessingService`:
- **Validasi Koordinat Geografis**: Memfilter dan memastikan koordinat berada dalam rentang bounding box koridor Tangerang – Jakarta (Latitude: -6.50 s/d -6.00, Longitude: 106.50 s/d 107.10).
- **Validasi Timestamp**: Memvalidasi format waktu berstandar ISO `Y-m-d H:i:s`.
- **Sorting Kronologis**: Mengurutkan data secara bertingkat berdasarkan `trip_id ASC` kemudian `waktu ASC`.
- **Pengecekan Missing Values**: Hasil audit menunjukkan **0 missing value**.
- **Pengecekan Duplikasi**: Hasil audit menunjukkan **0 duplikasi**.
- **Agregasi per Trip**: Menghitung jarak kumulatif, durasi, kecepatan rata-rata, kecepatan puncak, dan waktu diam (*idle* kecepatan <= 0.5 km/jam).
- **Hasil Preprocessing**: Disimpan terpisah ke `public/data/gps_clean.csv` dan `public/data/gps_preprocessed.json`.

---

## 10. Fitur WebGIS
1. **Sidebar Navigasi Transit GIS**: Navigasi cepat dengan status pulse armada real-time dan dukungan responsive menu pada layar tablet/mobile.
2. **Header Telemetri & Identitas**: Menampilkan nomor armada (K-05), rute (Tangerang – Jakarta), Nama Mahasiswa, dan Kode CaAs.
3. **Kartu Status Operasional Ringkas**: Ringkasan 4 Trip, 135 km, 50,1 Liter Biosolar, Total Biaya Rp340.740, dan Efisiensi Aktual.
4. **Peta Leaflet Ukuran Besar**:
   - Tampilan basemap standar OpenStreetMap (bebas watermark & tanpa API key).
   - 4 style warna garis rute unik untuk tiap trip (Cyan, Amber, Rose, Violet).
   - Marker titik awal (Pin A hijau) dan titik akhir (Pin B merah).
   - Filter interaktif: tombol `Semua Trip`, `Trip 1`, `Trip 2`, `Trip 3`, `Trip 4` dengan auto-zoom bounding box.
   - Popup interaktif multibahasa Indonesia informatif saat garis rute atau pin diklik.
   - Legend rute dan terminal.
5. **Panel Trip Performance**: 4 kartu ringkasan trip yang memuat data aktual langsung dari `rute.geojson`.
6. **Analisis BBM Komparatif**: Visualisasi perbandingan efisiensi acuan (3,3 km/L) vs aktual (2,69 km/L) beserta penjelasan naratif penyebab inefisiensi.
7. **3 Grafik Interaktif Chart.js**:
   - Grafik 1: Efisiensi km/liter per trip dengan garis target acuan (3,3 km/L).
   - Grafik 2: Komposisi konsumsi BBM (Jalan, Idle, Boros) per trip.
   - Grafik 3: Biaya BBM riil vs Biaya akibat pemborosan.
8. **Tabel Data Responsif**: Tabel log perjalanan 12 kolom dengan scrolling horizontal halus.
9. **Panel Audit Preprocessing & REST API**: Menampilkan ringkasan audit data serta tautan langsung ke endpoint API.

---

## 11. Analisis
- **Efisiensi Acuan**: `3,3 km/liter`
- **Efisiensi Aktual**: `135,0 km / 50,1 liter = 2,69 km/liter`
- **Tingkat Inefisiensi**: Defisit `0,61 km/liter` atau **18,48% lebih boros** dibanding standar.
- **Dinamika Antartrip**:
  - **Trip 1 (Pagi 05:22 - 06:22)**: Paling efisien dengan `2,98 km/liter` karena lalu lintas pagi relatif lancar (kecepatan rata-rata 33,8 km/jam, waktu idle 0 menit).
  - **Trip 2 (Siang 09:44 - 10:56)**: Mencapai `2,79 km/liter` dengan kecepatan rata-rata 28,1 km/jam.
  - **Trip 3 (Sore 14:55 - 16:27)**: Paling boros dengan `2,50 km/liter` akibat kecepatan rata-rata jatuh ke 21,9 km/jam karena kepadatan jam pulang kerja.
  - **Trip 4 (Malam 18:52 - 20:22)**: Menghasilkan waktu idle tertinggi (**9 menit / 0,39 liter BBM**), efisiensi `2,57 km/liter`.
- **Dampak Finansial**:
  - Total BBM terbuang: **9,2 liter**
  - Total kerugian biaya akibat pemborosan: **Rp62.472** (18,3% dari total anggaran bahan bakar).

---

## 12. Cara Menjalankan
### Prasyarat
- PHP >= 8.3 dengan ekstensi `pdo_sqlite`, `fileinfo`, `mbstring`
- Composer 2.x

### Langkah Instalasi
1. Buka terminal di direktori proyek `webgis-bus`:
   ```bash
   cd d:/Laragon/laragon/www/webgis-bus
   ```
2. Pastikan file `.env` sudah dikonfigurasi:
   ```bash
   STUDENT_NAME="Flarino Marco Cristvan Zakaria"
   CAAS_CODE="2675"
   ```
3. Jalankan automated test PHPUnit untuk memverifikasi seluruh komponen:
   ```bash
   php artisan test
   ```
4. Jalankan server lokal Laravel:
   ```bash
   php artisan serve
   ```
5. Akses aplikasi melalui browser pada alamat:
   ```text
   http://127.0.0.1:8000
   ```

### Daftar Endpoint REST API
- `GET /api/ringkasan`: Data ringkasan parameter kendaraan K-05.
- `GET /api/geojson/rute`: GeoJSON FeatureCollection 4 rute perjalanan.
- `GET /api/geojson/titik-ujung`: GeoJSON FeatureCollection 8 titik awal dan akhir.
- `GET /api/gps-mentah`: Data JSON 634 koordinat GPS mentah.
- `GET /api/gps-preprocessed`: Hasil audit integritas dan agregasi trip.

---

## 13. Cara Deployment
Aplikasi dapat dideploy secara cepat ke lingkungan produksi menggunakan:
1. **Shared Hosting / VPS (Nginx / Apache)**:
   - Arahkan *document root* web server ke direktori `public/`.
   - Set environment variable pada file `.env` produksi:
     ```bash
     APP_ENV=production
     APP_DEBUG=false
     APP_URL=https://domain-anda.com
     ```
   - Optimalkan cache Laravel:
     ```bash
     php artisan config:cache
     php artisan route:cache
     php artisan view:cache
     ```
2. **Laravel Cloud / PaaS**:
   - Hubungkan repositori ke platform deployment (misal Laravel Cloud, Railway, atau Render).
   - Tentukan build command: `composer install --no-dev --optimize-autoloader`.
   - Tentukan start command: `php artisan serve --host=0.0.0.0 --port=$PORT` atau gunakan container PHP-FPM / Nginx.

---

## 14. Keterbatasan Data
- **Data Simulasi**: Data merupakan hasil simulasi telemetri terkomputasi dan bukan rekaman sensor kendaraan fisik sungguhan di lapangan.
- **Satu Armada (K-05)**: Data hanya merefleksikan satu unit kendaraan bus kota (kode armada K-05).
- **Rentang Waktu Terbatas**: Dataset hanya mencakup 4 trip perjalanan dalam kurun waktu 1 hari operasional (Senin, 03-03-2025).
- **Bukan Generalisasi**: Hasil analisis efisiensi ini tidak dapat digeneralisasi untuk merepresentasikan seluruh armada bus pada koridor Tangerang – Jakarta.
- **Kondisi BBM Acuan**: Angka efisiensi acuan (3,3 km/liter) didasarkan pada standar estimasi dataset yang tersedia.
