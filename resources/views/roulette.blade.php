@extends('layouts.casino')
@section('title','Ruleta')

@section('content')
<div class="max-w-4xl mx-auto slidein">
<h1 class="text-4xl font-black neon-gold text-center">🎡 RULETA EVROPIANE</h1>
<p class="text-white/60 text-center">Vë bast, rrotullo rrotën si në kazino të vërtetë dhe fito deri 36x!</p>

<div class="grid md:grid-cols-2 gap-6 mt-6">
  <div class="glass rounded-3xl p-6 text-center" id="wheelbox">
    <div class="relative inline-block">
      <canvas id="wheel" width="320" height="320" class="mx-auto max-w-full" style="filter:drop-shadow(0 20px 40px rgba(0,0,0,.7))"></canvas>
    </div>
    <div id="result" class="mt-3 text-5xl font-black h-16">🎡</div>
    <div id="rmsg" class="font-bold text-amber-200 h-6"></div>
    <div id="rhist" class="flex justify-center gap-1.5 mt-2 flex-wrap text-sm font-black min-h-[1.75rem]"></div>
    <div class="text-[11px] text-white/40 mt-1">🔊 Mbaje zërin ndezur për efektin e topit</div>
  </div>

  <div class="glass rounded-3xl p-6">
    <div class="text-sm text-white/50">Basti (€):</div>
    <div class="flex gap-2 mt-1 flex-wrap" id="bets">
      <button data-bet="5" class="chip bg-blue-600 text-sm">5</button>
      <button data-bet="10" class="chip bg-green-600 text-sm active">10</button>
      <button data-bet="25" class="chip bg-purple-600 text-sm">25</button>
      <button data-bet="50" class="chip bg-red-600 text-sm">50</button>
      <button data-bet="100" class="chip bg-amber-600 text-sm">100</button>
    </div>
    <div class="text-sm text-white/50 mt-4">Zgjidh llojin:</div>
    <div class="grid grid-cols-2 gap-2 mt-1" id="types">
      <button data-type="red" class="py-3 rounded-xl bg-red-600 font-black active-type outline outline-4 outline-amber-400">🔴 E Kuqe x2</button>
      <button data-type="black" class="py-3 rounded-xl bg-gray-900 border border-white/20 font-black">⚫ E Zezë x2</button>
      <button data-type="even" class="py-3 rounded-xl bg-blue-600 font-black">Çift x2</button>
      <button data-type="odd" class="py-3 rounded-xl bg-indigo-600 font-black">Tek x2</button>
      <button data-type="green" class="py-3 rounded-xl bg-green-600 font-black">🟢 0 (x36)</button>
      <button data-type="number" class="py-3 rounded-xl bg-amber-600 text-black font-black">🔢 Numër (x36)</button>
    </div>
    <div id="numPick" class="hidden mt-3">
      <label class="text-sm">Numri (0-36):</label>
      <input id="numInput" type="number" min="0" max="36" value="7" class="w-full mt-1 bg-black/50 border border-white/20 rounded-xl px-4 py-2 text-white font-black">
    </div>
    <button onclick="play()" id="btn" class="btn-gold w-full mt-5 py-4 rounded-2xl text-xl">RROTULLO 🎡</button>
  </div>
</div>
</div>
@endsection

@section('scripts')
<script>
let bet=10, type='red';
document.querySelectorAll('#bets .chip').forEach(c=>c.onclick=()=>{document.querySelectorAll('#bets .chip').forEach(x=>x.classList.remove('active'));c.classList.add('active');bet=+c.dataset.bet;});
document.querySelectorAll('#types button').forEach(b=>b.onclick=()=>{
  document.querySelectorAll('#types button').forEach(x=>x.classList.remove('outline','outline-4','outline-amber-400'));
  b.classList.add('outline','outline-4','outline-amber-400'); type=b.dataset.type;
  document.getElementById('numPick').classList.toggle('hidden', type!=='number');
});

// ===== RROTA REALISTE =====
const cv=document.getElementById('wheel'), ctx=cv.getContext('2d');
const CX=160, CY=160;
const nums=[0,32,15,19,4,21,2,25,17,34,6,27,13,36,11,30,8,23,10,5,24,16,33,1,20,14,31,9,22,18,29,7,28,12,35,3,26];
const reds=new Set([1,3,5,7,9,12,14,16,18,19,21,23,25,27,30,32,34,36]);
let angle=0, ballAngle=0.6, ballR=126, winIdx=-1;
const ARC=Math.PI*2/nums.length;

function draw(){
  ctx.clearRect(0,0,320,320);
  // hija e jashtme
  ctx.beginPath(); ctx.arc(CX,CY+6,152,0,7); ctx.fillStyle='rgba(0,0,0,.5)'; ctx.fill();
  // korniza prej druri
  let wood=ctx.createRadialGradient(CX,CY,128,CX,CY,152);
  wood.addColorStop(0,'#7a4e26'); wood.addColorStop(.45,'#5b3a1a'); wood.addColorStop(.8,'#33200d'); wood.addColorStop(1,'#1c1106');
  ctx.beginPath(); ctx.arc(CX,CY,152,0,7); ctx.fillStyle=wood; ctx.fill();
  // vija ari dekorative në dru
  ctx.beginPath(); ctx.arc(CX,CY,146,0,7); ctx.strokeStyle='rgba(251,191,36,.55)'; ctx.lineWidth=1.5; ctx.stroke();
  ctx.beginPath(); ctx.arc(CX,CY,134,0,7); ctx.strokeStyle='rgba(251,191,36,.55)'; ctx.lineWidth=1.5; ctx.stroke();
  // bulonat metalikë
  for(let i=0;i<8;i++){
    const a=i*Math.PI/4+angle*0.02, x=CX+Math.cos(a)*140, y=CY+Math.sin(a)*140;
    let bg=ctx.createRadialGradient(x-1.5,y-1.5,.5,x,y,5.5);
    bg.addColorStop(0,'#fef9c3'); bg.addColorStop(.5,'#d4a017'); bg.addColorStop(1,'#713f12');
    ctx.beginPath(); ctx.arc(x,y,5,0,7); ctx.fillStyle=bg; ctx.fill();
  }
  // kanali i topit (groove)
  ctx.beginPath(); ctx.arc(CX,CY,126,0,7); ctx.strokeStyle='#160d04'; ctx.lineWidth=14; ctx.stroke();
  ctx.beginPath(); ctx.arc(CX,CY,126,0,7); ctx.strokeStyle='rgba(251,191,36,.25)'; ctx.lineWidth=1; ctx.stroke();
  // xhepat me numra
  nums.forEach((n,i)=>{
    const a0=angle+i*ARC, a1=a0+ARC;
    ctx.beginPath();
    ctx.moveTo(CX+Math.cos(a0)*74, CY+Math.sin(a0)*74);
    ctx.arc(CX,CY,119,a0,a1);
    ctx.lineTo(CX+Math.cos(a1)*74, CY+Math.sin(a1)*74);
    ctx.closePath();
    ctx.fillStyle = n===0 ? '#15803d' : reds.has(n) ? '#a31621' : '#101418';
    ctx.fill();
    ctx.strokeStyle='rgba(212,175,55,.9)'; ctx.lineWidth=1; ctx.stroke();
    // numri
    ctx.save(); ctx.translate(CX,CY); ctx.rotate(a0+ARC/2);
    ctx.fillStyle='#f8fafc'; ctx.font='bold 11px Outfit,sans-serif'; ctx.textAlign='center'; ctx.textBaseline='middle';
    ctx.shadowColor='rgba(0,0,0,.8)'; ctx.shadowBlur=2;
    ctx.fillText(n, 97, 0); ctx.restore();
  });
  // xhepi fitues ndizet (kur topi bie brenda)
  if(winIdx>=0){
    const wa0=angle+winIdx*ARC, wa1=wa0+ARC;
    ctx.beginPath(); ctx.arc(CX,CY,96,wa0,wa1);
    ctx.strokeStyle='#fbbf24'; ctx.lineWidth=46; ctx.shadowColor='#fbbf24'; ctx.shadowBlur=18; ctx.stroke(); ctx.shadowBlur=0;
  }
  // unaza e brendshme metalike
  let steel=ctx.createLinearGradient(CX-70,CY-70,CX+70,CY+70);
  steel.addColorStop(0,'#e7e5e4'); steel.addColorStop(.5,'#a8a29e'); steel.addColorStop(1,'#57534e');
  ctx.beginPath(); ctx.arc(CX,CY,74,0,7); ctx.strokeStyle=steel; ctx.lineWidth=5; ctx.stroke();
  // koni qendror
  let cone=ctx.createRadialGradient(CX-12,CY-12,6,CX,CY,70);
  cone.addColorStop(0,'#44403c'); cone.addColorStop(.7,'#1c1917'); cone.addColorStop(1,'#0c0a09');
  ctx.beginPath(); ctx.arc(CX,CY,70,0,7); ctx.fillStyle=cone; ctx.fill();
  // krahët rrotullues (cross)
  ctx.strokeStyle='rgba(212,175,55,.55)'; ctx.lineWidth=6; ctx.lineCap='round';
  for(let i=0;i<4;i++){
    const a=angle*0.6+i*Math.PI/2;
    ctx.beginPath(); ctx.moveTo(CX+Math.cos(a)*30, CY+Math.sin(a)*30); ctx.lineTo(CX+Math.cos(a)*62, CY+Math.sin(a)*62); ctx.stroke();
  }
  // qendra e artë
  let gold=ctx.createRadialGradient(CX-5,CY-5,2,CX,CY,27);
  gold.addColorStop(0,'#fefce8'); gold.addColorStop(.5,'#f59e0b'); gold.addColorStop(1,'#92400e');
  ctx.beginPath(); ctx.arc(CX,CY,26,0,7); ctx.fillStyle=gold; ctx.fill();
  ctx.fillStyle='#451a03'; ctx.font='black 19px sans-serif'; ctx.textAlign='center'; ctx.textBaseline='middle';
  ctx.fillText('★',CX,CY+1);
  // topi (i bardhë, me shkëlqim + hije) — bie nga kanali në xhep
  const bx=CX+Math.cos(ballAngle)*ballR, by=CY+Math.sin(ballAngle)*ballR;
  const bs=ballR<110?5.5:7; // topi "futet" pak kur bie në xhep
  ctx.beginPath(); ctx.arc(bx+2.5,by+3.5,bs,0,7); ctx.fillStyle='rgba(0,0,0,.5)'; ctx.fill();
  let ball=ctx.createRadialGradient(bx-2.5,by-2.5,1,bx,by,bs+0.5);
  ball.addColorStop(0,'#ffffff'); ball.addColorStop(.65,'#e2e8f0'); ball.addColorStop(1,'#64748b');
  ctx.beginPath(); ctx.arc(bx,by,bs,0,7); ctx.fillStyle=ball; ctx.fill();
}
draw();

// zëri i topit (klikime si në kazino të vërtetë)
let AC=null;
function tick(){
  try{
    AC = AC || new (window.AudioContext||window.webkitAudioContext)();
    if(AC.state==='suspended') AC.resume();
    const o=AC.createOscillator(), g=AC.createGain();
    o.type='square'; o.frequency.value=1500+Math.random()*700;
    g.setValueAtTime(0.05, AC.currentTime);
    g.exponentialRampToValueAtTime(0.0001, AC.currentTime+0.035);
    o.connect(g); g.connect(AC.destination);
    o.start(); o.stop(AC.currentTime+0.04);
  }catch(e){}
}

// historia e numrave të fundit (vetëm vizuale)
function addHist(n,color){
  const bg=color==='red'?'#a31621':color==='black'?'#1f2937':'#15803d';
  const box=document.getElementById('rhist');
  const s=document.createElement('span');
  s.className='inline-flex items-center justify-center w-8 h-8 rounded-full text-white border-2 border-amber-400/70';
  s.style.background=bg; s.textContent=n;
  box.prepend(s);
  while(box.children.length>10)box.lastChild.remove();
}

async function play(){
  const btn=document.getElementById('btn');btn.disabled=true;
  const numberBet=parseInt(document.getElementById('numInput').value||'0');
  document.getElementById('rmsg').textContent='Topi u hodh... 🎱';
  try{
    const j=await api('/api/roulette/spin',{bet,type,number:numberBet},{settle:'manual'});
    const idx=nums.indexOf(j.number);
    // S'KA shigjetë: topi ndalet në një pozitë natyrale dhe xhepi
    // ku bie topi është numri fitues — rrota ndalet ashtu që
    // xhepi fitues të jetë saktësisht nën top
    const finalBall = ballAngle - (Math.PI*2*6 + Math.random()*Math.PI*2);
    const totalB = finalBall - ballAngle;
    const targetW = finalBall-(idx+.5)*ARC;
    const curW = angle%(Math.PI*2);
    const totalW = ((targetW-curW)%(Math.PI*2)+Math.PI*2)%(Math.PI*2) + Math.PI*2*4;
    const startA=angle, startB=ballAngle, t0=performance.now(), dur=6000;
    winIdx=-1; ballR=126; draw();
    let lastPk=-1;
    const DROP_AT=0.62; // topi fillon me ra në xhepa pas 62% të kohës
    function frame(t){
      const p=Math.min(1,(t-t0)/dur);
      // rrota: rrotullohet dhe ndalet me xhepin fitues te treguesi
      const eW=1-Math.pow(1-p,5);
      angle=startA+totalW*eW;
      // topi: rrotullohet në drejtim të kundërt, ndalet te treguesi pak më herët
      const pb=Math.min(1,p/0.85);
      const eB=1-Math.pow(1-pb,3);
      ballAngle=startB+totalB*eB;
      // topi BIE nga kanali në xhep, me kërcime
      if(p<DROP_AT){ ballR=126; }
      else{
        const q=(p-DROP_AT)/(1-DROP_AT);
        const e=1-Math.pow(1-q,2);
        const bounce=Math.sin(q*Math.PI*4)*(1-q)*9;
        ballR=126+(96-126)*e+bounce;
      }
      draw();
      // klikim sa herë topi kalon një xhep (rrallohet kur bie)
      const rel=((ballAngle-angle)%(Math.PI*2)+Math.PI*2)%(Math.PI*2);
      const pk=Math.floor(rel/ARC);
      if(pk!==lastPk){ lastPk=pk; if(p<0.9) tick(); }
      if(p<1) requestAnimationFrame(frame);
      else{
        tick();
        ballR=96; winIdx=idx; draw(); // topi pushon në xhepin fitues
        const bg=j.color==='red'?'#a31621':j.color==='black'?'#1f2937':'#15803d';
        document.getElementById('result').innerHTML=`<span class="inline-flex items-center justify-center w-16 h-16 rounded-full text-white border-4 border-amber-400" style="background:${bg}">${j.number}</span>`;
        addHist(j.number,j.color);
        if(j.won){
          document.getElementById('rmsg').textContent=`🎉 Topi ra në ${j.number}! Fitimi neto +${j.profit}€!`;
          confetti(100); flyCoins('wheelbox', 14);
          setTimeout(()=>settle(j.balance, 1300), 950);   // paratë kreditohen PASI bie topi
          toast(`Fitore neto +${j.profit}€!`,'win');
        } else {
          document.getElementById('rmsg').textContent=`Topi ra në ${j.number} — humbët. Provo përsëri!`;
          setTimeout(()=>settle(j.balance, 700), 200);
          toast('Humbët '+bet+'€','lose');
        }
        btn.disabled=false;
      }
    }
    requestAnimationFrame(frame);
  }catch(e){toast(e.message,'lose');btn.disabled=false;}
}
</script>
@endsection
