<?php

namespace App\Http\Controllers;

use App\Services\GpsPreprocessingService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class WebGISController extends Controller
{
    public function __construct(
        protected GpsPreprocessingService $gpsService
    ) {}

    /**
     * Tampilkan halaman utama Transit Operations Dashboard.
     */
    public function index(): View
    {
        $ringkasanPath = public_path('data/ringkasan.json');
        $ringkasan = file_exists($ringkasanPath)
            ? json_decode(file_get_contents($ringkasanPath), true)
            : [];

        $rutePath = public_path('data/rute.geojson');
        $ruteGeoJson = file_exists($rutePath)
            ? json_decode(file_get_contents($rutePath), true)
            : [];

        $titikUjungPath = public_path('data/titik_ujung.geojson');
        $titikUjungGeoJson = file_exists($titikUjungPath)
            ? json_decode(file_get_contents($titikUjungPath), true)
            : [];

        // Jalankan preprocessing untuk mendapatkan hasil audit integritas GPS
        $preprocessResult = $this->gpsService->process(true);

        $studentName = env('STUDENT_NAME', 'Flarino Marco Cristvan Zakaria');
        $caasCode = env('CAAS_CODE', '2675');

        return view('dashboard', [
            'ringkasan' => $ringkasan,
            'ruteGeoJson' => $ruteGeoJson,
            'titikUjungGeoJson' => $titikUjungGeoJson,
            'gpsAudit' => $preprocessResult['audit'],
            'agregasiTrip' => $preprocessResult['agregasi_trip'],
            'studentName' => $studentName,
            'caasCode' => $caasCode,
        ]);
    }

    /**
     * Endpoint API: /api/ringkasan
     */
    public function getRingkasan(): JsonResponse
    {
        $path = public_path('data/ringkasan.json');
        if (! file_exists($path)) {
            return response()->json(['error' => 'File ringkasan.json tidak ditemukan'], 404);
        }

        $data = json_decode(file_get_contents($path), true);

        return response()->json($data);
    }

    /**
     * Endpoint API: /api/geojson/rute
     */
    public function getRuteGeoJson(): JsonResponse
    {
        $path = public_path('data/rute.geojson');
        if (! file_exists($path)) {
            return response()->json(['error' => 'File rute.geojson tidak ditemukan'], 404);
        }

        $data = json_decode(file_get_contents($path), true);

        return response()->json($data);
    }

    /**
     * Endpoint API: /api/geojson/titik-ujung
     */
    public function getTitikUjungGeoJson(): JsonResponse
    {
        $path = public_path('data/titik_ujung.geojson');
        if (! file_exists($path)) {
            return response()->json(['error' => 'File titik_ujung.geojson tidak ditemukan'], 404);
        }

        $data = json_decode(file_get_contents($path), true);

        return response()->json($data);
    }

    /**
     * Endpoint API: /api/gps-mentah
     */
    public function getGpsMentah(): JsonResponse
    {
        $path = public_path('data/gps_mentah.csv');
        if (! file_exists($path)) {
            return response()->json(['error' => 'File gps_mentah.csv tidak ditemukan'], 404);
        }

        $rows = [];
        if (($handle = fopen($path, 'r')) !== false) {
            $header = fgetcsv($handle, 1000, ',');
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                if (count($data) >= 6) {
                    $rows[] = [
                        'trip_id' => (int) $data[0],
                        'waktu' => $data[1],
                        'latitude' => (float) $data[2],
                        'longitude' => (float) $data[3],
                        'kecepatan_kmh' => (float) $data[4],
                        'jarak_km' => (float) $data[5],
                    ];
                }
            }
            fclose($handle);
        }

        return response()->json([
            'total_titik' => count($rows),
            'data' => $rows,
        ]);
    }

    /**
     * Endpoint API: /api/gps-preprocessed
     */
    public function getGpsPreprocessed(): JsonResponse
    {
        $res = $this->gpsService->process(true);

        return response()->json($res);
    }
}
