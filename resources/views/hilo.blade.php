@extends('layouts.casino')
@section('title','Hi-Lo')

@section('content')
<style>
  .hlfelt{background:radial-gradient(ellipse at 50% 25%,#134e3a,#0a3d2c 55%,#04241a 100%);border:10px solid #3d2c17;border-radius:36px;box-shadow:0 30px 80px -20px #000,inset 0 0 80px rgba(0,0,0,.55);position:relative;outline:3px solid #b8922e}
  .hlfelt-arc{position:absolute;left:10%;right:10%;top:30%;height:62%;border:3px solid rgba(255,255,255,.22);border-top:none;border-radius:0 0 200px 200px;pointer-events:none}
  .hlfelt-text{position:absolute;left:0;right:0;top:12%;text-align:center;color:rgba(255,255,255,.3);font-weight:900;letter-spacing:3px;pointer-events:none}
  .shoebox{position:absolute;top:12px;right:16px;text-align:center;color:#fff8;z-index:5}
  .shoebox .deck{font-size:42px;filter:drop-shadow(0 6px 6px rgba(0,0,0,.5))}
  #hlstage{perspective:900px;display:flex;justify-content:center;padding:18px 0 10px;position:relative;z-index:10;min-height:200px}
  #hlcard{transform-style:preserve-3d}
  .hlc{width:118px;height:168px;border-radius:14px;background:linear-gradient(160deg,#ffffff,#dde3ec);color:#111;position:relative;box-shadow:0 14px 30px rgba(0,0,0,.6);flex-shrink:0}
  .hlc.red{color:#c81e1e}
  .hlc .cnr{position:absolute;display:flex;flex-direction:column;align-items:center;line-height:1.05;font-size:17px;font-weight:900}
  .hlc .cnr.tl{top:7px;left:8px}.hlc .cnr.br{bottom:7px;right:8px;transform:rotate(180deg)}
  .hlc .center{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center}
  .hlc .big{font-size:52px;font-weight:900;line-height:1}
  .hlc .bigsuit{font-size:40px;text-shadow:0 2px 0 rgba(0,0,0,.12)}
  .hlc .face{font-size:44px;font-weight:900;border:3px solid currentColor;border-radius:12px;width:64px;height:76px;display:flex;flex-direction:column;align-items:center;justify-content:center}
  .hlc .face small{font-size:22px}
  .hlc.back{background:repeating-linear-gradient(45deg,#7f1d1d 0 9px,#450a0a 9px 18px);border:5px solid #f8fafc;display:flex;align-items:center;justify-content:center;font-size:40px}
  .hlc.glow-win{box-shadow:0 0 0 3px #22c55e,0 0 34px #22c55e}
  .hlc.glow-lose{box-shadow:0 0 0 3px #ef4444,0 0 34px #ef4444}
  .minic{width:44px;height:62px;border-radius:9px;background:linear-gradient(160deg,#fff,#dde3ec);color:#111;display:flex;flex-direction:column;align-items:center;justify-content:center;font-weight:900;box-shadow:0 6px 14px rgba(0,0,0,.5);border:2px solid transparent;flex-shrink:0}
  .minic.red{color:#c81e1e}
  #bets .chip:disabled{opacity:.4;cursor:not-allowed;transform:none}
  @keyframes hlpop{0%{transform:scale(.7)}60%{transform:scale(1.08)}100%{transform:scale(1)}}
  .popping{animation:hlpop .3s ease}
</style>
<div class="max-w-xl mx-auto text-center slidein">
<h1 class="text-4xl font-black neon-gold">🃏 HI-LO</h1>
<p class="text-white/60">Gjej a është letra tjetër më e lartë apo më e ulët — ndërto serinë!</p>

<div class="hlfelt mt-6 px-4 pt-4 pb-6" id="hilobox">
  <div class="hlfelt-arc"></div>
  <div class="hlfelt-text text-sm">★ HI-LO ★<br><span class="text-xs font-bold">BARAZIMI HUMB • CASHOUT KUR TË DUASH</span></div>
  <div class="shoebox"><div class="deck">🂠</div><div class="text-[10px] tracking-widest">SHOE</div></div>

  <div id="hlstage"><div id="hlcard"><div class="hlc back">🃏</div></div></div>

  <div class="relative z-10 font-black text-lg">Seria: <span id="streak" class="text-amber-300">0</span> • x<span id="hmult">1.00</span> • Cashout: <span id="hval" class="text-green-400">0.00€</span></div>
  <div id="ladder" class="relative z-10 flex justify-center gap-1.5 mt-2 flex-wrap text-xs font-black min-h-[1.5rem]"></div>
  <div id="hmsg" class="relative z-10 font-bold text-amber-200 h-7 mt-1">Vendos bastin dhe starto!</div>
  <div id="hhist" class="relative z-10 flex justify-center mt-2 min-h-[2rem]" style="padding-left:0"></div>
</div>

  <div class="grid grid-cols-2 gap-2 mt-4">
    <button onclick="guess('lower')" id="loBtn" disabled class="py-3 rounded-2xl text-lg font-black bg-blue-600 hover:bg-blue-500 disabled:opacity-40 leading-tight">⬇️ MË E ULËT<div id="loOdds" class="text-xs font-bold opacity-80">—</div></button>
    <button onclick="guess('higher')" id="hiBtn" disabled class="py-3 rounded-2xl text-lg font-black bg-red-600 hover:bg-red-500 disabled:opacity-40 leading-tight">MË E LARTË ⬆️<div id="hiOdds" class="text-xs font-bold opacity-80">—</div></button>
  </div>

  <div class="flex justify-center gap-2 mt-4 flex-wrap" id="bets">
    <button data-bet="5" class="chip bg-blue-600 text-sm">5</button>
    <button data-bet="10" class="chip bg-green-600 text-sm active">10</button>
    <button data-bet="25" class="chip bg-purple-600 text-sm">25</button>
    <button data-bet="50" class="chip bg-red-600 text-sm">50</button>
  </div>
  <div class="grid grid-cols-2 gap-2 mt-4">
    <button onclick="start()" id="startBtn" class="btn-gold py-4 rounded-2xl text-xl">START 🃏</button>
    <button onclick="cashout()" id="outBtn" disabled class="py-4 rounded-2xl text-xl font-black bg-green-600 hover:bg-green-500 disabled:opacity-40">CASHOUT</button>
  </div>
</div>
@endsection

@section('scripts')
<script>
let bet=10, playing=false, ladderMults=[];
document.querySelectorAll('#bets .chip').forEach(c=>c.onclick=()=>{document.querySelectorAll('#bets .chip').forEach(x=>x.classList.remove('active'));c.classList.add('active');bet=+c.dataset.bet;});
let AC=null;
function actx(){ try{ AC=AC||new (window.AudioContext||window.webkitAudioContext)(); if(AC.state==='suspended')AC.resume(); }catch(e){} return AC; }
function snd(f,dur=0.08,type='triangle',vol=0.07,slide=null){
  try{ const ac=actx(); if(!ac)return;
    const t=ac.currentTime,o=ac.createOscillator(),g=ac.createGain();
    o.type=type; o.frequency.setValueAtTime(f,t);
    if(slide)o.frequency.exponentialRampToValueAtTime(slide,t+dur);
    g.setValueAtTime(vol,t); g.exponentialRampToValueAtTime(0.0001,t+dur);
    o.connect(g); g.connect(ac.destination); o.start(t); o.stop(t+dur+0.02);
  }catch(e){}
}
const flipSnd=()=>{ snd(700,0.08,'sawtooth',0.035,1400); };
const goodSnd=()=>{ snd(880,0.09,'sine',0.08); setTimeout(()=>snd(1320,0.14,'sine',0.08),90); };
const badSnd=()=>{ snd(220,0.25,'sawtooth',0.09,90); };
const winSnd=()=>{ [523,659,784,1047].forEach((f,i)=>snd(f,0.15,'triangle',0.08)); setTimeout(()=>snd(1047,0.2,'triangle',0.08),450); };
// letra realiste me kënde + qendër
function cardFace(c,glow=''){
  const red=(c.suit==='♥'||c.suit==='♦');
  let center='';
  if(c.label==='A')center=`<div class="bigsuit">${c.suit}</div><div class="big">${c.label}</div>`;
  else if(['J','Q','K'].includes(c.label))center=`<div class="face">${c.label}<small>${c.suit}</small></div>`;
  else center=`<div class="big">${c.label}</div><div class="bigsuit">${c.suit}</div>`;
  return `<div class="hlc ${red?'red':''} ${glow} popping">
    <div class="cnr tl"><b>${c.label}</b><span>${c.suit}</span></div>
    <div class="center">${center}</div>
    <div class="cnr br"><b>${c.label}</b><span>${c.suit}</span></div></div>`;
}
async function flipTo(html){
  const el=document.getElementById('hlcard');
  flipSnd();
  try{
    await el.animate([{transform:'rotateY(0deg)'},{transform:'rotateY(90deg)'}],{duration:150,easing:'ease-in',fill:'forwards'}).finished;
    el.innerHTML=html;
    await el.animate([{transform:'rotateY(90deg)'},{transform:'rotateY(0deg)'}],{duration:200,easing:'ease-out',fill:'forwards'}).finished;
  }catch(e){ el.innerHTML=html; }
  el.style.transform='';
}
function setBtns(v){ document.getElementById('loBtn').disabled=!v; document.getElementById('hiBtn').disabled=!v; }
function lockBets(v){ document.querySelectorAll('#bets .chip').forEach(c=>c.disabled=v); }
// shansi + pagesa si Stake, nga letra aktuale (barazimi humb)
function updateOdds(rank){
  const hiFav=13-rank, loFav=rank-1;
  const hiP=(hiFav/13*100), loP=(loFav/13*100);
  const hiPay=hiFav>0?((13/hiFav)*0.98).toFixed(2):'—';
  const loPay=loFav>0?((13/loFav)*0.98).toFixed(2):'—';
  document.getElementById('hiOdds').textContent=hiFav>0?`${hiP.toFixed(1)}% • x${hiPay}`:'e pamundur';
  document.getElementById('loOdds').textContent=loFav>0?`${loP.toFixed(1)}% • x${loPay}`:'e pamundur';
  document.getElementById('hiBtn').disabled=hiFav<=0||!playing;
  document.getElementById('loBtn').disabled=loFav<=0||!playing;
}
function pushHist(c,good){
  const box=document.getElementById('hhist');
  const red=(c.suit==='♥'||c.suit==='♦');
  const s=document.createElement('div');
  s.className='minic'+(red?' red':'');
  s.style.cssText=`margin-left:${box.children.length?-14:0}px;`+(good===false?'outline:2px solid #ef4444;':good===true?'outline:2px solid #22c55e;':'');
  s.innerHTML=`<div>${c.label}</div><div>${c.suit}</div>`;
  box.appendChild(s);
  while(box.children.length>8)box.firstChild.remove();
}
function pushLadder(mult){
  ladderMults.push(mult);
  const box=document.getElementById('ladder');
  box.innerHTML=ladderMults.slice(-6).map(m=>`<span class="px-2 py-0.5 rounded-full bg-emerald-600/25 border border-emerald-500 text-emerald-300">x${Number(m).toFixed(2)}</span>`).join('');
}
async function start(){
  const sb=document.getElementById('startBtn'); sb.disabled=true;
  try{
    const j=await api('/api/hilo/start',{bet},{settle:'manual'});
    playing=true; lockBets(true);
    ladderMults=[]; document.getElementById('ladder').innerHTML='';
    document.getElementById('hhist').innerHTML='';
    await flipTo(cardFace(j.card));
    pushHist(j.card,null);
    updateOdds(j.card.rank);
    settle(j.balance,400);
    document.getElementById('streak').textContent='0';
    document.getElementById('hmult').textContent='1.00';
    document.getElementById('hval').textContent='0.00€';
    document.getElementById('hmsg').textContent='Lartë apo ulët?🧐';
    document.getElementById('outBtn').disabled=true;
    setBtns(true); updateOdds(j.card.rank);
  }catch(e){ toast(e.message,'lose'); }
  sb.disabled=false;
}
async function guess(dir){
  if(!playing) return;
  setBtns(false);
  try{
    const j=await api('/api/hilo/guess',{dir},{settle:'manual'});
    if(j.correct){
      goodSnd();
      await flipTo(cardFace(j.card,'glow-win'));
      pushHist(j.card,true); pushLadder(j.mult);
      document.getElementById('streak').textContent=j.streak;
      document.getElementById('hmult').textContent=j.mult.toFixed(2);
      document.getElementById('hval').textContent=j.cashout.toFixed(2)+'€';
      document.getElementById('hmsg').textContent=`Saktë! ✅ Seria ${j.streak}`;
      document.getElementById('outBtn').disabled=false;
      document.getElementById('outBtn').textContent='CASHOUT '+j.cashout.toFixed(2)+'€';
      setBtns(true); updateOdds(j.card.rank);
    }else{
      badSnd();
      await flipTo(cardFace(j.card,'glow-lose'));
      pushHist(j.card,false);
      playing=false; lockBets(false);
      document.getElementById('hmsg').textContent=j.message;
      settle(j.balance,700); toast(j.message,'lose');
      document.getElementById('outBtn').disabled=true;
    }
  }catch(e){ toast(e.message,'lose'); if(playing) setBtns(true); }
}
async function cashout(){
  if(!playing) return;
  try{
    const j=await api('/api/hilo/cashout',{},{settle:'manual'});
    playing=false; lockBets(false); setBtns(false);
    document.getElementById('hmsg').textContent=j.message;
    winSnd(); confetti(90); flyCoins('hilobox',12);
    setTimeout(()=>settle(j.balance,1200),800);
    toast(j.message,'win');
    document.getElementById('outBtn').disabled=true;
  }catch(e){ toast(e.message,'lose'); }
}
</script>
@endsection
