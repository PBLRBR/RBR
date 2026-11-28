/* Validasi UX nomor seluler Indonesia; validasi final tetap ada di BE. */
(() => {
  const normalize = value => {
    let phone = value.trim().replace(/[\s().-]+/g, '');
    if (phone.startsWith('+62')) phone = `0${phone.slice(3)}`;
    else if (phone.startsWith('62')) phone = `0${phone.slice(2)}`;
    return /^08[0-9]{8,11}$/.test(phone) ? phone : null;
  };
  window.simartNormalizePhone = normalize;
  const message = 'Gunakan nomor seluler Indonesia 10–13 digit, misalnya 081234567890 atau +6281234567890.';
  const setup = input => {
    input.setAttribute('inputmode', 'tel');
    input.setAttribute('autocomplete', 'tel');
    const validate = () => {
      const value = input.value.trim();
      input.setCustomValidity(value && !normalize(value) ? message : '');
    };
    input.addEventListener('input', validate);
    input.addEventListener('blur', validate);
    input.closest('form')?.addEventListener('submit', validate);
    validate();
  };
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('input[type="tel"][data-simart-phone]').forEach(setup);
  });
})();
