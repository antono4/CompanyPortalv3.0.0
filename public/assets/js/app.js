/* Company Portal - small UI enhancements */
(function () {
  'use strict';

  // Password visibility toggle (login page)
  const toggle = document.getElementById('togglePassword');
  const password = document.getElementById('password');

  if (toggle && password) {
    toggle.addEventListener('click', function () {
      const isPassword = password.type === 'password';
      password.type = isPassword ? 'text' : 'password';
      const icon = toggle.querySelector('i');
      if (icon) {
        icon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
      }
    });
  }

  // Prevent double submission of forms
  document.querySelectorAll('form').forEach(function (form) {
    form.addEventListener('submit', function () {
      const submit = form.querySelector('button[type="submit"]');
      if (submit && !submit.disabled) {
        setTimeout(function () {
          submit.disabled = true;
        }, 10);
      }
    });
  });

  // Auto-dismiss success alerts after 6s
  document.querySelectorAll('.alert-success').forEach(function (alert) {
    setTimeout(function () {
      if (window.bootstrap && bootstrap.Alert) {
        bootstrap.Alert.getOrCreateInstance(alert).close();
      }
    }, 6000);
  });
})();
