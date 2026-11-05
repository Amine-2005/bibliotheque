document.querySelectorAll('form[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (event) {
        if (!confirm(form.dataset.confirm)) {
            event.preventDefault();
        }
    });
});

document.querySelectorAll('.flash').forEach(function (message) {
    setTimeout(function () {
        message.style.transition = 'opacity 0.5s';
        message.style.opacity = '0';
        setTimeout(function () {
            message.remove();
        }, 500);
    }, 4000);
});
