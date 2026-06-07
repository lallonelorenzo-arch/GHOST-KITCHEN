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

  document.querySelectorAll('[data-booking-calendar]').forEach((calendar) => {
    calendar.addEventListener('click', (event) => {
      const slot = event.target.closest('[data-slot-select]');
      if (!slot) {
        return;
      }

      const flow = calendar.closest('.ops-flow') || document;
      const form = flow.querySelector('[data-booking-form]');
      if (!form) {
        return;
      }

      const dateInput = form.querySelector('[name="dataServizio"]');
      const startInput = form.querySelector('[name="oraInizio"]');
      const endInput = form.querySelector('[name="oraFine"]');

      if (dateInput) {
        dateInput.value = slot.dataset.date || '';
      }
      if (startInput) {
        startInput.value = slot.dataset.start || '';
      }
      if (endInput) {
        endInput.value = slot.dataset.end || '';
      }

      calendar.querySelectorAll('[data-slot-select]').forEach((item) => {
        item.classList.toggle('is-selected', item === slot);
        item.setAttribute('aria-pressed', item === slot ? 'true' : 'false');
      });

      form.classList.add('is-slot-selected');
      form.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
  });

  document.querySelectorAll('[data-registration-form]').forEach((form) => {
    const clientRole = form.querySelector('[data-role-client]');
    const professionalRoles = form.querySelectorAll('[data-role-professional]');
    const steps = Array.from(form.querySelectorAll('[data-registration-step]'));
    const indicators = Array.from(form.querySelectorAll('[data-step-indicator]'));
    let currentStep = Number(form.dataset.initialStep || 1);

    if (!clientRole || professionalRoles.length === 0) {
      return;
    }

    const isChefSelected = () => {
      const chefRole = form.querySelector('[data-role-professional][value="chef"]');
      return Boolean(chefRole && chefRole.checked);
    };

    const isGestoreSelected = () => {
      const gestoreRole = form.querySelector('[data-role-professional][value="gestore"]');
      return Boolean(gestoreRole && gestoreRole.checked);
    };

    const hasRoleSelected = () => clientRole.checked || Array.from(professionalRoles).some((role) => role.checked);

    const showStep = (step) => {
      currentStep = Math.min(5, Math.max(1, step));
      steps.forEach((item) => {
        const isActive = Number(item.dataset.registrationStep || 0) === currentStep;
        item.classList.toggle('is-active', isActive);
        item.toggleAttribute('hidden', !isActive);
      });
      indicators.forEach((indicator) => {
        const indicatorStep = Number(indicator.dataset.stepIndicator || 0);
        indicator.classList.toggle('is-active', indicatorStep === currentStep);
        indicator.classList.toggle('is-complete', indicatorStep < currentStep);
      });
      form.querySelector(`[data-registration-step="${currentStep}"] input, [data-registration-step="${currentStep}"] textarea, [data-registration-step="${currentStep}"] button`)?.focus({ preventScroll: true });
    };

    const validateCurrentStep = () => {
      const step = form.querySelector(`[data-registration-step="${currentStep}"]`);
      if (!step) {
        return true;
      }

      if (currentStep === 2 && !hasRoleSelected()) {
        clientRole.setCustomValidity('Seleziona almeno un tipo di account.');
        clientRole.reportValidity();
        clientRole.setCustomValidity('');
        return false;
      }

      const fields = Array.from(step.querySelectorAll('input, textarea, select'));
      return fields.every((field) => {
        if (typeof field.reportValidity === 'function' && !field.reportValidity()) {
          return false;
        }
        return true;
      });
    };

    const nextStep = () => {
      if (!validateCurrentStep()) {
        return;
      }

      if (currentStep === 2 && !isChefSelected()) {
        showStep(isGestoreSelected() ? 4 : 5);
        return;
      }

      if (currentStep === 3 && !isGestoreSelected()) {
        showStep(5);
        return;
      }

      showStep(currentStep + 1);
    };

    const previousStep = () => {
      if (currentStep === 5) {
        if (isGestoreSelected()) {
          showStep(4);
          return;
        }
        if (isChefSelected()) {
          showStep(3);
          return;
        }
        showStep(2);
        return;
      }

      if (currentStep === 4 && isChefSelected()) {
        showStep(3);
        return;
      }

      showStep(currentStep - 1);
    };

    clientRole.addEventListener('change', () => {
      if (clientRole.checked) {
        professionalRoles.forEach((role) => {
          role.checked = false;
        });
      }
    });

    professionalRoles.forEach((role) => {
      role.addEventListener('change', () => {
        if (role.checked) {
          clientRole.checked = false;
        }
      });
    });

    form.querySelectorAll('[data-wizard-next]').forEach((button) => {
      button.addEventListener('click', nextStep);
    });

    form.querySelectorAll('[data-wizard-prev]').forEach((button) => {
      button.addEventListener('click', previousStep);
    });

    form.addEventListener('submit', (event) => {
      if (form.checkValidity()) {
        return;
      }

      const invalidField = form.querySelector(':invalid');
      const invalidStep = invalidField ? invalidField.closest('[data-registration-step]') : null;
      if (invalidStep) {
        event.preventDefault();
        showStep(Number(invalidStep.dataset.registrationStep || 1));
        window.setTimeout(() => invalidField.reportValidity(), 50);
      }
    });

    showStep(currentStep);
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
    const roleSwitchLink = event.target.closest('.nav-role-toggle a');
    if (roleSwitchLink && !event.metaKey && !event.ctrlKey && !event.shiftKey && !event.altKey && roleSwitchLink.target !== '_blank') {
      const toggle = roleSwitchLink.closest('.nav-role-toggle');
      const href = roleSwitchLink.href;
      if (toggle && href && !roleSwitchLink.classList.contains('is-active')) {
        event.preventDefault();
        toggle.classList.add('is-switching');
        toggle.classList.toggle('is-to-gestore', roleSwitchLink === toggle.querySelector('a:nth-child(2)'));
        toggle.classList.toggle('is-to-chef', roleSwitchLink === toggle.querySelector('a:nth-child(1)'));
        toggle.querySelectorAll('a').forEach((link) => {
          link.classList.toggle('is-active', link === roleSwitchLink);
        });
        window.setTimeout(() => {
          window.location.href = href;
        }, 430);
        return;
      }
    }

    const requestToggle = event.target.closest('[data-request-toggle]');
    if (requestToggle) {
      toggleRequestCard(requestToggle);
    }

    const modalOpenButton = event.target.closest('[data-modal-open]');
    if (modalOpenButton) {
      const modal = document.getElementById(modalOpenButton.getAttribute('data-modal-open'));
      if (modal && typeof modal.showModal === 'function') {
        modal.showModal();
      }
    }

    const modalCloseButton = event.target.closest('[data-modal-close]');
    if (modalCloseButton) {
      const modal = modalCloseButton.closest('dialog');
      if (modal) {
        modal.close();
      }
    }

    const dialog = event.target.closest('dialog');
    if (dialog && event.target === dialog) {
      dialog.close();
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
