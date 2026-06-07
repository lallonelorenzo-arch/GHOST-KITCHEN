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

  const activateRevenuePoint = (point) => {
    const chart = point.closest('.revenue-chart');
    const tooltip = chart ? chart.querySelector('[data-revenue-tooltip]') : null;
    const cursor = chart ? chart.querySelector('[data-revenue-cursor]') : null;
    const label = chart ? chart.querySelector('[data-revenue-label]') : null;
    const value = chart ? chart.querySelector('[data-revenue-value]') : null;
    const x = Number(point.dataset.x || 0);
    const y = Number(point.dataset.y || 0);

    if (!chart || !tooltip || !cursor || !label || !value) {
      return;
    }

    chart.querySelectorAll('[data-chart-point]').forEach((item) => {
      item.classList.toggle('is-active', item === point);
      item.setAttribute('r', item === point ? '6' : '5');
    });

    cursor.setAttribute('x1', String(x));
    cursor.setAttribute('x2', String(x));
    tooltip.setAttribute('transform', `translate(${Math.min(478, Math.max(78, x + 16))} ${Math.max(58, y - 16)})`);
    label.textContent = point.dataset.label || '';
    value.textContent = `Ricavo: €${point.dataset.value || '0,00'}`;
  };

  document.querySelectorAll('[data-chart-point]').forEach((point) => {
    point.addEventListener('mouseenter', () => activateRevenuePoint(point));
    point.addEventListener('focus', () => activateRevenuePoint(point));
    point.addEventListener('click', () => activateRevenuePoint(point));
  });

  const activateBarPoint = (bar) => {
    const shell = bar.closest('.bar-chart-shell');
    const tooltip = shell ? shell.querySelector('[data-bar-tooltip]') : null;
    const label = shell ? shell.querySelector('[data-bar-label]') : null;
    const value = shell ? shell.querySelector('[data-bar-value]') : null;

    if (!shell || !tooltip || !label || !value) {
      return;
    }

    shell.querySelectorAll('[data-bar-point]').forEach((item) => {
      item.classList.toggle('is-active', item === bar);
    });

    label.textContent = bar.dataset.label || '';
    value.textContent = `${bar.dataset.value || '0'} prenotazioni`;
    tooltip.hidden = false;

    const shellBox = shell.getBoundingClientRect();
    const barBox = bar.getBoundingClientRect();
    const x = barBox.left - shellBox.left + (barBox.width / 2);
    tooltip.style.left = `${x}px`;
  };

  document.querySelectorAll('[data-bar-point]').forEach((bar) => {
    bar.addEventListener('mouseenter', () => activateBarPoint(bar));
    bar.addEventListener('focus', () => activateBarPoint(bar));
    bar.addEventListener('click', () => activateBarPoint(bar));
  });

  document.querySelectorAll('.bar-chart-shell').forEach((shell) => {
    shell.addEventListener('mouseleave', () => {
      const tooltip = shell.querySelector('[data-bar-tooltip]');
      if (tooltip) {
        tooltip.hidden = true;
      }
      shell.querySelectorAll('[data-bar-point]').forEach((item) => item.classList.remove('is-active'));
    });
  });

  const toggleRequestCard = (requestToggle) => {
    const card = requestToggle.closest('.request-card');
    const detail = card ? card.querySelector('.request-card-detail') : null;
    const chevron = requestToggle.querySelector('.request-chevron');

    if (detail) {
      const nextOpen = detail.hasAttribute('hidden');
      detail.toggleAttribute('hidden', !nextOpen);
      card.classList.toggle('is-open', nextOpen);
      requestToggle.setAttribute('aria-expanded', nextOpen ? 'true' : 'false');
      if (chevron) {
        chevron.innerHTML = nextOpen ? '&#8963;' : '&#8964;';
      }
    }
  };

  document.addEventListener('keydown', (event) => {
    const requestToggle = event.target.closest('[data-request-toggle]');
    if (requestToggle && (event.key === 'Enter' || event.key === ' ')) {
      event.preventDefault();
      toggleRequestCard(requestToggle);
    }
  });

  document.addEventListener('click', (event) => {
    const requestToggle = event.target.closest('[data-request-toggle]');
    if (requestToggle) {
      toggleRequestCard(requestToggle);
    }

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
