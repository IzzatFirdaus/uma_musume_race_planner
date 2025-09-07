{{-- Footer partial — supports theme classes and provides safe fallbacks. --}}
@php
  $appVersion = config('app.version') ?: 'dev';
  $lastUpdated = config('app.last_updated') ?: now()->toDateString();
  $repoUrl = 'https://github.com/IzzatFirdaus/uma_musume_race_planner';
@endphp

<footer class="text-center small mt-5 py-3 bg-transparent footer-theme" role="contentinfo">
  <div class="container">
    <div class="d-flex flex-column flex-md-row justify-content-center align-items-center gap-3">
      <a href="{{ $repoUrl }}" target="_blank" rel="noopener noreferrer" class="d-inline-flex align-items-center">
        <i class="bi bi-github me-1" aria-hidden="true"></i>
        <span class="visually-hidden">Repository</span>
        <span class="ms-1">Uma Musume Planner {{ $appVersion }}</span>
      </a>

      <span class="text-muted">|</span>

      <div class="text-muted">Last Updated: <time datetime="{{ $lastUpdated }}">{{ $lastUpdated }}</time></div>
    </div>

    <div class="mt-2 d-flex flex-wrap justify-content-center gap-2">
      <a href="https://umamusume.com/" target="_blank" rel="noopener noreferrer">Official English Site</a>
      <span class="text-muted">|</span>
      <a href="https://umamusume.jp/" target="_blank" rel="noopener noreferrer">Official JP Site</a>
      <span class="text-muted">|</span>
      <a href="https://store.steampowered.com/app/3224770/Umamusume_Pretty_Derby/" target="_blank" rel="noopener noreferrer">Steam</a>
    </div>

    <div class="mt-2 d-flex justify-content-center gap-3" aria-label="social links">
      <a href="https://x.com/umamusume_eng?lang=en" target="_blank" rel="noopener noreferrer" title="X / Twitter" aria-label="X / Twitter"><i class="bi bi-twitter-x"></i></a>
      <a href="https://www.facebook.com/umamusume.eng" target="_blank" rel="noopener noreferrer" title="Facebook" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
      <a href="https://www.youtube.com/@umamusume_eng" target="_blank" rel="noopener noreferrer" title="YouTube" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
      <a href="https://discord.gg/umamusume-eng" target="_blank" rel="noopener noreferrer" title="Discord" aria-label="Discord"><i class="bi bi-discord"></i></a>
    </div>

    <div class="mt-3 text-center small text-muted">
      <em>This fan-made planner is not affiliated with Cygames or the Uma Musume franchise. All trademarks and rights belong to their respective owners.</em>
    </div>
  </div>
</footer>
