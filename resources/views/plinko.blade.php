@extends('layouts.casino')
@section('title','Plinko')

@section('content')
<div class="max-w-2xl mx-auto text-center slidein">
<h1 class="text-4xl font-black neon-gold">🔴 PLINKO</h1>
<p class="text-white/60">Lëshoje topin nëpër kunja — ku bie, aq fiton!</p>

<div class="glass rounded-3xl mt-6 p-4 sm:p-6" id="plinkobox">
  <div class="flex justify-center gap-2 flex-wrap text-sm font-black items-center">
    <span class="text-white/60">Rreziku:</span>
    <div id="risks" class="flex gap-2">
      <button data-r="low" class="px-4 py-2 rounded-xl bg-white/10">🟢 Ulët</button>
      <button data-r="medium" class="px-4 py-2 rounded-xl bg-emerald-600 text-white">🟡 Mesëm</button>
      <button data-r="high" class="px-4 py-2 rounded-xl bg-white/10">🔴 Lartë</button>
    </div>
    <span class="text-white/60 ml-2">Rreshta:</span>
    <div id="rowsbtns" class="flex gap-2">
      <button data-rows="8" class="px-4 py-2 rounded-xl bg-white/10">8</button>
      <button data-rows="12" class="px-4 py-2 rounded-xl bg-emerald-600 text-white">12</button>
      <button data-rows="16" class="px-4 py-2 rounded-xl bg-white/10">16</button>
    </div>
  </div>

  <div id="board" class="relative rounded-2xl mt-4 overflow-hidden border border-white/10" style="background:radial-gradient(ellipse at 50% 0%,#14243f,#05090f 75%)">
    <canvas id="pcv" class="w-full block"></canvas>
  </div>
  <div id="pmsg" class="font-bold text-amber-200 h-7 mt-2">Zgjidh dhe lësho topin! 🔴</div>
  <div id="recent" class="flex justify-center gap-1.5 mt-1 flex-wrap text-xs font-black min-h-[1.5rem]"></div>

  <div class="flex justify-center gap-2 mt-3 flex-wrap" id="bets">
    <button data-bet="5" class="chip bg-blue-600 text-sm">5</button>
    <button data-bet="10" class="chip bg-green-600 text-sm active">10</button>
    <button data-bet="25" class="chip bg-purple-600 text-sm">25</button>
    <button data-bet="50" class="chip bg-red-600 text-sm">50</button>
  </div>
  <button onclick="drop()" id="dropBtn" class="btn-gold w-full mt-4 py-4 rounded-2xl text-xl">LËSHO TOPIN 🔴</button>
</div>
</div>
@endsection

@section('scripts')
<script>
let bet=10, risk='medium', rows=12, table=[], dropping=false;
const ROW_H=26, TOP_PAD=34, BIN_H=42;
let AC=null;
// pamja e bordit: llogaritet nga madhësia
let LW=480, gap=28, cx=240, pegs=[], binRects=[], binsTop=0;
let ball=null, trail=[], ripples=[], flashes={}, binGlow=null, floatTxt=null, squash=1;

document.querySelectorAll('#bets .chip').forEach(c=>c.onclick=()=>{document.querySelectorAll('#bets .chip').forEach(x=>x.classList.remove('active'));c.classList.add('active');bet=+c.dataset.bet;});
function selBtns(box,attr,val){
  document.querySelectorAll('#'+box+' button').forEach(x=>{x.classList.remove('bg-emerald-600','text-white');x.classList.add('bg-white/10');});
  const b=document.querySelector(`#${box} button[data-${attr}="${val}"]`);
  if(b){b.classList.add('bg-emerald-600','text-white');b.classList.remove('bg-white/10');}
}
document.querySelectorAll('#risks button').forEach(b=>b.onclick=()=>{ if(dropping)return; risk=b.dataset.r; selBtns('risks','r',risk); loadTable(); });
document.querySelectorAll('#rowsbtns button').forEach(b=>b.onclick=()=>{ if(dropping)return; rows=+b.dataset.rows; selBtns('rowsbtns','rows',rows); loadTable(); });
function binColor(m){ return m>=10?'#ec4899':m>=2?'#a855f7':m>=1?'#3b82f6':'#6b7280'; }
// --- zëri ---
function actx(){ try{ AC=AC||new (window.AudioContext||window.webkitAudioContext)(); if(AC.state==='suspended')AC.resume(); }catch(e){} return AC; }
function knock(step){
  try{ const ac=actx(); if(!ac)return;
    const o=ac.createOscillator(),g=ac.createGain();
    o.type='triangle'; o.frequency.value=(340+step*30)*(1+(Math.random()-0.5)*0.06);
    g.setValueAtTime(0.11,ac.currentTime); g.exponentialRampToValueAtTime(0.0001,ac.currentTime+0.08);
    o.connect(g); g.connect(ac.destination); o.start(); o.stop(ac.currentTime+0.1);
  }catch(e){}
}
function whoosh(){
  try{ const ac=actx(); if(!ac)return;
    const o=ac.createOscillator(),g=ac.createGain();
    o.type='sine'; o.frequency.setValueAtTime(650,ac.currentTime); o.frequency.exponentialRampToValueAtTime(180,ac.currentTime+0.25);
    g.setValueAtTime(0.05,ac.currentTime); g.exponentialRampToValueAtTime(0.0001,ac.currentTime+0.28);
    o.connect(g); g.connect(ac.destination); o.start(); o.stop(ac.currentTime+0.3);
  }catch(e){}
}
function winJingle(big){
  const seq=big?[523,659,784,1046,1318]:[660,880,1320];
  seq.forEach((f,i)=>setTimeout(()=>{ try{ const ac=actx(); if(!ac)return; const o=ac.createOscillator(),g=ac.createGain(); o.type='sine'; o.frequency.value=f; g.setValueAtTime(0.09,ac.currentTime); g.exponentialRampToValueAtTime(0.0001,ac.currentTime+0.2); o.connect(g); g.connect(ac.destination); o.start(); o.stop(ac.currentTime+0.22);}catch(e){} },i*110));
}
function loseThud(){
  try{ const ac=actx(); if(!ac)return;
    const o=ac.createOscillator(),g=ac.createGain();
    o.type='sine'; o.frequency.setValueAtTime(180,ac.currentTime); o.frequency.exponentialRampToValueAtTime(60,ac.currentTime+0.2);
    g.setValueAtTime(0.12,ac.currentTime); g.exponentialRampToValueAtTime(0.0001,ac.currentTime+0.25);
    o.connect(g); g.connect(ac.destination); o.start(); o.stop(ac.currentTime+0.27);
  }catch(e){}
}
// --- canvas ---
const cv=document.getElementById('pcv'), ctx=cv.getContext('2d');
function layout(){
  const board=document.getElementById('board');
  LW=board.clientWidth||480;
  gap=Math.min(34,(LW-30)/(rows+1));
  cx=LW/2;
  const H=TOP_PAD+rows*ROW_H+14+BIN_H+10;
  const dpr=window.devicePixelRatio||1;
  cv.width=LW*dpr; cv.height=H*dpr; cv.style.height=H+'px';
  ctx.setTransform(dpr,0,0,dpr,0,0);
  pegs=[];
  for(let r=0;r<rows;r++)for(let i=0;i<=r;i++)pegs.push({r,i,x:cx+(i-r/2)*gap,y:TOP_PAD+r*ROW_H});
  binsTop=TOP_PAD+rows*ROW_H+14;
  const bw=(LW-16)/ (rows+1);
  binRects=table.map((m,b)=>({b,m,x:8+b*bw,y:binsTop,w:bw-4,h:BIN_H,cx:8+b*bw+(bw-4)/2}));
}
function pegAt(r,i){ return pegs.find(p=>p.r===r&&p.i===i); }
function drawStatic(){
  ctx.clearRect(0,0,LW,cv.height);
  // kunjat çeliku
  pegs.forEach(p=>{
    const f=flashes[p.r+':'+p.i];
    const R=f?7:3.4;
    if(f){ ctx.save(); ctx.shadowColor='#fff'; ctx.shadowBlur=12; }
    const g=ctx.createRadialGradient(p.x-1,p.y-1,0.5,p.x,p.y,R);
    if(f){ g.addColorStop(0,'#ffffff'); g.addColorStop(1,'#f5c518'); }
    else{ g.addColorStop(0,'#e8edf5'); g.addColorStop(0.55,'#8b94a7'); g.addColorStop(1,'#3c4356'); }
    ctx.beginPath(); ctx.arc(p.x,p.y,R,0,7); ctx.fillStyle=g; ctx.fill();
    if(f)ctx.restore();
  });
  // kutitë
  binRects.forEach(r=>{
    const glow=binGlow&&binGlow.bin===r.b;
    ctx.save();
    if(glow){ ctx.shadowColor='#22c55e'; ctx.shadowBlur=18; }
    ctx.fillStyle=glow?'#16a34a':binColor(r.m)+'2e';
    ctx.strokeStyle=glow?'#4ade80':binColor(r.m);
    ctx.lineWidth=glow?2:1;
    roundRect(r.x,r.y,r.w,r.h,7); ctx.fill(); ctx.stroke();
    ctx.restore();
    ctx.fillStyle=glow?'#fff':binColor(r.m);
    ctx.font=`900 ${rows>12?10:12}px Inter,sans-serif`;
    ctx.textAlign='center'; ctx.textBaseline='middle';
    ctx.fillText((r.m>=100?Math.round(r.m):r.m)+'x',r.cx,r.y+r.h/2+0.5);
  });
}
function roundRect(x,y,w,h,r){
  ctx.beginPath();
  ctx.moveTo(x+r,y); ctx.arcTo(x+w,y,x+w,y+h,r); ctx.arcTo(x+w,y+h,x,y+h,r);
  ctx.arcTo(x,y+h,x,y,r); ctx.arcTo(x,y,x+w,y,r); ctx.closePath();
}
function drawBall(){
  if(!ball)return;
  // gjurma
  for(let i=0;i<trail.length;i++){
    const t=trail[i], a=(i+1)/trail.length;
    ctx.beginPath(); ctx.arc(t.x,t.y,7*a,0,7);
    ctx.fillStyle=`rgba(245,197,24,${(0.28*a).toFixed(2)})`; ctx.fill();
  }
  // hija
  ctx.beginPath(); ctx.ellipse(ball.x+2.5,ball.y+4.5,7,3.4,0,0,7);
  ctx.fillStyle='rgba(0,0,0,.4)'; ctx.fill();
  // trupi metalik i artë
  ctx.save();
  ctx.translate(ball.x,ball.y); ctx.scale(1,squash);
  ctx.shadowColor='rgba(245,197,24,.8)'; ctx.shadowBlur=10;
  const g=ctx.createRadialGradient(-2.5,-3,1,0,0,8.5);
  g.addColorStop(0,'#fffbe6'); g.addColorStop(0.45,'#f5c518'); g.addColorStop(1,'#92400e');
  ctx.beginPath(); ctx.arc(0,0,8,0,7); ctx.fillStyle=g; ctx.fill();
  ctx.shadowBlur=0;
  ctx.fillStyle='rgba(255,255,255,.85)';
  ctx.beginPath(); ctx.arc(-2.5,-3,2,0,7); ctx.fill();
  ctx.restore();
}
function drawRipples(){
  ripples=ripples.filter(r=>r.a>0.03);
  ripples.forEach(r=>{
    ctx.beginPath(); ctx.arc(r.x,r.y,r.r,0,7);
    ctx.strokeStyle=`rgba(255,255,255,${r.a.toFixed(2)})`; ctx.lineWidth=1.5; ctx.stroke();
    r.r+=2.6; r.a*=0.88;
  });
}
function drawFloat(){
  if(!floatTxt)return;
  ctx.save(); ctx.globalAlpha=Math.max(0,floatTxt.a);
  ctx.font='900 17px Inter,sans-serif'; ctx.textAlign='center';
  ctx.fillStyle=floatTxt.color; ctx.shadowColor='#000'; ctx.shadowBlur=6;
  ctx.fillText(floatTxt.text,floatTxt.x,floatTxt.y);
  ctx.restore();
  floatTxt.y-=0.7; floatTxt.a-=0.012;
  if(floatTxt.a<=0)floatTxt=null;
}
function frame(){
  drawStatic(); drawRipples(); drawBall(); drawFloat();
}
async function loadTable(){
  try{
    const j=await api('/api/plinko/table',{risk,rows},{settle:'manual'});
    table=j.table; layout(); frame();
  }catch(e){ toast(e.message,'lose'); }
}
window.addEventListener('resize',()=>{ if(!dropping){ layout(); frame(); } });
const sleep=ms=>new Promise(r=>setTimeout(r,ms));
const easeOut=p=>1-Math.pow(1-p,3);
function addRecent(mult,profit){
  const box=document.getElementById('recent');
  const s=document.createElement('span');
  s.className='px-2 py-0.5 rounded-full '+(profit>0?'bg-green-500/20 text-green-300':'bg-red-500/20 text-red-300');
  s.textContent=mult+'x';
  box.prepend(s);
  while(box.children.length>10)box.lastChild.remove();
}
async function drop(){
  if(dropping) return;
  const btn=document.getElementById('dropBtn'); btn.disabled=true; dropping=true;
  actx();
  let j;
  try{ j=await api('/api/plinko/drop',{bet,risk,rows},{settle:'manual'}); }
  catch(e){ toast(e.message,'lose'); btn.disabled=false; dropping=false; return; }
  table=j.table; layout();
  document.getElementById('pmsg').textContent='Topi po bie... 🔴';
  whoosh();
  // pikat e rrugës me gravitet: shpejt poshtë, kthesë e butë anash + kërcim i vogël
  const pts=[{x:cx,y:TOP_PAD-ROW_H*0.9}];
  let drift=0;
  for(let k=0;k<j.path.length;k++){
    if(j.path[k]==='R')drift++; else drift--;
    pts.push({x:cx+drift*gap/2, y:TOP_PAD+(k+0.5)*ROW_H});
  }
  const bin=binRects[j.bin];
  pts.push({x:bin.cx, y:bin.y+bin.h/2});
  ball={x:pts[0].x,y:pts[0].y}; trail=[]; squash=1; flashes={}; binGlow=null; floatTxt=null;
  frame();
  let rights=0;
  for(let s=1;s<pts.length;s++){
    const a=pts[s-1], b=pts[s];
    const SEG=s<pts.length-1?70:120;
    const t0=performance.now();
    await new Promise(res=>{
      (function step(){
        const p=Math.min(1,(performance.now()-t0)/SEG);
        const x=a.x+(b.x-a.x)*easeOut(p);
        const y=a.y+(b.y-a.y)*(p*p)-Math.sin(p*Math.PI)*2.5;
        ball.x=x; ball.y=y;
        trail.push({x,y}); if(trail.length>14)trail.shift();
        frame();
        if(p<1)requestAnimationFrame(step); else res();
      })();
    });
    if(s<pts.length-1){
      if(j.path[s-1]==='R')rights++;
      const peg=pegAt(s-1,rights);
      if(peg){ flashes[(s-1)+':'+rights]=1; ripples.push({x:peg.x,y:peg.y,r:4,a:0.8}); }
      knock(s-1);
    }
  }
  // ulja: squash + shkëlqim kutie
  squash=0.55; frame(); await sleep(110); squash=1; frame();
  binGlow={bin:j.bin};
  floatTxt={x:bin.cx,y:bin.y-8,text:(j.profit>0?'+':'')+j.profit.toFixed(2)+'€',color:j.profit>0?'#4ade80':'#f87171',a:1};
  frame();
  const big=j.mult>=10;
  addRecent(j.mult,j.profit);
  if(j.profit>0){
    document.getElementById('pmsg').textContent=`🎉 ${j.mult}x! Fitimi neto +${j.profit}€`;
    winJingle(big); confetti(big?160:60); flyCoins('plinkobox',10);
    setTimeout(()=>settle(j.balance,1100),750);
    toast(`Fituat neto +${j.profit}€!`,'win');
  }else{
    document.getElementById('pmsg').textContent=`${j.mult}x — humbje neto ${j.profit}€`;
    loseThud();
    settle(j.balance,700); toast(`Humbët ${-j.profit}€`,'lose');
  }
  setTimeout(()=>{ binGlow=null; if(!dropping)frame(); },1200);
  btn.disabled=false; dropping=false;
}
loadTable();
</script>
@endsection
