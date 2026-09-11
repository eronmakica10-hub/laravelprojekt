<!DOCTYPE html>
<html lang="sq">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Kazino Online') | Golden Eagle Casino</title>
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800;900&display=swap" rel="stylesheet">
<style>
  *{font-family:'Outfit',sans-serif}
  body{background:#07070f;color:#fff;overflow-x:hidden}
  .bg-animated{background: radial-gradient(1000px 500px at 10% 10%, #7c3aed33, transparent), radial-gradient(800px 400px at 90% 20%, #f59e0b33, transparent), radial-gradient(900px 600px at 50% 100%, #ec489933, transparent), #07070f;}
  .coins{position:fixed;inset:0;pointer-events:none;overflow:hidden;z-index:0}
  .coin{position:absolute;top:-40px;animation:fall linear infinite;opacity:.7;font-size:22px;filter:drop-shadow(0 0 8px gold)}
  @keyframes fall{to{transform:translateY(110vh) rotate(720deg)}}
  .neon-gold{background:linear-gradient(135deg,#fde68a,#f59e0b,#b45309,#fde68a);background-size:200% auto;-webkit-background-clip:text;background-clip:text;color:transparent;animation:shine 3s linear infinite}
  @keyframes shine{to{background-position:200% center}}
  .card-glow{transition:.3s}
  .card-glow:hover{transform:translateY(-8px) scale(1.02);box-shadow:0 20px 60px -10px #f59e0b66,0 0 0 1px #f59e0b}
  .btn-gold{background:linear-gradient(135deg,#fbbf24,#f59e0b,#d97706);color:#1a1000;font-weight:800;transition:.25s;box-shadow:0 8px 25px -5px #f59e0baa}
  .btn-gold:hover{transform:translateY(-2px) scale(1.03);box-shadow:0 15px 40px -5px #f59e0bcc;filter:brightness(1.1)}
  .btn-gold:active{transform:scale(.97)}
  .btn-gold:disabled{opacity:.5;transform:none;cursor:not-allowed}
  .glass{background:#ffffff0d;backdrop-filter:blur(16px);border:1px solid #ffffff1a}
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
  .chip.active{outline:3px solid #fbbf24;transform:scale(1.15);box-shadow:0 0 20px #fbbf24}
  .odds-btn{transition:.2s}
  .odds-btn.selected{background:#f59e0b!important;color:#000!important;font-weight:800;transform:scale(1.05)}
  @keyframes floaty{0%,100%{transform:translateY(0)}50%{transform:translateY(-12px)}}
  .floaty{animation:floaty 3s ease-in-out infinite}
  @keyframes marquee{from{transform:translateX(0)}to{transform:translateX(-50%)}}
  .marquee-track{animation:marquee 20s linear infinite}
  .confetti{position:fixed;top:-20px;z-index:9999;pointer-events:none;animation:confall linear forwards}
  @keyframes confall{to{transform:translateY(110vh) rotate(720deg)}}
  ::-webkit-scrollbar{width:8px}::-webkit-scrollbar-track{background:#111}::-webkit-scrollbar-thumb{background:#f59e0b;border-radius:4px}
</style>
@yield('head')
</head>
<body class="bg-animated min-h-screen relative">
<div class="coins" id="coins"></div>

<!-- NAVBAR -->
<nav class="sticky top-0 z-50 glass border-b border-amber-500/20">
  <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-2 flex-wrap">
    <a href="/" class="flex items-center gap-2">
      <span class="text-3xl floaty">🦅</span>
      <div>
        <div class="font-black text-xl leading-none neon-gold">GOLDEN EAGLE</div>
        <div class="text-[11px] tracking-[.3em] text-amber-200/70 font-semibold">KAZINO • BASTORE</div>
      </div>
    </a>
    <div class="hidden md:flex items-center gap-1 text-sm font-semibold">
      <a href="/" class="px-4 py-2 rounded-full hover:bg-white/10 {{ request()->is('/')?'bg-amber-500 text-black':'' }}">🏠 Ballina</a>
      <a href="/slots" class="px-4 py-2 rounded-full hover:bg-white/10 {{ request()->is('slots')?'bg-amber-500 text-black':'' }}">🎰 Slotet</a>
      <a href="/roulette" class="px-4 py-2 rounded-full hover:bg-white/10 {{ request()->is('roulette')?'bg-amber-500 text-black':'' }}">🎡 Ruleta</a>
      <a href="/blackjack" class="px-4 py-2 rounded-full hover:bg-white/10 {{ request()->is('blackjack')?'bg-amber-500 text-black':'' }}">🃏 Blackjack</a>
      <a href="/dice" class="px-4 py-2 rounded-full hover:bg-white/10 {{ request()->is('dice')?'bg-amber-500 text-black':'' }}">🎲 Zari</a>
      <a href="/sports" class="px-4 py-2 rounded-full hover:bg-white/10 {{ request()->is('sports')?'bg-amber-500 text-black':'' }}">⚽ Bastet</a>
    </div>
    <div class="flex items-center gap-2">
      <div class="glass rounded-full px-4 py-2 flex items-center gap-2">
        <span>💰</span><span id="balance" class="font-black text-amber-300">{{ number_format(session('balance',0),2) }}€</span>
      </div>
      <button onclick="openWallet()" class="text-xs btn-gold px-4 py-2 rounded-full">💳 Portofoli</button>
      @if(session('user_id'))
        <span class="hidden sm:inline text-sm font-bold text-amber-200">👋 {{ session('user_name') }}</span>
        <form method="POST" action="/logout" class="inline">@csrf<button class="text-xs bg-red-500/80 hover:bg-red-500 px-3 py-2 rounded-full font-bold">Dil ↪</button></form>
      @else
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
    <a href="/sports" class="px-3 py-1.5 rounded-full bg-white/10 whitespace-nowrap">⚽ Bastet</a>
  </div>
</nav>

<main class="relative z-10 max-w-7xl mx-auto px-4 py-6">
  @if(session('success'))
    <div class="mb-4 bg-green-500/20 border border-green-500 rounded-2xl px-5 py-3 font-bold text-green-200 slidein">🎉 {{ session('success') }}</div>
  @endif
  @yield('content')
</main>

<footer class="relative z-10 text-center text-white/40 text-xs py-8">
  🦅 Golden Eagle Casino • 18+ Luaj me përgjegjësi • Demo pa para reale • Laravel {{ app()->version() }}
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
  d.className='fixed bottom-6 left-1/2 -translate-x-1/2 z-[9999] px-6 py-3 rounded-2xl font-bold shadow-2xl slidein '+(type==='win'?'bg-green-500 text-white win-pulse':type==='lose'?'bg-red-500 text-white':'bg-amber-500 text-black');
  d.textContent=msg; document.body.appendChild(d); setTimeout(()=>d.remove(),2600);
}
function confetti(n=80){
  const colors=['#f59e0b','#ef4444','#22c55e','#3b82f6','#eab308','#ec4899'];
  for(let i=0;i<n;i++){const c=document.createElement('div');c.className='confetti';c.style.left=Math.random()*100+'vw';c.style.background=colors[i%colors.length];c.style.width=(6+Math.random()*8)+'px';c.style.height=(8+Math.random()*10)+'px';c.style.animationDuration=(2+Math.random()*2)+'s';document.body.appendChild(c);setTimeout(()=>c.remove(),4000);}
}
// falling coins background
(function(){const box=document.getElementById('coins');const em=['🪙','✨','💎','🍀'];for(let i=0;i<18;i++){const s=document.createElement('span');s.className='coin';s.textContent=em[i%em.length];s.style.left=Math.random()*100+'vw';s.style.animationDuration=(6+Math.random()*10)+'s';s.style.animationDelay=(Math.random()*10)+'s';s.style.fontSize=(12+Math.random()*20)+'px';box.appendChild(s);}})();
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
      <div class="rounded-2xl p-4 text-black font-bold" style="background:linear-gradient(135deg,#f59e0b,#b45309 60%,#451a03);color:#fff">
        <div class="flex justify-between text-xs opacity-80"><span>GOLDEN EAGLE CARD</span><span>💳 VISA</span></div>
        <div id="cardPrev" class="text-xl tracking-widest mt-3 font-mono">•••• •••• •••• ••••</div>
        <div class="flex justify-between text-xs mt-2"><span id="cardName">EMRI JUAJ</span><span id="cardExp">MM/YY</span></div>
      </div>
      <div class="flex gap-2 mt-3 flex-wrap">
        <button onclick="wamount(20)" class="wamt flex-1 bg-white/10 rounded-xl py-2 font-black hover:bg-amber-500 hover:text-black">20€</button>
        <button onclick="wamount(50)" class="wamt flex-1 bg-white/10 rounded-xl py-2 font-black hover:bg-amber-500 hover:text-black">50€</button>
        <button onclick="wamount(100)" class="wamt flex-1 bg-white/10 rounded-xl py-2 font-black hover:bg-amber-500 hover:text-black">100€</button>
        <button onclick="wamount(500)" class="wamt flex-1 bg-white/10 rounded-xl py-2 font-black hover:bg-amber-500 hover:text-black">500€</button>
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
@yield('scripts')
</body>
</html>
