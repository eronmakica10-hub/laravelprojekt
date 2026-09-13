<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Ndeshje REALE të ardhshme (OpenLigaDB — falas, pa API key).
 *
 * Kuotat janë të modeluara (stabiliteti garantohet nga seed-i i
 * ndeshjes, kështu që nuk ndryshojnë gjatë ditës) — kuota reale
 * të basteve do të kërkonin API me pagesë.
 *
 * Kthen [] nëse feed-i nuk arrihet — thirrësi bie në ofertën demo.
 */
class RealSportsFeed
{
    public const LEAGUES = [
        'bl1' => 'Bundesliga 🇩🇪',
        'bl2' => '2. Bundesliga 🇩🇪',
        'pl' => 'Premier League 🏴󐁧󐁢󐁥󐁮󐁧󐁿',
        'la1' => 'La Liga 🇪🇸',
    ];

    private const UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0 Safari/537.36';

    /** Bundle me certifikata CA (për PHP pa curl.cainfo të konfiguruar). */
    private static function verify(): bool|string
    {
        $p = storage_path('app/cacert.pem');
        return is_file($p) ? $p : true;
    }

    /**
     * @return array ndeshje në formatin: id, league, home, away, time, o1, ox, o2, over, under, line, kickoff
     */
    public static function fetch(int $days = 7, int $limit = 12): array
    {
        try {
            $responses = Http::pool(function ($pool) {
                $reqs = [];
                foreach (array_keys(self::LEAGUES) as $slug) {
                    $reqs[$slug] = $pool->as($slug)
                        ->withHeaders(['User-Agent' => self::UA, 'Accept' => 'application/json'])
                        ->withOptions(['verify' => self::verify()])
                        ->timeout(10)->connectTimeout(5)
                        ->get("https://api.openligadb.de/getmatchdata/{$slug}");
                }
                return $reqs;
            });

            $tz = new \DateTimeZone('Europe/Belgrade');
            $now = new \DateTime('now', $tz);
            $end = (clone $now)->modify("+$days days");

            $matches = [];
            foreach ($responses as $slug => $res) {
                // Http::pool kthen ConnectionException kur hosti nuk arrihet — mos thirr successful() mbi Throwable
                if ($res instanceof \Throwable) continue;
                if (!method_exists($res, 'successful') || !$res->successful()) continue;
                try {
                    $data = $res->json();
                } catch (\Throwable $e) {
                    continue;
                }
                if (!is_array($data)) continue;
                foreach ($data as $m) {
                    if (!empty($m['matchIsFinished'])) continue;
                    if (empty($m['matchDateTime'])) continue;
                    $home = trim($m['team1']['teamName'] ?? '');
                    $away = trim($m['team2']['teamName'] ?? '');
                    if ($home === '' || $away === '') continue;
                    $dt = new \DateTime($m['matchDateTime']);
                    $dt->setTimezone($tz);
                    if ($dt < $now || $dt > $end) continue;

                    [$o1, $ox, $o2] = SportsOffer::stableOdds($home . '|' . $away . '|' . $dt->format('Y-m-d'));
                    [$over, $under] = SportsOffer::stableTotals($home . '|' . $away . '|' . $dt->format('Y-m-d'));

                    $matches[] = array_merge([
                        'league' => self::LEAGUES[$slug] ?? $slug,
                        'home' => $home,
                        'away' => $away,
                        'time' => self::timeLabel($dt, $now),
                        'kickoff' => $dt->format('c'),
                        'o1' => $o1, 'ox' => $ox, 'o2' => $o2,
                        'over' => $over, 'under' => $under, 'line' => 2.5,
                    ], SportsOffer::extraMarkets($o1, $ox, $o2, $home . '|' . $away . '|' . $dt->format('Y-m-d')));
                }
            }

            usort($matches, fn ($a, $b) => strcmp($a['kickoff'], $b['kickoff']));
            $matches = array_slice($matches, 0, $limit);
            foreach ($matches as $i => &$m) $m['id'] = $i + 1;

            return $matches;
        } catch (\Throwable $e) {
            report($e);
            return [];
        }
    }

    /** "Sot 20:45" / "Nesër 18:00" / "14.09 20:45" (Europe/Belgrade) */
    private static function timeLabel(\DateTime $dt, \DateTime $now): string
    {
        $hm = $dt->format('H:i');
        $day = $dt->format('Y-m-d');
        if ($day === $now->format('Y-m-d')) return "Sot $hm";
        if ($day === (clone $now)->modify('+1 day')->format('Y-m-d')) return "Nesër $hm";
        return $dt->format('d.m.') . " $hm";
    }
}
