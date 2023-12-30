document.addEventListener("DOMContentLoaded", () => {
  const logoutBtn = document.getElementById('logout_btn')
  logoutBtn?.addEventListener('click', e => {
    e.preventDefault()
    handleLogout()
  })
})

async function handleLogout() {
  const failed = () => alert("Nie udało się wylogować")

  try {
    const res = await fetch('/api/session.php', {
      method: 'DELETE'
    }).then(res => res.json());

    if(res == true) {
      window.location.replace("/login.php");
    } else {
      failed();
    }
  } catch(err) {
    failed();
  }
}