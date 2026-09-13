@extends('layouts.casino')
@section('title','Bastet Sportive')

@section('content')
<div class="slidein">
<h1 class="text-4xl font-black neon-gold text-center">⚽ BASTORE SPORTIVE</h1>
<p class="text-white/60 text-center">Kliko kuotat për të ndërtuar biletën • Kombino për fitore të mëdha!</p>
<p class="text-white/40 text-center text-xs mt-1">1X2 • Shans i Dyfishtë (1X/12/X2) • GG/NG • Over/Under</p>
<p class="text-center mt-2 text-sm">
  @if(($offerSource ?? 'demo') === 'live')
    <span class="inline-block bg-green-500/15 border border-green-500/40 text-green-200 font-bold px-4 py-1 rounded-full">✅ Ndeshje reale ({{ $offerProvider ?? '' }}) • {{ $offerDate ?? '' }}</span>
    <div class="text-white/40 text-xs mt-1">Kuotat janë informative/demo — ndeshjet dhe oraret janë reale.</div>
  @else
    <span class="inline-block bg-amber-500/15 border border-amber-500/40 text-amber-200 font-bold px-4 py-1 rounded-full">📅 Oferta e ditës: {{ $offerDate ?? '' }} • Ndryshon automatikisht çdo ditë në 00:00</span>
  @endif
</p>

<div class="grid lg:grid-cols-3 gap-6 mt-6">
  <div class="lg:col-span-2 space-y-4">
    @foreach($matches as $m)
    <div class="glass rounded-2xl p-4 card-glow">
      <div class="flex justify-between items-center text-xs text-white/50">
        <span>🏆 {{ $m['league'] }}</span>
        <span class="flex items-center gap-2">⏰ {{ $m['time'] }}
          @if(str_starts_with($m['time'],'Sot'))
            <span class="bg-red-500 text-white font-black px-2 py-0.5 rounded-full animate-pulse">SOT</span>
          @endif
        </span>
      </div>
      <div class="font-black text-lg mt-1">{{ $m['home'] }} <span class="text-white/40">vs</span> {{ $m['away'] }}</div>
      @php $si1=1/$m['o1']; $six=1/$m['ox']; $si2=1/$m['o2']; $st=$si1+$six+$si2; $sp1=round($si1/$st*100); $spx=round($six/$st*100); $sp2=100-$sp1-$spx; @endphp
      <div class="flex h-1.5 rounded-full overflow-hidden mt-2 bg-white/10" title="Probabiliteti i nënkuptuar">
        <div style="width:{{ $sp1 }}%;background:#3b82f6"></div><div style="width:{{ $spx }}%;background:#6b7280"></div><div style="width:{{ $sp2 }}%;background:#ef4444"></div>
      </div>
      <div class="flex justify-between text-[10px] text-white/40 mt-0.5 font-bold"><span>1 {{ $sp1 }}%</span><span>X {{ $spx }}%</span><span>2 {{ $sp2 }}%</span></div>
      <div class="grid grid-cols-5 gap-2 mt-3 text-center text-sm">
        <button class="odds-btn bg-white/10 rounded-xl py-2" onclick="toggle(this,{{ $m['id'] }},'1','{{ $m['home'] }}','{{ $m['o1'] }}')"><div class="text-[11px] text-white/50">1</div><div class="font-black">{{ number_format($m['o1'],2) }}</div></button>
        <button class="odds-btn bg-white/10 rounded-xl py-2" onclick="toggle(this,{{ $m['id'] }},'X','Barazim','{{ $m['ox'] }}')"><div class="text-[11px] text-white/50">X</div><div class="font-black">{{ number_format($m['ox'],2) }}</div></button>
        <button class="odds-btn bg-white/10 rounded-xl py-2" onclick="toggle(this,{{ $m['id'] }},'2','{{ $m['away'] }}','{{ $m['o2'] }}')"><div class="text-[11px] text-white/50">2</div><div class="font-black">{{ number_format($m['o2'],2) }}</div></button>
        <button class="odds-btn bg-white/10 rounded-xl py-2 border border-green-500/30" onclick="toggle(this,{{ $m['id'] }},'Over {{ $m['line'] ?? '2.5' }}','Over {{ $m['line'] ?? '2.5' }}','{{ $m['over'] }}')"><div class="text-[11px] text-white/50">O {{ $m['line'] ?? '2.5' }}</div><div class="font-black">{{ number_format($m['over'],2) }}</div></button>
        <button class="odds-btn bg-white/10 rounded-xl py-2 border border-red-500/30" onclick="toggle(this,{{ $m['id'] }},'Under {{ $m['line'] ?? '2.5' }}','Under {{ $m['line'] ?? '2.5' }}','{{ $m['under'] }}')"><div class="text-[11px] text-white/50">U {{ $m['line'] ?? '2.5' }}</div><div class="font-black">{{ number_format($m['under'],2) }}</div></button>
      </div>
      <div class="grid grid-cols-5 gap-2 mt-2 text-center text-sm">
        <button class="odds-btn bg-sky-500/15 border border-sky-500/30 rounded-xl py-2" onclick="toggle(this,{{ $m['id'] }},'1X','Shans i dyfishtë 1X','{{ $m['dc1x'] ?? 1.5 }}')"><div class="text-[11px] text-white/50">1X</div><div class="font-black">{{ number_format($m['dc1x'] ?? 1.5,2) }}</div></button>
        <button class="odds-btn bg-sky-500/15 border border-sky-500/30 rounded-xl py-2" onclick="toggle(this,{{ $m['id'] }},'12','Shans i dyfishtë 12','{{ $m['dc12'] ?? 1.5 }}')"><div class="text-[11px] text-white/50">12</div><div class="font-black">{{ number_format($m['dc12'] ?? 1.5,2) }}</div></button>
        <button class="odds-btn bg-sky-500/15 border border-sky-500/30 rounded-xl py-2" onclick="toggle(this,{{ $m['id'] }},'X2','Shans i dyfishtë X2','{{ $m['dcx2'] ?? 1.5 }}')"><div class="text-[11px] text-white/50">X2</div><div class="font-black">{{ number_format($m['dcx2'] ?? 1.5,2) }}</div></button>
        <button class="odds-btn bg-cyan-500/15 border border-cyan-500/30 rounded-xl py-2" onclick="toggle(this,{{ $m['id'] }},'GG','GG','{{ $m['gg'] ?? 1.8 }}')"><div class="text-[11px] text-white/50">GG</div><div class="font-black">{{ number_format($m['gg'] ?? 1.8,2) }}</div></button>
        <button class="odds-btn bg-cyan-500/15 border border-cyan-500/30 rounded-xl py-2" onclick="toggle(this,{{ $m['id'] }},'NG','NG','{{ $m['ng'] ?? 1.8 }}')"><div class="text-[11px] text-white/50">NG</div><div class="font-black">{{ number_format($m['ng'] ?? 1.8,2) }}</div></button>
      </div>
      <div class="text-[11px] text-white/40 mt-1 match-label" data-match="{{ $m['id'] }}">{{ $m['home'] }} - {{ $m['away'] }}</div>
    </div>
    @endforeach
  </div>

  <div>
    <div class="glass rounded-3xl p-5 sticky top-24 border-amber-500/30" id="slipbox" style="border-width:2px">
      <h3 class="font-black text-xl">🧾 Bileta Ime</h3>
      <div id="slip" class="space-y-2 mt-3 text-sm"><div class="text-white/40">Kliko kuotat majtas për të shtuar...</div></div>
      <div class="mt-3 text-sm">Kuota totale: <b id="totalOdd" class="text-amber-300">1.00</b></div>
      <label class="text-sm text-white/60">Stake (€):</label>
      <input id="stake" type="number" value="10" min="1" class="w-full bg-black/50 border border-white/20 rounded-xl px-4 py-2 font-black mt-1">
      <div class="mt-1 text-sm">Fitimi potencial: <b id="potential" class="text-green-400">0.00€</b></div>
      <button onclick="placeBet()" id="betBtn" class="btn-gold w-full mt-3 py-3 rounded-2xl">VË BASTIN ⚽</button>
      <button onclick="simulate()" class="w-full mt-2 py-2 rounded-2xl bg-white/10 font-bold hover:bg-white/20">🎲 Simulo Ndeshjet</button>
    </div>

    <div class="glass rounded-3xl p-5 mt-4">
      <h3 class="font-black">📜 Biletat e Mia</h3>
      <div id="tickets" class="space-y-2 mt-2 text-sm">
        @forelse(array_reverse($tickets) as $t)
          <div class="bg-black/40 rounded-xl p-3 border {{ $t['status']=='won'?'border-green-500':($t['status']=='lost'?'border-red-500':'border-white/10') }}">
            <div class="flex justify-between"><b>Bileta #{{ $t['id'] }}</b>
              <span class="px-2 rounded-full text-xs font-black {{ $t['status']=='won'?'bg-green-500':($t['status']=='lost'?'bg-red-500':'bg-amber-500 text-black') }}">{{ strtoupper($t['status']) }}</span></div>
            @foreach($t['selections'] as $s)<div class="text-white/60">• {{ $s['label'] }} @ {{ $s['odd'] }}</div>@endforeach
            <div>Stake: {{ $t['stake'] }}€ • Kuota: {{ $t['totalOdd'] }} • Potencial: {{ $t['potential'] }}€</div>
          </div>
        @empty
          <div class="text-white/40">Ende asnjë biletë.</div>
        @endforelse
      </div>
    </div>
  </div>
</div>
</div>
@endsection

@section('scripts')
<script>
let slip={}; // match_id -> {match_id,pick,odd,label}
const matchNames={};document.querySelectorAll('.match-label').forEach(e=>matchNames[e.dataset.match]=e.textContent);
function toggle(btn,mid,pick,label,odd){
  // deselect others of same match
  document.querySelectorAll(`button[onclick*="toggle(this,${mid},"]`).forEach(b=>b.classList.remove('selected'));
  if(slip[mid] && slip[mid].pick===pick){delete slip[mid];}
  else{btn.classList.add('selected');slip[mid]={match_id:mid,pick,odd:parseFloat(odd),label:matchNames[mid]+' → '+label+' ('+pick+')'};}
  render();
}
function render(){
  const box=document.getElementById('slip');const arr=Object.values(slip);
  if(!arr.length){box.innerHTML='<div class="text-white/40">Kliko kuotat majtas për të shtuar...</div>';}
  else box.innerHTML=arr.map(s=>`<div class="bg-black/40 rounded-xl p-2 flex justify-between"><span>${s.label} @ <b class="text-amber-300">${s.odd.toFixed(2)}</b></span><button onclick="removeSel(${s.match_id})" class="text-red-400 font-black">✕</button></div>`).join('');
  let tot=1;arr.forEach(s=>tot*=s.odd);
  document.getElementById('totalOdd').textContent=tot.toFixed(2);
  const stake=+document.getElementById('stake').value||0;
  document.getElementById('potential').textContent=(tot*stake).toFixed(2)+'€';
}
function removeSel(mid){delete slip[mid];document.querySelectorAll(`button[onclick*="toggle(this,${mid},"]`).forEach(b=>b.classList.remove('selected'));render();}
document.getElementById('stake').oninput=render;
async function placeBet(){
  const arr=Object.values(slip);const stake=+document.getElementById('stake').value||0;
  try{
    const j=await api('/api/sports/bet',{selections:arr,stake},{settle:'manual'});
    settle(j.balance, 800);
    toast(`Bileta #${j.ticket.id} u vendos! Potencial ${j.ticket.potential}€`,'win');confetti(50);
    slip={};document.querySelectorAll('.odds-btn').forEach(b=>b.classList.remove('selected'));render();
    setTimeout(()=>location.reload(),800);
  }catch(e){toast(e.message,'lose');}
}
async function simulate(){
  try{
    const j=await api('/api/sports/simulate',{},{settle:'manual'});
    const won=j.tickets.filter(t=>t.status==='won').length;
    if(won>0){
      confetti(150); flyCoins('slipbox', 14);
      setTimeout(()=>settle(j.balance, 1400), 950);   // paratë kreditohen PASI kryhet simulimi
      toast(`🎉 Fituat ${won} biletë(a)!`,'win');
    }else{ settle(j.balance, 600); toast('Fatkeqësisht humbët. Provo përsëri!','lose'); }
    setTimeout(()=>location.reload(),2200);
  }catch(e){toast(e.message,'lose');}
}
render();
(function autoRefreshAtMidnight(){
  const now = new Date();
  const midnight = new Date(now);
  midnight.setHours(24, 0, 5, 0);
  setTimeout(()=>location.reload(), midnight - now);
})();
</script>
@endsection
