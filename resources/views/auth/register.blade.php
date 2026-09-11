@extends('layouts.casino')
@section('title','Regjistrohu')

@section('content')
<div class="max-w-md mx-auto slidein">
  <div class="glass rounded-3xl p-8 border-amber-500/30" style="border-width:2px">
    <div class="text-center">
      <div class="text-6xl floaty">🎁</div>
      <h1 class="text-3xl font-black neon-gold mt-2">KRIJO LLOGARI</h1>
      <p class="text-white/60 text-sm mt-1">Krijo llogari, <b class="text-amber-300">depozito</b> dhe fillo të fitosh!</p>
    </div>

    @if($errors->any())
      <div class="mt-4 bg-red-500/15 border border-red-500 rounded-2xl p-3 text-sm text-red-300">
        @foreach($errors->all() as $e)<div>⚠️ {{ $e }}</div>@endforeach
      </div>
    @endif

    <form method="POST" action="/register" class="mt-5 space-y-4">
      @csrf
      <div>
        <label class="text-sm font-bold text-white/70">👤 Emri</label>
        <input type="text" name="name" value="{{ old('name') }}" required minlength="3" placeholder="psh. Arben Krasniqi"
          class="w-full mt-1 bg-black/50 border border-white/20 rounded-2xl px-4 py-3 font-semibold focus:border-amber-400 focus:outline-none">
      </div>
      <div>
        <label class="text-sm font-bold text-white/70">📧 Email</label>
        <input type="email" name="email" value="{{ old('email') }}" required placeholder="psh. arben@gmail.com"
          class="w-full mt-1 bg-black/50 border border-white/20 rounded-2xl px-4 py-3 font-semibold focus:border-amber-400 focus:outline-none">
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="text-sm font-bold text-white/70">🔑 Fjalëkalimi</label>
          <input type="password" name="password" required minlength="6" placeholder="Min. 6"
            class="w-full mt-1 bg-black/50 border border-white/20 rounded-2xl px-4 py-3 font-semibold focus:border-amber-400 focus:outline-none">
        </div>
        <div>
          <label class="text-sm font-bold text-white/70">🔑 Përsërit</label>
          <input type="password" name="password_confirmation" required placeholder="Përsërit"
            class="w-full mt-1 bg-black/50 border border-white/20 rounded-2xl px-4 py-3 font-semibold focus:border-amber-400 focus:outline-none">
        </div>
      </div>
      <button class="btn-gold w-full py-3 rounded-2xl text-lg">REGJISTROHU 🎉</button>
    </form>

    <div class="text-center text-sm text-white/60 mt-4">
      Ke llogari? <a href="/login" class="text-amber-300 font-black hover:underline">Hyr këtu 🔓</a>
    </div>
    <div class="text-center text-xs text-white/40 mt-2">🔒 Demo pa para reale • 18+</div>
  </div>
</div>
@endsection
