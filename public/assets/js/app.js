(function () {
  'use strict';

  const themeToggle = document.querySelector('[data-toggle="theme"]');
  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)');
  const savedTheme = localStorage.getItem('solveclone-theme');

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
      applyTheme(this.checked ? 'dark' : 'light');
    });
  }

  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
    new bootstrap.Tooltip(el);
  });

  const toastElList = [].slice.call(document.querySelectorAll('.toast'));
  toastElList.map(function (toastEl) {
    return new bootstrap.Toast(toastEl).show();
  });
})();
