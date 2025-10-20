// base js
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js').catch(err => {
      console.warn('SW registration failed', err);
    });
  });
}

const installBanner = document.getElementById('installPrompt');
let deferredPrompt;
window.addEventListener('beforeinstallprompt', (e) => {
  e.preventDefault();
  deferredPrompt = e;
  if (installBanner) {
    installBanner.classList.remove('tw-hidden');
  }
});

if (installBanner) {
  const installBtn = installBanner.querySelector('button');
  if (installBtn) {
    installBtn.addEventListener('click', async () => {
      if (!deferredPrompt) return;
      deferredPrompt.prompt();
      await deferredPrompt.userChoice;
      installBanner.classList.add('tw-hidden');
      deferredPrompt = null;
    });
  }
}

(function showPlatformTip(){
  const el = document.getElementById('platformTip');
  if (!el) return;
  const ua = navigator.userAgent.toLowerCase();
  if (/iphone|ipad|ipod/.test(ua)) {
    el.textContent = 'iOS cihazlarda paylaş menüsünden \'Ana Ekrana Ekle\' seçeneğini kullanabilirsiniz.';
  } else if (/android/.test(ua)) {
    el.textContent = 'Android cihazlarda tarayıcı menüsünden veya yukarıdaki butondan uygulamayı ana ekrana ekleyin.';
  } else {
    el.textContent = 'Tarayıcınızda bu sayfayı yer imlerine ekleyebilir veya QR kodu paylaşabilirsiniz.';
  }
})();
