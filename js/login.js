// js/login.js
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('loginForm');
  if (form) {
    form.addEventListener('submit', async function (e) {
      e.preventDefault();
      
      const email = document.getElementById('loginEmail').value;
      const password = document.getElementById('loginPassword').value;
      
      try {
        const response = await fetch('login.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({ email, password })
        });
        const result = await response.json();
        if (result.status === 'success') {
          window.location.href = 'profile.php';
        } else {
          alert(result.message);
        }
      } catch (error) {
        console.error('Ошибка входа:', error);
        alert('Ошибка связи с сервером.');
      }
    });
  }
});
