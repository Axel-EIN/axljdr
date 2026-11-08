document.querySelectorAll('[data-carousel]').forEach(function (carousel) {
  const slides = carousel.querySelectorAll('.top-carousel-slide');
  const dots = carousel.querySelectorAll('[data-carousel-to]');
  const SWIPE_DISTANCE = 40;
  let current = 0;
  let touchX = null;

  function show(index) {
    current = (index + slides.length) % slides.length;
    slides.forEach(function (slide, i) {
      slide.classList.toggle('active', i === current);
    });
    dots.forEach(function (dot, i) {
      dot.classList.toggle('active', i === current);
      if (i === current) dot.setAttribute('aria-current', 'true');
      else dot.removeAttribute('aria-current');
    });
  }

  carousel.querySelector('[data-carousel-prev]').addEventListener('click', function () {
    show(current - 1);
  });

  carousel.querySelector('[data-carousel-next]').addEventListener('click', function () {
    show(current + 1);
  });

  dots.forEach(function (dot) {
    dot.addEventListener('click', function () {
      show(Number(dot.dataset.carouselTo));
    });
  });

  carousel.addEventListener('keydown', function (event) {
    if (event.key === 'ArrowLeft') show(current - 1);
    if (event.key === 'ArrowRight') show(current + 1);
  });

  carousel.addEventListener('touchstart', function (event) {
    touchX = event.touches[0].clientX;
  }, { passive: true });

  carousel.addEventListener('touchend', function (event) {
    if (touchX === null) return;
    const distance = event.changedTouches[0].clientX - touchX;
    touchX = null;
    if (Math.abs(distance) > SWIPE_DISTANCE) show(current + (distance < 0 ? 1 : -1));
  });
});
