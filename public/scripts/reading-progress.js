(function () {
  const bar = document.querySelector('.reading-progress');
  const parts = document.querySelectorAll('.part-header, .part-body');
  if (!bar || parts.length === 0) return;

  function update() {
    const start = parts[0].getBoundingClientRect().top + window.scrollY;
    const end = parts[parts.length - 1].getBoundingClientRect().bottom + window.scrollY;
    const span = end - start - window.innerHeight;
    const progress = span > 0 ? (window.scrollY - start) / span : 1;
    bar.style.setProperty('--progress', Math.min(1, Math.max(0, progress)));
  }

  window.addEventListener('scroll', update, { passive: true });
  window.addEventListener('resize', update);
  update();
})();
