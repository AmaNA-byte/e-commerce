// Six boxes for a 6-digit email code (verify email, reset password).
// Typing moves to the next box, Backspace goes back, and pasting the whole code fills every box.
// The digits are joined into the hidden <input name="code"> that the form sends.
document.querySelectorAll('.code-inputs').forEach(function (group) {
  const boxes = group.querySelectorAll('input');
  const form = group.closest('form');
  const hidden = form.querySelector('input[name="code"]');
  const error = form.querySelector('.code-error');

  function update() {
    hidden.value = Array.prototype.map.call(boxes, function (b) { return b.value; }).join('');
  }

  boxes.forEach(function (box, index) {
    box.addEventListener('input', function () {
      box.value = box.value.replace(/\D/g, '').slice(0, 1);
      if (box.value && index < boxes.length - 1) boxes[index + 1].focus();
      update();
    });
    box.addEventListener('keydown', function (event) {
      if (event.key === 'Backspace' && box.value === '' && index > 0) boxes[index - 1].focus();
    });
    box.addEventListener('paste', function (event) {
      event.preventDefault();
      const digits = (event.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, boxes.length);
      digits.split('').forEach(function (d, i) { boxes[i].value = d; });
      update();
      boxes[Math.min(digits.length, boxes.length - 1)].focus();
    });
  });

  form.addEventListener('submit', function (event) {
    update();
    const ok = /^\d{6}$/.test(hidden.value);
    if (error) error.textContent = ok ? '' : 'Please enter all 6 digits of the code.';
    if (!ok) event.preventDefault();
  });

  boxes[0].focus();
});
