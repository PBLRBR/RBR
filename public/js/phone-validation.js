/* Validasi UX nomor seluler Indonesia; validasi final tetap ada di BE. */
(() => {
  const isMobile = value => /^(?:08[0-9]{8,11}|628[0-9]{8,11}|\+628[0-9]{8,11})$/.test(value);
  const message = 'Nomor WhatsApp harus 10–13 digit (08...) atau format +628...';
  const setup = input => {
    input.setAttribute('inputmode', 'tel');
    input.setAttribute('autocomplete', 'tel');
    const validate = () => {
      const value = input.value.trim();
      input.setCustomValidity(value && !isMobile(value) ? message : '');
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
