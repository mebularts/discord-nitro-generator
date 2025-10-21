(function () {
    const root = document.documentElement;
    const storageKey = 'solveclone-theme';
    const preferred = localStorage.getItem(storageKey) || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    root.setAttribute('data-theme', preferred);

    document.querySelectorAll('[data-theme-toggle]').forEach(function (button) {
        button.addEventListener('click', function () {
            const current = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            root.setAttribute('data-theme', current);
            localStorage.setItem(storageKey, current);
        });
    });

    const questionModal = document.querySelector('[data-question-modal]');
    const questionForm = document.querySelector('[data-question-form]');
    document.querySelectorAll('[data-open-question-modal]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (questionModal) {
                questionModal.hidden = false;
                questionModal.querySelector('textarea').focus();
            }
        });
    });
    document.querySelectorAll('[data-close-modal]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (questionModal) {
                questionModal.hidden = true;
            }
        });
    });

    if (questionForm) {
        questionForm.addEventListener('submit', function (event) {
            if (!navigator.onLine) {
                event.preventDefault();
                alert('Offline modda soru gönderemezsin. Lütfen bağlantını kontrol et.');
            }
        });
    }

    document.querySelectorAll('[data-answer-form]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!navigator.onLine) {
                event.preventDefault();
                alert('Offline modda cevap gönderemezsin.');
            }
        });
    });

    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/service-worker.js').catch(function () {
                console.warn('Service worker kaydedilemedi.');
            });
        });
    }
})();
