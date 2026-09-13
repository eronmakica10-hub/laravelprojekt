@extends('layouts.casino')
@section('title','Ballina')

@section('content')
<!-- HERO -->
<div class="glass rounded-3xl p-8 md:p-12 text-center relative overflow-hidden slidein">
  <div class="absolute inset-0 bg-gradient-to-r from-emerald-600/15 via-slate-500/5 to-emerald-600/15"></div>
  <div class="relative">
    <div class="text-6xl mb-2">🦅🎰⚽</div>
    <h1 class="text-4xl md:text-6xl font-black neon-gold">KAZINO ONLINE</h1>
    <p class="text-white/70 mt-3 text-lg">Slotet • Ruleta • Blackjack • Zari • Crash • Mines • Hi-Lo • Plinko • Chicken • Bastore Sportive</p>
    <div class="inline-block mt-5 rounded-2xl px-8 py-4 border border-white/10" style="background:#0a1526">
      <div class="text-xs tracking-widest text-slate-400">💰 JACKPOT PROGRESIV</div>
      <div id="jackpot" class="text-4xl md:text-5xl font-black tabular-nums" style="color:#f5c518">{{ number_format($jackpot,0,',','.') }}€</div>
    </div>
    <div class="mt-6 flex justify-center gap-3 flex-wrap">
      <a href="/slots" class="btn-gold px-8 py-3 rounded-full text-lg">🎰 Luaj Tani</a>
      <a href="/sports" class="px-8 py-3 rounded-full bg-white/10 border border-white/20 font-bold hover:bg-white/20">⚽ Baste Sportive</a>
    </div>
  </div>
</div>

<!-- marquee -->
<div class="mt-4 overflow-hidden glass rounded-full py-2 text-sm font-bold text-slate-300">
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
    <p class="text-white/60 text-sm mt-1">4 lojëra: E Artë • Frutat • Egjipti • Super 777!</p>
    <div class="mt-3 text-emerald-300 font-bold text-sm">RTP 96% • Min 1€ →</div>
  </a>
  <a href="/roulette" class="glass rounded-3xl p-6 card-glow block">
    <div class="text-6xl mb-3 floaty" style="animation-delay:.4s">🎡</div>
    <h3 class="text-xl font-black">Ruleta Evropiane</h3>
    <p class="text-white/60 text-sm mt-1">E kuqe / E zezë / Numër. Rrotë e animuar canvas.</p>
    <div class="mt-3 text-emerald-300 font-bold text-sm">Paguan deri 36x →</div>
  </a>
  <a href="/blackjack" class="glass rounded-3xl p-6 card-glow block">
    <div class="text-6xl mb-3 floaty" style="animation-delay:.8s">🃏</div>
    <h3 class="text-xl font-black">Blackjack 21</h3>
    <p class="text-white/60 text-sm mt-1">Mundi bankën. Blackjack paguan 2.5x.</p>
    <div class="mt-3 text-emerald-300 font-bold text-sm">Hit / Stand →</div>
  </a>
  <a href="/dice" class="glass rounded-3xl p-6 card-glow block">
    <div class="text-6xl mb-3 floaty" style="animation-delay:1.2s">🎲</div>
    <h3 class="text-xl font-black">Zari i Fatit</h3>
    <p class="text-white/60 text-sm mt-1">Hi/Low, Çift/Tek, Numër ekzakt 5.5x. Zar 3D.</p>
    <div class="mt-3 text-emerald-300 font-bold text-sm">Rrotullo zarin →</div>
  </a>
  <a href="/crash" class="glass rounded-3xl p-6 card-glow block border-red-500/30" style="border-width:2px">
    <div class="text-6xl mb-3 floaty" style="animation-delay:.2s">🚀</div>
    <h3 class="text-xl font-black">Crash 🔥</h3>
    <p class="text-white/60 text-sm mt-1">Cashout para rrëzimit. Deri 100x!</p>
    <div class="mt-3 text-emerald-300 font-bold text-sm">Ngrihu lart →</div>
  </a>
  <a href="/mines" class="glass rounded-3xl p-6 card-glow block">
    <div class="text-6xl mb-3 floaty" style="animation-delay:.6s">💣</div>
    <h3 class="text-xl font-black">Mines</h3>
    <p class="text-white/60 text-sm mt-1">5x5, ruju nga minat. Cashout kur të duash.</p>
    <div class="mt-3 text-emerald-300 font-bold text-sm">Hap katrorët →</div>
  </a>
  <a href="/hilo" class="glass rounded-3xl p-6 card-glow block">
    <div class="text-6xl mb-3 floaty" style="animation-delay:1s">🃏</div>
    <h3 class="text-xl font-black">Hi-Lo</h3>
    <p class="text-white/60 text-sm mt-1">Më e lartë apo më e ulët? Ndërto serinë.</p>
    <div class="mt-3 text-emerald-300 font-bold text-sm">Gjej letrën →</div>
  </a>
  <a href="/plinko" class="glass rounded-3xl p-6 card-glow block border-emerald-500/30" style="border-width:2px">
    <div class="text-6xl mb-3 floaty" style="animation-delay:2s">🔴</div>
    <h3 class="text-xl font-black">Plinko ✨</h3>
    <p class="text-white/60 text-sm mt-1">Topi bie nëpër kunja. Deri 1000x!</p>
    <div class="mt-3 text-emerald-300 font-bold text-sm">Lësho topin →</div>
  </a>
  <a href="/chicken" class="glass rounded-3xl p-6 card-glow block border-amber-500/30" style="border-width:2px">
    <div class="text-6xl mb-3 floaty" style="animation-delay:2.4s">🐔</div>
    <h3 class="text-xl font-black">Chicken Road 🛣️</h3>
    <p class="text-white/60 text-sm mt-1">Kalo 15 korsi me pulën. Deri 600x!</p>
    <div class="mt-3 text-emerald-300 font-bold text-sm">Kalo rrugën →</div>
  </a>
  <a href="/poker" class="glass rounded-3xl p-6 card-glow block border-emerald-500/30" style="border-width:2px">
    <div class="text-6xl mb-3 floaty" style="animation-delay:2.8s">🃏</div>
    <h3 class="text-xl font-black">Poker Hold'em ♠️</h3>
    <p class="text-white/60 text-sm mt-1">Me bota offline ose online me shokë!</p>
    <div class="mt-3 text-emerald-300 font-bold text-sm">Ulu në tavolinë →</div>
  </a>
  <a href="/sports" class="glass rounded-3xl p-6 card-glow block border-emerald-500/30" style="border-width:2px">
    <div class="text-6xl mb-3 floaty" style="animation-delay:1.6s">⚽</div>
    <h3 class="text-xl font-black">Bastore Sportive 🏆</h3>
    <p class="text-white/60 text-sm mt-1">Ndeshje reale të ardhshme: 1X2, Shans i Dyfishtë, GG/NG, Over/Under. Biletë e kombinuar.</p>
    <div class="mt-3 text-emerald-300 font-bold text-sm">Vë bast →</div>
  </a>
  <div class="rounded-3xl p-6 bg-gradient-to-br from-emerald-600 to-teal-800 text-white">
    <div class="text-5xl mb-2">💳</div>
    <h3 class="text-xl font-black">Depozito & Luaj</h3>
    <p class="text-sm font-semibold mt-1 text-emerald-50/90">Shto para në Portofol dhe fito pa limit. Tërhiq kur të duash!</p>
    <button onclick="openWallet()" class="mt-3 bg-white text-emerald-800 px-6 py-2 rounded-full font-bold hover:scale-105 transition">Hap Portofolin</button>
  </div>
</div>

<div class="grid md:grid-cols-3 gap-5 mt-6 text-center">
  <div class="glass rounded-2xl p-5"><div class="text-3xl">⚡</div><div class="font-black text-xl">Pagesa Instant</div><div class="text-white/60 text-sm">Fitimet shtohen menjëherë në balancë</div></div>
  <div class="glass rounded-2xl p-5"><div class="text-3xl">🎬</div><div class="font-black text-xl">Animacione Live</div><div class="text-white/60 text-sm">Rrotulla, letra, zar, konfeti</div></div>
  <div class="glass rounded-2xl p-5"><div class="text-3xl">🔒</div><div class="font-black text-xl">100% Demo</div><div class="text-white/60 text-sm">Pa para reale — vetëm argëtim</div></div>
</div>

<!-- Bastet live si Stake -->
<div class="glass rounded-3xl mt-6 p-5">
  <div class="flex items-center justify-between">
    <h2 class="text-xl font-black">🔴 Bastet Live</h2>
    <span class="text-xs text-white/40 font-bold">përditësohet automatikisht</span>
  </div>
  <div class="mt-3 text-sm">
    <div class="grid grid-cols-4 gap-2 text-[11px] uppercase tracking-wider text-white/40 font-bold px-3 pb-1">
      <span>Lojtari</span><span>Loja</span><span class="text-right">Basti</span><span class="text-right">Fitimi</span>
    </div>
    <div id="livefeed" class="space-y-1.5"></div>
  </div>
</div>
@endsection

@section('scripts')
<script>
let jp = {{ $jackpot }};
setInterval(()=>{ jp += Math.floor(Math.random()*25); document.getElementById('jackpot').textContent = jp.toLocaleString('de-DE')+'€'; }, 1500);
// feed i basteve live (demo, si Stake)
(function(){
  const names=['Arben','Blerta','Dren','Edona','Fisnik','Genta','Hana','Ilir','Jeta','Kushtrim','Liridon','Mirjeta','Nora','Petrit','Qëndrim','Rina','Shpetim','Teuta','Ujkan','Valon'];
  const games=[['🎰','Slotet'],['🎡','Ruleta'],['🃏','Blackjack'],['🎲','Zari'],['🚀','Crash'],['💣','Mines'],['🃏','Hi-Lo'],['🔴','Plinko'],['🐔','Chicken'],['⚽','Bastet']];
  const box=document.getElementById('livefeed');
  if(!box)return;
  function mask(n){ return n.slice(0,2)+'***'; }
  function row(){
    const g=games[Math.floor(Math.random()*games.length)];
    const stake=[5,10,10,20,25,50,50,100][Math.floor(Math.random()*8)];
    const mult=[1.1,1.4,1.9,2,2.5,3.2,5.5,10,36][Math.floor(Math.random()*9)];
    const win=Math.random()<0.42;
    const amt=win?+(stake*mult-stake).toFixed(2):-stake;
    const d=document.createElement('div');
    d.className='grid grid-cols-4 gap-2 items-center bg-black/30 rounded-xl px-3 py-2 slidein';
    d.innerHTML=`<span class="font-bold">🙂 ${mask(names[Math.floor(Math.random()*names.length)])}</span>
      <span class="text-white/60">${g[0]} ${g[1]}</span>
      <span class="text-right font-bold">${stake.toFixed(2)}€</span>
      <span class="text-right font-black ${amt>=0?'text-green-400':'text-red-400'}">${amt>=0?'+':''}${amt.toFixed(2)}€</span>`;
    box.prepend(d);
    while(box.children.length>7)box.lastChild.remove();
  }
  for(let i=0;i<5;i++)row();
  setInterval(row,3500);
})();
</script>
@endsection
