@extends('layouts.casino')
@section('title','Chicken Road')

@section('content')
<style>
  #road{position:relative;border-radius:18px;overflow:hidden;border:1px solid rgba(148,163,184,.2)}
  #chcv{width:100%;display:block}
  @keyframes rshake{0%,100%{transform:translateX(0)}20%{transform:translateX(-9px)}40%{transform:translateX(8px)}60%{transform:translateX(-6px)}80%{transform:translateX(4px)}}
  .shaking{animation:rshake .45s}
  #bets .chip:disabled, #diffs button:disabled{opacity:.4;cursor:not-allowed}
  .multcell{transition:all .2s}
  .multcell.done{background:#059669!important;color:#fff!important}
  .multcell.now{background:#10b981!important;color:#fff!important;transform:scale(1.12);box-shadow:0 0 12px #10b981}
</style>
<div class="max-w-3xl mx-auto text-center slidein">
<h1 class="text-4xl font-black neon-gold">🐔 CHICKEN ROAD</h1>
<p class="text-white/60">Kalo korsitë me pulën — cashout para se ta kap makina! 🚗💨</p>

<div class="glass rounded-3xl mt-6 p-4 sm:p-6" id="chickbox">
  <div class="flex justify-center gap-2 flex-wrap text-sm font-black items-center" id="diffs">
    <button data-d="easy" class="px-4 py-2 rounded-xl bg-white/10">🟢 Lehtë</button>
    <button data-d="medium" class="px-4 py-2 rounded-xl bg-emerald-600 text-white">🟡 Mesëm</button>
    <button data-d="hard" class="px-4 py-2 rounded-xl bg-white/10">🟠 Vështirë</button>
    <button data-d="hardcore" class="px-4 py-2 rounded-xl bg-white/10">🔴 Ekstrem</button>
  </div>

  <div id="road" class="mt-4">
    <canvas id="chcv"></canvas>
  </div>
  <div id="mults" class="flex gap-1 mt-2"></div>

  <div class="mt-3 font-black text-lg">Korsia: <span id="clane" class="text-amber-300">0/15</span> • x<span id="cmult">1.00</span> • Cashout: <span id="cval" class="text-green-400">0.00€</span></div>
  <div id="cmsg" class="font-bold text-amber-200 h-7">Zgjidh vështirësinë dhe starto! 🐔</div>
  <div id="chist" class="flex justify-center gap-1.5 mt-1 flex-wrap text-xs font-black min-h-[1.5rem]"></div>

  <div class="flex justify-center gap-2 mt-3 flex-wrap" id="bets">
    <button data-bet="5" class="chip bg-blue-600 text-sm">5</button>
    <button data-bet="10" class="chip bg-green-600 text-sm active">10</button>
    <button data-bet="25" class="chip bg-purple-600 text-sm">25</button>
    <button data-bet="50" class="chip bg-red-600 text-sm">50</button>
  </div>
  <div class="grid grid-cols-3 gap-2 mt-4">
    <button onclick="start()" id="startBtn" class="btn-gold py-4 rounded-2xl text-xl">START 🐔</button>
    <button onclick="go()" id="goBtn" disabled class="py-4 rounded-2xl text-xl font-black bg-amber-500 hover:bg-amber-400 text-black disabled:opacity-40">KALO ➡️</button>
    <button onclick="cashout()" id="outBtn" disabled class="py-4 rounded-2xl text-xl font-black bg-green-600 hover:bg-green-500 disabled:opacity-40">CASHOUT</button>
  </div>
</div>
</div>
@endsection

@section('scripts')
<script>
let bet=10, diff='medium', lanes=[], playing=false, lane=0, busy=false;
const LANES_N=15, ROAD_H=210;
let AC=null;
// gjendja vizuale
let LW=600, startW=56, finishW=44, laneW=30, chickY=0, chickX=0;
let cars=[], parts=[], hop=null, winHop=0, vignette=0, walkPhase=0, boomDone=false;
let chickAlive=true;
function actx(){ try{ AC=AC||new (window.AudioContext||window.webkitAudioContext)(); if(AC.state==='suspended')AC.resume(); }catch(e){} return AC; }
function snd(f,dur=0.1,type='sine',vol=0.08,slide=null){
  try{ const ac=actx(); if(!ac)return;
    const t=ac.currentTime,o=ac.createOscillator(),g=ac.createGain();
    o.type=type; o.frequency.setValueAtTime(f,t);
    if(slide)o.frequency.exponentialRampToValueAtTime(slide,t+dur);
    g.setValueAtTime(vol,t); g.exponentialRampToValueAtTime(0.0001,t+dur);
    o.connect(g); g.connect(ac.destination); o.start(t); o.stop(t+dur+0.02);
  }catch(e){}
}
const cluck=()=>{ snd(650,0.07,'square',0.05,950); setTimeout(()=>snd(800,0.08,'square',0.05,1100),80); };
function horn(){
  snd(185,0.4,'sawtooth',0.12,140); snd(147,0.4,'sawtooth',0.1,110);
  try{ const ac=actx(); if(!ac)return;
    const len=ac.sampleRate*0.4,buf=ac.createBuffer(1,len,ac.sampleRate),d=buf.getChannelData(0);
    for(let i=0;i<len;i++)d[i]=(Math.random()*2-1)*(1-i/len);
    const s=ac.createBufferSource(); s.buffer=buf;
    const g=ac.createGain(); g.gain.value=0.2;
    s.connect(g); g.connect(ac.destination); s.start();
  }catch(e){}
}
const winSnd=()=>{ snd(880,0.1,'sine',0.08); setTimeout(()=>snd(1320,0.16,'sine',0.08),110); };
document.querySelectorAll('#bets .chip').forEach(c=>c.onclick=()=>{document.querySelectorAll('#bets .chip').forEach(x=>x.classList.remove('active'));c.classList.add('active');bet=+c.dataset.bet;});
document.querySelectorAll('#diffs button').forEach(b=>b.onclick=()=>{
  if(playing)return;
  document.querySelectorAll('#diffs button').forEach(x=>{x.classList.remove('bg-emerald-600','text-white');x.classList.add('bg-white/10');});
  b.classList.add('bg-emerald-600','text-white');b.classList.remove('bg-white/10');
  diff=b.dataset.d; loadTable();
});
function lockConfig(v){
  document.querySelectorAll('#bets .chip').forEach(b=>b.disabled=v);
  document.querySelectorAll('#diffs button').forEach(b=>b.disabled=v);
}
// --- canvas ---
const cv=document.getElementById('chcv'), ctx=cv.getContext('2d');
const CAR_COLORS=['#e11d48','#3b82f6','#f59e0b','#8b5cf6','#22c55e','#e2e8f0','#f97316'];
function layout(){
  const road=document.getElementById('road');
  LW=road.clientWidth||600;
  const dpr=window.devicePixelRatio||1;
  cv.width=LW*dpr; cv.height=ROAD_H*dpr; cv.style.height=ROAD_H+'px';
  ctx.setTransform(dpr,0,0,dpr,0,0);
  laneW=(LW-startW-finishW)/LANES_N;
  chickY=ROAD_H-36;
  chickX=laneX(lane);
  cars=[];
  for(let k=0;k<LANES_N;k++){
    const n=1+(k%2);
    for(let c=0;c<n;c++){
      cars.push({
        lane:k,
        x:startW+(k+0.5)*laneW,
        y:Math.random()*ROAD_H,
        v:(60+((k*37+c*53)%80))*( (k+c)%2?1:-1 ),
        color:CAR_COLORS[(k*3+c)%CAR_COLORS.length],
        wob:Math.random()*7
      });
    }
  }
}
function laneX(k){
  if(k<=0)return startW/2;
  return startW+(Math.min(k,LANES_N)-0.5)*laneW;
}
function drawRoad(){
  // asfalti
  const g=ctx.createLinearGradient(0,0,0,ROAD_H);
  g.addColorStop(0,'#262e3b'); g.addColorStop(0.5,'#1b2230'); g.addColorStop(1,'#121722');
  ctx.fillStyle=g; ctx.fillRect(0,0,LW,ROAD_H);
  // zona e nisjes (bari)
  ctx.fillStyle='#14532d'; ctx.fillRect(0,0,startW,ROAD_H);
  ctx.fillStyle='rgba(255,255,255,.75)';
  for(let y=6;y<ROAD_H;y+=22)ctx.fillRect(startW-7,y,5,11);
  // vija e finishit (karo)
  for(let yy=0;yy<ROAD_H;yy+=12)for(let xx=0;xx<finishW;xx+=12){
    ctx.fillStyle=((xx+yy)/12)%2?'#0f172a':'#f8fafc';
    ctx.fillRect(LW-finishW+xx,yy,Math.min(12,finishW-xx),12);
  }
  // ndarësit e korsive
  ctx.fillStyle='rgba(255,255,255,.14)';
  for(let k=1;k<LANES_N;k++){
    const x=startW+k*laneW;
    for(let y=8;y<ROAD_H;y+=26)ctx.fillRect(x-1,y,2,13);
  }
  // vija e verdhë qendrore e rrugës
  ctx.fillStyle='rgba(245,197,24,.35)';
  ctx.fillRect(0,ROAD_H/2-1.5,LW,3);
}
// makinë nga lart: trup, xhama, drita
function drawCar(c,now){
  const w=17,h=34;
  const y=c.y+Math.sin(now/300+c.wob)*1.5;
  ctx.save(); ctx.translate(c.x,y);
  if(c.v<0)ctx.rotate(Math.PI);
  ctx.fillStyle='rgba(0,0,0,.45)';
  roundR(-w/2+2,-h/2+3,w,h,6); ctx.fill();
  const g=ctx.createLinearGradient(-w/2,0,w/2,0);
  g.addColorStop(0,shade(c.color,-25)); g.addColorStop(0.5,c.color); g.addColorStop(1,shade(c.color,-25));
  ctx.fillStyle=g;
  roundR(-w/2,-h/2,w,h,6); ctx.fill();
  ctx.fillStyle='rgba(15,23,42,.85)';
  roundR(-w/2+3,-h/2+7,w-6,8,3); ctx.fill();
  roundR(-w/2+3,h/2-12,w-6,6,3); ctx.fill();
  ctx.fillStyle='#fef9c3';
  ctx.fillRect(-w/2+2,-h/2-2,4,2.5); ctx.fillRect(w/2-6,-h/2-2,4,2.5);
  ctx.fillStyle='#ef4444';
  ctx.fillRect(-w/2+2,h/2-0.5,4,2); ctx.fillRect(w/2-6,h/2-0.5,4,2);
  ctx.restore();
}
function shade(hex,amt){
  const n=parseInt(hex.slice(1),16);
  const r=Math.max(0,Math.min(255,(n>>16)+amt)),g=Math.max(0,Math.min(255,((n>>8)&255)+amt)),b=Math.max(0,Math.min(255,(n&255)+amt));
  return `rgb(${r},${g},${b})`;
}
function roundR(x,y,w,h,r){
  ctx.beginPath();
  ctx.moveTo(x+r,y); ctx.arcTo(x+w,y,x+w,y+h,r); ctx.arcTo(x+w,y+h,x,y+h,r);
  ctx.arcTo(x,y+h,x,y,r); ctx.arcTo(x,y,x+w,y,r); ctx.closePath();
}
// pula e vizatuar: trup, krahë që rrahin, këmbë që ecin, kreshtë
function drawChicken(x,y,hopP,flap){
  const lift=Math.sin(Math.min(1,hopP)*Math.PI)*22;
  const yy=y-lift;
  ctx.save(); ctx.translate(x,yy);
  // hija
  ctx.fillStyle='rgba(0,0,0,.35)';
  ctx.beginPath(); ctx.ellipse(0,14-lift*0.3,11,3.6,0,0,7); ctx.fill();
  // këmbët (alternojnë kur kërcen)
  const lp=hopP>0&&hopP<1?Math.sin(hopP*Math.PI*2)*4:Math.sin(walkPhase)*2;
  ctx.strokeStyle='#f59e0b'; ctx.lineWidth=2; ctx.lineCap='round';
  ctx.beginPath(); ctx.moveTo(-3,8); ctx.lineTo(-3+lp,13); ctx.moveTo(4,8); ctx.lineTo(4-lp,13); ctx.stroke();
  // bishti
  ctx.fillStyle='#e2e8f0';
  [[-11,-4,-0.5],[-13,-1,-0.9],[-11,2,-1.2]].forEach(t=>{
    ctx.save(); ctx.translate(t[0],t[1]); ctx.rotate(t[2]);
    ctx.beginPath(); ctx.ellipse(0,0,5.5,2.6,0,0,7); ctx.fill(); ctx.restore();
  });
  // trupi
  const g=ctx.createRadialGradient(-3,-4,2,0,0,15);
  g.addColorStop(0,'#ffffff'); g.addColorStop(0.7,'#f1f5f9'); g.addColorStop(1,'#cbd5e1');
  ctx.fillStyle=g;
  ctx.beginPath(); ctx.ellipse(0,0,13,9.5,0,0,7); ctx.fill();
  // krahu që rreh
  const wa=-0.5-Math.abs(Math.sin(flap))*1.1;
  ctx.save(); ctx.translate(-2,-1); ctx.rotate(wa);
  ctx.fillStyle='#e2e8f0';
  ctx.beginPath(); ctx.ellipse(-4,0,8,4.4,0.35,0,7); ctx.fill(); ctx.restore();
  // koka
  ctx.fillStyle='#ffffff';
  ctx.beginPath(); ctx.arc(10,-8,6.4,0,7); ctx.fill();
  ctx.strokeStyle='#cbd5e1'; ctx.lineWidth=1;
  ctx.beginPath(); ctx.arc(10,-8,6.4,0,7); ctx.stroke();
  // kreshta
  ctx.fillStyle='#e11d48';
  [[7,-14],[10,-15.4],[13,-14]].forEach(p=>{ ctx.beginPath(); ctx.arc(p[0],p[1],2.2,0,7); ctx.fill(); });
  // sqepi + syri
  ctx.fillStyle='#f59e0b';
  ctx.beginPath(); ctx.moveTo(15.5,-9); ctx.lineTo(20,-7); ctx.lineTo(15.5,-5); ctx.closePath(); ctx.fill();
  ctx.fillStyle='#0f172a';
  ctx.beginPath(); ctx.arc(11.5,-9,1.5,0,7); ctx.fill();
  ctx.fillStyle='#fff';
  ctx.beginPath(); ctx.arc(12,-9.5,0.5,0,7); ctx.fill();
  ctx.restore();
}
function drawParts(dt){
  parts=parts.filter(p=>p.life>0);
  parts.forEach(p=>{
    p.x+=p.vx*dt; p.y+=p.vy*dt; p.vy+=p.g*dt; p.rot+=p.vr*dt; p.life-=dt;
    ctx.save(); ctx.translate(p.x,p.y); ctx.rotate(p.rot); ctx.globalAlpha=Math.max(0,Math.min(1,p.life*2));
    if(p.kind==='fire'){
      const g=ctx.createRadialGradient(0,0,0,0,0,p.r);
      g.addColorStop(0,'#fefce8'); g.addColorStop(0.5,p.color); g.addColorStop(1,'rgba(0,0,0,0)');
      ctx.fillStyle=g; ctx.beginPath(); ctx.arc(0,0,p.r,0,7); ctx.fill();
    }else if(p.kind==='smoke'){
      ctx.fillStyle=p.color; ctx.beginPath(); ctx.arc(0,0,p.r*(1.6-p.life),0,7); ctx.fill();
    }else if(p.kind==='feather'){
      ctx.fillStyle=p.color; ctx.beginPath(); ctx.ellipse(0,0,p.r,p.r*0.55,0,0,7); ctx.fill();
    }else if(p.kind==='coin'){
      ctx.fillStyle='#f5c518'; ctx.beginPath(); ctx.arc(0,0,p.r,0,7); ctx.fill();
      ctx.fillStyle='#92400e'; ctx.font='bold 7px Inter,sans-serif'; ctx.textAlign='center'; ctx.textBaseline='middle';
      ctx.fillText('€',0,0.5);
    }else if(p.kind==='dust'){
      ctx.fillStyle=p.color; ctx.beginPath(); ctx.arc(0,0,p.r,0,7); ctx.fill();
    }
    ctx.restore();
  });
}
function boom(x,y){
  for(let i=0;i<26;i++){
    const a=Math.random()*Math.PI*2, sp=60+Math.random()*180;
    parts.push({kind:'fire',x,y,vx:Math.cos(a)*sp,vy:Math.sin(a)*sp-60,g:260,r:4+Math.random()*6,rot:0,vr:0,life:0.5+Math.random()*0.4,color:['#f97316','#ef4444','#f5c518'][i%3]});
  }
  for(let i=0;i<10;i++){
    parts.push({kind:'smoke',x:x+(Math.random()-0.5)*16,y:y-6,vx:(Math.random()-0.5)*30,vy:-40-Math.random()*40,g:-30,r:5+Math.random()*5,rot:0,vr:0,life:0.9+Math.random()*0.5,color:'rgba(100,116,139,.55)'});
  }
  for(let i=0;i<12;i++){
    const a=Math.random()*Math.PI*2, sp=40+Math.random()*120;
    parts.push({kind:'feather',x,y,vx:Math.cos(a)*sp,vy:Math.sin(a)*sp-90,g:170,r:2.5+Math.random()*2,rot:Math.random()*7,vr:(Math.random()-0.5)*12,life:1+Math.random()*0.7,color:'#f8fafc'});
  }
}
function dust(x,y){
  for(let i=0;i<7;i++){
    parts.push({kind:'dust',x:x+(Math.random()-0.5)*14,y:y+10,vx:(Math.random()-0.5)*50,vy:-20-Math.random()*30,g:60,r:2+Math.random()*2.5,rot:0,vr:0,life:0.4+Math.random()*0.3,color:'rgba(203,213,225,.5)'});
  }
}
function coins(x,y,n=12){
  for(let i=0;i<n;i++){
    parts.push({kind:'coin',x:x+(Math.random()-0.5)*20,y,vx:(Math.random()-0.5)*90,vy:-90-Math.random()*90,g:300,r:4,rot:0,vr:0,life:0.9+Math.random()*0.4});
  }
}
let lastT=0;
function loop(t){
  const dt=Math.min(0.05,(t-lastT)/1000||0.016); lastT=t;
  // makinat lëvizin gjithmonë (rruga e gjallë)
  cars.forEach(c=>{
    c.y+=c.v*dt;
    if(c.v>0&&c.y>ROAD_H+44)c.y=-44;
    if(c.v<0&&c.y<-44)c.y=ROAD_H+44;
  });
  walkPhase+=dt*7;
  ctx.clearRect(0,0,LW,ROAD_H);
  drawRoad();
  cars.forEach(c=>drawCar(c,t));
  // pula
  if(chickAlive){
    let hx=chickX, hopP=0;
    if(hop){
      const p=Math.min(1,(performance.now()-hop.t0)/hop.dur);
      hx=hop.from+(hop.to-hop.from)*(1-Math.pow(1-p,2.2));
      hopP=p;
      if(p>=1){ hop=null; dust(hx,chickY); }
    }
    if(winHop>0){ winHop-=dt; hx=chickX; hopP=(winHop%0.4)/0.4; }
    drawChicken(hx,chickY,hopP,performance.now()/90);
  }
  drawParts(dt);
  if(vignette>0.01){
    ctx.fillStyle=`rgba(239,68,68,${vignette.toFixed(2)})`; ctx.fillRect(0,0,LW,ROAD_H);
    vignette*=0.9;
  }
  requestAnimationFrame(loop);
}
function lanePosX(k){ return k<=0?startW/2:startW+(Math.min(k,LANES_N)-0.5)*laneW; }
function renderMults(){
  const box=document.getElementById('mults'); box.innerHTML='';
  lanes.forEach((m,i)=>{
    const d=document.createElement('div');
    d.className='multcell flex-1 rounded-lg py-1 font-black bg-white/5 border border-white/10';
    d.style.fontSize='10px'; d.dataset.lane=i+1;
    d.textContent=(m>=100?Math.round(m):m)+'x';
    box.appendChild(d);
  });
  paintLanes();
}
function paintLanes(){
  document.querySelectorAll('#mults .multcell').forEach(c=>{
    const k=+c.dataset.lane;
    c.classList.toggle('done',k<lane);
    c.classList.toggle('now',k===lane&&playing);
  });
}
window.addEventListener('resize',()=>{ layout(); chickX=lanePosX(lane); });
const sleep=ms=>new Promise(r=>setTimeout(r,ms));
function pushHist(label,profit){
  const box=document.getElementById('chist');
  const s=document.createElement('span');
  s.className='px-2 py-0.5 rounded-full '+(profit>=0?'bg-green-500/20 text-green-300':'bg-red-500/20 text-red-300');
  s.textContent=label;
  box.prepend(s);
  while(box.children.length>8)box.lastChild.remove();
}
async function loadTable(){
  try{
    const j=await api('/api/chicken/table',{difficulty:diff},{settle:'manual'});
    lanes=j.lanes; renderMults();
  }catch(e){ toast(e.message,'lose'); }
}
async function start(){
  const sb=document.getElementById('startBtn'); sb.disabled=true;
  try{
    const j=await api('/api/chicken/start',{bet,difficulty:diff},{settle:'manual'});
    lanes=j.lanes; lane=0; playing=true; chickAlive=true; boomDone=false; lockConfig(true);
    settle(j.balance,400);
    chickX=lanePosX(0); renderMults();
    document.getElementById('clane').textContent='0/15';
    document.getElementById('cmult').textContent='1.00';
    document.getElementById('cval').textContent='0.00€';
    document.getElementById('cmsg').textContent=`Shtyp KALO — ${j.diffName}! 🐔💨`;
    document.getElementById('goBtn').disabled=false;
    document.getElementById('outBtn').disabled=true;
    document.getElementById('outBtn').textContent='CASHOUT';
  }catch(e){ toast(e.message,'lose'); }
  sb.disabled=false;
}
async function go(){
  if(!playing||busy) return;
  busy=true; document.getElementById('goBtn').disabled=true;
  let j;
  try{ j=await api('/api/chicken/go',{},{settle:'manual'}); }
  catch(e){ toast(e.message,'lose'); busy=false; document.getElementById('goBtn').disabled=false; return; }
  const from=chickX, to=lanePosX(j.lane);
  hop={from,to,t0:performance.now(),dur:430};
  cluck();
  await sleep(470);
  lane=j.lane;
  chickX=to;
  paintLanes();
  if(j.hit){
    horn();
    chickAlive=false; boomDone=true;
    boom(to,chickY-6);
    vignette=0.45;
    const road=document.getElementById('road');
    road.classList.remove('shaking'); void road.offsetWidth; road.classList.add('shaking');
    playing=false; lockConfig(false);
    document.getElementById('cmsg').textContent=j.message;
    pushHist(`K${lane} • -${bet}€`,-bet);
    settle(j.balance,700); toast(j.message,'lose');
    document.getElementById('outBtn').disabled=true;
  }else{
    document.getElementById('clane').textContent=`${lane}/15`;
    document.getElementById('cmult').textContent=j.mult.toFixed(2);
    document.getElementById('cval').textContent=j.cashout.toFixed(2)+'€';
    document.getElementById('outBtn').disabled=false;
    document.getElementById('outBtn').textContent='CASHOUT '+j.cashout.toFixed(2)+'€';
    if(!j.finished){ document.getElementById('cmsg').textContent=`Korsia ${lane} — vazhdo apo cashout?`; document.getElementById('goBtn').disabled=false; }
    else document.getElementById('cmsg').textContent='🏁 Arrite në fund! Bëj CASHOUT!';
  }
  busy=false;
}
async function cashout(){
  if(!playing) return;
  try{
    const j=await api('/api/chicken/cashout',{},{settle:'manual'});
    playing=false; lockConfig(false);
    winHop=1.2;
    coins(chickX,chickY-20,14);
    document.getElementById('cmsg').textContent=j.message;
    pushHist(`K${j.lane} • +${j.profit}€`,j.profit);
    winSnd(); confetti(90); flyCoins('chickbox',12);
    setTimeout(()=>settle(j.balance,1200),800);
    toast(j.message,'win');
    document.getElementById('goBtn').disabled=true;
    document.getElementById('outBtn').disabled=true;
  }catch(e){ toast(e.message,'lose'); }
}
layout(); chickX=lanePosX(0); loadTable();
requestAnimationFrame(loop);
</script>
@endsection
