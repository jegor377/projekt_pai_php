document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("register_form");
  const role = document.getElementById("role");
  const trainer_id = document.getElementById("trainer_id");
  const password = document.getElementById("password");
  const password_verify = document.getElementById("password_verify");

  role.addEventListener('change', e => {
    e.preventDefault();
    
    if(trainer_id != null) {
      switch(role.value) {
        case 'sportsman': {
          trainer_id.style.visibility = 'visible';
        } break;
        case 'trainer': {
          trainer_id.style.visibility = 'hidden';
        } break;
      }
    }
  })

  password_verify.addEventListener('input', e => {
    password_verify.setCustomValidity("");
    password_verify.reportValidity();
  })

  form.addEventListener("submit", e => {
    e.preventDefault();
    if(password.value != password_verify.value) {
      password_verify.setCustomValidity("Hasła nie są takie same");
      password_verify.reportValidity();
      return;
    }

    register(form);
  })
})

async function register(formData) {
  
}