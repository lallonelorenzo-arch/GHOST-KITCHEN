document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.querySelector('[data-nav-toggle]');
  const links = document.querySelector('[data-nav-links]');
  const user = document.querySelector('.nav-user');
  const accountMenuToggle = document.querySelector('[data-account-menu-toggle]');
  const accountMenuPanel = document.querySelector('[data-account-menu-panel]');

  if (toggle && links) {
    toggle.addEventListener('click', () => {
      links.classList.toggle('is-open');
      if (user) {
        user.classList.toggle('is-open');
      }
      toggle.setAttribute('aria-expanded', links.classList.contains('is-open') ? 'true' : 'false');
    });
  }

  if (accountMenuToggle && accountMenuPanel) {
    const closeAccountMenu = () => {
      accountMenuPanel.hidden = true;
      accountMenuToggle.setAttribute('aria-expanded', 'false');
    };

    accountMenuToggle.addEventListener('click', (event) => {
      event.stopPropagation();
      const nextOpen = accountMenuPanel.hidden;
      accountMenuPanel.hidden = !nextOpen;
      accountMenuToggle.setAttribute('aria-expanded', nextOpen ? 'true' : 'false');
    });

    accountMenuPanel.addEventListener('click', (event) => event.stopPropagation());
    document.addEventListener('click', closeAccountMenu);
    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') {
        closeAccountMenu();
        accountMenuToggle.focus();
      }
    });
  }

  document.querySelectorAll('[data-filter-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
      const form = button.closest('form');
      const panel = form ? form.querySelector('[data-filter-panel]') : null;
      if (!panel) {
        return;
      }

      const nextOpen = panel.hasAttribute('hidden');
      panel.toggleAttribute('hidden', !nextOpen);
      button.setAttribute('aria-expanded', nextOpen ? 'true' : 'false');
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

  document.querySelectorAll('[data-chef-booking]').forEach((bookingRoot) => {
    const form = document.querySelector('[data-chef-booking-form]');
    const dialog = document.getElementById('chef-booking-dialog');
    const accessDialog = document.getElementById('chef-booking-access-modal');
    const availabilityNode = bookingRoot.querySelector('[data-chef-availability]');
    const menuOptions = Array.from(bookingRoot.querySelectorAll('[data-menu-option]'));
    const menuPopover = bookingRoot.querySelector('[data-menu-required-popover]');
    const selectedLabel = bookingRoot.querySelector('[data-selected-menu-label]');
    const boxSummary = bookingRoot.querySelector('[data-booking-box-summary]');
    const boxMenu = bookingRoot.querySelector('[data-booking-box-menu]');
    const startButton = bookingRoot.querySelector('[data-chef-booking-start]');

    if (!form || !dialog || !availabilityNode || !startButton) {
      return;
    }

    let availability = [];
    try {
      availability = JSON.parse(availabilityNode.textContent || '[]');
    } catch (error) {
      availability = [];
    }

    const slotsByDate = availability.reduce((result, slot) => {
      if (!slot.date || !slot.period) {
        return result;
      }
      result[slot.date] = result[slot.date] || [];
      if (!result[slot.date].some((item) => item.period === slot.period)) {
        result[slot.date].push(slot);
      }
      return result;
    }, {});

    const steps = Array.from(form.querySelectorAll('[data-chef-booking-step]'));
    const indicators = Array.from(form.querySelectorAll('[data-chef-step-indicator]'));
    const prevButton = form.querySelector('[data-chef-booking-prev]');
    const nextButton = form.querySelector('[data-chef-booking-next]');
    const submitButton = form.querySelector('[data-chef-booking-submit]');
    const dateInput = form.querySelector('[data-booking-date]');
    const periodInput = form.querySelector('[data-booking-period]');
    const menuInput = form.querySelector('[data-booking-menu-id]');
    const calendarGrid = form.querySelector('[data-calendar-grid]');
    const calendarTitle = form.querySelector('[data-calendar-title]');
    const calendarPrev = form.querySelector('[data-calendar-prev]');
    const calendarNext = form.querySelector('[data-calendar-next]');
    const periodPicker = form.querySelector('[data-period-picker]');
    const periodOptions = form.querySelector('[data-period-options]');
    const useSavedAddress = form.querySelector('[data-use-saved-address]');
    const guestInput = form.querySelector('[data-booking-guests]');
    let currentStep = 1;
    let selectedMenu = null;
    let selectedDate = '';
    let selectedPeriod = '';
    let menuPopoverTimer = null;

    const today = new Date();
    const firstVisibleMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    const lastVisibleMonth = new Date(today.getFullYear(), today.getMonth() + 11, 1);
    let visibleMonth = new Date(firstVisibleMonth);

    const toDateKey = (date) => {
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const day = String(date.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
    };

    const showDialog = (target) => {
      if (target && typeof target.showModal === 'function') {
        target.showModal();
      }
    };

    const hideMenuPopover = () => {
      if (menuPopoverTimer !== null) {
        window.clearTimeout(menuPopoverTimer);
        menuPopoverTimer = null;
      }
      if (menuPopover) {
        menuPopover.hidden = true;
      }
    };

    const showMenuPopover = () => {
      if (!menuPopover) {
        return;
      }
      hideMenuPopover();
      menuPopover.hidden = false;
      menuPopoverTimer = window.setTimeout(hideMenuPopover, 2600);
    };

    const formatMoney = (value) => new Intl.NumberFormat('it-IT', {
      style: 'currency',
      currency: 'EUR',
    }).format(value);

    const formatDate = (dateKey) => {
      const parts = dateKey.split('-').map(Number);
      if (parts.length !== 3) {
        return dateKey;
      }
      return new Intl.DateTimeFormat('it-IT', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
      }).format(new Date(parts[0], parts[1] - 1, parts[2]));
    };

    const setError = (step, message = '') => {
      const error = form.querySelector(`[data-step-error="${step}"]`);
      if (!error) {
        return;
      }
      error.textContent = message;
      error.hidden = message === '';
    };

    const showStep = (step) => {
      currentStep = Math.max(1, Math.min(3, step));
      steps.forEach((item) => {
        const active = Number(item.dataset.chefBookingStep || 0) === currentStep;
        item.classList.toggle('is-active', active);
        item.toggleAttribute('hidden', !active);
      });
      indicators.forEach((item) => {
        const itemStep = Number(item.dataset.chefStepIndicator || 0);
        item.classList.toggle('is-active', itemStep === currentStep);
        item.classList.toggle('is-complete', itemStep < currentStep);
      });
      prevButton.hidden = currentStep === 1;
      nextButton.hidden = currentStep === 3;
      submitButton.hidden = currentStep !== 3;
      setError(currentStep);
      form.scrollTop = 0;
      form.querySelector(`[data-chef-booking-step="${currentStep}"] h3`)?.focus({ preventScroll: true });
    };

    const selectPeriod = (slot) => {
      selectedPeriod = slot.period;
      periodInput.value = slot.period;
      periodOptions.querySelectorAll('button').forEach((button) => {
        const selected = button.dataset.period === selectedPeriod;
        button.classList.toggle('is-selected', selected);
        button.setAttribute('aria-pressed', selected ? 'true' : 'false');
      });
      setError(1);
    };

    const renderPeriods = () => {
      const slots = slotsByDate[selectedDate] || [];
      periodOptions.replaceChildren();
      slots
        .sort((a, b) => (a.period === 'pranzo' ? 0 : 1) - (b.period === 'pranzo' ? 0 : 1))
        .forEach((slot) => {
          const button = document.createElement('button');
          const periodName = document.createElement('strong');
          const periodTime = document.createElement('small');
          button.type = 'button';
          button.dataset.period = slot.period;
          button.setAttribute('aria-pressed', 'false');
          periodName.textContent = slot.period === 'pranzo' ? 'Pranzo' : 'Cena';
          periodTime.textContent = `${slot.start} - ${slot.end}`;
          button.append(periodName, periodTime);
          button.addEventListener('click', () => selectPeriod(slot));
          periodOptions.append(button);
        });
      periodPicker.hidden = slots.length === 0;
      if (slots.length === 1) {
        selectPeriod(slots[0]);
      }
    };

    const selectDate = (dateKey) => {
      selectedDate = dateKey;
      selectedPeriod = '';
      dateInput.value = dateKey;
      periodInput.value = '';
      calendarGrid.querySelectorAll('[data-calendar-date]').forEach((button) => {
        const selected = button.dataset.calendarDate === dateKey;
        button.classList.toggle('is-selected', selected);
        button.setAttribute('aria-pressed', selected ? 'true' : 'false');
      });
      renderPeriods();
      setError(1);
    };

    const renderCalendar = () => {
      calendarGrid.replaceChildren();
      calendarTitle.textContent = new Intl.DateTimeFormat('it-IT', {
        month: 'long',
        year: 'numeric',
      }).format(visibleMonth);

      const year = visibleMonth.getFullYear();
      const month = visibleMonth.getMonth();
      const firstDay = new Date(year, month, 1);
      const offset = (firstDay.getDay() + 6) % 7;
      const days = new Date(year, month + 1, 0).getDate();

      for (let blank = 0; blank < offset; blank += 1) {
        const spacer = document.createElement('span');
        spacer.className = 'is-empty';
        spacer.setAttribute('aria-hidden', 'true');
        calendarGrid.append(spacer);
      }

      for (let day = 1; day <= days; day += 1) {
        const date = new Date(year, month, day);
        const dateKey = toDateKey(date);
        const slots = slotsByDate[dateKey] || [];
        const button = document.createElement('button');
        button.type = 'button';
        button.innerHTML = `<span>${day}</span>${slots.map((slot) => `<i>${slot.period === 'pranzo' ? 'P' : 'C'}</i>`).join('')}`;

        if (slots.length === 0 || date < new Date(today.getFullYear(), today.getMonth(), today.getDate())) {
          button.disabled = true;
          button.className = 'is-unavailable';
        } else {
          button.dataset.calendarDate = dateKey;
          button.setAttribute('aria-label', `${formatDate(dateKey)}, disponibile per ${slots.map((slot) => slot.period).join(' e ')}`);
          button.setAttribute('aria-pressed', dateKey === selectedDate ? 'true' : 'false');
          button.classList.toggle('is-selected', dateKey === selectedDate);
          button.addEventListener('click', () => selectDate(dateKey));
        }
        calendarGrid.append(button);
      }

      calendarPrev.disabled = visibleMonth <= firstVisibleMonth;
      calendarNext.disabled = visibleMonth >= lastVisibleMonth;
    };

    const updateReview = () => {
      const guests = Math.max(1, Number(guestInput?.value || 1));
      const price = Number(selectedMenu?.price || 0);
      const address = form.querySelector('[name="indirizzo"]')?.value.trim() || '';
      const civic = form.querySelector('[name="numeroCivico"]')?.value.trim() || '';
      const city = form.querySelector('[name="citta"]')?.value.trim() || '';
      const province = form.querySelector('[name="provincia"]')?.value.trim() || '';
      const requests = form.querySelector('[data-booking-requests]')?.value.trim() || '';
      const wine = form.querySelector('[name="abbinamentoVini"]:checked')?.value === '1';

      form.querySelector('[data-review-menu]').textContent = selectedMenu?.name || '';
      form.querySelector('[data-review-date]').textContent = `${formatDate(selectedDate)} · ${selectedPeriod === 'pranzo' ? 'Pranzo' : 'Cena'}`;
      form.querySelector('[data-review-address]').textContent = `${address} ${civic}, ${city} (${province})`;
      form.querySelector('[data-review-guests]').textContent = String(guests);
      form.querySelector('[data-review-wine]').textContent = wine ? 'Sì' : 'No';
      form.querySelector('[data-review-total]').textContent = formatMoney(price * guests);
      form.querySelector('[data-review-requests]').textContent = requests || 'Nessuna richiesta particolare';
    };

    const validateStep = () => {
      if (currentStep === 1) {
        if (!selectedDate || !selectedPeriod) {
          setError(1, 'Seleziona una data e il servizio pranzo o cena.');
          return false;
        }
        return true;
      }

      if (currentStep === 2) {
        const fields = Array.from(form.querySelectorAll('[data-chef-booking-step="2"] input[required], [data-chef-booking-step="2"] textarea[required]'));
        for (const field of fields) {
          if (!field.reportValidity()) {
            setError(2, 'Completa tutti i dati obbligatori.');
            return false;
          }
        }
        return true;
      }

      const paymentMethod = form.querySelector('[name="idMetodoPagamento"]');
      if (!paymentMethod || !paymentMethod.reportValidity()) {
        setError(3, 'Seleziona un metodo di pagamento.');
        return false;
      }
      return true;
    };

    menuOptions.forEach((option) => {
      option.addEventListener('change', () => {
        selectedMenu = {
          id: option.value,
          name: option.dataset.menuName || '',
          price: option.dataset.menuPrice || '0',
        };
        menuInput.value = selectedMenu.id;
        selectedLabel.textContent = selectedMenu.name;
        boxMenu.textContent = selectedMenu.name;
        boxSummary.hidden = false;
        hideMenuPopover();
      });
    });

    startButton.addEventListener('click', () => {
      if (!selectedMenu) {
        showMenuPopover();
        return;
      }
      if (form.dataset.canBook !== '1') {
        showDialog(accessDialog);
        return;
      }
      showStep(1);
      renderCalendar();
      showDialog(dialog);
    });

    document.addEventListener('click', (event) => {
      if (!startButton.contains(event.target) && !menuPopover?.contains(event.target)) {
        hideMenuPopover();
      }
    });

    calendarPrev.addEventListener('click', () => {
      visibleMonth = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth() - 1, 1);
      renderCalendar();
    });

    calendarNext.addEventListener('click', () => {
      visibleMonth = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth() + 1, 1);
      renderCalendar();
    });

    if (useSavedAddress) {
      useSavedAddress.addEventListener('change', () => {
        form.querySelectorAll('[data-address-field]').forEach((field) => {
          if (useSavedAddress.checked) {
            field.value = field.dataset.savedValue || '';
          }
          field.readOnly = useSavedAddress.checked;
        });
      });
    }

    nextButton.addEventListener('click', () => {
      if (!validateStep()) {
        return;
      }
      if (currentStep === 2) {
        updateReview();
      }
      showStep(currentStep + 1);
    });

    prevButton.addEventListener('click', () => showStep(currentStep - 1));

    form.addEventListener('submit', (event) => {
      if (!validateStep()) {
        event.preventDefault();
        return;
      }
      submitButton.disabled = true;
      submitButton.textContent = 'Pagamento in corso...';
    });

    renderCalendar();
  });

  document.querySelectorAll('.service-period-options').forEach((periodGroup) => {
    const form = periodGroup.closest('form');
    const checkboxes = Array.from(periodGroup.querySelectorAll('input[type="checkbox"]'));
    if (!form || checkboxes.length === 0) {
      return;
    }

    const clearValidity = () => checkboxes[0].setCustomValidity('');
    checkboxes.forEach((checkbox) => checkbox.addEventListener('change', clearValidity));
    form.addEventListener('submit', (event) => {
      if (checkboxes.some((checkbox) => checkbox.checked)) {
        clearValidity();
        return;
      }
      event.preventDefault();
      checkboxes[0].setCustomValidity('Seleziona almeno una fascia tra pranzo e cena.');
      checkboxes[0].reportValidity();
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
    if (detail) {
      const nextOpen = detail.hasAttribute('hidden');
      detail.toggleAttribute('hidden', !nextOpen);
      card.classList.toggle('is-open', nextOpen);
      requestToggle.setAttribute('aria-expanded', nextOpen ? 'true' : 'false');
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
