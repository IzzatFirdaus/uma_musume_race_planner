{{-- Converted from components/footer.php --}}
<footer class="text-center small mt-5">
  <div>
    <a href="https://github.com/IzzatFirdaus/uma_musume_race_planner" target="_blank" rel="noopener noreferrer">
      {{-- This replaces the original getenv('APP_VERSION') call --}}
      <i class="bi bi-github me-1"></i> Uma Musume Planner {{ env('APP_VERSION', '1.5.0') }}
    </a>
    {{-- This replaces the original getenv('LAST_UPDATED') call --}}
    | Last Updated: {{ env('LAST_UPDATED', 'August 4, 2025') }}
  </div>
  <div class="mt-2">
    <a href="https://umamusume.jp/" target="_blank" rel="noopener noreferrer">JP Official Site</a> |
    <a href="https://umamusume.com/" target="_blank" rel="noopener noreferrer">Global Site (EN)</a> |
    <a href="https://store.steampowered.com/app/3224770/Umamusume_Pretty_Derby/" target="_blank" rel="noopener noreferrer">Steam Page</a>
  </div>
  <div class="mt-2">
    <a href="https://x.com/umamusume_eng?lang=en" target="_blank" rel="noopener noreferrer" title="X / Twitter"><i class="bi bi-twitter-x me-2"></i></a>
    <a href="https://www.facebook.com/umamusume.eng" target="_blank" rel="noopener noreferrer" title="Facebook"><i class="bi bi-facebook me-2"></i></a>
    <a href="https://www.youtube.com/@umamusume_eng" target="_blank" rel="noopener noreferrer" title="YouTube"><i class="bi bi-youtube me-2"></i></a>
    <a href="https://discord.com/invite/umamusume-eng" target="_blank" rel="noopener noreferrer" title="Discord"><i class="bi bi-discord"></i></a>
  </div>
  <div class="mt-2">
    <em>This fan-made planner is not affiliated with Cygames or the Uma Musume franchise. All trademarks and rights belong to their respective owners.</em>
  </div>
</footer>
