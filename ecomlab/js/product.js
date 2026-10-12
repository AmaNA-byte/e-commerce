// Checks the product picture before the form is sent (for a quick message only;
// the real check happens again in PHP, see save_picture() in core/core.php).
const productForm = document.getElementById('productForm');
const imageInput = productForm.product_image;
const imageError = document.getElementById('product_image_error');
const maxBytes = 8 * 1024 * 1024; // 8 MB

function checkImage() {
  const file = imageInput.files[0];
  let message = '';
  // Some browsers leave the type empty for newer formats (e.g. HEIC), so only reject known non-images.
  if (file && file.type !== '' && !file.type.startsWith('image/')) {
    message = 'That file is not a picture. Please choose an image file.';
  } else if (file && file.size > maxBytes) {
    message = 'The picture must be 8 MB or smaller.';
  }
  imageError.textContent = message;
  return message === '';
}

imageInput.addEventListener('change', checkImage);
productForm.addEventListener('submit', function (event) {
  if (!checkImage()) event.preventDefault();
});
