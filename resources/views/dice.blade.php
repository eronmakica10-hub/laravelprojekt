@extends('layouts.casino')
@section('title','Zari')

@section('content')
<style>
  #tray{background:radial-gradient(ellipse at 50% 35%,#14532d,#052e16 70%);border:8px solid #5b3a1a;border-radius:28px;box-shadow:0 25px 60px -15px #000, inset 0 0 60px rgba(0,0,0,.6);outline:2px solid #d4a017;perspective:700px;min-height:300px;display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden}
  #tray::after{content:'🎲 ROLL THE DICE 🎲';position:absolute;bottom:10px;left:0;right:0;text-align:center;color:rgba(255,255,255,.25);font-weight:900;letter-spacing:3px;font-size:12px}
  #cubeWrap{position:relative;z-index:2}
  #cubeWrap.bouncing{animation:dbounce .8s cubic-bezier(.3,1.4,.4,1)}
  @keyframes dbounce{0%{transform:translateY(-110px)}55%{transform:translateY(0)}70%{transform:translateY(-34px)}85%{transform:translateY(0)}93%{transform:translateY(-10px)}100%{transform:translateY(0)}}
  #diceShadow{position:absolute;left:50%;bottom:44px;width:130px;height:24px;transform:translateX(-50%);background:radial-gradient(ellipse,rgba(0,0,0,.55),transparent 70%);z-index:1;transition:transform .3s,opacity .3s}
  #tray.rolling #diceShadow{animation:shdw .3s infinite}
  @keyframes shdw{50%{transform:translateX(-50%) scale(.75);opacity:.6}}
  #cube{width:120px;height:120px;position:relative;transform-style:preserve-3d;transition:transform .85s cubic-bezier(.2,.75,.25,1.05)}
  #cube.shaking{animation:dshake .12s infinite;transition:none}
  @keyframes dshake{0%{transform:rotateX(20deg) rotateY(30deg) translateY(-6px)}50%{transform:rotateX(-25deg) rotateY(-40deg) translateY(6px)}100%{transform:rotateX(15deg) rotateY(45deg) translateY(-4px)}}
  .dface{position:absolute;width:120px;height:120px;background:linear-gradient(145deg,#ffffff,#cfd8e3);border-radius:20px;display:grid;grid-template-columns:repeat(3,1fr);grid-template-rows:repeat(3,1fr);padding:20px;box-shadow:inset 0 0 14px rgba(0,0,0,.25)}
  .pipdot{width:22px;height:22px;border-radius:50%;background:radial-gradient(circle at 35% 30%,#4b5563,#111827);place-self:center;box-shadow:inset 0 -2px 3px #000}
  .f1{transform:rotateY(0deg) translateZ(60px)}
  .f6{transform:rotateY(180deg) translateZ(60px)}
  .f3{transform:rotateY(90deg) translateZ(60px)}
  .f4{transform:rotateY(-90deg) translateZ(60px)}
  .f2{transform:rotateX(90deg) translateZ(60px)}
  .f5{transform:rotateX(-90deg) translateZ(60px)}
</style>

<div class="max-w-xl mx-auto text-center slidein">
<h1 class="text-4xl font-black neon-gold">🎲 ZARI I FATIT</h1>
<p class="text-white/60">Zgjidh parashikimin, tunde kupën dhe zbulo fatin!</p>

<div class="glass rounded-3xl mt-6 p-6" id="dicebox">
  <div id="tray">
    <div id="diceShadow"></div>
    <div id="cubeWrap">
      <div id="cube">
        <div class="dface f1"><span></span><span></span><span></span><span></span><span class="pipdot"></span><span></span><span></span><span></span><span></span></div>
        <div class="dface f2"><span></span><span></span><span class="pipdot"></span><span></span><span></span><span></span><span class="pipdot"></span><span></span><span></span></div>
        <div class="dface f3"><span></span><span></span><span class="pipdot"></span><span></span><span class="pipdot"></span><span></span><span class="pipdot"></span><span></span><span></span></div>
        <div class="dface f4"><span class="pipdot"></span><span></span><span class="pipdot"></span><span></span><span></span><span></span><span class="pipdot"></span><span></span><span class="pipdot"></span></div>
        <div class="dface f5"><span class="pipdot"></span><span></span><span class="pipdot"></span><span></span><span class="pipdot"></span><span></span><span class="pipdot"></span><span></span><span class="pipdot"></span></div>
        <div class="dface f6"><span class="pipdot"></span><span></span><span class="pipdot"></span><span class="pipdot"></span><span></span><span class="pipdot"></span><span class="pipdot"></span><span></span><span class="pipdot"></span></div>
      </div>
    </div>
  </div>
  <div class="text-2xl font-black mt-2">Doli: <span id="num" class="text-amber-300">?</span></div>
  <div id="msg" class="font-bold text-amber-200 h-7 mt-1">Zgjidh dhe tunde!</div>
  <div id="dhist" class="flex justify-center gap-1.5 mt-2 flex-wrap text-sm font-black min-h-[2rem]"></div>

  <div class="grid grid-cols-2 gap-2 mt-4 text-sm font-black" id="choices">
    <button data-c="low" class="py-3 rounded-xl bg-blue-600 outline outline-4 outline-amber-400">⬇️ LOW 1-3 (x1.9)</button>
    <button data-c="high" class="py-3 rounded-xl bg-green-600">⬆️ HIGH 4-6 (x1.9)</button>
    <button data-c="even" class="py-3 rounded-xl bg-purple-600">ÇIFT (x1.9)</button>
    <button data-c="odd" class="py-3 rounded-xl bg-pink-600">TEK (x1.9)</button>
  </div>
  <div class="mt-3 flex items-center justify-center gap-2">
    <button id="exactBtn" class="py-2 px-4 rounded-xl bg-white/10 border border-white/20 text-sm font-bold">🎯 Numër ekzakt (x5.5):</button>
    <select id="exact" class="bg-black/50 border border-white/20 rounded-xl px-3 py-2 font-black">
      <option value="1">1</option><option value="2">2</option><option value="3">3</option>
      <option value="4">4</option><option value="5">5</option><option value="6" selected>6</option>
    </select>
  </div>

  <div class="flex justify-center gap-2 mt-4 flex-wrap" id="bets">
    <button data-bet="5" class="chip bg-blue-600 text-sm">5</button>
    <button data-bet="10" class="chip bg-green-600 text-sm active">10</button>
    <button data-bet="25" class="chip bg-purple-600 text-sm">25</button>
    <button data-bet="50" class="chip bg-red-600 text-sm">50</button>
  </div>
  <button onclick="roll()" id="btn" class="btn-gold w-full mt-5 py-4 rounded-2xl text-xl">TUNDE 🎲</button>
</div>
</div>
@endsection

@section('scripts')
<script>
let bet=10, choice='low';
document.querySelectorAll('#bets .chip').forEach(c=>c.onclick=()=>{document.querySelectorAll('#bets .chip').forEach(x=>x.classList.remove('active'));c.classList.add('active');bet=+c.dataset.bet;});
document.querySelectorAll('#choices button').forEach(b=>b.onclick=()=>{
  document.querySelectorAll('#choices button').forEach(x=>x.classList.remove('outline','outline-4','outline-amber-400'));
  document.getElementById('exactBtn').classList.remove('outline','outline-4','outline-amber-400');
  b.classList.add('outline','outline-4','outline-amber-400');choice=b.dataset.c;
});
document.getElementById('exactBtn').onclick=()=>{
  document.querySelectorAll('#choices button').forEach(x=>x.classList.remove('outline','outline-4','outline-amber-400'));
  document.getElementById('exactBtn').classList.add('outline','outline-4','outline-amber-400');choice='exact';
};
// zëri: kërcitje + përplasje
let AC=null, noiseBuf=null;
function actx(){AC=AC||new (window.AudioContext||window.webkitAudioContext)();if(AC.state==='suspended')AC.resume();return AC;}
function rattle(){
  try{
    const ac=actx();
    if(!noiseBuf){noiseBuf=ac.createBuffer(1,ac.sampleRate*0.05,ac.sampleRate);const d=noiseBuf.getChannelData(0);for(let i=0;i<d.length;i++)d[i]=(Math.random()*2-1)*(1-i/d.length);}
    const s=ac.createBufferSource();s.buffer=noiseBuf;
    const g=ac.createGain();g.gain.value=0.25;
    s.connect(g);g.connect(ac.destination);s.start();
  }catch(e){}
}
function thud(){
  try{
    const ac=actx(),o=ac.createOscillator(),g=ac.createGain();
    o.type='sine';o.frequency.setValueAtTime(160,ac.currentTime);o.frequency.exponentialRampToValueAtTime(50,ac.currentTime+0.15);
    g.setValueAtTime(0.3,ac.currentTime);g.exponentialRampToValueAtTime(0.0001,ac.currentTime+0.2);
    o.connect(g);g.connect(ac.destination);o.start();o.stop(ac.currentTime+0.22);
  }catch(e){}
}
// rrotullimi final që e sjell faqen fituese përpara (+2 rrotullime të plota për efekt)
function finalRot(v){
  const base={1:[0,0],6:[0,180],3:[0,-90],4:[0,90],2:[-90,0],5:[90,0]}[v];
  return `rotateX(${base[0]+720}deg) rotateY(${base[1]+720}deg)`;
}
function pushDiceHist(v,won){
  const box=document.getElementById('dhist');
  const faces=['','⚀','⚁','⚂','⚃','⚄','⚅'];
  const s=document.createElement('span');
  s.className='w-9 h-9 rounded-xl flex items-center justify-center text-xl border '+(won?'bg-green-600/30 border-green-500':'bg-red-600/20 border-red-500/50');
  s.textContent=faces[v]||v;
  box.prepend(s);
  while(box.children.length>10)box.lastChild.remove();
}
async function roll(){
  const btn=document.getElementById('btn');btn.disabled=true;
  const cube=document.getElementById('cube'), wrap=document.getElementById('cubeWrap'), tray=document.getElementById('tray');
  document.getElementById('msg').textContent='Po tundet kupa... 🎲';
  let j;
  try{ j=await api('/api/dice/roll',{bet,choice,exact:+document.getElementById('exact').value},{settle:'manual'}); }
  catch(e){ toast(e.message,'lose'); btn.disabled=false; return; }
  // faza 1: tundja
  cube.classList.add('shaking'); tray.classList.add('rolling');
  const rattleIv=setInterval(rattle,110);
  setTimeout(()=>{
    // faza 2: rënia — faqja fituese vjen përpara
    clearInterval(rattleIv);
    cube.classList.remove('shaking'); tray.classList.remove('rolling');
    cube.style.transform=finalRot(j.roll);
    wrap.classList.remove('bouncing'); void wrap.offsetWidth; wrap.classList.add('bouncing');
    setTimeout(()=>thud(),620);
    setTimeout(()=>{
      document.getElementById('num').textContent=j.roll;
      pushDiceHist(j.roll,j.won);
      if(j.won){
        document.getElementById('msg').textContent=`🎉 Fitimi neto +${j.profit}€!`;
        confetti(70); flyCoins('dicebox',12);
        setTimeout(()=>settle(j.balance,1200),850);   // paratë kreditohen PASI bie zari
        toast(`Fituat neto +${j.profit}€!`,'win');
      } else {
        document.getElementById('msg').textContent=`Doli ${j.roll}. Humbët.`;
        settle(j.balance,700); toast('Humbët '+bet+'€','lose');
      }
      btn.disabled=false;
    },900);
  },1000);
}
</script>
@endsection
