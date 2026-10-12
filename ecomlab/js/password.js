// Password strength, shared by the register and change-password forms.
// Rule: 8+ characters and at least 3 of: lowercase, uppercase, number, symbol.
function passwordStrength(pass) {
  const missing = [];
  if (!/[a-z]/.test(pass)) missing.push('a lowercase letter');
  if (!/[A-Z]/.test(pass)) missing.push('an uppercase letter');
  if (!/[0-9]/.test(pass)) missing.push('a number');
  if (!/[^a-zA-Z0-9]/.test(pass)) missing.push('a symbol');
  const types = 4 - missing.length;

  // Score out of 100: up to 40 for length (12+ characters), 15 for each character type.
  const score = Math.round(Math.min(pass.length, 12) / 12 * 40) + types * 15;

  return { score: score, ok: pass.length >= 8 && types >= 3, tooShort: pass.length < 8, missing: missing };
}

// Adds a live strength bar under every password box marked with data-strength.
document.querySelectorAll('input[data-strength]').forEach(function (input) {
  const meter = document.createElement('div');
  meter.className = 'meter';
  const bar = document.createElement('div');
  meter.appendChild(bar);
  const text = document.createElement('span');
  text.className = 'hint';
  input.after(meter, text);

  input.addEventListener('input', function () {
    const pass = input.value;
    const result = passwordStrength(pass);
    bar.style.width = (pass === '' ? 0 : result.score) + '%';

    if (pass === '') {
      text.textContent = '';
    } else if (result.ok) {
      bar.style.background = result.score >= 90 ? '#16a34a' : '#65a30d';
      text.textContent = 'Strength: ' + result.score + '/100 - ' + (result.score >= 90 ? 'the password is strong.' : 'the password is good.');
    } else {
      bar.style.background = result.score < 50 ? '#dc2626' : '#f59e0b';
      const needs = result.missing.slice();
      if (result.tooShort) needs.unshift('at least 8 characters');
      text.textContent = 'Strength: ' + result.score + '/100 - the password needs more: ' + needs.join(', ') + '.';
    }
  });
});
