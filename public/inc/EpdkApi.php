<?php

namespace App\Api;

class EpdkApi
{
    private const SARJET_URL = 'https://sarjtr.epdk.gov.tr/sarjet/api';
    private const CACHE_TTL  = 1800; // 30 dakika

    private function fetch(string $url): ?string
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => 'gzip',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_HTTPHEADER     => [
                'User-Agent: Dart/3.1 (dart:io)',
                'Host: sarjtr.epdk.gov.tr',
                'Accept: application/json',
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);
        $response = curl_exec($ch);
        $err      = curl_error($ch);
        curl_close($ch);

        if ($err || $response === false) {
            return null;
        }
        return $response;
    }

    private function getCacheFile(string $key): string
    {
        return sys_get_temp_dir() . '/sarjnet_' . md5($key) . '.json';
    }

    private function getFromCache(string $key)
    {
        $file = $this->getCacheFile($key);
        if (!file_exists($file) || (time() - filemtime($file)) > self::CACHE_TTL) {
            return null;
        }
        $data = file_get_contents($file);
        return $data ? json_decode($data, true) : null;
    }

    private function saveToCache(string $key, $data): void
    {
        file_put_contents($this->getCacheFile($key), json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    public function getAllStations(): array
    {
        // Statik dosya öncelikli — her zaman hızlı ve güvenilir
        $staticFile = ROOT_PATH . '/assets/stations_data.json';
        if (file_exists($staticFile)) {
            $data = json_decode(file_get_contents($staticFile), true);
            if (is_array($data) && count($data) > 0) {
                return $data;
            }
        }

        // Fallback: canlı API
        $raw = $this->fetch(self::SARJET_URL . '/stations');
        if ($raw !== null) {
            $data = json_decode($raw, true);
            if (is_array($data) && count($data) > 0) {
                return $data;
            }
        }

        return [];
    }

    public function getStationDetail($id, string $date = ''): ?array
    {
        if (!$date) {
            $date = date('Y-m-d H:i:s');
        }
        $cacheKey = 'detail_' . $id;
        $cached   = $this->getFromCache($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $encodedDate = str_replace(' ', '%20', $date);
        $raw = $this->fetch(self::SARJET_URL . '/stations/id/' . $id . '/' . $encodedDate);

        if ($raw !== null) {
            $data = json_decode($raw, true);
            if (is_array($data) && isset($data['id'])) {
                $this->saveToCache($cacheKey, $data);
                return $data;
            }
        }

        return null;
    }
}
