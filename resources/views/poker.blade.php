@extends('layouts.casino')
@section('title','Poker')

@section('content')
<style>
  #ptable{position:relative;height:760px;background:radial-gradient(ellipse at 50% 42%,#12934e,#0a6b38 55%,#043d22 100%);border:16px solid #3d2c17;border-radius:50%/42%;box-shadow:0 30px 80px -20px #000,inset 0 0 80px rgba(0,0,0,.5);outline:4px solid #b8922e;overflow:hidden}
  .pseat{position:absolute;transform:translate(-50%,-50%);width:232px;text-align:center;z-index:5;transition:all .3s}
  .pseat.bot{width:150px}
  .pseat.bot .pav{width:40px;height:40px;font-size:20px;border-width:2px}
  .pseat.bot .nm{font-size:12px}
  .pseat.bot .st{font-size:12px}
  .pseat.bot .pcard{width:34px;height:46px;font-size:13px;border-radius:6px}
  .pseat.bot .lact{font-size:11px;padding:2px 10px;bottom:-21px}
  .pseat .nm{font-size:16px}
  .pseat .st{font-size:15px}
  .pseat .cards{gap:6px;margin-top:7px;min-height:56px}
  .pseat .box{background:rgba(5,10,20,.85);border:2px solid rgba(255,255,255,.18);border-radius:14px;padding:5px 6px}
  .pseat.actor .box{border-color:#22c55e;box-shadow:0 0 18px #22c55e}
  .pseat.folded{opacity:.45;filter:grayscale(.8)}
  .pseat.winner .box{border-color:#f5c518;box-shadow:0 0 22px #f5c518}
  .pseat .nm{font-weight:900;font-size:12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .pseat .st{font-size:11px;color:#4ade80;font-weight:800}
  .pseat .cards{display:flex;justify-content:center;gap:3px;margin-top:3px;min-height:44px}
  .pbet{position:absolute;transform:translate(-50%,-50%);background:#0b1526;border:1px solid #f5c518;color:#f5c518;font-weight:900;font-size:11px;border-radius:999px;padding:2px 9px;z-index:6;white-space:nowrap}
  .pcard{width:50px;height:68px;border-radius:9px;background:linear-gradient(160deg,#fff,#dde3ec);color:#111;font-weight:900;font-size:19px;display:flex;flex-direction:column;align-items:center;justify-content:center;line-height:1.05;box-shadow:0 4px 10px rgba(0,0,0,.5)}
  .pcard.red{color:#c81e1e}
  .pcard.back{background:repeating-linear-gradient(45deg,#1e3a8a 0 6px,#172554 6px 12px);border:2px solid #f8fafc;color:transparent}
  .pcard.big{width:72px;height:98px;font-size:26px}
  .dbtn{position:absolute;width:30px;height:30px;border-radius:50%;background:#f5c518;color:#000;font-weight:900;font-size:15px;display:flex;align-items:center;justify-content:center;box-shadow:0 0 14px #f5c518;z-index:7}
  #potbox{position:absolute;left:50%;top:34%;transform:translate(-50%,-50%);text-align:center;z-index:4}
  #commbox{position:absolute;left:50%;top:55%;transform:translate(-50%,-50%);display:flex;gap:6px;z-index:4}
  .turnbar{height:4px;background:#22c55e;border-radius:2px;margin-top:3px;animation:turnpulse 1s infinite}
  @keyframes turnpulse{50%{opacity:.4}}
  @keyframes dealin{from{opacity:0;transform:translateY(-18px) scale(.7)}to{opacity:1;transform:none}}
  .dealin{animation:dealin .35s ease both}
  #winbanner{position:absolute;left:50%;top:16%;transform:translateX(-50%);z-index:20;pointer-events:none}
</style>
<div class="mx-auto slidein" style="max-width:1400px">
<style>
  /* stil profesional PokerStars */
  #ptable{background:radial-gradient(ellipse at 50% 40%,#15803d 0%,#0b6b39 45%,#063d23 75%,#032a18 100%)}
  #ptable::before{content:'';position:absolute;inset:14px;border-radius:50%/44%;border:2px solid rgba(245,197,24,.28);pointer-events:none;z-index:3}
  #ptable::after{content:'';position:absolute;inset:0;border-radius:50%/42%;box-shadow:inset 0 0 90px rgba(0,0,0,.65);pointer-events:none;z-index:3}
  #feltmark{position:absolute;left:50%;top:72%;transform:translate(-50%,-50%);z-index:1;font-weight:900;letter-spacing:3px;color:rgba(255,255,255,.13);font-size:15px;white-space:nowrap;pointer-events:none}
  .pseat{width:150px}
  .pseat .box{display:flex;align-items:center;gap:9px;text-align:left;padding:9px 12px}
  .pav{width:60px;height:60px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:30px;font-weight:900;background:linear-gradient(160deg,#334155,#0f172a);border:3px solid rgba(255,255,255,.25);color:#fff}
  .pseat.actor .pav{border-color:#22c55e;box-shadow:0 0 12px #22c55e}
  .pinf{min-width:0;flex:1}
  .pseat .cards{margin-top:5px}
  .lact{position:absolute;left:50%;transform:translateX(-50%);bottom:-24px;background:#0b1526;border:2px solid #f5c518;color:#f5c518;font-weight:900;font-size:15px;border-radius:999px;padding:4px 15px;white-space:nowrap;z-index:6}
  .allinb{position:absolute;top:-13px;right:2px;background:#dc2626;color:#fff;font-weight:900;font-size:12px;border-radius:999px;padding:3px 11px;z-index:6;box-shadow:0 0 12px #dc2626;animation:turnpulse 1s infinite}
  .turnbar{height:7px;background:rgba(255,255,255,.15);border-radius:4px;margin-top:5px;overflow:hidden}
  .turnbar>div{height:100%;background:linear-gradient(90deg,#22c55e,#4ade80);border-radius:3px;transition:width 1s linear}
  .pbet{background:linear-gradient(180deg,#132238,#0b1526);box-shadow:0 4px 10px rgba(0,0,0,.6)}
  .pbet .stk{font-size:13px;letter-spacing:-2px;margin-right:2px}
  .sdglow{box-shadow:0 0 0 3px #f5c518,0 0 26px #f5c518!important}
  @keyframes streetpop{0%{transform:scale(.6);opacity:0}60%{transform:scale(1.12)}100%{transform:scale(1);opacity:1}}
  .streetpop{animation:streetpop .45s ease both}
  @keyframes potpulse{0%{transform:scale(1)}40%{transform:scale(1.25);color:#f5c518}100%{transform:scale(1)}}
  .potpulse{animation:potpulse .5s ease}
  #fx{position:absolute;inset:0;z-index:30;pointer-events:none;overflow:hidden}
  .actbubble{position:absolute;transform:translate(-50%,-100%);font-weight:900;font-size:24px;padding:12px 26px;border-radius:999px;white-space:nowrap;box-shadow:0 8px 26px rgba(0,0,0,.7);animation:bubblepop 3s ease forwards;border-width:3px;z-index:31}
  @keyframes bubblepop{0%{transform:translate(-50%,-30%) scale(.4);opacity:0}10%{transform:translate(-50%,-105%) scale(1.15);opacity:1}20%{transform:translate(-50%,-105%) scale(1)}85%{opacity:1;transform:translate(-50%,-105%) scale(1)}100%{transform:translate(-50%,-150%) scale(.9);opacity:0}}
  .actbubble.bdown{transform:translate(-50%,0);animation-name:bubblepopD}
  @keyframes bubblepopD{0%{transform:translate(-50%,30%) scale(.4);opacity:0}10%{transform:translate(-50%,5%) scale(1.15);opacity:1}20%{transform:translate(-50%,5%) scale(1)}85%{opacity:1;transform:translate(-50%,5%) scale(1)}100%{transform:translate(-50%,50%) scale(.9);opacity:0}}
  @keyframes bubblepop{0%{transform:translate(-50%,-30%) scale(.4);opacity:0}10%{transform:translate(-50%,-105%) scale(1.15);opacity:1}20%{transform:translate(-50%,-105%) scale(1)}85%{opacity:1;transform:translate(-50%,-105%) scale(1)}100%{transform:translate(-50%,-150%) scale(.9);opacity:0}}
  /* RESPONSIVE — telefona */
  @media (max-width:768px){
    #ptable{height:560px;border-width:10px}
    .pseat{width:118px}
    .pseat.bot{width:104px}
    .pav{width:40px!important;height:40px!important;font-size:20px!important}
    .pseat .nm{font-size:10px!important}
    .pseat .st{font-size:10px!important}
    .pcard{width:36px;height:50px;font-size:14px;border-radius:7px}
    .pcard.big{width:48px;height:66px;font-size:18px}
    .pbet{font-size:10px;padding:1px 7px}
    .lact{font-size:11px!important;padding:2px 10px!important;bottom:-20px!important}
    #potAmt{font-size:1.4rem}
    .actbubble{font-size:16px;padding:8px 16px}
    .dbtn{width:24px;height:24px;font-size:12px}
    #commentTxt{font-size:15px!important}
    h1.text-4xl{font-size:1.6rem}
  }
  @media (max-width:420px){
    #ptable{height:500px}
    .pseat{width:104px}
    .pcard{width:32px;height:44px;font-size:12px}
    .pcard.big{width:42px;height:58px;font-size:15px}
    #commbox{gap:4px}
  }
</style>
<h1 class="text-4xl font-black neon-gold text-center">🃏 POKER TEXAS HOLD'EM</h1>
<p class="text-white/60 text-center">Me bota offline • Ose online me shokë me kod dhome!</p>

<div class="flex justify-center gap-2 mt-4 text-sm font-black" id="modetabs">
  <button data-m="off" onclick="setMode('off')" class="px-6 py-2.5 rounded-xl bg-emerald-600 text-white">🤖 Me Bota</button>
  <button data-m="on" onclick="setMode('on')" class="px-6 py-2.5 rounded-xl bg-white/10">🌐 Online me shokë</button>
</div>

<!-- OFFLINE SETUP -->
<div id="offSetup" class="glass rounded-3xl mt-4 p-5 max-w-xl mx-auto text-center">
  <div class="grid grid-cols-2 gap-3 text-left">
    <div><label class="text-sm text-white/60 font-bold">Buy-in:</label>
      <select id="offBuyin" class="w-full mt-1 bg-black/50 border border-white/20 rounded-xl px-3 py-2.5 font-black">
        <option value="100" selected>100€ (blinds 1/2)</option>
        <option value="500">500€ (blinds 5/10)</option>
        <option value="1000">1000€ (blinds 10/20)</option>
      </select></div>
    <div><label class="text-sm text-white/60 font-bold">Bota:</label>
      <select id="offBots" class="w-full mt-1 bg-black/50 border border-white/20 rounded-xl px-3 py-2.5 font-black">
        <option value="2">2 bota</option><option value="3">3 bota</option>
        <option value="4" selected>4 bota</option><option value="5">5 bota</option>
      </select></div>
  </div>
  <button onclick="offSit()" class="btn-gold w-full mt-4 py-3 rounded-2xl text-lg">ULU NË TAVOLINË 🃏</button>
</div>

<!-- ONLINE LOBBY -->
<div id="onSetup" class="hidden glass rounded-3xl mt-4 p-5 max-w-xl mx-auto text-center">
  <label class="text-sm text-white/60 font-bold">Emri yt:</label>
  <input id="onName" maxlength="16" value="{{ session('user_name','') }}" placeholder="Emri" class="w-full mt-1 bg-black/50 border border-white/20 rounded-xl px-4 py-2.5 font-black text-center">
  <label class="text-sm text-white/60 font-bold mt-3 block">Buy-in:</label>
  <select id="onBuyin" class="w-full mt-1 bg-black/50 border border-white/20 rounded-xl px-3 py-2.5 font-black">
    <option value="100" selected>100€ (bonus falas mjafton!)</option><option value="500">500€</option><option value="1000">1000€</option>
  </select>
  <label class="text-sm text-white/60 font-bold mt-3 block">Sa lojtarë max në dhomë?</label>
  <select id="onMax" class="w-full mt-1 bg-black/50 border border-white/20 rounded-xl px-3 py-2.5 font-black">
    <option value="2">2 lojtarë (heads-up)</option>
    <option value="3">3 lojtarë</option>
    <option value="4">4 lojtarë</option>
    <option value="5">5 lojtarë</option>
    <option value="6" selected>6 lojtarë (max)</option>
  </select>
  <label class="text-sm text-white/60 font-bold mt-3 block">Bota në dhomë (mbushin vendet)?</label>
  <select id="onBots" class="w-full mt-1 bg-black/50 border border-white/20 rounded-xl px-3 py-2.5 font-black">
    <option value="0">0 bota (vetëm shokë)</option>
    <option value="1">1 bot</option>
    <option value="2" selected>2 bota</option>
    <option value="3">3 bota</option>
    <option value="4">4 bota</option>
    <option value="5">5 bota</option>
  </select>
  <div class="grid grid-cols-2 gap-2 mt-4">
    <button onclick="roomCreate()" class="btn-gold py-3 rounded-2xl">KRIJO DHOMË 🏠</button>
    <div class="flex gap-2">
      <input id="joinCode" maxlength="4" placeholder="KODI" class="w-full bg-black/50 border border-white/20 rounded-2xl px-3 py-3 font-black text-center uppercase tracking-widest">
      <button onclick="roomJoin()" class="px-5 py-3 rounded-2xl bg-blue-600 font-black hover:bg-blue-500 whitespace-nowrap">HYR ➡️</button>
    </div>
  </div>
  <div id="roomBox" class="hidden mt-4 bg-black/40 rounded-2xl p-4 border border-emerald-500/30">
    <div class="text-xs text-white/50 font-bold">KODI I DHOMËS (jepi shokëve):</div>
    <div class="flex items-center justify-center gap-2 mt-1">
      <span id="roomCode" class="text-4xl font-black tracking-[.3em] text-emerald-300">----</span>
      <button onclick="copyCode()" class="bg-white/10 hover:bg-white/20 px-3 py-2 rounded-xl font-black text-sm">📋</button>
    </div>
    <div id="roomPlayers" class="mt-2 space-y-1 text-sm"></div>
    <div class="flex gap-2 mt-3">
      <button onclick="roomAddBot()" id="addBotBtn" class="flex-1 py-2.5 rounded-xl bg-white/10 font-bold hover:bg-white/20">🤖 Shto bot</button>
      <button onclick="roomStart()" id="startBtn2" class="flex-1 py-2.5 rounded-xl btn-gold">FILLO ▶️</button>
    </div>
    <button onclick="roomLeave()" class="w-full mt-2 py-2 rounded-xl bg-red-600/70 font-bold hover:bg-red-600">Dil nga dhoma ↪</button>
  </div>
</div>

<!-- TAVOLINA -->
<div id="tableWrap" class="hidden mt-4">
  <div id="commentBar" class="glass rounded-2xl px-5 py-3 mb-2 flex items-center gap-3" style="border-left:5px solid #f5c518">
    <span class="text-2xl">🎙️</span>
    <span id="commentTxt" class="font-black text-xl text-white">Mirësevjen në tavolinë! 🍀</span>
  </div>
  <div id="roomBar" class="hidden glass rounded-2xl px-5 py-2.5 mb-2 flex items-center justify-center gap-3 border-emerald-500/40" style="border-width:2px">
    <span class="text-xs text-white/50 font-black">DHOMA:</span>
    <span id="roomBarCode" class="text-2xl font-black tracking-[.25em] text-emerald-300">----</span>
    <button onclick="copyCode()" class="bg-white/10 hover:bg-white/20 px-3 py-1.5 rounded-xl font-black text-sm">📋 Kopjo</button>
    <span id="roomBarCount" class="text-xs text-white/50 font-black"></span>
  </div>
  <div class="text-center text-sm font-black text-white/60" id="streetLabel">LOBBY</div>
  <div id="ptable" class="mt-1">
    <div id="feltmark">♠ GOLDEN EAGLE ♥</div>
    <div id="fx"></div>
    <div id="potbox"><div class="text-xs font-black text-white/50 tracking-widest">POT</div><div id="potAmt" class="text-3xl font-black text-amber-300">0</div></div>
    <div id="commbox"></div>
    <div id="winbanner"></div>
    <div id="seats"></div>
    <div id="pbets"></div>
    <div id="dealers"></div>
  </div>
  <div id="pmsg" class="text-center font-bold text-amber-200 h-7 mt-2"></div>
  <div id="actionBar" class="hidden glass rounded-2xl p-4 mt-1">
    <div class="text-center text-sm text-white/60 font-bold" id="toCallLine"></div>
    <div class="flex justify-center gap-2 mt-2 flex-wrap">
      <button onclick="pact('fold')" class="px-6 py-3 rounded-2xl bg-red-600 font-black hover:bg-red-500">FOLD</button>
      <button onclick="pact('check')" id="btnCheck" class="px-6 py-3 rounded-2xl bg-slate-600 font-black hover:bg-slate-500">CHECK</button>
      <button onclick="pact('call')" id="btnCall" class="px-6 py-3 rounded-2xl bg-blue-600 font-black hover:bg-blue-500">CALL</button>
    </div>
    <div class="flex justify-center gap-2 mt-2 flex-wrap items-center">
      <input id="raiseAmt" type="number" class="w-28 bg-black/50 border border-white/20 rounded-xl px-3 py-2.5 font-black text-center">
      <button onclick="quickRaise(0)" class="px-3 py-2.5 rounded-xl bg-white/10 text-sm font-black">MIN</button>
      <button onclick="quickRaise(0.5)" class="px-3 py-2.5 rounded-xl bg-white/10 text-sm font-black">½ POT</button>
      <button onclick="quickRaise(1)" class="px-3 py-2.5 rounded-xl bg-white/10 text-sm font-black">POT</button>
      <button onclick="quickRaise(99)" class="px-3 py-2.5 rounded-xl bg-white/10 text-sm font-black">MAX</button>
      <button onclick="pact('betraise')" id="btnBet" class="px-6 py-2.5 rounded-2xl btn-gold">BET / RAISE</button>
      <button onclick="pact('allin')" class="px-6 py-2.5 rounded-2xl bg-purple-700 font-black hover:bg-purple-600">ALL-IN</button>
    </div>
  </div>
  <div class="flex justify-center gap-2 mt-2">
    <button onclick="nextHand()" id="nextBtn" class="hidden px-6 py-2.5 rounded-2xl btn-gold font-black">DORA TJETËR ▶️</button>
    <button onclick="leaveTable()" class="px-6 py-2.5 rounded-2xl bg-white/10 font-black hover:bg-white/20">NGRIHU (cashout) 💰</button>
  </div>
  <div class="glass rounded-2xl mt-3 p-3 text-xs max-h-32 overflow-y-auto" id="plog"></div>
</div>
</div>
@endsection

@section('scripts')
<script>
let MODE='off', S=null, ROOM=null, TOKEN=null, CODE=null, MYSEAT=0, seated=false;
let pollIv=null, prevActor='X', lastWinnersKey='';
let prevComm='', prevStreet='', prevPot=-1, prevHandNo=-1, prevActions={};
let fxQueue=[], fxBusy=false, lastFxAt=0, autoChain=0;
const sleepMs=ms=>new Promise(r=>setTimeout(r,ms));
const SUIT={s:'♠',h:'♥',d:'♦',c:'♣'};
const POS=[{l:50,t:88},{l:11,t:70},{l:11,t:28},{l:50,t:7},{l:89,t:28},{l:89,t:70}];
function setMode(m){
  MODE=m;
  document.querySelectorAll('#modetabs button').forEach(b=>{
    const on=b.dataset.m===m;
    b.className='px-6 py-2.5 rounded-xl font-black text-sm '+(on?'bg-emerald-600 text-white':'bg-white/10');
  });
  document.getElementById('offSetup').classList.toggle('hidden',m!=='off'||seated);
  document.getElementById('onSetup').classList.toggle('hidden',m!=='on'||seated);
  document.getElementById('tableWrap').classList.toggle('hidden',!seated);
}
function fmtC(c){
  const r=c[0]==='T'?'10':c[0], s=SUIT[c[1]], red=(c[1]==='h'||c[1]==='d');
  return {r,s,red};
}
function cardHTML(c,big=false,anim=true){
  const f=fmtC(c);
  return `<div class="pcard ${big?'big':''} ${f.red?'red':''} ${anim?'dealin':''}"><span>${f.r}</span><span>${f.s}</span></div>`;
}
function backHTML(big=false,anim=true){ return `<div class="pcard ${big?'big':''} back ${anim?'dealin':''}"></div>`; }
const dealSnd=(i=0)=>{ setTimeout(()=>tone(1200+i*150,0.05,'triangle',0.06),i*120); };
// zëri
let AC=null;
function tone(f,dur=0.07,type='triangle',vol=0.07,when=0){
  try{ AC=AC||new (window.AudioContext||window.webkitAudioContext)(); if(AC.state==='suspended')AC.resume();
    const t=AC.currentTime+when,o=AC.createOscillator(),g=AC.createGain();
    o.type=type;o.frequency.value=f; g.setValueAtTime(vol,t); g.exponentialRampToValueAtTime(0.0001,t+dur);
    o.connect(g);g.connect(AC.destination);o.start(t);o.stop(t+dur+0.02);
  }catch(e){}
}
const chipSnd=()=>{tone(2400,0.04,'square',0.05);tone(1800,0.05,'square',0.04,0.05);};
const turnSnd=()=>{tone(880,0.12,'sine',0.09);setTimeout(()=>tone(1174,0.14,'sine',0.09),130);};
const winSnd=()=>{[523,659,784,1047].forEach((f,i)=>tone(f,0.15,'triangle',0.08,i*0.1));};
// ---------- OFFLINE ----------
// dora e re — nëse mbaron pa lujt askush (të gjithë fold), kalo automatikisht
function handleNewHand(j){
  if(MODE==='off'){S=j;}else{ROOM=j;S=j.state;}
  lastWinnersKey=''; renderAll();
  if(S&&S.street==='done'&&autoChain<3){
    autoChain++;
    toast('Të gjithë fold — dora tjetër fillon…','info');
    setTimeout(()=>nextHand(),1700);
  }else autoChain=0;
}
async function offSit(){
  try{
    const j=await api('/api/poker/off/sit',{buyIn:+document.getElementById('offBuyin').value,bots:+document.getElementById('offBots').value},{settle:'manual'});
    seated=true; MYSEAT=0; autoChain=0; settle(j.balance,500);
    setMode('off'); handleNewHand(j); startPoll();
    toast('U ule në tavolinë! Fat! 🍀','win');
  }catch(e){ toast(e.message,'lose'); }
}
async function offRefresh(){
  try{ const j=await apiGet('/api/poker/off/state'); if(j.seated){S=j;renderAll();} }
  catch(e){}
}
async function apiGet(url){
  const res=await fetch(url); const j=await res.json();
  if(!res.ok)throw new Error(j.error||'Gabim');
  return j;
}
async function pact(kind){
  let action=kind, amount=0;
  if(kind==='betraise'){
    amount=Math.floor(+document.getElementById('raiseAmt').value||0);
    action=S.toCall>0?'raise':'bet';
  }
  chipSnd();
  autoChain=0; // hero luajti — reseto zinxhirin
  try{
    const url=MODE==='off'?'/api/poker/off/action':'/api/poker/rooms/action';
    const body=MODE==='off'?{action,amount}:{code:CODE,token:TOKEN,action,amount};
    const j=await api(url,body,{settle:'manual'});
    if(MODE==='off'){S=j;}else{ROOM=j;S=j.state;}
    if(j.timeout)toast(j.message||'Koha mbaroi (40s)!','lose');
    renderAll();
  }catch(e){
    toast(e.message,'lose');
    if(MODE==='on')refreshRoom();
  }
}
function quickRaise(f){
  if(!S)return;
  const me=S.players[MYSEAT];
  const minTotal=S.currentBet+S.minRaise;
  let v;
  if(f===0)v=minTotal;
  else if(f===99)v=me.bet+me.stack;
  else v=Math.floor(S.currentBet+(S.pot*f));
  v=Math.max(minTotal,Math.min(me.bet+me.stack,v));
  if(S.toCall<=0&&f!==0&&f!==99)v=Math.max(S.bb*2,v);
  document.getElementById('raiseAmt').value=v;
}
async function nextHand(){
  try{
    const url=MODE==='off'?'/api/poker/off/next':'/api/poker/rooms/next';
    const body=MODE==='off'?{}:{code:CODE,token:TOKEN};
    const j=await api(url,body,{settle:'manual'});
    if(j.needRebuy){
      if(confirm(`Stack: ${j.stack}€. Bëj REBUY ${document.getElementById('offBuyin')?.value||500}€?`)){
        const r=await api('/api/poker/off/rebuy',{},{settle:'manual'});
        autoChain=0; handleNewHand(r);
      }
      return;
    }
    handleNewHand(j);
  }catch(e){ toast(e.message,'lose'); }
}
async function leaveTable(){
  if(!confirm('Të ngrihesh? Stack-u kthehet në balancë.'))return;
  stopPoll();
  try{
    if(MODE==='off'){ const j=await api('/api/poker/off/leave',{},{settle:'manual'}); settle(j.balance,600); }
    else{ await api('/api/poker/rooms/leave',{code:CODE,token:TOKEN},{settle:'manual'}); stopPoll(); CODE=TOKEN=ROOM=null; }
    seated=false; S=null; setMode(MODE); renderLobby();
  }catch(e){ toast(e.message,'lose'); }
}
// ---------- ONLINE ----------
async function roomCreate(){
  try{
    const j=await api('/api/poker/rooms/create',{name:document.getElementById('onName').value.trim(),buyIn:+document.getElementById('onBuyin').value,maxSeats:+document.getElementById('onMax').value,bots:+document.getElementById('onBots').value},{settle:'manual'});
    CODE=j.code; TOKEN=j.token; ROOM=j; S=j.state; MYSEAT=j.you.seat; seated=true;
    settle(j.balance,500); setMode('on'); renderAll(); startPoll();
    toast(`Dhoma ${CODE} u krijua! Jepi kodin shokëve 🏠`,'win');
  }catch(e){ toast(e.message,'lose'); }
}
async function roomJoin(){
  const code=document.getElementById('joinCode').value.trim().toUpperCase();
  if(code.length!==4){ toast('Shkruaj kodin me 4 shkronja.','lose'); return; }
  try{
    const j=await api('/api/poker/rooms/join',{code,name:document.getElementById('onName').value.trim()},{settle:'manual'});
    CODE=j.code; TOKEN=j.token; ROOM=j; S=j.state; MYSEAT=j.you.seat; seated=true;
    settle(j.balance,500); setMode('on'); renderAll(); startPoll();
    toast(`Hyre në dhomën ${CODE}! 🎉`,'win');
  }catch(e){ toast(e.message,'lose'); }
}
function copyCode(){ try{ navigator.clipboard.writeText(CODE); toast('Kodi u kopjua: '+CODE,'win'); }catch(e){} }
async function roomAddBot(){
  try{ const j=await api('/api/poker/rooms/addbot',{code:CODE,token:TOKEN},{settle:'manual'}); ROOM=j; S=j.state; renderAll(); }
  catch(e){ toast(e.message,'lose'); }
}
async function roomStart(){
  try{ const j=await api('/api/poker/rooms/start',{code:CODE,token:TOKEN},{settle:'manual'}); autoChain=0; handleNewHand(j); }
  catch(e){ toast(e.message,'lose'); }
}
async function roomLeave(){
  try{ await api('/api/poker/rooms/leave',{code:CODE,token:TOKEN},{settle:'manual'}); }catch(e){}
  stopPoll(); CODE=TOKEN=ROOM=null; seated=false; S=null; setMode('on'); renderLobby();
}
function startPoll(){ stopPoll(); pollIv=setInterval(()=>{ if(MODE==='on'&&CODE)refreshRoom(); else if(MODE==='off'&&seated)offRefresh(); },1500); }
function stopPoll(){ if(pollIv){clearInterval(pollIv);pollIv=null;} }
async function refreshRoom(){
  if(MODE!=='on'||!CODE)return;
  try{
    const res=await fetch(`/api/poker/rooms/state?code=${CODE}&token=${TOKEN}`);
    const j=await res.json();
    if(!res.ok)throw new Error(j.error||'Gabim');
    ROOM=j; S=j.state; MYSEAT=j.you.seat;
    renderAll();
  }catch(e){ /* hesht — provo prap pas 1.5s */ }
}
function renderLobby(){
  const box=document.getElementById('roomBox');
  const bar=document.getElementById('roomBar');
  if(!ROOM){ box.classList.add('hidden'); bar.classList.add('hidden'); return; }
  box.classList.remove('hidden');
  document.getElementById('roomCode').textContent=ROOM.room.code;
  document.getElementById('roomPlayers').innerHTML=ROOM.room.seats.map(s=>
    `<div class="flex justify-between bg-black/40 rounded-xl px-3 py-1.5">
      <span>${s.isBot?'🤖':'🙂'} ${s.name} ${s.isHost?'👑':''} ${s.isYou?'(ti)':''}</span>
      <span class="text-white/40">${s.sittingOut?'jashtë':''}</span>
    </div>`).join('');
  document.getElementById('addBotBtn').style.display=ROOM.room.isHost?'':'none';
  document.getElementById('startBtn2').style.display=ROOM.room.isHost?'':'none';
  // shiriti i kodit mbi tavolinë — duket gjithmonë kur je në dhomë
  if(seated&&MODE==='on'&&CODE){
    bar.classList.remove('hidden');
    document.getElementById('roomBarCode').textContent=CODE;
    document.getElementById('roomBarCount').textContent=`${ROOM.room.count}/${ROOM.room.max||6} lojtarë`;
  }else{
    bar.classList.add('hidden');
  }
}
// ---------- RENDER ----------
// efekti i veprimit të kundërshtarit: bubble + chipsa + zë
function actionStyle(la){
  if(la.startsWith('FOLD'))return {bg:'#374151',fg:'#fff',br:'#9ca3af'};
  if(la.startsWith('CHECK'))return {bg:'#1d4ed8',fg:'#fff',br:'#93c5fd'};
  if(la.startsWith('CALL'))return {bg:'#15803d',fg:'#fff',br:'#4ade80'};
  if(la.startsWith('ALL-IN'))return {bg:'#b91c1c',fg:'#fff',br:'#fca5a5'};
  return {bg:'#92610a',fg:'#fff',br:'#f5c518'}; // BET / RAISE
}
function actionSnd(la){
  if(la.startsWith('FOLD'))tone(200,0.18,'sawtooth',0.08,120);
  else if(la.startsWith('CHECK')){ tone(1400,0.04,'square',0.05); setTimeout(()=>tone(1100,0.04,'square',0.05),70); }
  else if(la.startsWith('CALL'))chipSnd();
  else if(la.startsWith('ALL-IN')){ tone(440,0.12,'sawtooth',0.09,880); setTimeout(()=>tone(660,0.16,'sawtooth',0.09,880),130); }
  else chipSnd();
}
function flyChipToPot(seatEl){
  try{
    const t=document.getElementById('ptable').getBoundingClientRect();
    const r=seatEl.getBoundingClientRect();
    const c=document.createElement('div');
    c.textContent='🪙';
    c.style.cssText=`position:fixed;left:${r.left+r.width/2-11}px;top:${r.top+6}px;font-size:22px;z-index:9999;pointer-events:none;`;
    document.body.appendChild(c);
    const dx=(t.left+t.width/2)-(r.left+r.width/2), dy=(t.top+t.height*0.36)-r.top;
    c.animate([
      {transform:'translate(0,0) scale(1)',opacity:1},
      {transform:`translate(${dx*0.5}px,${dy*0.5-34}px) scale(1.1)`,opacity:1,offset:0.55},
      {transform:`translate(${dx}px,${dy}px) scale(.5)`,opacity:0.9}
    ],{duration:550,easing:'cubic-bezier(.3,.7,.4,1)'}).onfinish=()=>c.remove();
  }catch(e){}
}
function showActionFx(i,la,pos){
  const fx=document.getElementById('fx');
  const st=actionStyle(la);
  const below=pos.t<40; // vendet nalt: bubble poshtë që mos pritet nga tavolina
  const b=document.createElement('div');
  b.className='actbubble'+(below?' bdown':'');
  b.style.left=pos.l+'%'; b.style.top=below?`calc(${pos.t}% + 60px)`:`calc(${pos.t}% - 64px)`;
  b.style.background=st.bg; b.style.color=st.fg; b.style.border=`3px solid ${st.br}`;
  b.textContent=(la.startsWith('CALL')||la.startsWith('BET')||la.startsWith('RAISE')||la.startsWith('ALL-IN'))?('🪙 '+la):la;
  fx.appendChild(b);
  setTimeout(()=>b.remove(),3050);
  actionSnd(la);
  if(la.startsWith('CALL')||la.startsWith('BET')||la.startsWith('RAISE')||la.startsWith('ALL-IN')){
    const seatEl=document.querySelectorAll('#seats .pseat')[i];
    if(seatEl)setTimeout(()=>flyChipToPot(seatEl),250);
  }
}
// radha e animacioneve: secili veprim shfaqet me highlight + bubble + zë
async function pumpFx(n,v){
  if(fxBusy)return;
  fxBusy=true;
  try{
  // shumë veprime grumbull → trego pa pritje të gjata
  if(fxQueue.length>6){
    while(fxQueue.length){
      const {i,la}=fxQueue.shift();
      const slot=((i-MYSEAT)%n+n)%n;
      showActionFx(i,la,POS[Math.round(slot*6/n)%6]);
    }
    return;
  }
  while(fxQueue.length){
    const {i,la}=fxQueue.shift();
    const slot=((i-MYSEAT)%n+n)%n;
    const pos=POS[Math.round(slot*6/n)%6];
    const seatEl=document.querySelectorAll('#seats .pseat')[i];
    const wasActor=seatEl&&seatEl.classList.contains('actor');
    // SPOTLIGHT: të tjerët zbehen, aktori zmadhohet
    document.querySelectorAll('#seats .pseat').forEach((el,j)=>{
      if(j!==i){ el.style.filter='brightness(.5)'; } else { el.style.transform='translate(-50%,-50%) scale(1.14)'; el.style.zIndex='15'; }
    });
    if(seatEl&&!wasActor)seatEl.classList.add('actor');
    const think=document.createElement('div');
    think.className='actbubble'+(pos.t<40?' bdown':'');
    think.style.left=pos.l+'%'; think.style.top=pos.t<40?`calc(${pos.t}% + 60px)`:`calc(${pos.t}% - 64px)`;
    think.style.background='#0b1526'; think.style.color='#94a3b8'; think.style.border='3px solid #475569';
    think.textContent='…';
    document.getElementById('fx').appendChild(think);
    await sleepMs(750+Math.random()*400); // boti "mendon" — kadalë si njeri
    think.remove();
    showActionFx(i,la,pos);
    await sleepMs(950);
    if(seatEl&&!wasActor&&seatEl.isConnected)seatEl.classList.remove('actor');
    document.querySelectorAll('#seats .pseat').forEach((el)=>{
      el.style.filter=''; el.style.transform=''; el.style.zIndex='';
    });
  }
  }finally{ fxBusy=false; }
}
function renderAll(){
  renderLobby();
  if(!S){return;}
  const v=S, n=v.players.length;
  if(v.handNo!==prevHandNo){ prevHandNo=v.handNo; prevComm=''; prevStreet=''; prevPot=-1; lastWinnersKey=''; prevActions={}; fxQueue=[]; fxBusy=false; }
  const sl=document.getElementById('streetLabel');
  sl.textContent=
    '🃏 '+({preflop:'PRE-FLOP',flop:'FLOP',turn:'TURN',river:'RIVER',showdown:'SHOWDOWN',done:'FUND',lobby:'LOBBY'}[v.street]||v.street)
    +(v.handNo?` • Dora #${v.handNo}`:'');
  if(v.street!==prevStreet){ prevStreet=v.street; sl.classList.remove('streetpop'); void sl.offsetWidth; sl.classList.add('streetpop'); }
  const potEl=document.getElementById('potAmt');
  potEl.textContent=v.pot;
  if(v.pot!==prevPot){ prevPot=v.pot; potEl.classList.remove('potpulse'); void potEl.offsetWidth; potEl.classList.add('potpulse'); }
  // letra publike — vetëm të rejat animohen, te showdown ndizen me ar
  const commKey=v.community.join('|');
  const prevArr=prevComm?prevComm.split('|'):[];
  const freshDeal=v.community.length>prevArr.length&&v.community.slice(0,prevArr.length).join('|')===prevComm;
  const newCount=freshDeal?v.community.length-prevArr.length:0;
  if(newCount>0)dealSnd(0);
  prevComm=commKey;
  const cb=document.getElementById('commbox'); cb.innerHTML='';
  const isSD=v.street==='done'&&v.winners&&v.winners.length;
  for(let i=0;i<5;i++){
    if(v.community[i]){
      const anim=freshDeal&&i>=v.community.length-newCount;
      let h=cardHTML(v.community[i],true,anim);
      if(isSD)h=h.replace('pcard ', 'pcard sdglow ');
      if(anim)h=h.replace('class="pcard', `style="animation-delay:${(i-(v.community.length-newCount))*0.12}s" class="pcard`);
      cb.innerHTML+=h;
    }
    else cb.innerHTML+=`<div class="pcard big" style="opacity:.25;border:2px dashed #ffffff55;background:transparent"></div>`;
  }
  // lojtarët rreth tavolinës
  const seats=document.getElementById('seats'), bets=document.getElementById('pbets'), dl=document.getElementById('dealers');
  seats.innerHTML=''; bets.innerHTML=''; dl.innerHTML='';
  const wonSeats=new Set((v.winners||[]).map(w=>w.seat));
  v.players.forEach((p,i)=>{
    const slot=((i-MYSEAT)%n+n)%n;
    const pos=POS[Math.round(slot*6/n)%6];
    const d=document.createElement('div');
    d.className='pseat'+(p.isBot?' bot':'')+(p.folded?' folded':'')+(p.isActor?' actor':'')+(wonSeats.has(i)?' winner':'');
    d.style.left=pos.l+'%'; d.style.top=pos.t+'%';
    const av=p.isBot?'🤖':(p.name||'?').slice(0,1).toUpperCase();
    let cards='';
    if(p.cards&&p.cards.length)cards=p.cards.map(c=>cardHTML(c,false)).join('');
    else if(!p.sittingOut&&v.street!=='lobby'&&v.street!=='done')cards=backHTML(false)+backHTML(false);
    const showdownGlow=v.street==='done'&&wonSeats.has(i)?' sdglow':'';
    let timer='';
    if(p.isActor){
      let secs=null;
      if(MODE==='on'&&ROOM&&ROOM.turnSecs!=null)secs=ROOM.turnSecs;
      else if(MODE==='off'&&S&&S.turnSecs!=null)secs=S.turnSecs;
      if(secs!=null){
        const pct=Math.max(0,Math.min(100,secs/40*100));
        timer=`<div class="turnbar"><div style="width:${pct}%"></div></div><div class="seccount text-center font-black ${secs<=10?'text-red-400':'text-emerald-300'}" style="font-size:14px" data-secs="${secs}">⏱ ${secs}s</div>`;
      }else{
        timer=`<div class="turnbar"><div style="width:100%"></div></div>`;
      }
    }
    d.innerHTML=`<div class="box${showdownGlow}">
        <div class="pav">${av}</div>
        <div class="pinf"><div class="nm">${p.name}${p.isYou?' (ti)':''}</div>
        <div class="st">${p.sittingOut?'jashtë':p.stack+'€'}</div></div>
      </div>
      <div class="cards">${cards}</div>
      ${p.lastAction?`<div class="lact">${p.lastAction}</div>`:''}
      ${p.allin&&v.street!=='done'?'<div class="allinb">ALL-IN</div>':''}
      ${timer}`;
    seats.appendChild(d);
    if(p.bet>0){
      const b=document.createElement('div');
      b.className='pbet';
      b.style.left=pos.l+'%'; b.style.top=(pos.t+(pos.t>50?-13:13))+'%';
      const chips=p.bet>=100?'🪙🪙🪙':p.bet>=25?'🪙🪙':'🪙';
      b.innerHTML=`<span class="stk">${chips}</span>${p.bet}`;
      bets.appendChild(b);
    }
    if(p.isDealer){
      const dd=document.createElement('div');
      dd.className='dbtn'; dd.textContent='D';
      dd.style.left=`calc(${pos.l}% + 66px)`; dd.style.top=`calc(${pos.t}% - 36px)`;
      dl.appendChild(dd);
    }
  });
  // animacion për ÇDO veprim të ri — me radhë, një nga një (edhe botat "mendojnë")
  if(v.street!=='done'){
    v.players.forEach((p,i)=>{
      const la=p.lastAction||'';
      if(la&&prevActions[i]!==undefined&&prevActions[i]!==la){
        fxQueue.push({i,la});
        // komenti shkruhet MENJËHERË (sinkron) — s'varet nga animacioni
        try{
          const nm=p.name||'';
          const ct=document.getElementById('commentTxt');
          if(nm&&ct){ ct.textContent=`${i===MYSEAT?'🙂':'🤖'} ${nm}: ${la}`; lastFxAt=Date.now(); }
        }catch(e){}
      }
      prevActions[i]=la;
    });
    pumpFx(n,v);
  }
  // mesazhi + fituesit (shkruhet veç kur ndryshon — ndryshe pulson pa ndal)
  const wb=document.getElementById('winbanner');
  if(v.street==='done'&&v.winners&&v.winners.length){
    const key=v.handNo+':'+JSON.stringify(v.winners);
    if(key!==lastWinnersKey){
      lastWinnersKey=key;
      wb.innerHTML=v.winners.map(w=>{
        const p=v.players[w.seat];
        return `<div class="bg-black/80 border-2 border-amber-400 rounded-2xl px-6 py-2 font-black text-lg slidein">🏆 ${p.name} +${w.amount}€ ${w.desc?'• '+w.desc:''}</div>`;
      }).join('');
      if(v.winners.some(w=>w.seat===MYSEAT&&w.amount>0)){ winSnd(); confetti(100); }
    }
  } else { wb.innerHTML=''; if(v.street!=='done')lastWinnersKey=''; }
  // shiriti i veprimeve
  const bar=document.getElementById('actionBar');
  const myTurn=v.actor===MYSEAT&&v.street!=='done'&&v.street!=='lobby';
  bar.classList.toggle('hidden',!myTurn);
  if(myTurn){
    if(prevActor!==MYSEAT)turnSnd();
    const me=v.players[MYSEAT];
    document.getElementById('toCallLine').textContent=
      (v.toCall>0?`Për të vazhduar: CALL ${v.toCall}€ • `:'')+`Pot: ${v.pot}€ • Min raise total: ${v.currentBet+v.minRaise}€`;
    document.getElementById('btnCheck').style.display=v.toCall>0?'none':'';
    const bc=document.getElementById('btnCall');
    bc.style.display=v.toCall>0?'':'none';
    if(v.toCall>0)bc.textContent=`CALL ${v.toCall}€`;
    document.getElementById('raiseAmt').value=v.currentBet+v.minRaise;
  }
  prevActor=v.actor;
  // butoni Dora tjetër
  const nb=document.getElementById('nextBtn');
  const showNext=v.street==='done'&&(MODE==='off'||(ROOM&&ROOM.room.isHost));
  nb.classList.toggle('hidden',!showNext);
  // logu + komenti live nalt (komenti i veprimit rri 5s pa u fshi nga poll-i)
  document.getElementById('plog').innerHTML=(v.log||[]).map(l=>`<div class="text-white/60">• ${l}</div>`).join('');
  const lastLog=(v.log||[]).length?v.log[v.log.length-1]:'';
  const ct=document.getElementById('commentTxt');
  if(Date.now()-lastFxAt>5000){
    const ctxt=v.street==='done'?'🏁 Dora mbaroi.':(lastLog||'Prit…');
    if(ct.textContent!==ctxt){ ct.textContent=ctxt; }
  }
  // mesazhi i statusit (+ sekondat kur e ke radhën — pas 40s hup radha: auto check/fold)
  const pm=document.getElementById('pmsg');
  if(v.street==='done')pm.textContent='Dora mbaroi.';
  else if(v.actor==null)pm.textContent='Prit...';
  else{
    let t=`Radha: ${v.players[v.actor].name}${v.actor===MYSEAT?' (TI!)':''}`;
    let s2=null;
    if(v.actor===MYSEAT){
      if(MODE==='on'&&ROOM&&ROOM.turnSecs!=null)s2=ROOM.turnSecs;
      else if(MODE==='off'&&S&&S.turnSecs!=null)s2=S.turnSecs;
    }
    if(s2!=null)t+=` — ⏱ ${s2}s (luaj shpejt, pas 40s hup radha!)`;
    pm.textContent=t;
  }
}
setMode('off');
// numërimi live i sekondave (çdo 1s) mes poll-eve — mos pret 1.5s për me pa kohën
setInterval(()=>{
  document.querySelectorAll('.seccount').forEach(el=>{
    let s=parseInt(el.dataset.secs||'0',10);
    if(s>0){
      s--; el.dataset.secs=s; el.textContent='⏱ '+s+'s';
      el.classList.toggle('text-red-400',s<=10);
      el.classList.toggle('text-emerald-300',s>10);
      const bar=el.parentElement?el.parentElement.querySelector('.turnbar>div'):null;
      if(bar)bar.style.width=Math.max(0,Math.min(100,s/40*100))+'%';
    }
  });
},1000);
// vazhdo lojën offline nëse ekziston (pas refresh-it)
(async()=>{ try{ const j=await apiGet('/api/poker/off/state'); if(j.seated){ seated=true; MYSEAT=0; S=j; setMode('off'); renderAll(); startPoll(); } }catch(e){} })();
</script>
@endsection
