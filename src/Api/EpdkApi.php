<?php

namespace App\Api;

class EpdkApi
{
    private const BASE_URL = 'https://lisansws.epdk.gov.tr/epvys-web/rest/sarjIstasyonlariRest';
    private const SARJET_URL = 'https://sarjtr.epdk.gov.tr:443/sarjet/api';
    private const CACHE_TTL = 3600;

    private function fetch(string $url, string $method = 'GET', array $body = []): ?array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            return null;
        }

        return json_decode($response, true);
    }

    private function getCacheFile(string $key): string
    {
        return sys_get_temp_dir() . '/sarj_cache_' . md5($key) . '.json';
    }

    private function getFromCache(string $key): ?array
    {
        $file = $this->getCacheFile($key);
        if (!file_exists($file)) {
            return null;
        }
        if (time() - filemtime($file) > self::CACHE_TTL) {
            return null;
        }
        $data = file_get_contents($file);
        return $data ? json_decode($data, true) : null;
    }

    private function saveToCache(string $key, array $data): void
    {
        $file = $this->getCacheFile($key);
        file_put_contents($file, json_encode($data));
    }

    public function getAllStations(): array
    {
        $cached = $this->getFromCache('all_stations');
        if ($cached !== null) {
            return $cached;
        }

        $staticFile = ROOT_PATH . '/public/assets/stations_data.json';
        if (file_exists($staticFile)) {
            $data = json_decode(file_get_contents($staticFile), true);
            if ($data) {
                $this->saveToCache('all_stations', $data);
                return $data;
            }
        }

        $data = $this->fetch(
            self::BASE_URL . '/sarjIstasyonlariSorgulaPublic',
            'POST'
        );

        if ($data === null) {
            return $this->getMockStations();
        }

        $stations = $this->enrichWithCoordinates($data);
        $this->saveToCache('all_stations', $stations);
        return $stations;
    }

    public function getStationSockets(string $stationNo): array
    {
        $cacheKey = 'sockets_' . $stationNo;
        $cached = $this->getFromCache($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $data = $this->fetch(
            self::BASE_URL . '/sarjIstasyonuSoketleriSorgulaPublic',
            'POST',
            ['sarjIstasyonuNo' => $stationNo]
        );

        if ($data === null) {
            return $this->getMockSockets();
        }

        $this->saveToCache($cacheKey, $data);
        return $data;
    }

    private function enrichWithCoordinates(array $stations): array
    {
        $cityCoords = $this->getCityCoordinates();
        foreach ($stations as &$station) {
            $city = $this->extractCity($station['adresMahalleCaddeSokak'] ?? '');
            if (isset($cityCoords[$city])) {
                $base = $cityCoords[$city];
                $station['lat'] = $base[0] + (mt_rand(-500, 500) / 10000);
                $station['lng'] = $base[1] + (mt_rand(-500, 500) / 10000);
            } else {
                $station['lat'] = 39.0 + (mt_rand(-3000, 3000) / 1000);
                $station['lng'] = 35.0 + (mt_rand(-3000, 3000) / 1000);
            }
        }
        return $stations;
    }

    private function extractCity(string $address): string
    {
        if (preg_match('/\/\s*([A-ZÇŞĞÜÖİ][A-ZÇŞĞÜÖİa-zçşğüöı]+)$/', $address, $m)) {
            return mb_strtoupper($m[1], 'UTF-8');
        }
        return '';
    }

    private function getCityCoordinates(): array
    {
        return [
            'İSTANBUL'   => [41.0082, 28.9784],
            'ANKARA'     => [39.9334, 32.8597],
            'İZMİR'      => [38.4192, 27.1287],
            'BURSA'      => [40.1885, 29.0610],
            'ANTALYA'    => [36.8969, 30.7133],
            'ADANA'      => [37.0000, 35.3213],
            'KONYA'      => [37.8746, 32.4932],
            'GAZİANTEP'  => [37.0662, 37.3833],
            'ŞANLIURFA'  => [37.1674, 38.7955],
            'KAYSERİ'    => [38.7312, 35.4787],
            'MERSİN'     => [36.8121, 34.6415],
            'ESKİŞEHİR'  => [39.7767, 30.5206],
            'DİYARBAKIR' => [37.9144, 40.2306],
            'SAMSUN'     => [41.2867, 36.3300],
            'TRABZON'    => [41.0015, 39.7178],
            'MALATYA'    => [38.3552, 38.3095],
            'GEBZİ'      => [40.7983, 29.4308],
            'KOCAELI'    => [40.8533, 29.8815],
            'SAKARYA'    => [40.6936, 30.4356],
            'TEKIRDAĞ'   => [40.9781, 27.5115],
            'MANISA'     => [38.6191, 27.4289],
            'MUĞLA'      => [37.2153, 28.3636],
            'HATAY'      => [36.4018, 36.3498],
            'DENİZLİ'    => [37.7765, 29.0864],
            'ZONGULDAK'  => [41.4564, 31.7987],
            'ORDU'       => [40.9839, 37.8764],
            'RİZE'       => [41.0201, 40.5234],
            'TRABZON'    => [41.0015, 39.7178],
            'KASTAMONU'  => [41.3887, 33.7827],
            'ÇANAKKALE'  => [40.1553, 26.4142],
            'BALIKISR'   => [39.6484, 27.8826],
            'BALIKESİR'  => [39.6484, 27.8826],
            'AYDIN'      => [37.8444, 27.8458],
            'MUĞLA'      => [37.2153, 28.3636],
            'AFYONKARAHISAR' => [38.7507, 30.5567],
            'NEVŞEHİR'   => [38.6939, 34.6857],
            'AKSARAY'    => [38.3687, 34.0370],
            'YOZGAT'     => [39.8181, 34.8147],
            'KİRİKKALE'  => [39.8468, 33.5153],
            'KIRIKKALE'  => [39.8468, 33.5153],
            'BARTIN'     => [41.6344, 32.3375],
            'KARABÜK'    => [41.2061, 32.6204],
            'OSMANİYE'   => [37.0742, 36.2464],
            'DÜZCE'      => [40.8438, 31.1565],
            'SİVAS'      => [39.7477, 37.0179],
            'TOKAT'      => [40.3167, 36.5544],
            'VAN'        => [38.4891, 43.4089],
            'ERZİNCAN'   => [39.7500, 39.5000],
            'ERZURUM'    => [39.9055, 41.2658],
            'KARS'       => [40.6013, 43.0975],
            'IĞDIR'      => [39.9237, 44.0450],
        ];
    }

    private function getMockStations(): array
    {
        return [
            [
                'sarjIstasyonuNo' => 'ŞRJ/0001',
                'sarjIstasyonuAdi' => 'Demo Şarj İstasyonu 1',
                'sarjAgiIsletmecisiUnvan' => 'VOLTRUN ENERJİ A.Ş.',
                'sarjIstasyonuMarkaTescilBelgesiMarkaAdi' => 'VOLTRUN',
                'adresMahalleCaddeSokak' => 'Merkez Mahallesi Atatürk Caddesi No:1 / İSTANBUL',
                'hizmetSekli' => 'HALKA_ACIK',
                'lat' => 41.0082,
                'lng' => 28.9784,
            ],
            [
                'sarjIstasyonuNo' => 'ŞRJ/0002',
                'sarjIstasyonuAdi' => 'Demo Şarj İstasyonu 2',
                'sarjAgiIsletmecisiUnvan' => 'ZES TOROSLAR EŞARJ A.Ş.',
                'sarjIstasyonuMarkaTescilBelgesiMarkaAdi' => 'ZES',
                'adresMahalleCaddeSokak' => 'Kızılay Mahallesi Atatürk Blv No:14 / ANKARA',
                'hizmetSekli' => 'HALKA_ACIK',
                'lat' => 39.9334,
                'lng' => 32.8597,
            ],
        ];
    }

    private function getMockSockets(): array
    {
        return [
            ['soketNo' => 'SKT/001', 'soketTipi' => 'AC', 'soketTuru' => 'AC_TYPE2', 'soketGucu' => 22],
            ['soketNo' => 'SKT/002', 'soketTipi' => 'DC', 'soketTuru' => 'DC_CCS2', 'soketGucu' => 50],
        ];
    }
}
