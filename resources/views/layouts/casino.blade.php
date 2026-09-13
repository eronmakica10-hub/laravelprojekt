<!DOCTYPE html>
<html lang="sq">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Kazino Online') | Golden Eagle Casino</title>
<meta name="description" content="Golden Eagle Casino — poker Texas Hold'em online me shokë, slots, ruletë, blackjack dhe baste sportive. Luaj falas në telefon e kompjuter.">
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
  *{font-family:'Inter',system-ui,sans-serif}
  ::selection{background:#059669;color:#fff}
  body{background:#081120;color:#e8edf5;overflow-x:hidden}
  .bg-animated{background:radial-gradient(1200px 500px at 50% -5%, #10b98114, transparent 70%),#081120;}
  .neon-gold{background:none;color:#f1f5f9;animation:none}
  @keyframes shine{to{background-position:200% center}}
  .card-glow{transition:.25s}
  .card-glow:hover{transform:translateY(-4px);box-shadow:0 18px 50px -12px #10b98144,0 0 0 1px #10b98166}
  .btn-gold{background:linear-gradient(135deg,#10b981,#047857);color:#fff;font-weight:800;transition:.25s;box-shadow:0 8px 22px -8px #10b98199}
  .btn-gold:hover{transform:translateY(-2px);box-shadow:0 14px 34px -8px #10b981cc;filter:brightness(1.08)}
  .btn-gold:active{transform:scale(.97)}
  .btn-gold:disabled{opacity:.5;transform:none;cursor:not-allowed}
  .glass{background:#0e1a30;border:1px solid rgba(148,163,184,.14);box-shadow:0 12px 32px -14px rgba(0,0,0,.7)}
  .reel{font-size:4.5rem;transition:.2s}
  .spinning{animation:reelspin .12s linear infinite;filter:blur(3px)}
  @keyframes reelspin{0%{transform:translateY(-20px)}100%{transform:translateY(20px)}}
  @keyframes jackpot{0%,100%{transform:scale(1) rotate(-2deg)}50%{transform:scale(1.15) rotate(2deg)}}
  .jackpot-anim{animation:jackpot .5s ease infinite}
  @keyframes winpulse{0%{box-shadow:0 0 0 0 #22c55eaa}100%{box-shadow:0 0 0 30px transparent}}
  .win-pulse{animation:winpulse .8s ease-out infinite}
  @keyframes slidein{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:none}}
  .slidein{animation:slidein .6s ease both}
  .playing-card{width:84px;height:120px;border-radius:12px;display:flex;flex-direction:column;align-items:center;justify-content:center;font-size:1.8rem;font-weight:800;background:linear-gradient(160deg,#fff,#e2e8f0);color:#111;box-shadow:0 10px 25px -5px #000a;animation:deal .4s cubic-bezier(.34,1.56,.64,1) both}
  .playing-card.red{color:#dc2626}
  @keyframes deal{from{opacity:0;transform:translateY(-40px) rotate(10deg) scale(.6)}to{opacity:1;transform:none}}
  .dice3d{font-size:6rem;display:inline-block;transition:.2s}
  .dice-rolling{animation:diceroll .15s linear infinite}
  @keyframes diceroll{0%{transform:rotate(0) scale(1)}50%{transform:rotate(180deg) scale(1.3)}100%{transform:rotate(360deg) scale(1)}}
  #roulette-wheel{transition:transform 4s cubic-bezier(.12,.8,.08,1)}
  .chip{width:52px;height:52px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:900;border:4px dashed #ffffffaa;cursor:pointer;transition:.2s;user-select:none}
  .chip:hover{transform:scale(1.15)}
  .chip.active{outline:3px solid #10b981;transform:scale(1.15);box-shadow:0 0 20px #10b98188}
  .odds-btn{transition:.2s}
  .odds-btn.selected{background:#059669!important;color:#fff!important;font-weight:800;transform:scale(1.03)}
  @keyframes floaty{0%,100%{transform:translateY(0)}50%{transform:translateY(-12px)}}
  .floaty{animation:floaty 3s ease-in-out infinite}
  @keyframes marquee{from{transform:translateX(0)}to{transform:translateX(-50%)}}
  .marquee-track{animation:marquee 20s linear infinite}
  .confetti{position:fixed;top:-20px;z-index:9999;pointer-events:none;animation:confall linear forwards}
  @keyframes confall{to{transform:translateY(110vh) rotate(720deg)}}
  ::-webkit-scrollbar{width:8px}::-webkit-scrollbar-track{background:#0a1322}::-webkit-scrollbar-thumb{background:#155e46;border-radius:4px}
  #customBet::-webkit-outer-spin-button,#customBet::-webkit-inner-spin-button{-webkit-appearance:none;margin:0}
  #customBet{-moz-appearance:textfield;appearance:textfield}
</style>
@yield('head')
</head>
<body class="bg-animated min-h-screen relative">

<!-- SHIRITI LART (info profesionale) -->
<div class="relative z-50 text-[11px] font-semibold" style="background:#050c18;border-bottom:1px solid rgba(148,163,184,.12)">
  <div class="max-w-7xl mx-auto px-4 py-1.5 flex items-center justify-between text-slate-400">
    <div class="flex items-center gap-2">
      <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
      <span>Oferta ditore aktive • Pagesa instant</span>
    </div>
    <div class="flex items-center gap-3">
      <span id="liveclock" class="tabular-nums text-slate-300">--.--.---- --:--:--</span>
      <span class="border border-red-500/60 text-red-400 rounded px-1.5 py-px font-black">18+</span>
      <span class="hidden sm:inline">Luaj me përgjegjësi</span>
    </div>
  </div>
</div>

<!-- NAVBAR -->
<nav class="sticky top-0 z-50 border-b border-white/10" style="background:#0b1526f2">
  <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-2 flex-wrap">
    <a href="/" class="flex items-center gap-2">
      <span class="text-3xl">🦅</span>
      <div>
        <div class="font-black text-xl leading-none tracking-wide"><span class="text-white">GOLDEN</span> <span class="text-emerald-400">EAGLE</span></div>
        <div class="text-[11px] tracking-[.3em] text-slate-400 font-semibold">KAZINO • BASTORE</div>
      </div>
    </a>
    <div class="hidden md:flex items-center gap-1 text-sm font-semibold">
      <a href="/" class="px-4 py-2 rounded-lg hover:bg-white/10 {{ request()->is('/')?'bg-emerald-600 text-white':'' }}">🏠 Ballina</a>
      <a href="/slots" class="px-4 py-2 rounded-lg hover:bg-white/10 {{ request()->is('slots')?'bg-emerald-600 text-white':'' }}">🎰 Slotet</a>
      <a href="/roulette" class="px-4 py-2 rounded-lg hover:bg-white/10 {{ request()->is('roulette')?'bg-emerald-600 text-white':'' }}">🎡 Ruleta</a>
      <a href="/blackjack" class="px-4 py-2 rounded-lg hover:bg-white/10 {{ request()->is('blackjack')?'bg-emerald-600 text-white':'' }}">🃏 Blackjack</a>
      <a href="/dice" class="px-4 py-2 rounded-lg hover:bg-white/10 {{ request()->is('dice')?'bg-emerald-600 text-white':'' }}">🎲 Zari</a>
      <a href="/crash" class="px-4 py-2 rounded-lg hover:bg-white/10 {{ request()->is('crash')?'bg-emerald-600 text-white':'' }}">🚀 Crash</a>
      <a href="/mines" class="px-4 py-2 rounded-lg hover:bg-white/10 {{ request()->is('mines')?'bg-emerald-600 text-white':'' }}">💣 Mines</a>
      <a href="/hilo" class="px-4 py-2 rounded-lg hover:bg-white/10 {{ request()->is('hilo')?'bg-emerald-600 text-white':'' }}">🃏 Hi-Lo</a>
      <a href="/plinko" class="px-4 py-2 rounded-lg hover:bg-white/10 {{ request()->is('plinko')?'bg-emerald-600 text-white':'' }}">🔴 Plinko</a>
      <a href="/chicken" class="px-4 py-2 rounded-lg hover:bg-white/10 {{ request()->is('chicken')?'bg-emerald-600 text-white':'' }}">🐔 Chicken</a>
      <a href="/poker" class="px-4 py-2 rounded-lg hover:bg-white/10 {{ request()->is('poker')?'bg-emerald-600 text-white':'' }}">🃏 Poker</a>
      <a href="/sports" class="px-4 py-2 rounded-lg hover:bg-white/10 {{ request()->is('sports')?'bg-emerald-600 text-white':'' }}">⚽ Bastet</a>
    </div>
    <div class="flex items-center gap-2">
      <div class="rounded-full px-4 py-2 flex items-center gap-2 border border-white/10" style="background:#0e1a30">
        <span>💰</span><span id="balance" class="font-black text-emerald-300">{{ number_format(session('balance',0),2) }}€</span>
      </div>
      @if(session('user_id'))
        <div class="relative">
          <button onclick="toggleProfile(event)" class="flex items-center gap-2 rounded-full pl-1 pr-3 py-1 border border-white/10 hover:bg-white/10 transition" style="background:#0e1a30">
            <span id="navAvatar" class="w-8 h-8 rounded-full flex items-center justify-center text-xl" style="background:#134e4a">{{ session('user_avatar','🦅') }}</span>
            <span class="text-left leading-tight">
              <span id="navName" class="block text-sm font-black max-w-[110px] truncate">{{ session('user_name') }}</span>
              <span class="block text-[10px] text-white/40 font-bold">PROFILI ▾</span>
            </span>
          </button>
          <div id="profileMenu" class="hidden absolute right-0 mt-2 w-56 rounded-2xl p-2 z-[9999] border border-white/10 shadow-2xl" style="background:#0b1526">
            <div class="px-4 py-2 text-[11px] text-white/40 font-bold">BALANCA: <span class="text-emerald-300">{{ number_format(session('balance',0),2) }}€</span></div>
            <button onclick="openWallet()" class="w-full text-left px-4 py-2.5 rounded-xl hover:bg-white/10 font-bold text-sm">💳 Portofoli</button>
            <button onclick="openProfile()" class="w-full text-left px-4 py-2.5 rounded-xl hover:bg-white/10 font-bold text-sm">⚙️ Setingjet e profilit</button>
            <form method="POST" action="/logout">@csrf<button class="w-full text-left px-4 py-2.5 rounded-xl hover:bg-red-500/20 font-bold text-sm text-red-300">↪ Dil</button></form>
          </div>
        </div>
      @else
        <button onclick="openWallet()" class="text-xs btn-gold px-4 py-2 rounded-full">💳 Portofoli</button>
        <a href="/login" class="text-xs bg-white/10 hover:bg-white/20 px-4 py-2 rounded-full font-bold">🔓 Hyr</a>
        <a href="/register" class="text-xs btn-gold px-4 py-2 rounded-full">🎁 Regjistrohu</a>
      @endif
    </div>
  </div>
  <div class="md:hidden flex overflow-x-auto gap-1 px-4 pb-2 text-sm font-semibold">
    <a href="/" class="px-3 py-1.5 rounded-full bg-white/10 whitespace-nowrap">🏠 Ballina</a>
    <a href="/slots" class="px-3 py-1.5 rounded-full bg-white/10 whitespace-nowrap">🎰 Slotet</a>
    <a href="/roulette" class="px-3 py-1.5 rounded-full bg-white/10 whitespace-nowrap">🎡 Ruleta</a>
    <a href="/blackjack" class="px-3 py-1.5 rounded-full bg-white/10 whitespace-nowrap">🃏 Blackjack</a>
    <a href="/dice" class="px-3 py-1.5 rounded-full bg-white/10 whitespace-nowrap">🎲 Zari</a>
    <a href="/crash" class="px-3 py-1.5 rounded-full bg-white/10 whitespace-nowrap">🚀 Crash</a>
    <a href="/mines" class="px-3 py-1.5 rounded-full bg-white/10 whitespace-nowrap">💣 Mines</a>
    <a href="/hilo" class="px-3 py-1.5 rounded-full bg-white/10 whitespace-nowrap">🃏 Hi-Lo</a>
    <a href="/plinko" class="px-3 py-1.5 rounded-full bg-white/10 whitespace-nowrap">🔴 Plinko</a>
    <a href="/chicken" class="px-3 py-1.5 rounded-full bg-white/10 whitespace-nowrap">🐔 Chicken</a>
    <a href="/poker" class="px-3 py-1.5 rounded-full bg-white/10 whitespace-nowrap">🃏 Poker</a>
    <a href="/sports" class="px-3 py-1.5 rounded-full bg-white/10 whitespace-nowrap">⚽ Bastet</a>
  </div>
</nav>

<main class="relative z-10 max-w-7xl mx-auto px-4 py-6">
  @if(session('success'))
    <div class="mb-4 bg-green-500/20 border border-green-500 rounded-2xl px-5 py-3 font-bold text-green-200 slidein">🎉 {{ session('success') }}</div>
  @endif
  @yield('content')
</main>

<footer class="relative z-10 text-center text-slate-500 text-xs py-8">
  🦅 Golden Eagle Casino • 18+ Luaj me përgjegjësi • Demo pa para reale<br>
  <span class="text-slate-600">VISA • Mastercard • Transfertë Bankare • Laravel {{ app()->version() }}</span>
</footer>

<script>
const csrf = document.querySelector('meta[name="csrf-token"]').content;
async function api(url, data={}, opts={}){
  const res = await fetch(url,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify(data)});
  const j = await res.json();
  if(!res.ok) throw new Error(j.error||'Gabim');
  // settle:'manual' => loja e thërret settle() vetë PASI të kryhet animacioni i fitores
  if(j.balance!==undefined && opts.settle!=='manual') updateBalance(j.balance);
  return j;
}
function updateBalance(v){ document.getElementById('balance').textContent = Number(v).toFixed(2)+'€'; }
function currentDisplayed(){
  const t = document.getElementById('balance').textContent.replace(/[^0-9.\-]/g,'');
  return parseFloat(t) || 0;
}
// Krediton balancën ME ANIMACION pasi kryhet veprimi i fitores (numërim + ngjyrë)
function settle(nv, dur=1100){
  nv = Number(nv);
  const el = document.getElementById('balance');
  const from = currentDisplayed();
  if(Math.abs(nv - from) < 0.005){ el.textContent = nv.toFixed(2)+'€'; return; }
  const t0 = performance.now();
  el.style.transform = 'scale(1.3)';
  el.style.color = nv > from ? '#4ade80' : '#f87171';
  function f(t){
    const p = Math.min(1, (t - t0) / dur), e = 1 - Math.pow(1 - p, 3);
    el.textContent = (from + (nv - from) * e).toFixed(2) + '€';
    if(p < 1) requestAnimationFrame(f);
    else{ el.textContent = nv.toFixed(2)+'€'; el.style.transform='scale(1)'; el.style.color=''; }
  }
  requestAnimationFrame(f);
}
// Monedha fluturojnë nga loja te balanca në navbar
function flyCoins(fromId, n=14){
  const from = document.getElementById(fromId), to = document.getElementById('balance');
  if(!from || !to) return;
  const a = from.getBoundingClientRect(), b = to.getBoundingClientRect();
  for(let i=0;i<n;i++){
    setTimeout(()=>{
      const c = document.createElement('div');
      c.textContent = '🪙';
      c.style.cssText = `position:fixed;z-index:9999;left:${a.left+a.width/2}px;top:${a.top+a.height/2}px;font-size:22px;pointer-events:none;transition:all .85s cubic-bezier(.3,.7,.4,1);`;
      document.body.appendChild(c);
      requestAnimationFrame(()=>{ c.style.left=(b.left+b.width/2+(Math.random()*36-18))+'px'; c.style.top=(b.top+b.height/2)+'px'; c.style.transform='scale(.45) rotate(360deg)'; });
      setTimeout(()=>c.remove(), 950);
    }, i*60);
  }
}
function toast(msg,type='info'){
  const d=document.createElement('div');
  d.className='fixed bottom-6 left-1/2 -translate-x-1/2 z-[9999] px-6 py-3 rounded-2xl font-bold shadow-2xl slidein '+(type==='win'?'bg-green-500 text-white win-pulse':type==='lose'?'bg-red-500 text-white':'bg-slate-200 text-slate-900');
  d.textContent=msg; document.body.appendChild(d); setTimeout(()=>d.remove(),2600);
}
function confetti(n=80){
  const colors=['#f59e0b','#ef4444','#22c55e','#3b82f6','#eab308','#ec4899'];
  for(let i=0;i<n;i++){const c=document.createElement('div');c.className='confetti';c.style.left=Math.random()*100+'vw';c.style.background=colors[i%colors.length];c.style.width=(6+Math.random()*8)+'px';c.style.height=(8+Math.random()*10)+'px';c.style.animationDuration=(2+Math.random()*2)+'s';document.body.appendChild(c);setTimeout(()=>c.remove(),4000);}
}
// ora live në shiritin lart
(function(){
  const el=document.getElementById('liveclock');
  if(!el) return;
  function pad(n){return String(n).padStart(2,'0');}
  function f(){
    const d=new Date();
    el.textContent=`${pad(d.getDate())}.${pad(d.getMonth()+1)}.${d.getFullYear()} ${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;
  }
  f(); setInterval(f,1000);
})();
</script>

<!-- ===== PORTOFOLI (Deposit / Withdraw) ===== -->
<div id="walletModal" class="hidden fixed inset-0 z-[9998] flex items-center justify-center p-4" style="background:rgba(0,0,0,.75);backdrop-filter:blur(4px)">
  <div class="glass rounded-3xl w-full max-w-md p-6 slidein" id="walletBox" style="background:#12121cee;border:2px solid #f59e0b55">
    <div class="flex justify-between items-center">
      <h2 class="text-2xl font-black neon-gold">💳 PORTOFOLI</h2>
      <button onclick="closeWallet()" class="bg-white/10 hover:bg-white/20 w-9 h-9 rounded-full font-black">✕</button>
    </div>
    <div class="text-center mt-2 bg-black/50 rounded-2xl py-3 border border-amber-500/30">
      <div class="text-xs text-white/50">BALANCA JUAJ</div>
      <div id="walletBal" class="text-3xl font-black text-amber-300">0.00€</div>
    </div>
    <div class="grid grid-cols-3 gap-2 mt-4 text-sm font-black">
      <button id="tabDep" onclick="wtab('dep')" class="py-2 rounded-xl btn-gold">➕ Depozito</button>
      <button id="tabWit" onclick="wtab('wit')" class="py-2 rounded-xl bg-white/10">➖ Tërhiq</button>
      <button id="tabHis" onclick="wtab('his')" class="py-2 rounded-xl bg-white/10">📜 Historiku</button>
    </div>

    <!-- DEPOZITO -->
    <div id="paneDep" class="mt-4">
      <div class="rounded-2xl p-4 text-black font-bold" style="background:linear-gradient(135deg,#0f766e,#134e4a 60%,#042f2e);color:#fff">
        <div class="flex justify-between text-xs opacity-80"><span>GOLDEN EAGLE CARD</span><span>💳 VISA</span></div>
        <div id="cardPrev" class="text-xl tracking-widest mt-3 font-mono">•••• •••• •••• ••••</div>
        <div class="flex justify-between text-xs mt-2"><span id="cardName">EMRI JUAJ</span><span id="cardExp">MM/YY</span></div>
      </div>
      <div class="flex gap-2 mt-3 flex-wrap">
        <button onclick="wamount(20)" class="wamt flex-1 bg-white/10 rounded-xl py-2 font-black hover:bg-emerald-600 hover:text-white">20€</button>
        <button onclick="wamount(50)" class="wamt flex-1 bg-white/10 rounded-xl py-2 font-black hover:bg-emerald-600 hover:text-white">50€</button>
        <button onclick="wamount(100)" class="wamt flex-1 bg-white/10 rounded-xl py-2 font-black hover:bg-emerald-600 hover:text-white">100€</button>
        <button onclick="wamount(500)" class="wamt flex-1 bg-white/10 rounded-xl py-2 font-black hover:bg-emerald-600 hover:text-white">500€</button>
      </div>
      <input id="depAmount" type="number" min="5" max="10000" value="50" class="w-full mt-3 bg-black/50 border border-white/20 rounded-xl px-4 py-2.5 font-black" placeholder="Shuma € (min 5€)">
      <input id="depCard" inputmode="numeric" maxlength="19" placeholder="Numri i kartës" class="w-full mt-2 bg-black/50 border border-white/20 rounded-xl px-4 py-2.5" oninput="this.value=this.value.replace(/\D/g,'').slice(0,16).replace(/(\d{4})(?=\d)/g,'$1 ');document.getElementById('cardPrev').textContent=(this.value||'•••• •••• •••• ••••')">
      <div class="grid grid-cols-2 gap-2 mt-2">
        <input id="depExp" maxlength="5" placeholder="MM/YY" class="bg-black/50 border border-white/20 rounded-xl px-4 py-2.5" oninput="document.getElementById('cardExp').textContent=(this.value||'MM/YY')">
        <input id="depCvc" maxlength="4" inputmode="numeric" placeholder="CVC" class="bg-black/50 border border-white/20 rounded-xl px-4 py-2.5" oninput="this.value=this.value.replace(/\D/g,'')">
      </div>
      <button onclick="doDeposit()" id="depBtn" class="btn-gold w-full mt-3 py-3 rounded-2xl text-lg">DEPOZITO ➕</button>
    </div>

    <!-- TËRHIQ -->
    <div id="paneWit" class="hidden mt-4">
      <label class="text-sm text-white/60">Shuma për tërheqje (min 10€):</label>
      <input id="witAmount" type="number" min="10" value="50" class="w-full mt-1 bg-black/50 border border-white/20 rounded-xl px-4 py-2.5 font-black">
      <label class="text-sm text-white/60 mt-3 block">IBAN-i juaj:</label>
      <input id="witIban" placeholder="XK05 1212 0123 4567 89" class="w-full mt-1 bg-black/50 border border-white/20 rounded-xl px-4 py-2.5 uppercase">
      <div class="text-xs text-white/40 mt-2">⏱️ Paratë arrijnë në llogari brenda 24 orëve. Pa komision.</div>
      <button onclick="doWithdraw()" id="witBtn" class="w-full mt-3 py-3 rounded-2xl text-lg font-black bg-green-600 hover:bg-green-500">TËRHIQ ➖</button>
    </div>

    <!-- HISTORIKU -->
    <div id="paneHis" class="hidden mt-4 max-h-64 overflow-y-auto space-y-2">
      <div id="txList" class="space-y-2 text-sm"><div class="text-white/40">Po ngarkohet...</div></div>
    </div>
  </div>
</div>
<script>
function wamount(v){ document.getElementById('depAmount').value=v; }
function openWallet(){
  document.getElementById('walletModal').classList.remove('hidden');
  document.getElementById('walletBal').textContent=currentDisplayed().toFixed(2)+'€';
  wtab('dep'); loadTx();
}
function closeWallet(){ document.getElementById('walletModal').classList.add('hidden'); }
document.getElementById('walletModal').addEventListener('click', e=>{ if(e.target.id==='walletModal') closeWallet(); });
function wtab(t){
  ['Dep','Wit','His'].forEach(x=>{
    document.getElementById('pane'+x).classList.add('hidden');
    const b=document.getElementById('tab'+x);
    b.classList.remove('btn-gold'); b.classList.add('bg-white/10');
  });
  const map={dep:'Dep',wit:'Wit',his:'His'};
  document.getElementById('pane'+map[t]).classList.remove('hidden');
  const b=document.getElementById('tab'+map[t]);
  b.classList.add('btn-gold'); b.classList.remove('bg-white/10');
  if(t==='his') loadTx();
}
async function loadTx(){
  try{
    const res=await fetch('/api/wallet/history'); const j=await res.json();
    document.getElementById('walletBal').textContent=Number(j.balance).toFixed(2)+'€';
    const box=document.getElementById('txList');
    if(!j.transactions.length){ box.innerHTML='<div class="text-white/40">Ende asnjë transaksion.</div>'; return; }
    box.innerHTML=j.transactions.map(t=>`
      <div class="bg-black/40 rounded-xl p-3 flex justify-between items-center border ${t.type==='deposit'?'border-green-500/40':'border-red-500/40'}">
        <div><div class="font-black">${t.type==='deposit'?'🟢 Depozitë':'🔴 Tërheqje'}</div>
        <div class="text-white/50 text-xs">${t.note||''} • ${t.date}</div></div>
        <div class="font-black ${t.type==='deposit'?'text-green-400':'text-red-400'}">${t.type==='deposit'?'+':'−'}${Number(t.amount).toFixed(2)}€</div>
      </div>`).join('');
  }catch(e){ document.getElementById('txList').innerHTML='<div class="text-red-400">Gabim në ngarkim.</div>'; }
}
async function doDeposit(){
  const btn=document.getElementById('depBtn'); btn.disabled=true;
  const amount=parseFloat(document.getElementById('depAmount').value||'0');
  const card=document.getElementById('depCard').value;
  if(card.replace(/\D/g,'').length<12){ toast('Shkruaj numrin e kartës (12-16 shifra).','lose'); btn.disabled=false; return; }
  try{
    const j=await api('/api/wallet/deposit',{amount,card},{settle:'manual'});
    closeWallet();
    // animacion: numërimi + konfeti pasi "kryhet pagesa"
    toast('⏳ Po procesohet pagesa...','info');
    setTimeout(()=>{ settle(j.balance,1400); confetti(90); toast(j.message,'win'); }, 900);
    loadTx();
  }catch(e){ toast(e.message,'lose'); }
  btn.disabled=false;
}
async function doWithdraw(){
  const btn=document.getElementById('witBtn'); btn.disabled=true;
  try{
    const j=await api('/api/wallet/withdraw',{amount:parseFloat(document.getElementById('witAmount').value||'0'),iban:document.getElementById('witIban').value},{settle:'manual'});
    closeWallet();
    toast('⏳ Po procesohet tërheqja...','info');
    setTimeout(()=>{ settle(j.balance,1400); toast(j.message,'win'); }, 900);
    loadTx();
  }catch(e){ toast(e.message,'lose'); }
  btn.disabled=false;
}
</script>
<!-- ===== PROFILI (Setingjet) ===== -->
<div id="profileModal" class="hidden fixed inset-0 z-[9998] flex items-center justify-center p-4" style="background:rgba(0,0,0,.75);backdrop-filter:blur(4px)">
  <div class="glass rounded-3xl w-full max-w-md p-6 slidein" style="background:#12121cee;border:2px solid #10b98155">
    <div class="flex justify-between items-center">
      <h2 class="text-2xl font-black">⚙️ PROFILI</h2>
      <button onclick="closeProfile()" class="bg-white/10 hover:bg-white/20 w-9 h-9 rounded-full font-black">✕</button>
    </div>
    <div class="flex items-center gap-3 mt-3 bg-black/50 rounded-2xl p-4 border border-white/10">
      <span id="pfAvatar" class="w-14 h-14 rounded-full flex items-center justify-center text-4xl" style="background:#134e4a">🦅</span>
      <div>
        <div class="font-black text-lg" id="pfName">—</div>
        <div class="text-xs text-white/50"><span id="pfBadge"></span> • Anëtar: <span id="pfSince">—</span></div>
        <div class="text-sm font-black text-emerald-300">💰 <span id="pfBal">0.00€</span> • <span id="pfTx">0</span> transaksione</div>
      </div>
    </div>
    <div class="grid grid-cols-3 gap-2 mt-4 text-sm font-black">
      <button id="ptabP" onclick="ptab('p')" class="py-2 rounded-xl btn-gold">👤 Profili</button>
      <button id="ptabC" onclick="ptab('c')" class="py-2 rounded-xl bg-white/10">💾 Ruaje</button>
      <button id="ptabS" onclick="ptab('s')" class="py-2 rounded-xl bg-white/10">🔒 Fjalëkalimi</button>
    </div>
    <div id="paneP" class="mt-4">
      <label class="text-sm text-white/60">Zgjidh avatarin:</label>
      <div id="avatarGrid" class="grid grid-cols-8 gap-1.5 mt-1"></div>
      <label class="text-sm text-white/60 mt-3 block">Emri:</label>
      <input id="pfNameInput" maxlength="30" class="w-full mt-1 bg-black/50 border border-white/20 rounded-xl px-4 py-2.5 font-black">
      <button onclick="saveProfile()" id="pfSaveBtn" class="btn-gold w-full mt-3 py-3 rounded-2xl">RUAJ ✅</button>
    </div>
    <div id="paneC" class="hidden mt-4">
      <div class="text-sm text-white/60">Ktheje llogarinë guest në të plotë — hyni kudo me email + fjalëkalim:</div>
      <input id="clName" maxlength="30" placeholder="Emri" class="w-full mt-2 bg-black/50 border border-white/20 rounded-xl px-4 py-2.5 font-black">
      <input id="clEmail" type="email" placeholder="Email" class="w-full mt-2 bg-black/50 border border-white/20 rounded-xl px-4 py-2.5">
      <input id="clPass" type="password" placeholder="Fjalëkalimi (min 6)" class="w-full mt-2 bg-black/50 border border-white/20 rounded-xl px-4 py-2.5">
      <input id="clPass2" type="password" placeholder="Përsërit fjalëkalimin" class="w-full mt-2 bg-black/50 border border-white/20 rounded-xl px-4 py-2.5">
      <button onclick="claimAccount()" id="clBtn" class="btn-gold w-full mt-3 py-3 rounded-2xl">RUAJ LLOGARINË 💾</button>
    </div>
    <div id="paneS" class="hidden mt-4">
      <input id="pwCur" type="password" placeholder="Fjalëkalimi aktual" class="w-full mt-1 bg-black/50 border border-white/20 rounded-xl px-4 py-2.5">
      <input id="pwNew" type="password" placeholder="Fjalëkalimi i ri (min 6)" class="w-full mt-2 bg-black/50 border border-white/20 rounded-xl px-4 py-2.5">
      <input id="pwNew2" type="password" placeholder="Përsërit të riun" class="w-full mt-2 bg-black/50 border border-white/20 rounded-xl px-4 py-2.5">
      <button onclick="changePw()" id="pwBtn" class="btn-gold w-full mt-3 py-3 rounded-2xl">NDRYSHO 🔒</button>
    </div>
  </div>
</div>
<script>
function toggleProfile(e){ e.stopPropagation(); document.getElementById('profileMenu').classList.toggle('hidden'); }
document.addEventListener('click',e=>{
  const m=document.getElementById('profileMenu');
  if(m&&!m.classList.contains('hidden')&&!m.contains(e.target))m.classList.add('hidden');
});
function closeProfile(){ document.getElementById('profileModal').classList.add('hidden'); }
document.getElementById('profileModal').addEventListener('click',e=>{ if(e.target.id==='profileModal')closeProfile(); });
let pfAvatar='🦅', pfIsGuest=false;
async function openProfile(){
  document.getElementById('profileMenu').classList.add('hidden');
  document.getElementById('profileModal').classList.remove('hidden');
  try{
    const res=await fetch('/api/profile'); const j=await res.json();
    if(!res.ok)throw new Error(j.error||'Gabim');
    pfAvatar=j.avatar; pfIsGuest=j.is_guest;
    document.getElementById('pfAvatar').textContent=j.avatar;
    document.getElementById('pfName').textContent=j.name;
    document.getElementById('pfBadge').textContent=j.is_guest?'👤 Guest':'✅ Anëtar';
    document.getElementById('pfSince').textContent=(j.created||'').slice(0,10).split('-').reverse().join('.');
    document.getElementById('pfBal').textContent=Number(j.balance).toFixed(2)+'€';
    document.getElementById('pfTx').textContent=j.tx_count;
    document.getElementById('pfNameInput').value=j.name;
    document.getElementById('avatarGrid').innerHTML=j.avatars.map(a=>
      `<button onclick="pickAvatar('${a}')" data-av="${a}" class="aspect-square rounded-xl text-2xl border-2 ${a===j.avatar?'border-emerald-500 bg-emerald-600/20':'border-white/10 bg-white/5 hover:bg-white/15'}">${a}</button>`
    ).join('');
    document.getElementById('clName').value=j.is_guest?j.name:'';
    ptab('p');
  }catch(e){ toast(e.message,'lose'); closeProfile(); }
}
function pickAvatar(a){
  pfAvatar=a;
  document.querySelectorAll('#avatarGrid button').forEach(b=>{
    const on=b.dataset.av===a;
    b.className=`aspect-square rounded-xl text-2xl border-2 ${on?'border-emerald-500 bg-emerald-600/20':'border-white/10 bg-white/5 hover:bg-white/15'}`;
  });
}
function ptab(t){
  ['P','C','S'].forEach(x=>{
    document.getElementById('pane'+x).classList.add('hidden');
    const b=document.getElementById('ptab'+x);
    b.classList.remove('btn-gold'); b.classList.add('bg-white/10');
  });
  const map={p:'P',c:'C',s:'S'};
  document.getElementById('pane'+map[t]).classList.remove('hidden');
  const b=document.getElementById('ptab'+map[t]);
  b.classList.add('btn-gold'); b.classList.remove('bg-white/10');
}
async function saveProfile(){
  const btn=document.getElementById('pfSaveBtn'); btn.disabled=true;
  try{
    const j=await api('/api/profile/update',{name:document.getElementById('pfNameInput').value.trim(),avatar:pfAvatar},{settle:'manual'});
    document.getElementById('navAvatar').textContent=j.avatar;
    document.getElementById('navName').textContent=j.name;
    document.getElementById('pfAvatar').textContent=j.avatar;
    document.getElementById('pfName').textContent=j.name;
    toast(j.message,'win');
  }catch(e){ toast(e.message,'lose'); }
  btn.disabled=false;
}
async function claimAccount(){
  const btn=document.getElementById('clBtn'); btn.disabled=true;
  try{
    const j=await api('/api/profile/claim',{
      name:document.getElementById('clName').value.trim(),
      email:document.getElementById('clEmail').value.trim(),
      password:document.getElementById('clPass').value,
      password_confirmation:document.getElementById('clPass2').value,
    },{settle:'manual'});
    document.getElementById('navName').textContent=j.name;
    toast(j.message,'win'); closeProfile();
  }catch(e){ toast(e.message,'lose'); }
  btn.disabled=false;
}
async function changePw(){
  const btn=document.getElementById('pwBtn'); btn.disabled=true;
  try{
    const j=await api('/api/profile/password',{
      current:document.getElementById('pwCur').value,
      password:document.getElementById('pwNew').value,
      password_confirmation:document.getElementById('pwNew2').value,
    },{settle:'manual'});
    document.getElementById('pwCur').value=document.getElementById('pwNew').value=document.getElementById('pwNew2').value='';
    toast(j.message,'win');
  }catch(e){ toast(e.message,'lose'); }
  btn.disabled=false;
}
</script>
@yield('scripts')
<script>
// Shuma e personalizuar e bastit — shfaqet automatikisht ku ka chips-a (#bets)
(function(){
  try{
    const box=document.getElementById('bets');
    if(!box||document.getElementById('customBet'))return;
    if(typeof bet==='undefined')return;
    const w=document.createElement('div');
    w.className='flex justify-center mt-2';
    w.innerHTML=`<div class="flex items-center gap-1.5 rounded-2xl px-2 py-1.5 border border-emerald-500/40" style="background:#0a1526;box-shadow:0 0 16px -4px #10b98166">
      <button id="betMinus" class="w-8 h-8 rounded-xl bg-white/10 hover:bg-emerald-600 font-black text-lg leading-none transition">−</button>
      <div class="relative">
        <input id="customBet" type="number" min="1" step="0.5" placeholder="10" class="w-24 bg-transparent font-black text-center text-white text-lg outline-none" style="appearance:textfield">
        <span class="absolute right-1 top-1/2 -translate-y-1/2 text-emerald-300 font-black">€</span>
      </div>
      <button id="betPlus" class="w-8 h-8 rounded-xl bg-white/10 hover:bg-emerald-600 font-black text-lg leading-none transition">+</button>
    </div>`;
    box.after(w);
    const cb=document.getElementById('customBet');
    const syncChips=()=>box.querySelectorAll('.chip').forEach(x=>x.classList.toggle('active',+x.dataset.bet===bet));
    cb.addEventListener('input',()=>{
      const v=parseFloat(cb.value);
      if(v>0){ bet=Math.round(v*100)/100; box.querySelectorAll('.chip').forEach(x=>x.classList.remove('active')); }
    });
    document.getElementById('betMinus').onclick=()=>{ bet=Math.max(1,Math.round((bet-5)*100)/100); cb.value=bet; syncChips(); };
    document.getElementById('betPlus').onclick=()=>{ bet=Math.round((bet+5)*100)/100; cb.value=bet; syncChips(); };
    box.querySelectorAll('.chip').forEach(c=>c.addEventListener('click',()=>{ cb.value=c.dataset.bet; }));
  }catch(e){}
})();
</script>
</body>
</html>
