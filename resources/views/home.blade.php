@extends('layouts.casino')
@section('title','Ballina')

@section('content')
<!-- HERO -->
<div class="glass rounded-3xl p-8 md:p-12 text-center relative overflow-hidden slidein">
  <div class="absolute inset-0 bg-gradient-to-r from-amber-500/10 via-fuchsia-500/10 to-amber-500/10"></div>
  <div class="relative">
    <div class="text-6xl mb-2">🦅🎰⚽</div>
    <h1 class="text-4xl md:text-6xl font-black neon-gold">KAZINO ONLINE</h1>
    <p class="text-white/70 mt-3 text-lg">Slotet • Ruleta • Blackjack • Zari • Bastore Sportive</p>
    <div class="inline-block mt-5 bg-black/50 border border-amber-500/40 rounded-2xl px-8 py-4">
      <div class="text-xs tracking-widest text-amber-200/70">💰 JACKPOT PROGRESIV</div>
      <div id="jackpot" class="text-4xl md:text-5xl font-black text-amber-300 tabular-nums">{{ number_format($jackpot,0,',','.') }}€</div>
    </div>
    <div class="mt-6 flex justify-center gap-3 flex-wrap">
      <a href="/slots" class="btn-gold px-8 py-3 rounded-full text-lg">🎰 Luaj Tani</a>
      <a href="/sports" class="px-8 py-3 rounded-full bg-white/10 border border-white/20 font-bold hover:bg-white/20">⚽ Baste Sportive</a>
    </div>
  </div>
</div>

<!-- marquee -->
<div class="mt-4 overflow-hidden glass rounded-full py-2 text-sm font-bold text-amber-200">
  <div class="flex whitespace-nowrap marquee-track gap-8 w-max">
    <span>🔥 Fituesi i fundit: Arben fitoi 2,450€ në Slote! &nbsp;•&nbsp; ⭐ Blerta fitoi 1,200€ në Ruletë! &nbsp;•&nbsp; 💎 Jackpot 125,000€! &nbsp;•&nbsp;</span>
    <span>🔥 Fituesi i fundit: Arben fitoi 2,450€ në Slote! &nbsp;•&nbsp; ⭐ Blerta fitoi 1,200€ në Ruletë! &nbsp;•&nbsp; 💎 Jackpot 125,000€! &nbsp;•&nbsp;</span>
  </div>
</div>

<!-- GAMES GRID -->
<h2 class="text-2xl font-black mt-8 mb-4">🎮 Lojërat e Kazinosë</h2>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
  <a href="/slots" class="glass rounded-3xl p-6 card-glow block">
    <div class="text-6xl mb-3 floaty">🎰</div>
    <h3 class="text-xl font-black">Slotet e Artë</h3>
    <p class="text-white/60 text-sm mt-1">3 rrotulla, jackpot x10. Animacion rrotullimi.</p>
    <div class="mt-3 text-amber-300 font-bold text-sm">RTP 96% • Min 1€ →</div>
  </a>
  <a href="/roulette" class="glass rounded-3xl p-6 card-glow block">
    <div class="text-6xl mb-3 floaty" style="animation-delay:.4s">🎡</div>
    <h3 class="text-xl font-black">Ruleta Evropiane</h3>
    <p class="text-white/60 text-sm mt-1">E kuqe / E zezë / Numër. Rrotë e animuar canvas.</p>
    <div class="mt-3 text-amber-300 font-bold text-sm">Paguan deri 36x →</div>
  </a>
  <a href="/blackjack" class="glass rounded-3xl p-6 card-glow block">
    <div class="text-6xl mb-3 floaty" style="animation-delay:.8s">🃏</div>
    <h3 class="text-xl font-black">Blackjack 21</h3>
    <p class="text-white/60 text-sm mt-1">Mundi bankën. Blackjack paguan 2.5x.</p>
    <div class="mt-3 text-amber-300 font-bold text-sm">Hit / Stand →</div>
  </a>
  <a href="/dice" class="glass rounded-3xl p-6 card-glow block">
    <div class="text-6xl mb-3 floaty" style="animation-delay:1.2s">🎲</div>
    <h3 class="text-xl font-black">Zari i Fatit</h3>
    <p class="text-white/60 text-sm mt-1">Hi/Low, Çift/Tek, Numër ekzakt 5.5x. Zar 3D.</p>
    <div class="mt-3 text-amber-300 font-bold text-sm">Rrotullo zarin →</div>
  </a>
  <a href="/sports" class="glass rounded-3xl p-6 card-glow block border-amber-500/30" style="border-width:2px">
    <div class="text-6xl mb-3 floaty" style="animation-delay:1.6s">⚽</div>
    <h3 class="text-xl font-black">Bastore Sportive 🏆</h3>
    <p class="text-white/60 text-sm mt-1">12 ndeshje reale, kuota 1X2 + Over/Under. Biletë e kombinuar.</p>
    <div class="mt-3 text-amber-300 font-bold text-sm">Vë bast →</div>
  </a>
  <div class="rounded-3xl p-6 bg-gradient-to-br from-amber-500 to-orange-700 text-black">
    <div class="text-5xl mb-2">💳</div>
    <h3 class="text-xl font-black">Depozito & Luaj</h3>
    <p class="text-sm font-semibold mt-1">Shto para në Portofol dhe fito pa limit. Tërhiq kur të duash!</p>
    <button onclick="openWallet()" class="mt-3 bg-black text-amber-300 px-6 py-2 rounded-full font-bold hover:scale-105 transition">Hap Portofolin</button>
  </div>
</div>

<div class="grid md:grid-cols-3 gap-5 mt-6 text-center">
  <div class="glass rounded-2xl p-5"><div class="text-3xl">⚡</div><div class="font-black text-xl">Pagesa Instant</div><div class="text-white/60 text-sm">Fitimet shtohen menjëherë në balancë</div></div>
  <div class="glass rounded-2xl p-5"><div class="text-3xl">🎬</div><div class="font-black text-xl">Animacione Live</div><div class="text-white/60 text-sm">Rrotulla, letra, zar, konfeti</div></div>
  <div class="glass rounded-2xl p-5"><div class="text-3xl">🔒</div><div class="font-black text-xl">100% Demo</div><div class="text-white/60 text-sm">Pa para reale — vetëm argëtim</div></div>
</div>
@endsection

@section('scripts')
<script>
let jp = {{ $jackpot }};
setInterval(()=>{ jp += Math.floor(Math.random()*25); document.getElementById('jackpot').textContent = jp.toLocaleString('de-DE')+'€'; }, 1500);
</script>
@endsection
