@extends('layouts.casino')
@section('title','Slotet')

@section('content')
<style>
  .cabinet{background:linear-gradient(180deg,#2b2118,#14100b 40%,#0a0806);border:3px solid #d4a017;border-radius:28px;box-shadow:0 30px 80px -20px #000, inset 0 0 60px #f59e0b22;position:relative}
  .marquee{background:linear-gradient(180deg,#7c2d12,#451a03);border:2px solid #fbbf24;border-radius:18px;text-shadow:0 2px 0 #000}
  #bulbs{display:flex;justify-content:center;gap:10px;padding:10px 0 2px}
  .bulb{width:14px;height:14px;border-radius:50%;background:#3f3f46;box-shadow:inset 0 -2px 3px #000;animation:chase 1s infinite}
  .bulb:nth-child(3n){animation-delay:.33s}.bulb:nth-child(3n+1){animation-delay:.66s}
  @keyframes chase{0%,100%{background:#3f3f46}50%{background:#fde047;box-shadow:0 0 12px #fde047}}
  .celebrate .bulb{animation-duration:.25s}
  .reel-window{width:104px;height:288px;overflow:hidden;border-radius:14px;background:linear-gradient(180deg,#fff,#e7e5e4 12%,#fafaf9 50%,#e7e5e4 88%,#a8a29e);border:3px solid #78350f;box-shadow:inset 0 14px 22px rgba(0,0,0,.45), inset 0 -14px 22px rgba(0,0,0,.45);position:relative;flex-shrink:0}
  .strip{display:flex;flex-direction:column;will-change:transform}
  .sym{height:96px;display:flex;align-items:center;justify-content:center;font-size:58px;flex-shrink:0;text-shadow:0 3px 0 rgba(0,0,0,.15);transition:opacity .25s}
  .sym.dim{opacity:.28;filter:grayscale(.6)}
  .fast{filter:blur(3px) brightness(1.15)}
  .payline{position:absolute;left:-14px;right:-14px;top:50%;height:0;pointer-events:none;z-index:5}
  .payline::before{content:'';position:absolute;left:0;right:0;top:-2px;height:4px;background:#ef4444;opacity:.45;box-shadow:0 0 8px #ef4444}
  .payline::after{content:'◀ PAYLINE ▶';position:absolute;left:50%;top:8px;transform:translateX(-50%);font-size:10px;font-weight:900;color:#fca5a5;letter-spacing:2px;white-space:nowrap}
  .hit::before{opacity:1;animation:lineflash .4s infinite}
  @keyframes lineflash{50%{opacity:.3}}
  .sym.win{background:radial-gradient(circle,rgba(245,197,24,.45),transparent 70%);box-shadow:inset 0 0 0 3px #f5c518,inset 0 0 26px #f5c51899;border-radius:12px;animation:winflash .5s infinite}
  @keyframes winflash{50%{box-shadow:inset 0 0 0 3px #f5c518,inset 0 0 40px #f5c518cc}}
  .jshake{animation:jshake .5s}
  @keyframes jshake{0%,100%{transform:translateX(0) rotate(0)}20%{transform:translateX(-7px) rotate(-.6deg)}40%{transform:translateX(6px) rotate(.6deg)}60%{transform:translateX(-4px)}80%{transform:translateX(3px)}}
  #lever{width:34px;height:220px;position:relative;cursor:pointer;user-select:none;flex-shrink:0}
  #leverTrack{position:absolute;left:50%;top:0;bottom:0;width:12px;transform:translateX(-50%);background:linear-gradient(90deg,#444,#999,#444);border-radius:6px}
  #leverArm{position:absolute;left:50%;top:6px;width:10px;height:150px;transform:translateX(-50%);background:linear-gradient(90deg,#b45309,#fbbf24,#b45309);border-radius:5px;transition:top .28s ease-in}
  #leverKnob{position:absolute;left:50%;top:-6px;transform:translateX(-50%);width:42px;height:42px;border-radius:50%;background:radial-gradient(circle at 35% 30%,#fca5a5,#dc2626 60%,#7f1d1d);box-shadow:0 6px 12px rgba(0,0,0,.6);transition:top .28s ease-in}
  #lever.pulled #leverArm{top:60px}
  #lever.pulled #leverKnob{top:48px}
  .win-display{background:#000;border:2px solid #fbbf24;border-radius:12px;font-family:monospace;text-shadow:0 0 10px #f59e0b}
  #bets .chip:disabled,#slotThemes button:disabled{opacity:.4;cursor:not-allowed}
  @media (max-width:640px){ .reel-window{width:62px;height:216px} .sym{height:72px;font-size:40px} }
</style>

<div class="max-w-3xl mx-auto text-center slidein">
  <h1 id="slotTitle" class="text-4xl font-black neon-gold">🎰 SLOTET E ARTË</h1>
  <p class="text-white/60 mt-1">Video slot 5x3 me 5 linja — 3+ njësoj nga e majta paguajnë!</p>

  <div class="flex justify-center gap-2 mt-4 text-sm font-black flex-wrap" id="slotThemes">
    <button data-th="gold" class="px-5 py-2 rounded-xl bg-emerald-600 text-white">🥇 E Artë</button>
    <button data-th="fruits" class="px-5 py-2 rounded-xl bg-white/10">🍉 Frutat</button>
    <button data-th="egypt" class="px-5 py-2 rounded-xl bg-white/10">🏺 Egjipti</button>
    <button data-th="deluxe777" class="px-5 py-2 rounded-xl bg-white/10">🎰 Super 777</button>
  </div>

  <div class="cabinet mt-4 px-4 md:px-8 pt-2 pb-6" id="machine">
    <div id="bulbs"></div>
    <div class="marquee mx-auto max-w-md py-2 px-4 mt-1">
      <div class="text-amber-300 font-black tracking-widest text-lg" id="slotMarquee">★ SLOTET E ARTË ★</div>
      <div class="grid grid-cols-2 gap-2 mt-1">
        <div class="win-display text-xl font-black text-amber-300 py-1">KREDI<br><span id="credNow">—</span></div>
        <div class="win-display text-xl font-black text-green-400 py-1" style="text-shadow:0 0 10px #22c55e">FITIMI<br><span id="winNow">0.00€</span></div>
      </div>
    </div>

    <div class="flex items-center justify-center gap-3 md:gap-5 mt-4">
      <div class="relative overflow-x-auto">
        <div class="flex gap-2 md:gap-3 justify-center" id="reelRow"></div>
        <div class="payline" id="payline"></div>
      </div>
      <div id="lever" onclick="spin()" title="Tërhiq levën!">
        <div id="leverTrack"></div>
        <div id="leverArm"></div>
        <div id="leverKnob"></div>
      </div>
    </div>

    <div id="lineInfo" class="h-7 font-black text-lg text-emerald-300 mt-2">5 LINJA AKTIVE</div>
    <div id="msg" class="h-8 font-black text-xl text-amber-300 mt-1">Tërhiq levën ose shtyp SPIN!</div>
    <div id="shist" class="flex justify-center gap-1.5 mt-1 flex-wrap text-sm font-black min-h-[2rem]"></div>

    <div class="mt-2">
      <div class="text-xs text-white/50 mb-2">Zgjidh bastin (5 linja):</div>
      <div class="flex justify-center gap-2 flex-wrap" id="bets">
        <button data-bet="5" class="chip bg-blue-600">5€</button>
        <button data-bet="10" class="chip bg-green-600 active">10€</button>
        <button data-bet="25" class="chip bg-purple-600">25€</button>
        <button data-bet="50" class="chip bg-red-600">50€</button>
        <button data-bet="100" class="chip bg-amber-600">100€</button>
      </div>
    </div>
    <div class="flex justify-center gap-2 mt-5 flex-wrap">
      <button id="spin" onclick="spin()" class="btn-gold px-14 py-4 rounded-full text-2xl">SPIN 🎰</button>
      <button id="turbo" onclick="toggleTurbo()" class="px-6 py-4 rounded-full text-xl font-black bg-white/10 border border-white/20">⚡ TURBO: OFF</button>
    </div>
  </div>

  <div class="glass rounded-2xl mt-4 p-4 text-left text-sm">
    <b class="text-amber-300">📋 Pagesat (3+ nga e majta, për linjë):</b>
    <div class="grid grid-cols-2 gap-1 mt-2 text-white/70" id="paygrid"></div>
    <div class="text-white/40 text-xs mt-2">5 linja: 3 horizontale + V + Λ • 4 njësoj = 5x • 5 njësoj = 25xfish • 2 njësoj = 0.4x</div>
  </div>
</div>

<!-- BIG WIN overlay si Stake -->
<div id="bigwin" class="hidden fixed inset-0 z-[9997] flex items-center justify-center" style="background:rgba(0,0,0,.82)">
  <div class="text-center">
    <div id="bigwinTitle" class="text-6xl md:text-8xl font-black" style="color:#f5c518;text-shadow:0 0 40px #f5c51888">BIG WIN</div>
    <div id="bigwinAmt" class="text-4xl md:text-6xl font-black text-white tabular-nums mt-3">+0.00€</div>
    <div class="text-white/60 font-bold mt-2">Shtyp kudo për të vazhduar</div>
  </div>
</div>
@endsection

@section('scripts')
<script>
let bet=10, spinning=false, theme='gold', turbo=false, cycleIv=null;
const THEMES=@json($themes);
const LINES=[[1,1,1,1,1],[0,0,0,0,0],[2,2,2,2,2],[0,1,2,1,0],[2,1,0,1,2]];
let SYMS=THEMES[theme].symbols, WEIGHTS=THEMES[theme].weights;
const symH=()=>document.querySelector('#reelRow .sym')?.offsetHeight||96;
const DUR_N=[1600,2000,2400,2800,3200], DUR_T=[700,850,1000,1150,1300];
document.querySelectorAll('#bets .chip').forEach(c=>c.onclick=()=>{document.querySelectorAll('#bets .chip').forEach(x=>x.classList.remove('active'));c.classList.add('active');bet=+c.dataset.bet;});
document.querySelectorAll('#slotThemes button').forEach(b=>b.onclick=()=>{ if(spinning)return; setTheme(b.dataset.th); });
function lockSpin(v){
  document.getElementById('spin').disabled=v;
  document.querySelectorAll('#bets .chip').forEach(c=>c.disabled=v);
  document.querySelectorAll('#slotThemes button').forEach(b=>b.disabled=v);
}
function toggleTurbo(){
  turbo=!turbo;
  const b=document.getElementById('turbo');
  b.textContent=turbo?'⚡ TURBO: ON':'⚡ TURBO: OFF';
  b.classList.toggle('bg-emerald-600',turbo); b.classList.toggle('text-white',turbo);
}
function setTheme(t){
  theme=t; SYMS=THEMES[t].symbols; WEIGHTS=THEMES[t].weights;
  document.querySelectorAll('#slotThemes button').forEach(x=>{x.classList.remove('bg-emerald-600','text-white');x.classList.add('bg-white/10');});
  const b=document.querySelector(`#slotThemes button[data-th="${t}"]`);
  if(b){b.classList.add('bg-emerald-600','text-white');b.classList.remove('bg-white/10');}
  document.getElementById('slotTitle').textContent='🎰 '+THEMES[t].name.toUpperCase();
  document.getElementById('slotMarquee').textContent='★ '+THEMES[t].name.toUpperCase()+' ★';
  stopCycle();
  document.getElementById('lineInfo').textContent='5 LINJA AKTIVE';
  renderPay(); fillInitial();
}
function renderPay(){
  document.getElementById('paygrid').innerHTML=Object.entries(THEMES[theme].pay)
    .map(([s,m])=>`<div>${s}${s}${s} → <b class="text-amber-300">x${m}</b></div>`).join('');
}
renderPay();
// llambat ndjekëse
(function(){const b=document.getElementById('bulbs');for(let i=0;i<16;i++){const s=document.createElement('div');s.className='bulb';b.appendChild(s);}})();
// zëri: klik + kërcitje + fanfarë
let AC=null;
function tone(f,dur=0.05,type='square',vol=0.05,when=0){
  try{
    AC=AC||new (window.AudioContext||window.webkitAudioContext)();
    if(AC.state==='suspended')AC.resume();
    const t=AC.currentTime+when, o=AC.createOscillator(), g=AC.createGain();
    o.type=type;o.frequency.value=f;
    g.setValueAtTime(vol,t);g.exponentialRampToValueAtTime(0.0001,t+dur);
    o.connect(g);g.connect(AC.destination);o.start(t);o.stop(t+dur+0.02);
  }catch(e){}
}
const tick=()=>tone(1600+Math.random()*500,0.03,'square',0.035);
const clunk=()=>{tone(180,0.12,'triangle',0.12);tone(90,0.15,'sine',0.1,0.02);};
function fanfare(){[523,659,784,1047,784,1047].forEach((f,i)=>tone(f,0.16,'triangle',0.09,i*0.11));}
// tërheqje me peshë si serveri (për mbushjen vizuale)
function wdraw(){
  const tot=WEIGHTS.reduce((a,b)=>a+b,0);
  let r=Math.floor(Math.random()*tot)+1, acc=0;
  for(let i=0;i<SYMS.length;i++){ acc+=WEIGHTS[i]; if(r<=acc)return SYMS[i]; }
  return SYMS[0];
}
function buildStrips(){
  const row=document.getElementById('reelRow');
  row.innerHTML='';
  for(let i=0;i<5;i++){
    const w=document.createElement('div'); w.className='reel-window';
    const st=document.createElement('div'); st.className='strip'; st.id='strip'+i;
    w.appendChild(st); row.appendChild(w);
  }
}
function fillInitial(){
  buildStrips();
  for(let i=0;i<5;i++){
    const st=document.getElementById('strip'+i);
    st.style.transition='none';st.style.transform='translateY(0)';
    st.innerHTML=[0,1,2].map(()=>`<div class="sym">${wdraw()}</div>`).join('');
  }
}
fillInitial();
document.getElementById('credNow').textContent=currentDisplayed().toFixed(2)+'€';

async function spin(){
  if(spinning) return;
  const lever=document.getElementById('lever');
  lever.classList.add('pulled'); tone(300,0.2,'sawtooth',0.05);
  setTimeout(()=>lever.classList.remove('pulled'),550);
  lockSpin(true); spinning=true;
  stopCycle();
  document.getElementById('payline').classList.remove('hit');
  document.getElementById('machine').classList.remove('celebrate','win-pulse');
  document.getElementById('msg').textContent='Po rrotullohet... 🎰';
  document.getElementById('winNow').textContent='0.00€';
  document.getElementById('lineInfo').textContent='FAT I MBARË... 🍀';

  let j;
  try{ j=await api('/api/slots/spin',{bet,theme},{settle:'manual'}); }
  catch(e){ toast(e.message,'lose'); lockSpin(false); spinning=false; return; }

  const DUR=turbo?DUR_T:DUR_N;
  let stopped=0;
  const tickIv=setInterval(tick,turbo?60:95);
  for(let i=0;i<5;i++){
    const st=document.getElementById('strip'+i);
    const cur=[...st.querySelectorAll('.sym')].map(d=>d.textContent);
    while(cur.length<3)cur.push(wdraw());
    const extra=12+i*6;
    const seq=[...cur];
    for(let k=0;k<extra;k++)seq.push(wdraw());
    seq.push(wdraw(), j.grid[i][0], j.grid[i][1], j.grid[i][2], wdraw());
    st.style.transition='none';st.style.transform='translateY(0)';
    st.innerHTML=seq.map(s=>`<div class="sym">${s}</div>`).join('');
    st.parentElement.classList.add('fast');
    void st.offsetHeight;
    st.style.transition=`transform ${DUR[i]}ms cubic-bezier(.12,.6,.08,1)`;
    st.style.transform=`translateY(${-(seq.length-3)*symH()}px)`;
    setTimeout(()=>{
      st.parentElement.classList.remove('fast');
      clunk();
      if(++stopped===5){ clearInterval(tickIv); finish(j); }
    }, DUR[i]+60);
  }
}
function cellOf(col,row){
  const st=document.getElementById('strip'+col);
  if(!st)return null;
  const syms=st.querySelectorAll('.sym');
  return syms[syms.length-3+row]||null;
}
function clearMarks(){
  document.querySelectorAll('#reelRow .sym').forEach(s=>s.classList.remove('win','dim'));
}
function showLine(lw){
  clearMarks();
  if(!lw)return;
  const rows=LINES[lw.line];
  document.querySelectorAll('#reelRow .sym').forEach(s=>s.classList.add('dim'));
  for(let c=0;c<lw.count;c++){
    const cell=cellOf(c,rows[c]);
    if(cell){ cell.classList.remove('dim'); cell.classList.add('win'); }
  }
  document.getElementById('lineInfo').textContent=`LINJA ${lw.line+1} • ${lw.count}x ${lw.sym} • +${lw.amount.toFixed(2)}€`;
}
function stopCycle(){ if(cycleIv){clearInterval(cycleIv);cycleIv=null;} }
function startCycle(lineWins){
  stopCycle();
  if(!lineWins.length)return;
  let k=0;
  showLine(lineWins[0]);
  if(lineWins.length===1)return;
  cycleIv=setInterval(()=>{
    k=(k+1)%lineWins.length;
    showLine(lineWins[k]);
    tone(900+k*120,0.06,'sine',0.05);
  },950);
}
function countUp(el,to,prefix=''){
  const from=parseFloat((el.textContent||'').replace(/[^0-9.\-]/g,''))||0;
  const t0=performance.now();
  (function f(t){
    const p=Math.min(1,(t-t0)/900), e=1-Math.pow(1-p,3);
    el.textContent=prefix+(from+(to-from)*e).toFixed(2)+'€';
    if(p<1)requestAnimationFrame(f);
  })(t0);
}
function finish(j){
  spinning=false;lockSpin(false);
  document.getElementById('msg').textContent=j.message;
  countUp(document.getElementById('credNow'),Number(j.balance));
  countUp(document.getElementById('winNow'),Number(j.win),'+');
  pushSlotHist(j);
  if(j.lineWins.length){
    document.getElementById('payline').classList.add('hit');
    startCycle(j.lineWins);
  }else{
    document.getElementById('lineInfo').textContent='ASNJË LINJË — PROVO PËRSËRI';
  }
  if(j.isWin){
    document.getElementById('machine').classList.add('celebrate','win-pulse');
    setTimeout(()=>document.getElementById('machine').classList.remove('win-pulse'),2500);
    fanfare();
    flyCoins('machine',14);
    if(j.isJackpot){
      const mc=document.getElementById('machine');
      mc.classList.remove('jshake'); void mc.offsetWidth; mc.classList.add('jshake');
      setTimeout(()=>mc.classList.remove('jshake'),600);
      showBigWin(j.profit);
    }
    setTimeout(()=>settle(j.balance,1300),j.isJackpot||j.profit>=bet*5?2600:950);
    if(j.isJackpot){confetti(150);toast('🎉 JACKPOT neto +'+j.profit+'€!','win');}
    else{confetti(40);toast('Fituat neto +'+j.profit+'€!','win');}
  } else {
    setTimeout(()=>settle(j.balance,700),200);
    toast(j.message,'lose');
  }
}
function pushSlotHist(j){
  const box=document.getElementById('shist');
  const mid=j.grid.map(c=>c[1]).join('');
  const s=document.createElement('span');
  s.className='px-2 py-1 rounded-xl border text-base '+(j.isWin?'bg-green-600/25 border-green-500':'bg-white/5 border-white/10 opacity-70');
  s.textContent=`${mid}${j.isWin?' +'+j.profit+'€':''}`;
  box.prepend(s);
  while(box.children.length>6)box.lastChild.remove();
}
function showBigWin(profit){
  const ov=document.getElementById('bigwin');
  document.getElementById('bigwinTitle').textContent=profit>=bet*10?'MEGA WIN':'BIG WIN';
  ov.classList.remove('hidden');
  confetti(180);
  const el=document.getElementById('bigwinAmt');
  const t0=performance.now(),dur=1800;
  (function cnt(t){
    const p=Math.min(1,(t-t0)/dur),e=1-Math.pow(1-p,3);
    el.textContent='+'+(profit*e).toFixed(2)+'€';
    if(p<1)requestAnimationFrame(cnt);
  })(t0);
  const hide=()=>{ ov.classList.add('hidden'); ov.removeEventListener('click',hide); };
  setTimeout(()=>ov.addEventListener('click',hide),400);
  setTimeout(hide,3200);
}
</script>
@endsection
