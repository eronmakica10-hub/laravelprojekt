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
  .reel-window{width:110px;height:288px;overflow:hidden;border-radius:14px;background:linear-gradient(180deg,#fff,#e7e5e4 12%,#fafaf9 50%,#e7e5e4 88%,#a8a29e);border:3px solid #78350f;box-shadow:inset 0 14px 22px rgba(0,0,0,.45), inset 0 -14px 22px rgba(0,0,0,.45);position:relative}
  .strip{display:flex;flex-direction:column;will-change:transform}
  .sym{height:96px;display:flex;align-items:center;justify-content:center;font-size:62px;flex-shrink:0;text-shadow:0 3px 0 rgba(0,0,0,.15)}
  .fast{filter:blur(3px) brightness(1.15)}
  .payline{position:absolute;left:-14px;right:-14px;top:50%;height:0;pointer-events:none;z-index:5}
  .payline::before{content:'';position:absolute;left:0;right:0;top:-2px;height:4px;background:#ef4444;opacity:.45;box-shadow:0 0 8px #ef4444}
  .payline::after{content:'◀ PAYLINE ▶';position:absolute;left:50%;top:8px;transform:translateX(-50%);font-size:10px;font-weight:900;color:#fca5a5;letter-spacing:2px;white-space:nowrap}
  .hit::before{opacity:1;animation:lineflash .4s infinite}
  @keyframes lineflash{50%{opacity:.3}}
  #lever{width:34px;height:220px;position:relative;cursor:pointer;user-select:none}
  #leverTrack{position:absolute;left:50%;top:0;bottom:0;width:12px;transform:translateX(-50%);background:linear-gradient(90deg,#444,#999,#444);border-radius:6px}
  #leverArm{position:absolute;left:50%;top:6px;width:10px;height:150px;transform:translateX(-50%);background:linear-gradient(90deg,#b45309,#fbbf24,#b45309);border-radius:5px;transition:top .28s ease-in}
  #leverKnob{position:absolute;left:50%;top:-6px;transform:translateX(-50%);width:42px;height:42px;border-radius:50%;background:radial-gradient(circle at 35% 30%,#fca5a5,#dc2626 60%,#7f1d1d);box-shadow:0 6px 12px rgba(0,0,0,.6);transition:top .28s ease-in}
  #lever.pulled #leverArm{top:60px}
  #lever.pulled #leverKnob{top:48px}
  .win-display{background:#000;border:2px solid #fbbf24;border-radius:12px;font-family:monospace;text-shadow:0 0 10px #f59e0b}
</style>

<div class="max-w-3xl mx-auto text-center slidein">
  <h1 class="text-4xl font-black neon-gold">🎰 SLOTET E ARTË</h1>
  <p class="text-white/60 mt-1">Tërhiq levën si në kazino të vërtetë — 3x simbol = JACKPOT x10!</p>

  <div class="cabinet mt-6 px-4 md:px-8 pt-2 pb-6" id="machine">
    <div id="bulbs"></div>
    <div class="marquee mx-auto max-w-md py-2 px-4 mt-1">
      <div class="text-amber-300 font-black tracking-widest text-lg">★ GOLDEN SLOTS ★</div>
      <div class="win-display text-2xl font-black text-amber-300 mt-1 py-1" id="winbox2">CREDITS: <span id="credNow">—</span></div>
    </div>

    <div class="flex items-center justify-center gap-3 md:gap-5 mt-4">
      <div class="relative">
        <div class="flex gap-3 md:gap-4">
          <div class="reel-window"><div class="strip" id="strip0"></div></div>
          <div class="reel-window"><div class="strip" id="strip1"></div></div>
          <div class="reel-window"><div class="strip" id="strip2"></div></div>
        </div>
        <div class="payline" id="payline"></div>
      </div>
      <div id="lever" onclick="spin()" title="Tërhiq levën!">
        <div id="leverTrack"></div>
        <div id="leverArm"></div>
        <div id="leverKnob"></div>
      </div>
    </div>

    <div id="msg" class="h-8 font-black text-xl text-amber-300 mt-3">Tërhiq levën ose shtyp SPIN!</div>

    <div class="mt-2">
      <div class="text-xs text-white/50 mb-2">Zgjidh bastin:</div>
      <div class="flex justify-center gap-2 flex-wrap" id="bets">
        <button data-bet="5" class="chip bg-blue-600">5€</button>
        <button data-bet="10" class="chip bg-green-600 active">10€</button>
        <button data-bet="25" class="chip bg-purple-600">25€</button>
        <button data-bet="50" class="chip bg-red-600">50€</button>
        <button data-bet="100" class="chip bg-amber-600">100€</button>
      </div>
    </div>
    <button id="spin" onclick="spin()" class="btn-gold mt-5 px-14 py-4 rounded-full text-2xl w-full md:w-auto">SPIN 🎰</button>
  </div>

  <div class="glass rounded-2xl mt-4 p-4 text-left text-sm">
    <b class="text-amber-300">📋 Pagesat (vija e mesit):</b>
    <div class="grid grid-cols-2 gap-1 mt-2 text-white/70">
      <div>7️⃣7️⃣7️⃣ → <b class="text-amber-300">x10</b></div><div>💎💎💎 → <b class="text-amber-300">x8</b></div>
      <div>⭐⭐⭐ → <b class="text-amber-300">x5</b></div><div>🔔🔔🔔 → <b class="text-amber-300">x4</b></div>
      <div>🍋🍋🍋 → <b class="text-amber-300">x3</b></div><div>🍒🍒🍒 → <b class="text-amber-300">x2.5</b></div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
let bet=10, spinning=false;
const SYMS=['🍒','🍋','🔔','⭐','💎','7️⃣'];
const SYM_H=96, DUR=[1800,2500,3200];
document.querySelectorAll('#bets .chip').forEach(c=>c.onclick=()=>{document.querySelectorAll('#bets .chip').forEach(x=>x.classList.remove('active'));c.classList.add('active');bet=+c.dataset.bet;});
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
// gjendja fillestare e shiritave
function fillInitial(){
  for(let i=0;i<3;i++){
    const st=document.getElementById('strip'+i);
    st.style.transition='none';st.style.transform='translateY(0)';
    st.innerHTML=[0,1,2].map(()=>`<div class="sym">${SYMS[Math.floor(Math.random()*SYMS.length)]}</div>`).join('');
  }
}
fillInitial();
document.getElementById('credNow').textContent=currentDisplayed().toFixed(2)+'€';

async function spin(){
  if(spinning) return;
  // leva tërhiqet
  const lever=document.getElementById('lever');
  lever.classList.add('pulled'); tone(300,0.2,'sawtooth',0.05);
  setTimeout(()=>lever.classList.remove('pulled'),550);
  const btn=document.getElementById('spin');btn.disabled=true;spinning=true;
  document.getElementById('payline').classList.remove('hit');
  document.getElementById('machine').classList.remove('celebrate','win-pulse');
  document.getElementById('msg').textContent='Po rrotullohet... 🎰';

  let j;
  try{ j=await api('/api/slots/spin',{bet},{settle:'manual'}); }
  catch(e){ toast(e.message,'lose'); btn.disabled=false; spinning=false; return; }

  // ndërto shiritat: 3 aktualet + N rastësore + [sipër, REZULTATI, poshtë]
  const rnd=()=>SYMS[Math.floor(Math.random()*SYMS.length)];
  let stopped=0;
  const tickIv=setInterval(tick,95);
  for(let i=0;i<3;i++){
    const st=document.getElementById('strip'+i);
    const cur=[...st.querySelectorAll('.sym')].map(d=>d.textContent);
    while(cur.length<3)cur.push(rnd());
    const extra=16+i*9;
    const seq=[...cur];
    for(let k=0;k<extra;k++)seq.push(rnd());
    seq.push(rnd(), j.reels[i], rnd());   // dritarja finale: mesi = rezultati
    st.style.transition='none';st.style.transform='translateY(0)';
    st.innerHTML=seq.map(s=>`<div class="sym">${s}</div>`).join('');
    st.parentElement.classList.add('fast');
    void st.offsetHeight;
    st.style.transition=`transform ${DUR[i]}ms cubic-bezier(.12,.6,.08,1)`;
    st.style.transform=`translateY(${-(seq.length-3)*SYM_H}px)`;
    setTimeout(()=>{
      st.parentElement.classList.remove('fast');
      clunk();
      if(++stopped===3){ clearInterval(tickIv); finish(j); }
    }, DUR[i]+60);
  }
}
function finish(j){
  spinning=false;document.getElementById('spin').disabled=false;
  document.getElementById('msg').textContent=j.message;
  document.getElementById('credNow').textContent=Number(j.balance).toFixed(2)+'€';
  if(j.isWin){
    document.getElementById('payline').classList.add('hit');
    document.getElementById('machine').classList.add('celebrate','win-pulse');
    setTimeout(()=>document.getElementById('machine').classList.remove('win-pulse'),2500);
    fanfare();
    flyCoins('machine',14);
    setTimeout(()=>settle(j.balance,1300),950);   // paratë kreditohen PASI ndalin rrotullat
    if(j.isJackpot){confetti(150);toast('🎉 JACKPOT neto +'+j.profit+'€!','win');}
    else{confetti(40);toast('Fituat neto +'+j.profit+'€!','win');}
  } else {
    setTimeout(()=>settle(j.balance,700),200);
    toast(j.message,'lose');
  }
}
</script>
@endsection
