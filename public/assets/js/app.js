(() => {
  const doc = document.documentElement;
  const body = document.body;
  const themeKey = 'solveclone:theme';
  const toggleBtn = document.querySelector('[data-action="toggle-theme"]');

  const applyTheme = (theme) => {
    doc.dataset.theme = theme;
    body.setAttribute('data-theme', theme);
    window.localStorage.setItem(themeKey, theme);
  };

  if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
      const current = doc.dataset.theme === 'dark' ? 'dark' : 'light';
      const next = current === 'dark' ? 'light' : 'dark';
      applyTheme(next);
    });
  }

  const storedTheme = window.localStorage.getItem(themeKey);
  if (storedTheme) {
    applyTheme(storedTheme);
  }

  if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
      navigator.serviceWorker.register('/sw.js').catch(() => {});
    });
  }

  const installPrompt = document.getElementById('installPrompt');
  const installButton = installPrompt?.querySelector('[data-action="install-pwa"]');
  const tip = document.getElementById('platformTip');
  let deferredPrompt = null;

  const platform = (() => {
    const ua = window.navigator.userAgent;
    if (/iphone|ipad|ipod/i.test(ua)) return 'ios';
    if (/android/i.test(ua)) return 'android';
    if (/windows|macintosh|linux/i.test(ua)) return 'desktop';
    return 'other';
  })();

  const updateTip = () => {
    if (!tip) return;
    if (platform === 'android') {
      tip.textContent = window.__('pwa.banner.android', 'Tap “Install” to pin SolveClone to your Android home screen.');
    } else if (platform === 'ios') {
      tip.textContent = window.__('pwa.banner.ios', 'Tap the share icon → “Add to Home Screen” on iOS to install.');
    } else {
      tip.textContent = window.__('pwa.banner.desktop', 'Use the browser install button to keep SolveClone one click away.');
    }
  };

  window.__ = function (key, fallback) {
    const dictionary = window.__solveTranslations || {};
    return dictionary[key] || fallback;
  };

  if (installPrompt) {
    updateTip();
    window.addEventListener('beforeinstallprompt', (event) => {
      event.preventDefault();
      deferredPrompt = event;
      installPrompt.classList.remove('tw-hidden');
    });

    installButton?.addEventListener('click', async () => {
      if (!deferredPrompt) {
        updateTip();
        return;
      }
      deferredPrompt.prompt();
      const { outcome } = await deferredPrompt.userChoice;
      if (outcome === 'accepted') {
        installPrompt.classList.add('tw-hidden');
      }
      deferredPrompt = null;
    });

    if (platform === 'ios') {
      // show iOS hint even without event
      installPrompt.classList.remove('tw-hidden');
    }
  }
})();
