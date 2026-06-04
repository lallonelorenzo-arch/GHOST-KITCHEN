document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.querySelector('[data-nav-toggle]');
  const links = document.querySelector('[data-nav-links]');
  const user = document.querySelector('.nav-user');

  if (toggle && links) {
    toggle.addEventListener('click', () => {
      links.classList.toggle('is-open');
      if (user) {
        user.classList.toggle('is-open');
      }
      toggle.setAttribute('aria-expanded', links.classList.contains('is-open') ? 'true' : 'false');
    });
  }

  document.querySelectorAll('[data-filter-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
      const form = button.closest('form');
      const panel = form ? form.querySelector('[data-filter-panel]') : null;
      const chevron = button.querySelector('.filter-chevron');

      if (!panel) {
        return;
      }

      const nextOpen = panel.hasAttribute('hidden');
      panel.toggleAttribute('hidden', !nextOpen);
      button.setAttribute('aria-expanded', nextOpen ? 'true' : 'false');

      if (chevron) {
        chevron.innerHTML = nextOpen ? '&#8963;' : '&#8964;';
      }
    });
  });

  document.querySelectorAll('form').forEach((form) => {
    form.querySelectorAll('[data-filter-copy-to]').forEach((select) => {
      select.addEventListener('change', () => {
        select.dataset.filterChanged = 'true';
      });
    });

    form.addEventListener('submit', () => {
      form.querySelectorAll('[data-filter-copy-to]').forEach((select) => {
        const targetName = select.getAttribute('data-filter-copy-to');
        const target = targetName ? form.querySelector(`[name="${targetName}"]`) : null;

        if (target && (select.value !== '' || select.dataset.filterChanged === 'true')) {
          target.value = select.value;
        }
      });
    });
  });
});
