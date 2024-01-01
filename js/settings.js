document.addEventListener("DOMContentLoaded", () => {
  const change_password_form = document.getElementById('change_password_form');
  const new_password = document.getElementById('new_password');
  const new_password_verify = document.getElementById('new_password_verify');

  new_password_verify.addEventListener('input', e => {
    new_password_verify.setCustomValidity("");
    new_password_verify.reportValidity();
  })

  change_password_form.addEventListener('submit', e => {
    if(new_password.value != new_password_verify.value) {
      new_password_verify.setCustomValidity("Hasła nie są takie same");
      new_password_verify.reportValidity();
      e.preventDefault();
      return;
    }
  })
})