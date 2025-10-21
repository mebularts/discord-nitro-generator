(function () {
  'use strict';

  const themeToggle = document.querySelector('[data-toggle="theme"]');
  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)');
  const savedTheme = localStorage.getItem('solveclone-theme');
  let themeLocked = savedTheme !== null;

  function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('solveclone-theme', theme);
    if (themeToggle) {
      themeToggle.checked = theme === 'dark';
    }
  }

  if (savedTheme) {
    applyTheme(savedTheme);
  } else if (prefersDark.matches) {
    applyTheme('dark');
  }

  if (themeToggle) {
    themeToggle.addEventListener('change', function () {
      themeLocked = true;
      applyTheme(this.checked ? 'dark' : 'light');
    });
  }

  if (typeof prefersDark.addEventListener === 'function') {
    prefersDark.addEventListener('change', function (event) {
      if (!themeLocked) {
        applyTheme(event.matches ? 'dark' : 'light');
      }
    });
  }

  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
    new bootstrap.Tooltip(el);
  });

  const toastElList = [].slice.call(document.querySelectorAll('.toast'));
  toastElList.map(function (toastEl) {
    return new bootstrap.Toast(toastEl).show();
  });

  const nav = document.querySelector('.app-navbar');
  if (nav) {
    var lastScroll = 0;
    window.addEventListener('scroll', function () {
      var current = window.scrollY;
      if (current > lastScroll && current > 80) {
        nav.classList.add('app-navbar-hidden');
      } else {
        nav.classList.remove('app-navbar-hidden');
      }
      lastScroll = current;
    }, { passive: true });
  }
})();
