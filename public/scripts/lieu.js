(function () {
  const frame = document.getElementById('map-frame');
  const map = document.getElementById('map-drag');
  const pointer = document.querySelector('#minimap .localisator-ui');
  const mapImage = map ? map.querySelector('.map-image') : null;
  const toggleBtn = document.getElementById('map-toggle-territoires');
  if (!frame || !map) return;

  const baseLocX = parseFloat(frame.dataset.locx);
  const baseLocY = parseFloat(frame.dataset.locy);

  const DRAG_THRESHOLD = 5;

  let isDown = false;
  let startX = 0, startY = 0;
  let offsetX = 0, offsetY = 0;
  let pendingX = 0, pendingY = 0;
  let baseOffsetX = 0, baseOffsetY = 0;
  let initialized = false;
  let dragMoved = false;

  function readInitialOffset() {
    const cs = window.getComputedStyle(map);
    const m = new DOMMatrixReadOnly(cs.transform);
    offsetX = m.m41;
    offsetY = m.m42;
    baseOffsetX = offsetX;
    baseOffsetY = offsetY;
    initialized = true;
  }

  function updatePointer(x, y) {
    if (!pointer) return;
    const w = map.offsetWidth;
    const h = map.offsetHeight;
    if (!w || !h) return;
    const dxFromBase = x - baseOffsetX;
    const dyFromBase = y - baseOffsetY;
    const newLocX = baseLocX - (dxFromBase / w) * 100;
    const newLocY = baseLocY - (dyFromBase / h) * 100;
    pointer.style.left = newLocX + '%';
    pointer.style.top = newLocY + '%';
  }

  function setPx(x, y) {
    map.style.transform = 'translate(' + x + 'px, ' + y + 'px)';
    updatePointer(x, y);
  }

  function start(clientX, clientY) {
    if (!initialized) readInitialOffset();
    isDown = true;
    startX = clientX;
    startY = clientY;
    pendingX = offsetX;
    pendingY = offsetY;
    dragMoved = false;
    frame.classList.add('dragging');
  }

  function move(clientX, clientY) {
    if (!isDown) return;
    const dx = clientX - startX;
    const dy = clientY - startY;
    if (!dragMoved && (Math.abs(dx) > DRAG_THRESHOLD || Math.abs(dy) > DRAG_THRESHOLD)) {
      dragMoved = true;
    }
    pendingX = offsetX + dx;
    pendingY = offsetY + dy;
    setPx(pendingX, pendingY);
  }

  function end() {
    if (!isDown) return;
    offsetX = pendingX;
    offsetY = pendingY;
    isDown = false;
    frame.classList.remove('dragging');
  }

  map.addEventListener('mousedown', function (e) {
    e.preventDefault();
    start(e.clientX, e.clientY);
  });
  document.addEventListener('mousemove', function (e) {
    move(e.clientX, e.clientY);
  });
  document.addEventListener('mouseup', end);

  map.addEventListener('click', function (e) {
    if (dragMoved) {
      e.preventDefault();
      e.stopPropagation();
      dragMoved = false;
    }
  }, true);

  map.addEventListener('touchstart', function (e) {
    if (e.touches.length !== 1) return;
    start(e.touches[0].clientX, e.touches[0].clientY);
  }, { passive: true });
  document.addEventListener('touchmove', function (e) {
    if (!isDown || e.touches.length !== 1) return;
    move(e.touches[0].clientX, e.touches[0].clientY);
  }, { passive: true });
  document.addEventListener('touchend', end);
  document.addEventListener('touchcancel', end);

  map.addEventListener('dragstart', function (e) { e.preventDefault(); });

  // TOGGLE TERRITOIRES — bascule entre la carte clean et la carte frontier
  if (toggleBtn && mapImage) {
    const cleanSrc = toggleBtn.dataset.mapClean;
    const frontierSrc = toggleBtn.dataset.mapFrontier;
    const label = toggleBtn.querySelector('.label');
    let showingFrontier = false;
    toggleBtn.addEventListener('click', function () {
      showingFrontier = !showingFrontier;
      mapImage.src = showingFrontier ? frontierSrc : cleanSrc;
      toggleBtn.classList.toggle('active', showingFrontier);
      if (label) label.textContent = showingFrontier ? 'Masquer territoires' : 'Afficher territoires';
    });
  }
})();
