(() => {
    document.addEventListener('click', (event) => {
        const toggle = event.target.closest('[data-action="toggle-answer"]');
        if (toggle) {
            const panel = toggle.closest('#answerPanel');
            if (!panel) return;
            const answer = panel.querySelector('[data-answer]');
            if (!answer) return;
            const hidden = answer.classList.toggle('tw-hidden');
            toggle.textContent = hidden ? toggle.dataset.showText || toggle.textContent : (toggle.dataset.hideText || toggle.textContent);
            if (!toggle.dataset.showText) {
                toggle.dataset.showText = window.translations?.showAnswer || 'Show answer';
            }
            if (!toggle.dataset.hideText) {
                toggle.dataset.hideText = window.translations?.hideAnswer || 'Hide answer';
            }
            toggle.textContent = hidden ? toggle.dataset.showText : toggle.dataset.hideText;
        }

        const voteButton = event.target.closest('[data-riddle-votes] [data-vote]');
        if (voteButton) {
            const widget = voteButton.closest('[data-riddle-votes]');
            if (!widget) return;
            const score = parseInt(voteButton.getAttribute('data-vote'), 10);
            const id = parseInt(widget.getAttribute('data-riddle-id'), 10);
            const error = widget.querySelector('[data-error]');
            error?.classList.add('tw-hidden');
            voteButton.disabled = true;
            fetch('/api/vote', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    _token: window.app?.csrfToken || '',
                    id: String(id),
                    score: String(score)
                })
            })
            .then((res) => res.json())
            .then((data) => {
                if (!data.success) {
                    throw new Error(data.error || 'Vote failed');
                }
                const stats = data.stats;
                const scoreLabel = widget.querySelector('[data-score]');
                if (scoreLabel) {
                    scoreLabel.textContent = `${stats.percent}% positive (${stats.upvotes}/${stats.downvotes})`;
                }
            })
            .catch((err) => {
                if (error) {
                    error.textContent = err.message;
                    error.classList.remove('tw-hidden');
                }
            })
            .finally(() => {
                voteButton.disabled = false;
            });
        }
    });
})();
