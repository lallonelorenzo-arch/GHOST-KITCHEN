document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.querySelector('[data-nav-toggle]');
  const links = document.querySelector('[data-nav-links]');
  const user = document.querySelector('.nav-user');

  if (!toggle || !links) {
    return;
  }

  toggle.addEventListener('click', () => {
    links.classList.toggle('is-open');
    if (user) {
      user.classList.toggle('is-open');
    }
  });
});
