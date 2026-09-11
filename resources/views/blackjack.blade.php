@extends('layouts.casino')
@section('title','Blackjack')

@section('content')
<style>
  .felt{background:radial-gradient(ellipse at 50% 30%,#12934e,#0a6b38 55%,#043d22 100%);border:10px solid #5b3a1a;border-radius:36px;box-shadow:0 30px 80px -20px #000, inset 0 0 80px rgba(0,0,0,.5);position:relative;outline:3px solid #d4a017}
  .felt-arc{position:absolute;left:8%;right:8%;top:34%;height:60%;border:3px solid rgba(255,255,255,.25);border-top:none;border-radius:0 0 200px 200px;pointer-events:none}
  .felt-text{position:absolute;left:0;right:0;top:46%;text-align:center;color:rgba(255,255,255,.35);font-weight:900;letter-spacing:2px;pointer-events:none}
  .shoe{position:absolute;top:14px;right:18px;text-align:center;color:#fff8}
  .shoe .deck{font-size:44px;filter:drop-shadow(0 6px 6px rgba(0,0,0,.5))}
  .bcard{width:76px;height:112px;border-radius:10px;background:linear-gradient(160deg,#ffffff,#dfe5ec);color:#111;position:relative;box-shadow:0 10px 22px rgba(0,0,0,.55);transform:rotate(var(--r,0deg)) translateY(var(--y,0px));flex-shrink:0}
  .bcard.bred{color:#c81e1e}
  .bcard .cnr{position:absolute;display:flex;flex-direction:column;align-items:center;line-height:1;font-size:15px;font-weight:900}
  .bcard .cnr.tl{top:6px;left:7px}.bcard .cnr.br{bottom:6px;right:7px;transform:rotate(180deg)}
  .bcard .pip{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:44px;text-shadow:0 2px 0 rgba(0,0,0,.12)}
  .bcard.deal{animation:dealfrom .45s cubic-bezier(.2,.8,.3,1) both}
  @keyframes dealfrom{from{transform:translate(160px,-160px) rotate(50deg) scale(.6);opacity:0}}
  .bcard.just-flipped{animation:fliprev .65s ease both}
  @keyframes fliprev{0%{transform:rotateY(90deg) scale(.92)}60%{transform:rotateY(-8deg)}100%{transform:rotateY(0)}}
  .bcard.cardback{background:repeating-linear-gradient(45deg,#1e3a8a 0 8px,#172554 8px 16px);border:4px solid #f8fafc}
  .bcard.cardback::after{content:'🦅';position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:34px}
  #betspot{width:120px;height:120px;border-radius:50%;border:3px dashed rgba(255,255,255,.5);display:flex;flex-direction:column;align-items:center;justify-content:center;margin:0 auto;transition:transform .8s ease,opacity .8s;position:relative;z-index:5}
  #betspot .stack{font-size:40px;filter:drop-shadow(0 4px 6px rgba(0,0,0,.5))}
  #betspot.to-player{transform:translateY(160px) scale(.5);opacity:0}
  #betspot.to-dealer{transform:translateY(-160px) scale(.5);opacity:0}
</style>

<div class="max-w-3xl mx-auto text-center slidein">
<h1 class="text-4xl font-black neon-gold">🃏 BLACKJACK 21</h1>
<p class="text-white/60">Mundi bankën pa e kaluar 21. Blackjack paguan 2.5x!</p>

<div class="felt mt-6 px-4 py-6" id="bjtable">
  <div class="felt-arc"></div>
  <div class="felt-text text-sm md:text-base">♠ BLACKJACK PAYS 3 TO 2 ♥<br><span class="text-xs">Dealer must stand on 17</span></div>
  <div class="shoe"><div class="deck">🂠</div><div class="text-[10px] tracking-widest">SHOE</div></div>

  <div class="text-xs tracking-widest text-white/70 relative z-10">🎩 BANKA <span id="dval" class="text-white font-black"></span></div>
  <div id="dealer" class="flex justify-center items-start gap-2 mt-2 min-h-[120px] flex-wrap relative z-10" style="gap:10px"></div>

  <div id="betspot" class="my-1"><span class="stack">🪙</span><span id="betamt" class="text-amber-300 font-black text-sm"></span></div>

  <div id="player" class="flex justify-center items-start gap-2 mt-1 min-h-[120px] flex-wrap relative z-10" style="gap:10px"></div>
  <div class="text-xs tracking-widest text-white/70 relative z-10">😎 TI <span id="pval" class="text-amber-300 font-black"></span></div>
</div>

<div id="msg" class="mt-4 font-black text-xl h-8">Vë bastin dhe shtyp DEAL! Depozito nga 💳 Portofoli nëse s'ke balancë.</div>

<div class="flex justify-center gap-2 mt-3 flex-wrap items-center">
  <span class="text-sm text-white/50">Basti:</span>
  <input id="bet" type="number" value="25" min="5" max="500" class="w-24 bg-black/50 border border-white/20 rounded-xl px-3 py-2 font-black text-center">
  <button onclick="newGame()" id="dealBtn" class="btn-gold px-8 py-2 rounded-full">DEAL 🃏</button>
  <button onclick="hit()" id="hitBtn" class="px-8 py-2 rounded-full bg-green-600 font-black hover:scale-105 transition disabled:opacity-40" disabled>HIT ➕</button>
  <button onclick="stand()" id="standBtn" class="px-8 py-2 rounded-full bg-red-600 font-black hover:scale-105 transition disabled:opacity-40" disabled>STAND ✋</button>
</div>
<div class="glass rounded-2xl mt-4 p-4 text-sm text-white/70 text-left">💡 <b>Këshillë:</b> Qëndro (STAND) në 17+, kërko letër (HIT) në 11 ose më pak. Banka ndalet në 17.</div>
</div>
@endsection

@section('scripts')
<script>
let AC=null;
function tone(f,dur=0.06,type='triangle',vol=0.08,when=0){
  try{
    AC=AC||new (window.AudioContext||window.webkitAudioContext)();
    if(AC.state==='suspended')AC.resume();
    const t=AC.currentTime+when,o=AC.createOscillator(),g=AC.createGain();
    o.type=type;o.frequency.value=f;
    g.setValueAtTime(vol,t);g.exponentialRampToValueAtTime(0.0001,t+dur);
    o.connect(g);g.connect(AC.destination);o.start(t);o.stop(t+dur+0.02);
  }catch(e){}
}
const whoosh=()=>{tone(900,0.09,'sawtooth',0.03);tone(1400,0.07,'sine',0.03,0.03);};
const chipSnd=()=>{tone(2400,0.04,'square',0.05);tone(1800,0.05,'square',0.04,0.05);};
const winSnd=()=>{[523,659,784,1047].forEach((f,i)=>tone(f,0.15,'triangle',0.08,i*0.1));};

let pc=0, dc=0;
function cardHTML(c,i,n,isNew){
  const red=(c.s==='♥'||c.s==='♦');
  const r=((i-(n-1)/2)*5).toFixed(1), y=(Math.abs(i-(n-1)/2)*7).toFixed(0);
  return `<div class="bcard ${red?'bred':''} ${isNew?'deal':''}" style="--r:${r}deg;--y:${y}px">
    <div class="cnr tl"><b>${c.v}</b><span>${c.s}</span></div>
    <div class="pip">${c.s}</div>
    <div class="cnr br"><b>${c.v}</b><span>${c.s}</span></div></div>`;
}
function backHTML(){ return `<div class="bcard cardback"></div>`; }
function render(p,d,pv,dv,hide,reveal){
  const pel=document.getElementById('player');
  pel.innerHTML=p.map((c,i)=>{const n=i>=pc;if(n)setTimeout(whoosh,i*120);return cardHTML(c,i,p.length,n);}).join('');
  const del=document.getElementById('dealer');
  if(hide){
    del.innerHTML=cardHTML(d[0],0,2,0>=dc)+backHTML();
  } else {
    del.innerHTML=d.map((c,i)=>cardHTML(c,i,d.length,i>=dc)).join('');
    if(reveal){const cards=del.querySelectorAll('.bcard');if(cards[1])cards[1].classList.add('just-flipped');}
  }
  pc=p.length; dc=hide?1:d.length;
  document.getElementById('pval').textContent=pv!==undefined?`(${pv})`:'';
  document.getElementById('dval').textContent=(dv!==undefined&&!hide)?`(${dv})`:'';
}
function setBtns(playing){document.getElementById('hitBtn').disabled=!playing;document.getElementById('standBtn').disabled=!playing;document.getElementById('dealBtn').disabled=playing;}
function showBet(bet){
  const s=document.getElementById('betspot');
  s.classList.remove('to-player','to-dealer');s.style.opacity='1';
  document.getElementById('betamt').textContent=bet?bet+'€':'';
}
function moveChips(where){
  const s=document.getElementById('betspot');
  s.classList.add(where);
  setTimeout(()=>{document.getElementById('betamt').textContent='';s.classList.remove('to-player','to-dealer');s.style.opacity='1';},900);
}
async function newGame(){
  const bet=+document.getElementById('bet').value||25;
  try{
    const j=await api('/api/blackjack/new',{bet},{settle:'manual'});
    pc=0;dc=0;chipSnd();showBet(bet);settle(j.balance,600);
    if(j.status==='playing'){render(j.player,j.dealer,j.pval,null,true,false);document.getElementById('msg').textContent=`Ke ${j.pval}. Hit apo Stand?`;setBtns(true);}
    else{render(j.player,j.dealer,j.pval,j.dval,false,true);document.getElementById('msg').textContent=j.message;setBtns(false);
      if(j.status==='blackjack'){confetti(120);flyCoins('bjtable',14);moveChips('to-player');winSnd();setTimeout(()=>settle(j.balance,1200),900);toast(j.message,'win');}
      else{moveChips('to-player');settle(j.balance,600);}}
  }catch(e){toast(e.message,'lose');}
}
async function hit(){
  try{const j=await api('/api/blackjack/hit',{},{settle:'manual'});
    if(j.status==='playing'){document.getElementById('player').innerHTML=j.player.map((c,i)=>{const n=i>=pc;if(n)setTimeout(whoosh,i*120);return cardHTML(c,i,j.player.length,n);}).join('');pc=j.player.length;document.getElementById('pval').textContent=`(${j.pval})`;}
    else{render(j.player,j.dealer,j.pval,j.dval,false,true);document.getElementById('msg').textContent=j.message;setBtns(false);moveChips('to-dealer');settle(j.balance,700);toast(j.message,'lose');}
  }catch(e){toast(e.message,'lose');}
}
async function stand(){
  try{const j=await api('/api/blackjack/stand',{},{settle:'manual'});
    render(j.player,j.dealer,j.pval,j.dval,false,true);document.getElementById('msg').textContent=j.message;setBtns(false);
    if(j.status==='win'){confetti(80);flyCoins('bjtable',14);moveChips('to-player');winSnd();setTimeout(()=>settle(j.balance,1200),900);toast(j.message,'win');}
    else if(j.status==='push'){moveChips('to-player');settle(j.balance,600);toast(j.message,'info');}
    else{moveChips('to-dealer');settle(j.balance,700);toast(j.message,'lose');}
  }catch(e){toast(e.message,'lose');}
}
</script>
@endsection
