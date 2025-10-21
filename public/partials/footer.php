    </main>
    <footer class="border-top py-4 mt-auto">
      <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
        <span class="text-muted small">© <?= date('Y') ?> SolveClone</span>
        <div class="d-flex gap-3 small">
          <a href="/privacy.php" class="text-muted">Gizlilik</a>
          <a href="/terms.php" class="text-muted">Koşullar</a>
        </div>
      </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous" defer></script>
    <script src="<?= \App\h(\App\asset_url('js/app.js')) ?>" defer></script>
    <script>
      if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
          navigator.serviceWorker.register('/sw.js');
        });
      }
    </script>
  </body>
</html>
