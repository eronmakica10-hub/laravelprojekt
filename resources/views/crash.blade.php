@extends('layouts.casino')
@section('title','Crash')

@section('content')
<style>
  #bets .chip:disabled{opacity:.4;cursor:not-allowed;transform:none}
</style>
<div class="max-w-2xl mx-auto text-center slidein">
<h1 class="text-4xl font-black neon-gold">🚀 CRASH</h1>
<p class="text-white/60">Aeroplani ngrihet — bëj cashout para se të rrëzohet!</p>

<div id="hist" class="flex justify-center gap-1.5 mt-4 flex-wrap text-xs font-black"></div>

<div class="glass rounded-3xl mt-4 p-4 sm:p-6" id="crashbox">
  <div class="relative bg-black/60 rounded-2xl overflow-hidden border border-red-500/20">
    <canvas id="sky" class="w-full block" height="300"></canvas>
    <div class="absolute top-3 left-0 right-0 pointer-events-none">
      <div id="mult" class="text-5xl font-black tabular-nums" style="color:#fff;transition:transform .15s">1.00x</div>
      <div id="cmsg" class="font-bold text-white/60 h-6 mt-1 text-sm">Vendos bastin dhe starto!</div>
    </div>
  </div>

  <div class="flex justify-center gap-2 mt-4 flex-wrap" id="bets">
    <button data-bet="5" class="chip bg-blue-600 text-sm">5</button>
    <button data-bet="10" class="chip bg-green-600 text-sm active">10</button>
    <button data-bet="25" class="chip bg-purple-600 text-sm">25</button>
    <button data-bet="50" class="chip bg-red-600 text-sm">50</button>
  </div>
  <div class="grid grid-cols-2 gap-2 mt-4">
    <button onclick="start()" id="startBtn" class="btn-gold py-4 rounded-2xl text-xl">START 🚀</button>
    <button onclick="cashout()" id="outBtn" disabled class="py-4 rounded-2xl text-xl font-black bg-green-600 hover:bg-green-500 disabled:opacity-40">CASHOUT</button>
  </div>

  <div class="mt-4 text-left">
    <div class="text-xs font-black text-white/40 tracking-widest mb-1">🕓 RAUNDET E MIA</div>
    <div id="myrounds" class="space-y-1 text-sm max-h-36 overflow-y-auto"><div class="text-white/30 text-xs">Ende asnjë raund.</div></div>
  </div>
</div>
</div>
@endsection

@section('scripts')
<script>
const K = 0.00006, TAKEOFF = 900;
const MILESTONES = [1.5, 2, 3, 5, 10, 25, 50];
let bet=10, startTs=0, flying=false, raf=null, pollIv=null, crashedAt=null, samples=[];
let fly=null, rings=[], milestonesHit=new Set(), stars=[], clouds=[], mounts=[], flash=0, curRound=null, curBet=10;
let planeTrail=[];
let AC=null, engine=null, wind=null, windGain=null;
document.querySelectorAll('#bets .chip').forEach(c=>c.onclick=()=>{document.querySelectorAll('#bets .chip').forEach(x=>x.classList.remove('active'));c.classList.add('active');bet=+c.dataset.bet;});
const cv=document.getElementById('sky'), ctx=cv.getContext('2d');
function sizeCanvas(){ const w=cv.clientWidth||520; cv.width=w; cv.height=300; genSky(); draw(); }
window.addEventListener('resize', sizeCanvas);
function genSky(){
  stars=[]; for(let i=0;i<55;i++)stars.push({x:Math.random(),y:Math.random()*0.7,r:Math.random()*1.3+0.4});
  clouds=[]; for(let i=0;i<6;i++)clouds.push({x:Math.random()*1.2,y:Math.random()*0.45+0.05,s:20+Math.random()*30,spd:0.008+Math.random()*0.02});
  mounts=[]; for(let i=0;i<6;i++)mounts.push({x:Math.random(),w:0.15+Math.random()*0.25,h:26+Math.random()*46});
}
function histColor(v){ return v<2 ? '#3b82f6' : v<10 ? '#a855f7' : '#ec4899'; }
function renderHist(h){
  document.getElementById('hist').innerHTML=(h||[]).map(v=>
    `<span class="px-2.5 py-1 rounded-full" style="background:${histColor(v)}33;border:1px solid ${histColor(v)};color:${histColor(v)}">${Number(v).toFixed(2)}x</span>`
  ).join('') || '<span class="text-white/30">Ende asnjë raund</span>';
}
function pushRound(r){
  const box=document.getElementById('myrounds');
  const empty=box.querySelector('.text-white\\/30'); if(empty)empty.remove();
  const d=document.createElement('div');
  d.className='flex justify-between bg-black/40 rounded-xl px-3 py-1.5 slidein';
  d.innerHTML=`<span class="text-white/50">#${r.round} • ${r.bet}€</span>
    <span class="font-black">${r.label}</span>
    <span class="font-black ${r.profit>=0?'text-green-400':'text-red-400'}">${r.profit>=0?'+':''}${r.profit}€</span>`;
  box.prepend(d);
  while(box.children.length>6)box.lastChild.remove();
}
// --- zëri ---
function actx(){ try{ AC=AC||new (window.AudioContext||window.webkitAudioContext)(); if(AC.state==='suspended')AC.resume(); }catch(e){} return AC; }
function blip(freq,dur=0.08,vol=0.06,type='square'){
  try{ const ac=actx(); if(!ac)return;
    const o=ac.createOscillator(),g=ac.createGain();
    o.type=type; o.frequency.value=freq;
    g.setValueAtTime(vol,ac.currentTime); g.exponentialRampToValueAtTime(0.0001,ac.currentTime+dur);
    o.connect(g); g.connect(ac.destination); o.start(); o.stop(ac.currentTime+dur+0.02);
  }catch(e){}
}
function chaching(){ blip(880,0.1,0.08,'sine'); setTimeout(()=>blip(1320,0.16,0.08,'sine'),110); }
function crashNoise(){
  try{ const ac=actx(); if(!ac)return;
    const len=ac.sampleRate*0.5, buf=ac.createBuffer(1,len,ac.sampleRate), d=buf.getChannelData(0);
    for(let i=0;i<len;i++)d[i]=(Math.random()*2-1)*(1-i/len);
    const s=ac.createBufferSource(); s.buffer=buf;
    const g=ac.createGain(); g.gain.value=0.22;
    s.connect(g); g.connect(ac.destination); s.start();
  }catch(e){}
}
function engineStart(){
  try{ const ac=actx(); if(!ac)return; engineStop();
    const o=ac.createOscillator(),g=ac.createGain();
    o.type='sawtooth'; o.frequency.value=70; g.gain.value=0.022;
    o.connect(g); g.connect(ac.destination); o.start(); engine=o;
  }catch(e){}
}
function engineStop(){ try{ if(engine){engine.stop();} }catch(e){} engine=null; }
// zhurma e erës — rritet me shpejtësinë
function windStart(){
  try{ const ac=actx(); if(!ac)return; windStop();
    const len=ac.sampleRate*2, buf=ac.createBuffer(1,len,ac.sampleRate), d=buf.getChannelData(0);
    let v=0; for(let i=0;i<len;i++){ v=(v+(Math.random()*2-1)*0.03)*0.985; d[i]=v*2.4; }
    const s=ac.createBufferSource(); s.buffer=buf; s.loop=true;
    const g=ac.createGain(); g.gain.value=0;
    s.connect(g); g.connect(ac.destination); s.start(); wind=s; windGain=g;
  }catch(e){}
}
function windStop(){ try{ if(wind){wind.stop();} }catch(e){} wind=null; windGain=null; }
// --- vizatimi ---
function lerp(a,b,t){ return a+(b-a)*t; }
function mix(c1,c2,t){ return [lerp(c1[0],c2[0],t)|0, lerp(c1[1],c2[1],t)|0, lerp(c1[2],c2[2],t)|0]; }
function rgb(c){ return `rgb(${c[0]},${c[1]},${c[2]})`; }
// avioni i vërtetë: helikë që rrotullohet, rrota që mbyllen, drita navigimi
function drawPlane(x,y,rot,alpha,gear){
  ctx.save(); ctx.translate(x,y); ctx.rotate(rot); ctx.globalAlpha=Math.max(0,alpha); ctx.scale(1.45,1.45);
  // krahu i pasmë
  ctx.fillStyle='#94a3b8';
  ctx.beginPath(); ctx.moveTo(-2,-1); ctx.lineTo(4,-1); ctx.lineTo(-4,-11); ctx.lineTo(-9,-11); ctx.closePath(); ctx.fill();
  // bishti
  ctx.fillStyle='#cbd5e1';
  ctx.beginPath(); ctx.moveTo(-13,0); ctx.lineTo(-7,0); ctx.lineTo(-9,-8); ctx.lineTo(-13,-8); ctx.closePath(); ctx.fill();
  // rrotat (dalin vetëm në pistë)
  if(gear){
    ctx.strokeStyle='#475569'; ctx.lineWidth=1.6;
    ctx.beginPath(); ctx.moveTo(-4,3); ctx.lineTo(-4,7); ctx.moveTo(6,3); ctx.lineTo(6,7); ctx.stroke();
    ctx.fillStyle='#0f172a';
    ctx.beginPath(); ctx.arc(-4,8.4,2,0,7); ctx.fill();
    ctx.beginPath(); ctx.arc(6,8.4,2,0,7); ctx.fill();
  }
  // trupi metalik
  const g=ctx.createLinearGradient(0,-4,0,4);
  g.addColorStop(0,'#ffffff'); g.addColorStop(0.55,'#dbe3ee'); g.addColorStop(1,'#8fa0b8');
  ctx.fillStyle=g;
  ctx.beginPath(); ctx.ellipse(1,0,13,3.6,0,0,7); ctx.fill();
  // shirit i kuq anësor
  ctx.fillStyle='#e11d48';
  ctx.fillRect(-9,-0.4,16,1.4);
  // hunda
  ctx.beginPath(); ctx.ellipse(11,0,3.4,3.2,0,0,7); ctx.fill();
  // dritaret
  ctx.fillStyle='#0f172a';
  [-2,1.5,5].forEach(wx=>{ ctx.beginPath(); ctx.arc(wx,-1.4,1.1,0,7); ctx.fill(); });
  // helika që rrotullohet (elips i turbullt)
  const wob=Math.sin(Date.now()/55)*1.4;
  ctx.strokeStyle='rgba(255,255,255,.4)'; ctx.lineWidth=2;
  ctx.beginPath(); ctx.ellipse(14.8,0,1.7,7.5+wob,0,0,7); ctx.stroke();
  ctx.strokeStyle='rgba(255,255,255,.18)';
  ctx.beginPath(); ctx.ellipse(14.8,0,3,9,0,0,7); ctx.stroke();
  // drita navigimi (pulsojnë)
  if(Math.floor(Date.now()/400)%2===0){
    ctx.fillStyle='#ef4444'; ctx.beginPath(); ctx.arc(-12,-7,1.4,0,7); ctx.fill();
    ctx.fillStyle='#22c55e'; ctx.beginPath(); ctx.arc(4,-10,1.2,0,7); ctx.fill();
  }
  ctx.restore();
}
// gjurma e bardhë pas avionit
function drawTrail(){
  planeTrail=planeTrail.filter(t=>t.a>0.03);
  planeTrail.forEach(t=>{
    ctx.beginPath(); ctx.arc(t.x,t.y,5*t.a+1,0,7);
    ctx.fillStyle=`rgba(255,255,255,${(0.3*t.a).toFixed(2)})`; ctx.fill();
    t.a*=0.93;
  });
}
function draw(){
  const W=cv.width,H=cv.height,pad=14;
  const last=samples[samples.length-1];
  const curM=last?last.m:1;
  const el=flying?Date.now()-startTs:0;
  // qielli: ditë -> natë
  const night=Math.min(1,Math.log(Math.max(1,curM))/Math.log(20));
  const sky=ctx.createLinearGradient(0,0,0,H);
  sky.addColorStop(0,rgb(mix([16,44,84],[4,8,20],night)));
  sky.addColorStop(1,rgb(mix([9,19,38],[3,6,14],night)));
  ctx.fillStyle=sky; ctx.fillRect(0,0,W,H);
  if(night>0.05){
    ctx.fillStyle=`rgba(255,255,255,${(0.8*night).toFixed(2)})`;
    stars.forEach(s=>{ ctx.beginPath(); ctx.arc(s.x*W,s.y*H,s.r,0,7); ctx.fill(); });
  }
  // dielli / hëna
  if(night<0.5){
    ctx.save(); ctx.globalAlpha=1-night*2;
    ctx.shadowColor='#f5c518'; ctx.shadowBlur=30;
    ctx.fillStyle='#f5c518'; ctx.beginPath(); ctx.arc(W-52,48,20,0,7); ctx.fill(); ctx.restore();
  }else{
    ctx.save(); ctx.globalAlpha=(night-0.5)*2;
    ctx.shadowColor='#e2e8f0'; ctx.shadowBlur=22;
    ctx.fillStyle='#e2e8f0'; ctx.beginPath(); ctx.arc(W-52,48,17,0,7); ctx.fill();
    ctx.shadowBlur=0; ctx.fillStyle='rgba(100,116,139,.6)';
    ctx.beginPath(); ctx.arc(W-58,42,4,0,7); ctx.fill();
    ctx.beginPath(); ctx.arc(W-46,54,3,0,7); ctx.fill();
    ctx.restore();
  }
  // retë me hije, lëvizëse
  clouds.forEach(c=>{
    const x=(((c.x+el*c.spd/60000)%1.3)-0.15)*W, y=c.y*H;
    ctx.fillStyle='rgba(10,18,34,.55)';
    ctx.beginPath();
    ctx.ellipse(x,y+3,c.s,c.s*0.42,0,0,7);
    ctx.ellipse(x-c.s*0.7,y+7,c.s*0.6,c.s*0.3,0,0,7);
    ctx.ellipse(x+c.s*0.7,y+7,c.s*0.6,c.s*0.3,0,0,7);
    ctx.fill();
    ctx.fillStyle='rgba(255,255,255,.17)';
    ctx.beginPath();
    ctx.ellipse(x,y-3,c.s*0.85,c.s*0.34,0,0,7);
    ctx.ellipse(x-c.s*0.55,y+1,c.s*0.5,c.s*0.24,0,0,7);
    ctx.ellipse(x+c.s*0.55,y+1,c.s*0.5,c.s*0.24,0,0,7);
    ctx.fill();
  });
  // malet në horizont
  ctx.fillStyle=`rgba(8,14,28,${(0.9-night*0.35).toFixed(2)})`;
  mounts.forEach(mt=>{
    const bx=mt.x*W;
    ctx.beginPath();
    ctx.moveTo(bx,H); ctx.lineTo(bx+mt.w*W/2,H-mt.h); ctx.lineTo(bx+mt.w*W,H);
    ctx.closePath(); ctx.fill();
  });
  // pista gjatë nisjes
  const takingOff=flying&&el<TAKEOFF;
  if(takingOff){
    ctx.fillStyle='#1c2433'; ctx.fillRect(0,H-34,W,34);
    ctx.fillStyle='rgba(255,255,255,.5)';
    for(let x=((el/8)%48)-48;x<W;x+=48)ctx.fillRect(x,H-19,26,3);
    const px=pad+30+(el/TAKEOFF)*(W*0.3);
    const lift=Math.max(0,(el-TAKEOFF+250)/250);
    drawPlane(px,H-48-lift*10,lift*-0.35,1,true);
    planeTrail.push({x:px-14,y:H-48-lift*10,a:0.6});
    drawTrail();
    if(flash>0.01){ ctx.fillStyle=`rgba(239,68,68,${flash.toFixed(2)})`; ctx.fillRect(0,0,W,H); flash*=0.9; }
    return;
  }
  // rrjeta
  ctx.strokeStyle='rgba(255,255,255,.07)'; ctx.lineWidth=1;
  for(let i=1;i<6;i++){ ctx.beginPath(); ctx.moveTo(W*i/6,0); ctx.lineTo(W*i/6,H); ctx.stroke(); }
  for(let i=1;i<4;i++){ ctx.beginPath(); ctx.moveTo(0,H*i/4); ctx.lineTo(W,H*i/4); ctx.stroke(); }
  if(!samples.length){ if(flash>0.01){ctx.fillStyle=`rgba(239,68,68,${flash})`;ctx.fillRect(0,0,W,H);flash*=0.9;} return; }
  const maxM=Math.max(2,last.m*1.15);
  const X=t=>pad+(t/(t+2500))*(W-2*pad);
  const Y=m=>H-pad-(Math.log(Math.max(1,m))/Math.log(maxM))*(H-2*pad-30);
  const crashed=!!crashedAt;
  const grad=ctx.createLinearGradient(0,0,0,H);
  grad.addColorStop(0,crashed?'rgba(239,68,68,.45)':'rgba(239,68,68,.35)');
  grad.addColorStop(1,'rgba(239,68,68,0)');
  ctx.beginPath(); ctx.moveTo(X(samples[0].t),H-pad);
  samples.forEach(s=>ctx.lineTo(X(s.t),Y(s.m)));
  ctx.lineTo(X(last.t),H-pad); ctx.closePath(); ctx.fillStyle=grad; ctx.fill();
  ctx.beginPath();
  samples.forEach((s,i)=>{ const x=X(s.t),y=Y(s.m); i?ctx.lineTo(x,y):ctx.moveTo(x,y); });
  ctx.strokeStyle=crashed?'#ef4444':'#f87171'; ctx.lineWidth=3;
  ctx.shadowColor='#ef4444'; ctx.shadowBlur=12; ctx.stroke(); ctx.shadowBlur=0;
  rings=rings.filter(r=>r.a>0.03);
  rings.forEach(r=>{
    ctx.beginPath(); ctx.arc(r.x,r.y,r.r,0,7);
    ctx.strokeStyle=`rgba(74,222,128,${r.a.toFixed(2)})`; ctx.lineWidth=2; ctx.stroke();
    r.r+=3.5; r.a*=0.92;
  });
  ctx.textAlign='center'; ctx.textBaseline='middle';
  drawTrail();
  if(fly){
    planeTrail.push({x:fly.x,y:fly.y,a:0.6});
    drawPlane(fly.x,fly.y,fly.rot,fly.a,false);
  }else if(!crashed){
    let rot=0;
    if(samples.length>3){
      const a=samples[samples.length-3];
      rot=Math.atan2(Y(last.m)-Y(a.m),X(last.t)-X(a.t));
      rot=Math.max(-1.0,Math.min(0.35,rot));
    }
    const pxx=X(last.t), pyy=Y(last.m)-10;
    planeTrail.push({x:pxx,y:pyy,a:0.6});
    drawPlane(pxx,pyy,rot,1,el<TAKEOFF+500);
  }
  // te rrëzimi avioni ka fluturu larg — kurba e kuqe mbetet
  if(flash>0.01){ ctx.fillStyle=`rgba(239,68,68,${flash.toFixed(2)})`; ctx.fillRect(0,0,W,H); flash*=0.9; }
}
function fmt(m){ return m.toFixed(2)+'x'; }
function popMult(){
  const me=document.getElementById('mult');
  me.style.transform='scale(1.22)';
  setTimeout(()=>{ me.style.transform='scale(1)'; },160);
}
function tick(){
  if(!flying) return;
  const el=Date.now()-startTs;
  if(el<TAKEOFF){ draw(); raf=requestAnimationFrame(tick); return; }
  const m=Math.floor(Math.exp(K*el)*100)/100;
  samples.push({t:el,m});
  if(samples.length>400)samples.shift();
  const W=cv.width,H=cv.height,pad=14;
  const maxM=Math.max(2,m*1.15);
  const X=t=>pad+(t/(t+2500))*(W-2*pad);
  const Y=mm=>H-pad-(Math.log(Math.max(1,mm))/Math.log(maxM))*(H-2*pad-30);
  MILESTONES.forEach(ms=>{
    if(m>=ms && !milestonesHit.has(ms)){
      milestonesHit.add(ms);
      rings.push({x:X(el),y:Y(m),r:6,a:0.9});
      blip(480+ms*36); popMult();
    }
  });
  if(engine){ try{ engine.frequency.value=70+m*9; }catch(e){} }
  if(windGain){ try{ windGain.gain.value=Math.min(0.09,0.004+m*0.005); }catch(e){} }
  draw();
  const me=document.getElementById('mult');
  me.textContent=fmt(m); me.style.color='#fff';
  document.getElementById('outBtn').textContent='CASHOUT '+(bet*m).toFixed(2)+'€';
  raf=requestAnimationFrame(tick);
}
async function poll(){
  try{
    const j=await api('/api/crash/state',{},{settle:'manual'});
    renderHist(j.history);
    if(j.round)curRound=j.round;
    if(j.crashed){ stopFly(); onBusted(j.crash); }
  }catch(e){}
}
function stopFly(){ flying=false; cancelAnimationFrame(raf); clearInterval(pollIv); engineStop(); windStop(); lockBets(false); document.getElementById('startBtn').disabled=false; document.getElementById('outBtn').disabled=true; }
function lockBets(v){ document.querySelectorAll('#bets .chip').forEach(c=>c.disabled=v); }
async function start(){
  const sb=document.getElementById('startBtn'); sb.disabled=true;
  crashedAt=null; samples=[]; fly=null; rings=[]; milestonesHit=new Set(); flash=0; planeTrail=[];
  document.getElementById('mult').style.color='#fff';
  document.getElementById('mult').textContent='1.00x';
  document.getElementById('cmsg').textContent='Nisja... ✈️';
  draw();
  try{
    const j=await api('/api/crash/start',{bet},{settle:'manual'});
    startTs=j.start; curRound=j.round; curBet=j.bet; settle(j.balance,400);
  }
  catch(e){ toast(e.message,'lose'); sb.disabled=false; return; }
  actx(); engineStart(); windStart();
  flying=true; lockBets(true); // basti zgjidhet para nisjes — kyçet gjatë fluturimit
  document.getElementById('cmsg').textContent='Po fluturon... bëj cashout në kohë! 🏃';
  document.getElementById('outBtn').disabled=false;
  tick(); pollIv=setInterval(poll,400);
}
function flyAway(){
  const W=cv.width,H=cv.height,pad=14;
  const last=samples[samples.length-1];
  const maxM=Math.max(2,last.m*1.15);
  const X=t=>pad+(t/(t+2500))*(W-2*pad);
  const Y=m=>H-pad-(Math.log(Math.max(1,m))/Math.log(maxM))*(H-2*pad-30);
  let rot=0;
  if(samples.length>3){
    const a=samples[samples.length-3];
    rot=Math.max(-1.0,Math.min(0.35,Math.atan2(Y(last.m)-Y(a.m),X(last.t)-X(a.t))));
  }
  fly={x:X(last.t),y:Y(last.m)-10,rot,vx:W*1.15,vy:-H*0.6,a:1};
  (function anim(){
    if(!fly) return;
    fly.x+=fly.vx*0.016; fly.y+=fly.vy*0.016; fly.a-=0.016/0.7;
    draw();
    if(fly.a>0 && fly.x<W+60)requestAnimationFrame(anim);
    else{ fly=null; draw(); }
  })();
}
function onBusted(crash){
  crashedAt=crash;
  const el=Date.now()-startTs;
  if(el>=TAKEOFF)samples.push({t:el,m:crash});
  flash=0.45; crashNoise(); flyAway();
  pushRound({round:curRound,bet:curBet,label:'RRËZIM @ '+fmt(crash),profit:-curBet});
  const me=document.getElementById('mult');
  me.textContent='RRËZIM @ '+fmt(crash); me.style.color='#ef4444';
  document.getElementById('cmsg').textContent=`Avioni fluturoi larg te ${fmt(crash)}!`;
  fetchBalance();
  toast(`U rrëzua te ${fmt(crash)}!`,'lose');
}
async function fetchBalance(){
  try{ const r=await fetch('/api/balance'); const j=await r.json(); settle(j.balance,700); }catch(e){}
}
async function cashout(){
  if(!flying) return;
  stopFly();
  try{
    const j=await api('/api/crash/cashout',{},{settle:'manual'});
    renderHist(j.history);
    if(j.busted){ onBusted(j.crash); }
    else{
      pushRound({round:j.round,bet:curBet,label:'Cashout @ '+fmt(j.mult),profit:j.profit});
      const me=document.getElementById('mult');
      me.textContent=fmt(j.mult); me.style.color='#4ade80';
      document.getElementById('cmsg').textContent=`Cashout te ${fmt(j.mult)}! +${j.profit}€`;
      chaching(); confetti(90); flyCoins('crashbox',12);
      setTimeout(()=>settle(j.balance,1200),800);
      toast(`Fituat neto +${j.profit}€!`,'win');
    }
  }catch(e){ toast(e.message,'lose'); }
}
sizeCanvas();
api('/api/crash/state',{},{settle:'manual'}).then(j=>renderHist(j.history)).catch(()=>{});
</script>
@endsection
