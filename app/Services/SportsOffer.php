<?php

namespace App\Services;

/**
 * Oferta ditore e basteve sportive.
 *
 * Ndeshjet gjenerohen në mënyrë deterministe nga data (Y-m-d),
 * kështu që oferta ndryshon vetvetiu çdo ditë në 00:00 —
 * pa cron, pa ndërhyrje manuale.
 */
class SportsOffer
{
    private const LEAGUES = [
        'Premier League 🏴󐁧󐁢󐁥󐁮󐁧󐁿' => ['Arsenal', 'Man City', 'Liverpool', 'Chelsea', 'Tottenham', 'Man United', 'Newcastle', 'Everton', 'Fulham', 'Sunderland'],
        'La Liga 🇪🇸' => ['Barcelona', 'Real Madrid', 'Atlético Madrid', 'Sevilla', 'Valencia', 'Villarreal', 'Levante', 'Real Sociedad'],
        'Serie A 🇮🇹' => ['Inter', 'Napoli', 'Juventus', 'Milan', 'Roma', 'Fiorentina', 'Bologna', 'Venezia'],
        'Bundesliga 🇩🇪' => ['Bayern', 'Dortmund', 'Leverkusen', 'Union Berlin', 'Schalke 04', 'Stuttgart', 'Leipzig'],
        'Superliga e Kosovës 🇽🇰' => ['Prishtina', 'Drita', 'Ballkani', 'Dukagjini', 'Feronikeli', 'Malisheva', 'Gjilani', 'Llapi'],
    ];

    private const TIMES = ['13:00', '14:30', '16:00', '17:30', '18:00', '19:00', '20:00', '20:30', '20:45', '21:00', '21:30'];

    public static function dateKey(?string $date = null): string
    {
        return $date ?? date('Y-m-d');
    }

    /** Data e formatuar për UI, p.sh. 12.09.2026 */
    public static function displayDate(?string $date = null): string
    {
        return date('d.m.Y', strtotime(self::dateKey($date)));
    }

    /**
     * Kthen 12 ndeshje për datën e dhënë. E njëjta datë → e njëjta ofertë,
     * datë tjetër → ofertë tjetër.
     */
    public static function matches(?string $date = null): array
    {
        $key = self::dateKey($date);
        $seed = crc32('sports-offer-v1|' . $key);
        mt_srand($seed);

        $leagues = array_keys(self::LEAGUES);
        // përziej ligat në mënyrë deterministe
        for ($i = count($leagues) - 1; $i > 0; $i--) {
            $j = mt_rand(0, $i);
            [$leagues[$i], $leagues[$j]] = [$leagues[$j], $leagues[$i]];
        }

        $times = self::TIMES;
        for ($i = count($times) - 1; $i > 0; $i--) {
            $j = mt_rand(0, $i);
            [$times[$i], $times[$j]] = [$times[$j], $times[$i]];
        }

        $matches = [];
        for ($id = 1; $id <= 12; $id++) {
            $league = $leagues[($id - 1) % count($leagues)];
            $teams = self::LEAGUES[$league];

            $hi = mt_rand(0, count($teams) - 1);
            do {
                $ai = mt_rand(0, count($teams) - 1);
            } while ($ai === $hi);

            [$o1, $ox, $o2] = self::odds();
            [$over, $under] = self::totalsOdds();

            $matches[] = array_merge([
                'id' => $id,
                'league' => $league,
                'home' => $teams[$hi],
                'away' => $teams[$ai],
                'time' => 'Sot ' . $times[($id - 1) % count($times)],
                'o1' => $o1, 'ox' => $ox, 'o2' => $o2,
                'over' => $over, 'under' => $under, 'line' => 2.5,
            ], self::extraMarkets($o1, $ox, $o2, $key . '|' . $id));
        }

        mt_srand(); // kthe gjeneratorin në gjendje të rastësishme për pjesën tjetër të app-it
        return $matches;
    }

    /** Kuotat zyrtare të serverit për një përzgjedhje (anti-mashtrim). */
    public static function oddFor(array $match, string $pick): ?float
    {
        if ($pick === '1') return $match['o1'];
        if ($pick === 'X') return $match['ox'];
        if ($pick === '2') return $match['o2'];
        if ($pick === '1X') return $match['dc1x'] ?? null;
        if ($pick === '12') return $match['dc12'] ?? null;
        if ($pick === 'X2') return $match['dcx2'] ?? null;
        if ($pick === 'GG') return $match['gg'] ?? null;
        if ($pick === 'NG') return $match['ng'] ?? null;
        if (str_starts_with($pick, 'Over')) return $match['over'];
        if (str_starts_with($pick, 'Under')) return $match['under'];
        return null;
    }

    /**
     * Tregje si 1xBet: Double Chance (1X/12/X2) nga probabilitetet e
     * normalizuara 1X2 + BTTS (GG/NG) e modeluar stabil me seed.
     */
    public static function extraMarkets(float $o1, float $ox, float $o2, string $seed): array
    {
        $i1 = 1 / $o1; $ix = 1 / $ox; $i2 = 1 / $o2;
        $t = $i1 + $ix + $i2;
        $p1 = $i1 / $t; $px = $ix / $t; $p2 = $i2 / $t;
        $m = 1.08;
        mt_srand(crc32('btts-v1|' . $seed));
        $gg = mt_rand(155, 235) / 100;
        mt_srand();
        return [
            'dc1x' => self::fmt2($m / ($p1 + $px), 1.05, 6.0),
            'dc12' => self::fmt2($m / ($p1 + $p2), 1.05, 6.0),
            'dcx2' => self::fmt2($m / ($px + $p2), 1.05, 6.0),
            'gg' => round($gg, 2),
            'ng' => round(1 / ((1 - 1 / $gg) * 1.06), 2),
        ];
    }

    private static function fmt2(float $v, float $min, float $max): float
    {
        return max($min, min($max, round($v, 2)));
    }

    /** 1X2 me marzh ~7%, të rrumbullakosura në 2 decimale. */
    private static function odds(): array
    {
        return self::stableOdds((string) mt_rand());
    }

    /**
     * Kuota deterministe për një seed (p.sh. ndeshje reale) —
     * e njëjta ndeshje merr gjithmonë të njëjtat kuota gjatë ditës.
     */
    public static function stableOdds(string $seed): array
    {
        mt_srand(crc32('odds-v1|' . $seed));
        // forca relative e ekipeve
        $s1 = mt_rand(30, 90) / 100;
        $s2 = mt_rand(30, 90) / 100;
        $drawP = mt_rand(22, 30) / 100;

        $p1 = (1 - $drawP) * ($s1 / ($s1 + $s2));
        $p2 = (1 - $drawP) * ($s2 / ($s1 + $s2));

        $margin = 1.07;
        $out = [
            self::fmt(1 / ($p1 * $margin)),
            self::fmt(1 / ($drawP * $margin)),
            self::fmt(1 / ($p2 * $margin)),
        ];
        mt_srand();
        return $out;
    }

    public static function stableTotals(string $seed): array
    {
        mt_srand(crc32('totals-v1|' . $seed));
        $out = self::totalsOdds();
        mt_srand();
        return $out;
    }

    private static function totalsOdds(): array
    {
        $over = mt_rand(165, 230) / 100;
        // under mbahet në korrelacion të përafërt me over
        $under = max(1.55, min(2.40, round(3.85 - $over + (mt_rand(-8, 8) / 100), 2)));
        return [round($over, 2), $under];
    }

    private static function fmt(float $odd): float
    {
        return max(1.12, min(11.00, round($odd, 2)));
    }
}
