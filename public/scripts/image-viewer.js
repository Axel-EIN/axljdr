(function () {
  const ZOOM_LEVELS = [1, 1.5, 2, 3, 4];
  const MAX_HEIGHT = 0.8;

  document.querySelectorAll('.image-viewer').forEach(function (viewer) {
    const picture = viewer.querySelector('.image-viewer-picture');
    const zoomIn = viewer.querySelector('[data-viewer-zoom="in"]');
    const zoomOut = viewer.querySelector('[data-viewer-zoom="out"]');
    const fullscreenBtn = viewer.querySelector('[data-viewer-fullscreen]');

    let x = 0, y = 0, zoom = 1;
    let drag = null;
    let animationTimer = null;

    function loadFullImage() {
      if (!picture.hasAttribute('srcset')) return;
      picture.removeAttribute('srcset');
      picture.removeAttribute('sizes');
    }

    function clampAxis(offset, base, frame, value) {
      const size = base * zoom;
      if (size <= frame) return (base - size) / 2;
      return Math.min(-offset, Math.max(frame - size - offset, value));
    }

    function clamp() {
      x = clampAxis(picture.offsetLeft, picture.offsetWidth, viewer.clientWidth, x);
      y = clampAxis(picture.offsetTop, picture.offsetHeight, viewer.clientHeight, y);
    }

    function render() {
      picture.style.transform = 'translate(' + x + 'px, ' + y + 'px) scale(' + zoom + ')';
      viewer.classList.toggle('zoomed', zoom > 1);
      zoomOut.disabled = zoom <= ZOOM_LEVELS[0];
      zoomIn.disabled = zoom >= ZOOM_LEVELS[ZOOM_LEVELS.length - 1];
    }

    function animate() {
      viewer.classList.add('animated');
      clearTimeout(animationTimer);
      animationTimer = setTimeout(function () { viewer.classList.remove('animated'); }, 400);
    }

    function zoomAt(next, cx, cy) {
      const px = (cx - picture.offsetLeft - x) / zoom;
      const py = (cy - picture.offsetTop - y) / zoom;
      zoom = next;
      x = cx - picture.offsetLeft - px * zoom;
      y = cy - picture.offsetTop - py * zoom;
      if (zoom > 1) loadFullImage();
      clamp();
      render();
    }

    function step(direction) {
      const levels = direction > 0 ? ZOOM_LEVELS : ZOOM_LEVELS.slice().reverse();
      const next = levels.find(function (level) {
        return direction > 0 ? level > zoom + 0.001 : level < zoom - 0.001;
      });
      if (next === undefined) return;
      animate();
      zoomAt(next, viewer.clientWidth / 2, viewer.clientHeight / 2);
    }

    function fit() {
      if (!picture.naturalWidth) return;
      const ratio = picture.naturalWidth / picture.naturalHeight;
      const maxHeight = document.fullscreenElement === viewer ? viewer.clientHeight : window.innerHeight * MAX_HEIGHT;
      const width = Math.min(viewer.clientWidth, maxHeight * ratio);
      picture.style.width = width + 'px';
      picture.style.height = width / ratio + 'px';
    }

    function refresh() {
      fit();
      clamp();
      render();
    }

    zoomIn.addEventListener('click', function () { step(1); });
    zoomOut.addEventListener('click', function () { step(-1); });

    if (!viewer.requestFullscreen) {
      fullscreenBtn.hidden = true;
    } else {
      fullscreenBtn.addEventListener('click', function () {
        if (document.fullscreenElement === viewer) {
          document.exitFullscreen();
        } else {
          loadFullImage();
          viewer.requestFullscreen();
        }
      });
      document.addEventListener('fullscreenchange', function () {
        fullscreenBtn.setAttribute('aria-pressed', document.fullscreenElement === viewer);
        zoom = 1;
        x = 0;
        y = 0;
        refresh();
      });
    }

    function start(clientX, clientY) {
      if (zoom <= 1) return;
      drag = { x: clientX, y: clientY, offsetX: x, offsetY: y };
      viewer.classList.add('dragging');
    }

    function move(clientX, clientY) {
      if (!drag) return;
      x = drag.offsetX + clientX - drag.x;
      y = drag.offsetY + clientY - drag.y;
      clamp();
      render();
    }

    function end() {
      drag = null;
      viewer.classList.remove('dragging');
    }

    picture.addEventListener('mousedown', function (e) {
      e.preventDefault();
      start(e.clientX, e.clientY);
    });
    document.addEventListener('mousemove', function (e) { move(e.clientX, e.clientY); });
    document.addEventListener('mouseup', end);

    picture.addEventListener('touchstart', function (e) {
      if (e.touches.length === 1) start(e.touches[0].clientX, e.touches[0].clientY);
    }, { passive: true });
    document.addEventListener('touchmove', function (e) {
      if (drag && e.touches.length === 1) move(e.touches[0].clientX, e.touches[0].clientY);
    }, { passive: true });
    document.addEventListener('touchend', end);
    document.addEventListener('touchcancel', end);

    window.addEventListener('resize', refresh);

    if (picture.complete && picture.naturalWidth) {
      refresh();
    } else {
      picture.addEventListener('load', refresh, { once: true });
    }
  });
})();
