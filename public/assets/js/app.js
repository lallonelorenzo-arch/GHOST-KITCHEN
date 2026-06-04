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

  document.querySelectorAll('[data-dynamic-action]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      const pattern = form.getAttribute('data-dynamic-action');
      const id = form.querySelector('[name="dynamicId"]')?.value;
      const action = form.querySelector('[name="dynamicAction"]')?.value;

      if (!pattern || !id || !action) {
        event.preventDefault();
        return;
      }

      form.action = `${window.location.origin}${window.GK_BASE_URL || ''}${pattern.replace('{id}', id).replace('{azione}', action)}`;
    });
  });

  document.addEventListener('click', (event) => {
    const passwordButton = event.target.closest('[data-password-toggle]');
    if (passwordButton) {
      const field = passwordButton.closest('.password-field');
      const input = field ? field.querySelector('input') : null;
      if (input) {
        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        passwordButton.setAttribute('aria-label', isHidden ? 'Nascondi password' : 'Mostra password');
      }
    }

    const toggleButton = event.target.closest('[data-toggle-target]');
    if (toggleButton) {
      const targetId = toggleButton.getAttribute('data-toggle-target');
      const target = targetId ? document.getElementById(targetId) : null;
      if (target) {
        const nextOpen = target.hasAttribute('hidden');
        target.toggleAttribute('hidden', !nextOpen);
        toggleButton.setAttribute('aria-expanded', nextOpen ? 'true' : 'false');
        toggleButton.textContent = nextOpen ? 'Nascondi form metodo di pagamento' : '+ Aggiungi Metodo di Pagamento';
        if (nextOpen) {
          target.querySelector('input, select, textarea')?.focus();
        }
      }
    }
  });
});
