<?php

namespace App\Services;

use DateTime;
use Exception;

class GpsPreprocessingService
{
    /**
     * Jalankan proses audit dan preprocessing data GPS mentah.
     *
     * @return array<string, mixed>
     */
    public function process(bool $writeCleanFiles = true): array
    {
        $rawFilePath = public_path('data/gps_mentah.csv');

        if (! file_exists($rawFilePath)) {
            throw new Exception("File GPS mentah tidak ditemukan pada: {$rawFilePath}");
        }

        $handle = fopen($rawFilePath, 'r');
        if ($handle === false) {
            throw new Exception("Gagal membuka file: {$rawFilePath}");
        }

        $header = fgetcsv($handle, 1000, ',');
        $rawRows = [];
        $missingCount = 0;
        $duplicateCount = 0;
        $invalidCoordCount = 0;
        $invalidTimestampCount = 0;

        $seenKeys = [];
        $validRows = [];

        // Bounding box area Jabodetabek (Tangerang - Jakarta)
        $latMin = -6.50;
        $latMax = -6.00;
        $lonMin = 106.50;
        $lonMax = 107.10;

        $rowIndex = 0;
        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            $rowIndex++;
            if (count($row) < 6) {
                $missingCount++;
                continue;
            }

            $record = [
                'row_index' => $rowIndex,
                'trip_id' => (int) trim($row[0]),
                'waktu' => trim($row[1]),
                'latitude' => (float) trim($row[2]),
                'longitude' => (float) trim($row[3]),
                'kecepatan_kmh' => (float) trim($row[4]),
                'jarak_km' => (float) trim($row[5]),
            ];

            // 1. Cek Missing Value
            if ($record['trip_id'] <= 0 || empty($record['waktu']) || $record['latitude'] == 0.0 || $record['longitude'] == 0.0) {
                $missingCount++;
                continue;
            }

            // 2. Cek Duplikasi data (trip, waktu, koordinat)
            $uniqueKey = $record['trip_id'].'_'.$record['waktu'].'_'.$record['latitude'].'_'.$record['longitude'];
            if (isset($seenKeys[$uniqueKey])) {
                $duplicateCount++;
                continue;
            }
            $seenKeys[$uniqueKey] = true;

            // 3. Validasi Koordinat geografis
            if ($record['latitude'] < $latMin || $record['latitude'] > $latMax ||
                $record['longitude'] < $lonMin || $record['longitude'] > $lonMax) {
                $invalidCoordCount++;
                continue;
            }

            // 4. Validasi Timestamp
            $dt = DateTime::createFromFormat('Y-m-d H:i:s', $record['waktu']);
            if (! $dt || $dt->format('Y-m-d H:i:s') !== $record['waktu']) {
                $invalidTimestampCount++;
                continue;
            }

            $record['timestamp'] = $dt->getTimestamp();
            $validRows[] = $record;
        }
        fclose($handle);

        // 5. Sorting berdasarkan trip_id ASC dan waktu ASC
        usort($validRows, function ($a, $b) {
            if ($a['trip_id'] === $b['trip_id']) {
                return $a['timestamp'] <=> $b['timestamp'];
            }

            return $a['trip_id'] <=> $b['trip_id'];
        });

        // 6. Agregasi per trip
        $tripGroups = [];
        foreach ($validRows as $item) {
            $tripGroups[$item['trip_id']][] = $item;
        }

        $tripAggregations = [];
        foreach ($tripGroups as $tripId => $items) {
            $speeds = array_column($items, 'kecepatan_kmh');
            $distances = array_column($items, 'jarak_km');

            $firstItem = $items[0];
            $lastItem = end($items);

            $durationMinutes = ($lastItem['timestamp'] - $firstItem['timestamp']) / 60.0;
            $idlePoints = count(array_filter($speeds, fn ($s) => $s <= 0.5));

            $tripAggregations[$tripId] = [
                'trip_id' => $tripId,
                'total_titik' => count($items),
                'jam_mulai' => date('H:i', $firstItem['timestamp']),
                'jam_selesai' => date('H:i', $lastItem['timestamp']),
                'waktu_mulai' => $firstItem['waktu'],
                'waktu_selesai' => $lastItem['waktu'],
                'durasi_menit' => round($durationMinutes, 1),
                'total_jarak_km' => round(array_sum($distances), 2),
                'kecepatan_rata_kmh' => round(array_sum($speeds) / count($speeds), 1),
                'kecepatan_maks_kmh' => round(max($speeds), 1),
                'titik_idle' => $idlePoints,
                'koordinat_awal' => [$firstItem['latitude'], $firstItem['longitude']],
                'koordinat_akhir' => [$lastItem['latitude'], $lastItem['longitude']],
            ];
        }

        // Tulis file hasil preprocessing jika diminta
        if ($writeCleanFiles) {
            $cleanCsvPath = public_path('data/gps_clean.csv');
            $cleanJsonPath = public_path('data/gps_preprocessed.json');

            // Tulis CSV bersih
            $outHandle = fopen($cleanCsvPath, 'w');
            if ($outHandle !== false) {
                fputcsv($outHandle, ['trip_id', 'waktu', 'latitude', 'longitude', 'kecepatan_kmh', 'jarak_km']);
                foreach ($validRows as $r) {
                    fputcsv($outHandle, [
                        $r['trip_id'],
                        $r['waktu'],
                        $r['latitude'],
                        $r['longitude'],
                        $r['kecepatan_kmh'],
                        $r['jarak_km'],
                    ]);
                }
                fclose($outHandle);
            }

            // Tulis JSON ringkasan audit dan data
            file_put_contents($cleanJsonPath, json_encode([
                'audit' => [
                    'total_baris_mentah' => $rowIndex,
                    'total_baris_valid' => count($validRows),
                    'jumlah_missing_values' => $missingCount,
                    'jumlah_duplikat' => $duplicateCount,
                    'jumlah_koordinat_invalid' => $invalidCoordCount,
                    'jumlah_timestamp_invalid' => $invalidTimestampCount,
                    'status_integritas' => ($missingCount === 0 && $duplicateCount === 0 && $invalidCoordCount === 0 && $invalidTimestampCount === 0) ? 'Sempurna (Clean)' : 'Dibersihkan',
                ],
                'agregasi_trip' => $tripAggregations,
            ], JSON_PRETTY_PRINT));
        }

        return [
            'audit' => [
                'total_baris_mentah' => $rowIndex,
                'total_baris_valid' => count($validRows),
                'jumlah_missing_values' => $missingCount,
                'jumlah_duplikat' => $duplicateCount,
                'jumlah_koordinat_invalid' => $invalidCoordCount,
                'jumlah_timestamp_invalid' => $invalidTimestampCount,
                'status_integritas' => ($missingCount === 0 && $duplicateCount === 0 && $invalidCoordCount === 0 && $invalidTimestampCount === 0) ? 'Sempurna (Clean)' : 'Dibersihkan',
            ],
            'agregasi_trip' => $tripAggregations,
            'data' => $validRows,
        ];
    }
}
