/**
 * Like button AJAX handler using Partial Rendering & Event Delegation
 */
document.addEventListener('click', function (event) {
    const button = event.target.closest('.like-button[data-url]');
    if (!button) return;

    if (button.disabled) {
        return;
    }

    event.preventDefault();

    const url = button.dataset.url;
    button.disabled = true;
    button.style.opacity = '0.5';

    fetch(url, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.text();
        })
        .then(html => {
            // Replace current button with the new HTML from server
            button.outerHTML = html;
        })
        .catch(error => {
            console.error('Like error:', error);
            button.disabled = false;
            button.style.opacity = '1';
        });
});
