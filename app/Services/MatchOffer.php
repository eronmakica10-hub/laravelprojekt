<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Oferta zyrtare e ditës — e vetmja pikë hyrëse për ndeshjet.
 *
 * Prioritet: ndeshje + kuota REALE (DraftKings via ESPN).
 * Rezervë: ofertë demo e gjeneruar (SportsOffer).
 *
 * Rezultati ruhet në cache 3 orë, që faqja dhe validimi i
 * biletave të përdorin gjithmonë të njëjtën ofertë.
 */
class MatchOffer
{
    private const MIN_REAL = 6;

    public static function today(): array
    {
        // v2: përfshin tregjet 1X/12/X2 + GG/NG
        return Cache::remember('match-offer-v2-' . date('Y-m-d'), 3 * 3600, function () {
            $real = RealSportsFeed::fetch();
            if (count($real) >= self::MIN_REAL) {
                return [
                    'matches' => $real,
                    'source' => 'live',
                    'provider' => 'OpenLigaDB',
                    'oddsLive' => false, // ndeshjet reale, kuotat të modeluara
                    'offerDate' => SportsOffer::displayDate(),
                ];
            }
            return [
                'matches' => SportsOffer::matches(),
                'source' => 'demo',
                'provider' => null,
                'oddsLive' => false,
                'offerDate' => SportsOffer::displayDate(),
            ];
        });
    }

    public static function clearCache(): void
    {
        Cache::forget('match-offer-v2-' . date('Y-m-d'));
    }
}
