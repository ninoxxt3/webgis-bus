<?php

namespace Tests\Feature;

use Tests\TestCase;

class WebGISTest extends TestCase
{
    /**
     * Uji halaman utama dashboard WebGIS.
     */
    public function test_dashboard_page_loads_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Bus Kota Operations');
        $response->assertSee('K-05 | Tangerang – Jakarta');
        $response->assertSee('Flarino Marco Cristvan Zakaria');
        $response->assertSee('Kode CaAs: 2675');
        $response->assertSee('Status Operasional');
        $response->assertSee('Trip Performance');
        $response->assertSee('Analisis Bahan Bakar (BBM)');
        $response->assertSee('Keterbatasan Data');
        $response->assertDontSee('NPM');
    }

    /**
     * Uji endpoint /api/ringkasan
     */
    public function test_api_ringkasan_returns_correct_dataset(): void
    {
        $response = $this->getJson('/api/ringkasan');

        $response->assertStatus(200);
        $response->assertJson([
            'kode_kendaraan' => 'K-05',
            'kendaraan' => 'Bus kota',
            'trayek' => 'Tangerang – Jakarta',
            'total_km' => 135.0,
            'total_liter' => 50.1,
            'total_biaya' => 340740,
            'liter_idle' => 0.4,
            'liter_boros' => 9.2,
            'biaya_boros' => 62472,
        ]);
    }

    /**
     * Uji endpoint /api/geojson/rute
     */
    public function test_api_geojson_rute_returns_feature_collection(): void
    {
        $response = $this->getJson('/api/geojson/rute');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'type',
            'features' => [
                '*' => [
                    'type',
                    'properties' => [
                        'trip_id',
                        'jarak_km',
                        'liter_total',
                        'km_per_liter',
                        'biaya_rp',
                    ],
                    'geometry' => [
                        'type',
                        'coordinates',
                    ],
                ],
            ],
        ]);

        $data = $response->json();
        $this->assertCount(4, $data['features']);
    }

    /**
     * Uji endpoint /api/geojson/titik-ujung
     */
    public function test_api_geojson_titik_ujung_returns_start_end_points(): void
    {
        $response = $this->getJson('/api/geojson/titik-ujung');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'type',
            'features' => [
                '*' => [
                    'type',
                    'properties' => [
                        'trip_id',
                        'jenis',
                        'waktu',
                    ],
                    'geometry',
                ],
            ],
        ]);

        $data = $response->json();
        $this->assertCount(8, $data['features']);
    }

    /**
     * Uji endpoint /api/gps-mentah
     */
    public function test_api_gps_mentah_returns_raw_data(): void
    {
        $response = $this->getJson('/api/gps-mentah');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_titik',
            'data',
        ]);

        $data = $response->json();
        $this->assertEquals(634, $data['total_titik']);
    }

    /**
     * Uji endpoint /api/gps-preprocessed
     */
    public function test_api_gps_preprocessed_returns_clean_audit(): void
    {
        $response = $this->getJson('/api/gps-preprocessed');

        $response->assertStatus(200);
        $response->assertJsonPath('audit.total_baris_valid', 634);
        $response->assertJsonPath('audit.jumlah_missing_values', 0);
        $response->assertJsonPath('audit.jumlah_duplikat', 0);
    }

    /**
     * Uji pemuatan aset vendor lokal (Leaflet dan Chart.js).
     */
    public function test_local_vendor_assets_are_accessible(): void
    {
        $this->assertFileExists(public_path('vendor/leaflet/leaflet.js'));
        $this->assertFileExists(public_path('vendor/leaflet/leaflet.css'));
        $this->assertFileExists(public_path('vendor/chartjs/chart.umd.min.js'));
    }

    /**
     * Uji integrasi embedded initial data dan ketiadaan kata terlarang.
     */
    public function test_dashboard_contains_embedded_data_and_clean_assets(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('window.INITIAL_RUTE_DATA =', false);
        $response->assertSee('window.INITIAL_TITIK_DATA =', false);
        $response->assertSee('window.INITIAL_RINGKASAN_DATA =', false);
        $response->assertSee('vendor/chartjs/chart.umd.min.js', false);
        $response->assertSee('vendor/leaflet/leaflet.js', false);
        $response->assertSee('vendor/leaflet/leaflet.css', false);
        $response->assertSee('id="efficiencyChart"', false);
        $response->assertSee('id="fuelChart"', false);
        $response->assertSee('id="costChart"', false);
        $response->assertSee('class="chart-container"', false);
        $response->assertDontSee('NPM');
        $response->assertDontSee('npm');
    }
}
