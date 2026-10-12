// Asks "are you sure?" before sending any form marked with data-confirm (used by the Delete buttons).
document.querySelectorAll('form[data-confirm]').forEach(function (form) {
  form.addEventListener('submit', function (event) {
    if (!confirm(form.dataset.confirm)) event.preventDefault();
  });
});
