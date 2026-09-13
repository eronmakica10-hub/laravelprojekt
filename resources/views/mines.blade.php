@extends('layouts.casino')
@section('title','Mines')

@section('content')
<style>
  .mtile{aspect-ratio:1/1;border-radius:12px;background:#16283f;border:1px solid rgba(148,163,184,.18);
    font-size:1.55rem;font-weight:900;color:#475569;box-shadow:0 4px 10px -4px #000;transition:background .2s}
  .mtile:not(:disabled):hover{background:#1e3550;transform:translateY(-2px)}
  .mtile.safe{background:#052e22!important;border-color:#10b981!important;box-shadow:0 0 14px #10b98166;color:#fff}
  .mtile.boom{background:#7f1d1d!important;border-color:#ef4444!important;box-shadow:0 0 18px #ef4444aa;color:#fff}
  .mtile.dim{background:#0b1526!important;color:#fff}
  #bets .chip:disabled, #mcounts button:disabled{opacity:.4;cursor:not-allowed}
  @keyframes mshake{0%,100%{transform:translateX(0)}20%{transform:translateX(-9px)}40%{transform:translateX(8px)}60%{transform:translateX(-6px)}80%{transform:translateX(4px)}}
  .shaking{animation:mshake .45s}
  @keyframes mpop{0%{transform:scale(.6)}60%{transform:scale(1.12)}100%{transform:scale(1)}}
  .popping{animation:mpop .3s ease}
</style>
<div class="max-w-xl mx-auto text-center slidein">
<h1 class="text-4xl font-black neon-gold">💣 MINES</h1>
<p class="text-white/60">Hap katrorë të sigurt dhe rrite shumëzuesin — por ruju nga minat!</p>

<div class="glass rounded-3xl mt-6 p-6" id="minesbox">
  <div class="flex justify-center gap-3 flex-wrap text-sm font-black items-center">
    <span class="text-white/60">Mina:</span>
    <div id="mcounts" class="flex gap-2">
      <button data-m="1" class="px-4 py-2 rounded-xl bg-white/10">1</button>
      <button data-m="3" class="px-4 py-2 rounded-xl bg-emerald-600 text-white">3</button>
      <button data-m="5" class="px-4 py-2 rounded-xl bg-white/10">5</button>
      <button data-m="8" class="px-4 py-2 rounded-xl bg-white/10">8</button>
    </div>
  </div>

  <div id="grid" class="grid grid-cols-5 gap-2 mt-4 max-w-sm mx-auto"></div>

  <div class="mt-3 font-black text-lg">x<span id="mmult">1.00</span> • Cashout: <span id="mval" class="text-green-400">0.00€</span></div>
  <div id="mprog" class="text-xs text-white/40 font-bold">💎 0 të hapura</div>
  <div id="mmsg" class="font-bold text-amber-200 h-7">Zgjidh minat dhe starto!</div>

  <div class="flex justify-center gap-2 mt-3 flex-wrap" id="bets">
    <button data-bet="5" class="chip bg-blue-600 text-sm">5</button>
    <button data-bet="10" class="chip bg-green-600 text-sm active">10</button>
    <button data-bet="25" class="chip bg-purple-600 text-sm">25</button>
    <button data-bet="50" class="chip bg-red-600 text-sm">50</button>
  </div>
  <div class="grid grid-cols-2 gap-2 mt-4">
    <button onclick="start()" id="startBtn" class="btn-gold py-4 rounded-2xl text-xl">START 💣</button>
    <button onclick="cashout()" id="outBtn" disabled class="py-4 rounded-2xl text-xl font-black bg-green-600 hover:bg-green-500 disabled:opacity-40">CASHOUT</button>
  </div>
</div>
</div>
@endsection

@section('scripts')
<script>
let bet=10, mines=3, playing=false, curBet=10, revealedCount=0, safeTotal=25;
let AC=null;
function actx(){ try{ AC=AC||new (window.AudioContext||window.webkitAudioContext)(); if(AC.state==='suspended')AC.resume(); }catch(e){} return AC; }
function snd(freq,dur=0.1,type='sine',vol=0.08,when=0){
  try{ const ac=actx(); if(!ac)return;
    const t=ac.currentTime+when,o=ac.createOscillator(),g=ac.createGain();
    o.type=type; o.frequency.value=freq;
    g.setValueAtTime(vol,t); g.exponentialRampToValueAtTime(0.0001,t+dur);
    o.connect(g); g.connect(ac.destination); o.start(t); o.stop(t+dur+0.02);
  }catch(e){}
}
const gemSnd=()=>{ snd(880,0.09); snd(1320,0.12,'sine',0.07,0.07); };
function boomSnd(){
  try{ const ac=actx(); if(!ac)return;
    const len=ac.sampleRate*0.5,buf=ac.createBuffer(1,len,ac.sampleRate),d=buf.getChannelData(0);
    for(let i=0;i<len;i++)d[i]=(Math.random()*2-1)*(1-i/len);
    const s=ac.createBufferSource(); s.buffer=buf;
    const g=ac.createGain(); g.gain.value=0.25;
    s.connect(g); g.connect(ac.destination); s.start();
    snd(120,0.3,'sine',0.15);
  }catch(e){}
}
document.querySelectorAll('#bets .chip').forEach(c=>c.onclick=()=>{document.querySelectorAll('#bets .chip').forEach(x=>x.classList.remove('active'));c.classList.add('active');bet=+c.dataset.bet;});
document.querySelectorAll('#mcounts button').forEach(b=>b.onclick=()=>{
  document.querySelectorAll('#mcounts button').forEach(x=>{x.classList.remove('bg-emerald-600','text-white');x.classList.add('bg-white/10');});
  b.classList.add('bg-emerald-600','text-white');b.classList.remove('bg-white/10');mines=+b.dataset.m;
});
function drawGrid(){
  const g=document.getElementById('grid'); g.innerHTML='';
  for(let i=0;i<25;i++){
    const b=document.createElement('button');
    b.className='mtile';
    b.textContent='?'; b.dataset.i=i;
    b.onclick=()=>reveal(i,b);
    g.appendChild(b);
  }
}
// kthimi 3D i katrorit: mbyllet, ndërrohet përmbajtja, hapet
function flipTile(btn, html, cls){
  return new Promise(res=>{
    btn.style.transition='transform .13s ease-in';
    btn.style.transform='rotateY(90deg)';
    setTimeout(()=>{
      btn.innerHTML=html;
      if(cls)btn.className=cls+' popping';
      btn.style.transition='transform .16s ease-out';
      btn.style.transform='rotateY(0deg)';
      setTimeout(()=>{ btn.classList.remove('popping'); res(); },180);
    },130);
  });
}
function lockGrid(v){ document.querySelectorAll('#grid button').forEach(b=>b.disabled=v); }
function lockConfig(v){
  document.querySelectorAll('#bets .chip').forEach(b=>b.disabled=v);
  document.querySelectorAll('#mcounts button').forEach(b=>b.disabled=v);
}
drawGrid(); lockGrid(true);
async function start(){
  const sb=document.getElementById('startBtn'); sb.disabled=true;
  drawGrid();
  try{
    const j=await api('/api/mines/start',{bet,mines},{settle:'manual'});
    curBet=j.bet; playing=true; lockGrid(false); lockConfig(true);
    revealedCount=0; safeTotal=25-j.mines;
    settle(j.balance,400);
    document.getElementById('mmult').textContent='1.00';
    document.getElementById('mval').textContent='0.00€';
    document.getElementById('mprog').textContent=`💎 0/${safeTotal} të hapura`;
    document.getElementById('mmsg').textContent=`Hap katrorë — ${j.mines} mina të fshehura! 🟢`;
    document.getElementById('outBtn').disabled=true;
    document.getElementById('outBtn').textContent='CASHOUT';
  }catch(e){ toast(e.message,'lose'); }
  sb.disabled=false;
}
async function reveal(i,btn){
  if(!playing||btn.disabled) return;
  btn.disabled=true;
  try{
    const j=await api('/api/mines/reveal',{index:i},{settle:'manual'});
    if(j.hit){
      boomSnd();
      await flipTile(btn,'💥','mtile boom');
      const grid=document.getElementById('grid');
      grid.classList.remove('shaking'); void grid.offsetWidth; grid.classList.add('shaking');
      // zbulo minat një nga një si te Stake
      for(const m of j.mines){
        const c=document.querySelector(`#grid button[data-i="${m}"]`);
        if(c && c!==btn && !c.disabled){ c.disabled=true; await flipTile(c,'💣','mtile boom'); }
        else if(c && c!==btn){ c.textContent='💣'; c.className='mtile boom'; }
        await new Promise(r=>setTimeout(r,90));
      }
      j.revealed.forEach(r=>{ const c=document.querySelector(`#grid button[data-i="${r}"]`); if(c&&c.textContent==='?'){c.textContent='💎';c.className='mtile safe';} });
      playing=false; lockGrid(true); lockConfig(false);
      document.getElementById('mmsg').textContent=j.message;
      settle(j.balance,700); toast(j.message,'lose');
      document.getElementById('outBtn').disabled=true;
    }else{
      gemSnd();
      revealedCount++;
      await flipTile(btn,'💎','mtile safe');
      document.getElementById('mmult').textContent=j.mult.toFixed(2);
      document.getElementById('mval').textContent=j.cashout.toFixed(2)+'€';
      document.getElementById('mprog').textContent=`💎 ${revealedCount}/${safeTotal} të hapura`;
      document.getElementById('outBtn').disabled=false;
      document.getElementById('outBtn').textContent='CASHOUT '+j.cashout.toFixed(2)+'€';
      if(j.safeLeft===0){ cashout(); }
    }
  }catch(e){ toast(e.message,'lose'); btn.disabled=false; }
}
async function cashout(){
  if(!playing) return;
  try{
    const j=await api('/api/mines/cashout',{},{settle:'manual'});
    playing=false; lockGrid(true); lockConfig(false);
    j.mines.forEach(m=>{ const c=document.querySelector(`#grid button[data-i="${m}"]`); if(c&&c.textContent==='?'){c.textContent='💣';c.className='mtile dim';} });
    document.getElementById('mmsg').textContent=j.message;
    confetti(90); flyCoins('minesbox',12);
    setTimeout(()=>settle(j.balance,1200),800);
    toast(j.message,'win');
    document.getElementById('outBtn').disabled=true;
  }catch(e){ toast(e.message,'lose'); }
}
</script>
@endsection
