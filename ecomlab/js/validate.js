document.getElementById('registerForm').addEventListener('submit', function (event) {
  const form = this;
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  const phoneRegex = /^[0-9+\-\s]{7,15}$/;
  let valid = true;

  // Show or clear the error message next to one field
  function check(field, isOk, message) {
    document.getElementById(field + '_error').textContent = isOk ? '' : message;
    if (!isOk) valid = false;
  }

  check('customer_name', form.customer_name.value.trim() !== '', 'Name is required.');
  check('customer_email', emailRegex.test(form.customer_email.value.trim()), 'Enter a valid email.');
  check('customer_pass', form.customer_pass.value.length >= 6, 'Password must be at least 6 characters.');
  check('customer_country', form.customer_country.value !== '', 'Choose a country.');
  check('customer_city', form.customer_city.value.trim() !== '', 'City is required.');
  check('customer_contact', phoneRegex.test(form.customer_contact.value.trim()), 'Enter a valid phone number.');

  if (!valid) {
    event.preventDefault();
    return;
  }

  // Loading state
  const button = form.querySelector('button');
  button.disabled = true;
  button.textContent = 'Registering...';
});
