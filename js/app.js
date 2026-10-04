document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('[data-toggle-password]').forEach(function (button) {
    button.addEventListener('click', function () {
      var input = document.getElementById(button.getAttribute('data-toggle-password'));
      if (!input) return;

      var showing = input.type === 'password';
      input.type = showing ? 'text' : 'password';
      button.setAttribute('aria-label', (showing ? 'Hide ' : 'Show ') + (input.name === 'confirm_password' ? 'confirmation password' : 'password'));
      var icon = button.querySelector('i');
      if (icon) icon.className = showing ? 'bi bi-eye-slash' : 'bi bi-eye';
    });
  });

  var toggle = document.getElementById('sidebarToggle');
  var sidebar = document.getElementById('appSidebar');

  if (toggle && sidebar) {
    toggle.setAttribute('aria-expanded', sidebar.classList.contains('open') ? 'true' : 'false');
    toggle.addEventListener('click', function () {
      var isOpen = sidebar.classList.toggle('open');
      toggle.setAttribute('aria-expanded', String(isOpen));
    });

    document.addEventListener('click', function (event) {
      if (sidebar.classList.contains('open') && !sidebar.contains(event.target) && !toggle.contains(event.target)) {
        sidebar.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');
      }
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && sidebar.classList.contains('open')) {
        sidebar.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.focus();
      }
    });

    sidebar.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        sidebar.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');
      });
    });
  }

  var page = document.body.getAttribute('data-page');
  if (!page) return;

  document.querySelectorAll('[data-nav]').forEach(function (link) {
    if (link.getAttribute('data-nav') === page) {
      link.classList.add('active');
      link.setAttribute('aria-current', 'page');
    }
  });

  var currentPath = window.location.pathname.split('/').pop();
  document.querySelectorAll('#appSidebar .nav-link[href]').forEach(function (link) {
    var linkPath = link.getAttribute('href').split(/[?#]/)[0];
    if (linkPath === currentPath) {
      link.classList.add('active');
      link.setAttribute('aria-current', 'page');
    }
  });
});
