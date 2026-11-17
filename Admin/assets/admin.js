document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('a[data-confirm]').forEach(function (link) {
        link.addEventListener('click', function (event) {
            var message = link.getAttribute('data-confirm');
            if (!confirm(message)) {
                event.preventDefault();
            }
        });
    });
});
