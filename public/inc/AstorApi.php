<?php

namespace App\Api;

class AstorApi
{
    private const GW_BASE   = 'https://api-gw.beefull.io/api/beefull-charge-management';
    private const AUTH      = 'Basic 5fe5db79fccb804dcc9e548272948ec901d36655c06fc618c87d0f148fca737459107398a5d2438b04d988538488635981a0eccf0c6deec31688a83aa62d61e5437b5af1261c917677cb6e363ef1fbecbda5da27c4fc69d141fa7d445e33b99d21434c2f1ea7cabfab9a5723379ccb2a8b8b0d7b4a96e6869e2d5ce0c6c2649eb1ec9cd06bbd349593e34835c77c22cd0edcc01725f18b7d78458a0e388b7c54e3443136b67f6f19237554c30f9a65b45a1f73c64c6fce4952fa3384fea620aaf1b48b378da71a3bdd123f8fa6288d7edb05beef279d0c43a09fdd7fb62dc740479417603bc44a7c02de204fd3c449ffe160b12a464a69e669c66d318a924a0b3e04adc01974b6a5aba4f2c7b25a16cf1a3d4b8ef0bc2c98f87d017ea8cfb305fd19685fcd15f7eeb58d06f3c4322d309a6a4adb43dc3ceba1681c226f672dad4c677f902d885d0939908c98933ba92c8d45097871a93a83bb39ddb1e0eb7d61081c02bf37dbcb16b510c660e0d2c725e8a2c0dd523bbb10709b705fa83aa63e01abbae06da6a03a3d59ebaee14fb5be5cf0d504c23fd696f925acdc3b248722895764d203a64a315347de0ca219dabe7eb096bc5846c20cb7315e18961ad7e22735997776af30224b1bc2da71e2d81f2fb26eae8805b2cf2b26282d755b26963f667da003e5bd985a3525013a85c2982d930a9b392d63c7698d69cd17ca82e3a202956ae4518627b99f6630b33453c869152042940e884101949fa32df033f07639d54c47ee49ce0ec4aba1736b7ed6736a889b5e38880f0267eb6bddffda90b38ef1f5f5c1ddc46dfadfbcb5d95d559343b0bfffccd6ded90a32f7bb3a6c5a0bd839d13313ecf34c50a39302756c6d32cd1e959c152f5c5026d194df2f51dfada5d0cc490af67e5b070e9a46385d1785229c254de6bacc51030c6e8d0b5401b517a1fac7f6d83483af4d9edf98841ecedabc36124c39c59caf09aed71e9c0615f582afedec61fa408590e2b63ff4185d7e20d42172a50292700fdf00340a00cdc03f10d6b404a3bf6a86008adbe0414df2425557546c0ec58705782e477ae9ce150e08a51b9cdc70d39b8f4b5d3c3d03cce335bcc9e3d30b79e6ea33f70418b5a9b384669688c0fe9648dce0bbc3356427bdcc66712e5308c7d7221ffa4e15bd9daff097c4a4410cadf80aa956b5448dbb0ca03d74eb431db5685fa5d967e321964ee36f38f8628502215034329608d021109d6f5b55dbaa80afd238b06230212e810828a4d728328fe0475d541274aa2c9f5a3001fe49b4c55bdd38f799fe1274edc9669ad0d810407b0db8836b1349ef0193729889b37441184e59842057e97714eff28d961677f2dd97f24672a4c9ff4fd41f6318dc5f008ef0d585edf1a975c0c2b054d3cb8691aa879159246777e92708ae1fa71eb78dd67c4990490eb916049441ca13c8d876726bcc87f2b22d7963785f913643d04ae5f9c91f08d6de1f6e486c50b1d98bb7b1c1fa025d3fc7468994f45acf1329171abd277bf9f4178df8fb2c4a6661f3669f8648736a12f6a0f232373ab0178d1c6d94d42dff3d2f31045fd7858b7fe6a62eaf0384cdbbc3b21b776fe40f2a27b9140e96ba44f352260fdfed1869441e1ca3cd6a92cbf838fdb85e6273c45c9b4e3cc9be30e5905125998158313560c7c104ff2d062ab430cba1234424aa49d3b967fa223f0e66ebbbf1cfc52b6b5548d1db650a63eb59823b5b8aca53273ea1bb482d28a041a4015265b47f411740adef1e45a700020a29bb6146345a7bb3cf2afdfa0969d63c98f1ffcf0928a975a2ee01c605a1612d2e6ef5069a2f6d0a083d2f3077d3f00f51de02d9979a6720bca0dfa32274f03bffd4725a74435b4e91b99dd3e4da65142a31f468a542875d678f5a1bf763fb4aeaa0dcbc1b1a05a1b4de1beccb069ecf561293e272d4cae05f58b7f4965b4214e9fb58d2d77316fbebe5ef7038993a23ce27a061d2b5fd1e3d530af042e29763af484184b21870199a2483d0adbe25eca971ee174dbd42016553c209bc598c1ec65e5407e4753a74afc5b38fca267b32d62b3669bb0b49b6c224ce893e0257458dbe58728b9022fe32a0fda54b4444edf7cb2efab2607fdc778a65d96b48c0317ea38639af38553cc67db73649b779121a625f7b77da464baf8c6973270159ff65bba4567389c6481d58363b0b19310267709b0edce500b3f0656753343f0c89b69d1930ff582991bdea95695d9c2d0e5c003ba084e3a55b36268f3d6f7565abf3e166ea6076cdbd1a717da6bbfc88c2206611ef3f073381fbdd8b7a38dc0fde12814d4a7f4bd744fed5b42ecda81d1265c4c0947fbde03fae0448cd08588f28e6af90129d4f2bca03b1949718735766501ec923d0b0b4ee1e67eae0aaca4833700a617d08ed36b4506cce104fba2f9cad8122576c6ddfb0b7bd912fed80d87b775fc54eec262108d2f37a8fdd9747facb0b89012940ad8cc0c45e6294616444a22be36297428ac8c0eab7cfe1e8720874410190f5f0e2c66b7d1293d66f3e3596d2d80e4c5ff3105a12db5952c10875da20f400c3f6396c813252bd1d42b5dac375d96e744cdbf308fd9a28377a43fa4faf52c957271c107b8696ecea144e9d9810db3ec00d2ff49777294ff0876f7c689a195942361a39cc1e353a5c1cf3385eef54e1365f9b7ea2b3005dca20ab0847729b72258e834908963891826a2457ccf22ac2c15c74d2bc1563d4b7dc85b3a2b5a34f857a36c90b7efe374e3020ae1f7b3c35006266c1251cbedb233c6dd23cef97766a32679a2d7639d99c508c1500e2a1b9ae3ad9bff0c3fd4e4ed1b99846e4586e44607c812e36509308450e48c55fe6d1d5c67b5e1a162c9d70d9bc3b';
    private const COOKIE    = '_csrf=kjOUNx7kd-66hHglCOuBQjmS';
    private const CACHE_TTL = 90; // 90 saniye

    /**
     * EPDK koordinatlarına göre Beefull'dan soket + SoC verisi çeker.
     * Yöntem: 1) grouped listesinden koordinat eşleştir → ID bul
     *         2) charge-points-alternative/{id} detayını çek
     */
    public function getSocketsByCoord(float $lat, float $lng): ?array
    {
        $cacheKey = 'astor_coord_' . round($lat, 4) . '_' . round($lng, 4);
        $cached   = $this->getFromCache($cacheKey);
        if ($cached !== null) return $cached;

        // 1. Aşama: koordinata göre Beefull ID'sini bul
        $beefullIds = $this->findIdsByCoord($lat, $lng);
        if (empty($beefullIds)) return null;

        // 2. Aşama: her ID için detay çek ve birleştir
        $allSockets = [];
        foreach ($beefullIds as $id) {
            $sockets = $this->fetchDetailSockets((int)$id);
            if ($sockets) {
                $allSockets = array_merge($allSockets, $sockets);
            }
        }

        if (empty($allSockets)) return null;

        $this->saveToCache($cacheKey, $allSockets);
        return $allSockets;
    }

    /** grouped-charge-points-alternative ile koordinat eşleştirmesi */
    private function findIdsByCoord(float $lat, float $lng): array
    {
        $cacheKey = 'astor_grouped';
        $list     = $this->getFromCache($cacheKey);

        if ($list === null) {
            $url = self::GW_BASE . '/grouped-charge-points-alternative?columns=id,coordinate&serviceTypeCode=ev&isVisible=true&tenantCode=beefull,astor&pbAvailabilityStatusCode=available';
            $raw = $this->curl($url, [
                'inavitas-tenant: astor',
                'authorization: ' . self::AUTH,
                'accept: application/json',
                'user-agent: okhttp/4.9.2',
            ]);
            if (!$raw) return [];
            $list = json_decode($raw, true);
            if (!is_array($list)) return [];
            // grouped listeyi 10 dakika cache'le
            file_put_contents(
                sys_get_temp_dir() . '/sarjnet_' . md5($cacheKey) . '.json',
                json_encode($list)
            );
        }

        $latStr = (string)$lat;
        $lngStr = (string)$lng;
        $found  = [];

        foreach ($list as $item) {
            $iLat = (string)($item['coordinate']['latitude']  ?? '');
            $iLng = (string)($item['coordinate']['longitude'] ?? '');
            // İlk 7 karakter eşleşmesi (±~10m tolerans)
            if (
                strlen($iLat) >= 7 && strlen($latStr) >= 7 &&
                strlen($iLng) >= 7 && strlen($lngStr) >= 7 &&
                substr($iLat, 0, 7) === substr($latStr, 0, 7) &&
                substr($iLng, 0, 7) === substr($lngStr, 0, 7)
            ) {
                $found[] = $item['id'];
            }
        }

        // Bulunamazsa daha geniş toleransla (ilk 5 karakter)
        if (empty($found)) {
            foreach ($list as $item) {
                $iLat = (string)($item['coordinate']['latitude']  ?? '');
                $iLng = (string)($item['coordinate']['longitude'] ?? '');
                if (
                    strlen($iLat) >= 5 && strlen($latStr) >= 5 &&
                    strlen($iLng) >= 5 && strlen($lngStr) >= 5 &&
                    substr($iLat, 0, 5) === substr($latStr, 0, 5) &&
                    substr($iLng, 0, 5) === substr($lngStr, 0, 5)
                ) {
                    $found[] = $item['id'];
                }
            }
        }

        return $found;
    }

    /** Detay API'sinden children → normalleştirilmiş soket listesi */
    private function fetchDetailSockets(int $beefullId): ?array
    {
        $url = self::GW_BASE . '/charge-points-alternative/' . $beefullId . '?tenantCode=astor';
        $raw = $this->curl($url, [
            'inavitas-tenant: astor',
            'authorization: ' . self::AUTH,
            'cookie: '        . self::COOKIE,
            'accept: application/json',
            'cache-control: no-cache',
        ]);

        if (!$raw) return null;
        $data = json_decode($raw, true);
        if (!is_array($data) || empty($data)) return null;

        $station  = $data[0];
        $children = $station['children'] ?? [];
        if (empty($children)) return null;

        return $this->normalizeChildren($children, $station);
    }

    private function normalizeChildren(array $children, array $parent): array
    {
        $statusMap = [
            'available'   => 'FREE',
            'charging'    => 'CHARGING',
            'finishing'   => 'CHARGING',
            'preparing'   => 'PREPARING',
            'reserved'    => 'RESERVED',
            'faulted'     => 'FAULTED',
            'unavailable' => 'OFFLINE',
            'offline'     => 'OFFLINE',
        ];

        $connectorMap = [
            'ccs2'   => 'DC_CCS2',
            'ccs1'   => 'DC_CCS1',
            'chademo'=> 'DC_CHAdeMO',
            'type2'  => 'AC_TYPE2',
            'type1'  => 'AC_TYPE1',
            'schuko' => 'AC_Schuko',
            'gb/t'   => 'DC_GBT',
        ];

        $result = [];
        foreach ($children as $child) {
            $meta        = $child['metadata'] ?? [];
            $statusRaw   = strtolower($child['availabilityStatusCode'] ?? '');
            $status      = $statusMap[$statusRaw] ?? strtoupper($statusRaw);
            $connType    = strtolower($meta['connectorType']        ?? '');
            $currentType = strtoupper($meta['connectorCurrentType'] ?? 'AC');
            $power       = isset($meta['power']) ? (float)$meta['power'] : null;
            $socRaw      = $meta['SoC'] ?? null;
            $soc         = ($socRaw !== null && $socRaw !== 'null' && $socRaw !== '') ? (int)$socRaw : null;
            $tariff      = $child['tariff'] ?? null;
            $price       = $tariff ? ($tariff['usage']['rate'][0]['value'] ?? null) : null;

            // DC + type2 → CCS2
            if ($currentType === 'DC' && $connType === 'type2') {
                $subType = 'DC_CCS2';
            } else {
                $subType = $connectorMap[$connType] ?? strtoupper($connType) ?: $currentType;
            }

            $result[] = [
                'id'          => $child['id'],
                'connectorNo' => $meta['connectorId'] ?? null,
                'type'        => $currentType,
                'subType'     => $subType,
                'socketNumber'=> 'ASTOR-' . ($child['id'] ?? '?'),
                'power'       => $power,
                'price'       => $price,
                'currency'    => $tariff['currencySymbol'] ?? '₺',
                'soc'         => $soc,           // Araç şarj yüzdesi
                'status'      => $status,
                'availability'=> [[
                    'status'    => $status,
                    'startTime' => date('Y-m-d') . 'T00:00:00',
                    'endTime'   => date('Y-m-d') . 'T23:59:59',
                ]],
                'prices' => $price !== null ? [[
                    'price'     => $price,
                    'startTime' => date('Y-m-d') . 'T00:00:00',
                    'endTime'   => date('Y-m-d') . 'T23:59:59',
                ]] : [],
                'source'      => 'astor',
                'lastUpdate'  => $parent['lastCommunication'] ?? null,
            ];
        }
        return $result;
    }

    private function curl(string $url, array $headers): ?string
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => 'gzip',
            CURLOPT_TIMEOUT        => 12,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);
        $response = curl_exec($ch);
        $err      = curl_error($ch);
        $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($err || $code !== 200 || !$response) return null;
        return $response;
    }

    private function getCacheFile(string $key): string
    {
        return sys_get_temp_dir() . '/sarjnet_' . md5($key) . '.json';
    }

    private function getFromCache(string $key): mixed
    {
        $file = $this->getCacheFile($key);
        if (!file_exists($file)) return null;
        $ttl = $key === 'astor_grouped' ? 600 : self::CACHE_TTL;
        if ((time() - filemtime($file)) > $ttl) return null;
        $data = file_get_contents($file);
        return $data ? json_decode($data, true) : null;
    }

    private function saveToCache(string $key, mixed $data): void
    {
        file_put_contents($this->getCacheFile($key), json_encode($data, JSON_UNESCAPED_UNICODE));
    }
}
