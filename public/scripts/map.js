(function () {
  const frame = document.querySelector('.map-frame');
  if (!frame) return;

  const map = frame.querySelector('.map-drag');
  const mapImage = map.querySelector('.map-image');
  const pointer = frame.querySelector('.minimap .localisator-ui');
  const view = frame.querySelector('.minimap .minimap-view');
  const zoomLevel = frame.querySelector('.map-zoom-level');
  const world = frame.classList.contains('world');
  const markerSize = parseFloat(getComputedStyle(frame).getPropertyValue('--marker-size')) || 1;

  const DRAG_THRESHOLD = 5;
  const WHEEL_PAUSE = 200;
  const ZOOM_LEVELS = [0.25, 0.5, 1, 1.5, 2];
  const MIN_SCALE = ZOOM_LEVELS[0];
  const MAX_SCALE = ZOOM_LEVELS[ZOOM_LEVELS.length - 1];
  const HD_SCALE = 1;
  const FOCUS_SCALE = 1;
  const ZOOMED_OUT_SCALE = 0.45;
  const MARKER_MIN_SCALE = 0.5;

  let x = 0, y = 0, scale = 1;
  let mapW = 0, mapH = 0;
  let territories = 'territories' in frame.dataset;
  let pendingSrc = null;
  let ready = false;
  let drag = null;
  let pinch = null;
  let dragMoved = false;
  let animationTimer = null;

  function frameCenter(clientX, clientY) {
    const rect = frame.getBoundingClientRect();
    return { x: clientX - rect.left - rect.width / 2, y: clientY - rect.top - rect.height / 2 };
  }

  function boundScale(value) {
    return Math.min(MAX_SCALE, Math.max(MIN_SCALE, value));
  }

  function nextLevel(direction) {
    const levels = direction > 0 ? ZOOM_LEVELS : ZOOM_LEVELS.slice().reverse();
    const next = levels.find(function (level) {
      return direction > 0 ? level > scale + 0.001 : level < scale - 0.001;
    });
    return next === undefined ? levels[levels.length - 1] : next;
  }

  function clamp() {
    if (!world) return;
    x = Math.min(0, Math.max(-mapW * scale, x));
    y = Math.min(0, Math.max(-mapH * scale, y));
  }

  function updateMinimap() {
    const width = mapW * scale;
    const height = mapH * scale;

    if (pointer) {
      pointer.style.left = (-x / width * 100) + '%';
      pointer.style.top = (-y / height * 100) + '%';
    }

    if (view) {
      view.style.left = ((-x - frame.clientWidth / 2) / width * 100) + '%';
      view.style.top = ((-y - frame.clientHeight / 2) / height * 100) + '%';
      view.style.width = (frame.clientWidth / width * 100) + '%';
      view.style.height = (frame.clientHeight / height * 100) + '%';
    }
  }

  function render() {
    map.style.transform = 'translate(' + x + 'px, ' + y + 'px) scale(' + scale + ')';
    if (zoomLevel) zoomLevel.textContent = '×' + scale.toLocaleString('fr-FR', { maximumFractionDigits: 2 });
    frame.style.setProperty('--marker-scale', markerSize * Math.min(Math.max(scale, MARKER_MIN_SCALE), 1) / scale);
    frame.classList.toggle('zoomed-out', scale < ZOOMED_OUT_SCALE);
    frame.style.setProperty('--icon-scale', Math.max(scale, 1));
    updateMinimap();
    updateImage();
  }

  function imageSource() {
    return mapImage.dataset[(territories ? 'frontier' : 'clean') + (scale > HD_SCALE ? 'Hd' : '')];
  }

  function updateImage() {
    const src = imageSource();
    if (src === pendingSrc || mapImage.getAttribute('src') === src) return;
    pendingSrc = src;
    const loader = new Image();
    loader.src = src;
    loader.decode()
      .then(function () {
        if (imageSource() === src) mapImage.src = src;
      })
      .catch(function () {})
      .finally(function () {
        if (pendingSrc === src) pendingSrc = null;
      });
  }

  function animate() {
    map.classList.add('animated');
    clearTimeout(animationTimer);
    animationTimer = setTimeout(function () { map.classList.remove('animated'); }, 600);
  }

  function centerOn(locX, locY, nextScale) {
    scale = boundScale(nextScale);
    x = -locX / 100 * mapW * scale;
    y = -locY / 100 * mapH * scale;
    clamp();
  }

  function zoomAt(nextScale, cx, cy) {
    nextScale = boundScale(nextScale);
    x = cx - (cx - x) * nextScale / scale;
    y = cy - (cy - y) * nextScale / scale;
    scale = nextScale;
    clamp();
    render();
  }

  function flyTo(locX, locY) {
    centerOn(locX, locY, Math.max(scale, FOCUS_SCALE));
    animate();
    render();
  }

  function highlight(id) {
    frame.querySelectorAll('.lieu-other-icon.focused').forEach(function (marker) {
      marker.classList.remove('focused');
    });
    if (!id) return;
    const marker = frame.querySelector('.lieu-other-icon[data-lieu="' + id + '"]');
    if (!marker) return;
    void marker.offsetWidth;
    marker.classList.add('focused');
  }

  function init() {
    mapW = mapImage.naturalWidth;
    mapH = mapImage.naturalHeight;
    if (!mapW || !mapH) return;
    mapImage.style.width = mapW + 'px';
    mapImage.style.height = mapH + 'px';

    const locX = parseFloat(frame.dataset.locx);
    const locY = parseFloat(frame.dataset.locy);

    if (world) {
      if (isNaN(locX) || isNaN(locY)) {
        scale = MIN_SCALE;
        x = -mapW * scale / 2;
        y = -mapH * scale / 2;
      } else {
        centerOn(locX, locY, FOCUS_SCALE);
      }
    } else {
      centerOn(locX, locY, parseFloat(frame.dataset.zoom) || FOCUS_SCALE);
    }

    ready = true;
    render();
    map.classList.add('ready');
    highlight(frame.dataset.highlight);
  }

  if (mapImage.complete && mapImage.naturalWidth) {
    init();
  } else {
    mapImage.addEventListener('load', init, { once: true });
  }

  function start(clientX, clientY) {
    if (!ready || !world) return;
    drag = { x: clientX, y: clientY, offsetX: x, offsetY: y };
    dragMoved = false;
    frame.classList.add('dragging');
  }

  function move(clientX, clientY) {
    if (!drag) return;
    const dx = clientX - drag.x;
    const dy = clientY - drag.y;
    if (!dragMoved && (Math.abs(dx) > DRAG_THRESHOLD || Math.abs(dy) > DRAG_THRESHOLD)) {
      dragMoved = true;
    }
    x = drag.offsetX + dx;
    y = drag.offsetY + dy;
    clamp();
    render();
  }

  function end() {
    if (!drag) return;
    drag = null;
    frame.classList.remove('dragging');
  }

  function pinchOf(touches) {
    const center = frameCenter((touches[0].clientX + touches[1].clientX) / 2, (touches[0].clientY + touches[1].clientY) / 2);
    center.distance = Math.hypot(touches[0].clientX - touches[1].clientX, touches[0].clientY - touches[1].clientY);
    return center;
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
    if (e.touches.length === 1) {
      start(e.touches[0].clientX, e.touches[0].clientY);
    } else if (world && ready && e.touches.length === 2) {
      end();
      pinch = Object.assign(pinchOf(e.touches), { offsetX: x, offsetY: y, scale: scale });
    }
  }, { passive: true });
  document.addEventListener('touchmove', function (e) {
    if (pinch && e.touches.length === 2) {
      const current = pinchOf(e.touches);
      const next = boundScale(pinch.scale * current.distance / pinch.distance);
      x = current.x - (pinch.x - pinch.offsetX) * next / pinch.scale;
      y = current.y - (pinch.y - pinch.offsetY) * next / pinch.scale;
      scale = next;
      dragMoved = true;
      clamp();
      render();
      return;
    }
    if (!drag || e.touches.length !== 1) return;
    move(e.touches[0].clientX, e.touches[0].clientY);
  }, { passive: true });
  function touchEnd(e) {
    end();
    if (e.touches.length < 2) pinch = null;
  }
  document.addEventListener('touchend', touchEnd);
  document.addEventListener('touchcancel', touchEnd);

  map.addEventListener('dragstart', function (e) { e.preventDefault(); });

  function stored(key) {
    try {
      return localStorage.getItem('map.' + key);
    } catch (e) {
      return null;
    }
  }

  function store(key, on) {
    try {
      localStorage.setItem('map.' + key, on ? '1' : '0');
    } catch (e) {}
  }

  const toggles = {
    labels: function (on) {
      frame.classList.toggle('labels-hidden', !on);
    },
    territories: function (on) {
      territories = on;
      updateImage();
    },
    party: function (on) {
      frame.classList.toggle('party-hidden', !on);
    },
  };

  frame.querySelectorAll('[data-map-toggle]').forEach(function (button) {
    const key = button.dataset.mapToggle;
    const apply = toggles[key];
    if (!apply) return;

    function set(on) {
      apply(on, button);
      button.classList.toggle('active', on);
      button.setAttribute('aria-pressed', on);
      if (world) store(key, on);
    }

    const saved = world ? stored(key) : null;
    if (saved !== null) set(saved === '1');

    button.addEventListener('click', function () {
      set(button.getAttribute('aria-pressed') !== 'true');
    });
  });

  const fullscreenBtn = frame.querySelector('[data-map-toggle="fullscreen"]');
  if (fullscreenBtn) {
    if (!frame.requestFullscreen) {
      fullscreenBtn.hidden = true;
    } else {
      fullscreenBtn.addEventListener('click', function () {
        if (document.fullscreenElement) {
          document.exitFullscreen();
        } else {
          frame.requestFullscreen();
        }
      });
      document.addEventListener('fullscreenchange', function () {
        const active = document.fullscreenElement === frame;
        fullscreenBtn.classList.toggle('active', active);
        fullscreenBtn.setAttribute('aria-pressed', active);
      });
    }
  }

  const recenterBtn = frame.querySelector('[data-map-recenter]');
  if (recenterBtn) {
    recenterBtn.addEventListener('click', function () {
      if (ready) flyTo(parseFloat(recenterBtn.dataset.locx), parseFloat(recenterBtn.dataset.locy));
    });
  }

  if (!world) return;

  window.addEventListener('resize', function () {
    if (!ready) return;
    clamp();
    render();
  });

  frame.querySelectorAll('[data-map-zoom]').forEach(function (button) {
    button.addEventListener('click', function () {
      if (!ready) return;
      animate();
      zoomAt(nextLevel(button.dataset.mapZoom === 'in' ? 1 : -1), 0, 0);
    });
  });

  let wheelLock = 0;
  frame.addEventListener('wheel', function (e) {
    if (!ready || e.target.closest('.map-panel, .map-search')) return;
    e.preventDefault();
    const now = Date.now();
    const locked = now < wheelLock;
    wheelLock = now + WHEEL_PAUSE;
    if (locked || e.deltaY === 0) return;
    const center = frameCenter(e.clientX, e.clientY);
    animate();
    zoomAt(nextLevel(e.deltaY < 0 ? 1 : -1), center.x, center.y);
  }, { passive: false });

  const controls = frame.querySelector('.map-controls');
  const controlsToggle = controls.querySelector('.map-controls-toggle');
  controlsToggle.addEventListener('click', function () {
    const open = controls.classList.toggle('open');
    controlsToggle.setAttribute('aria-expanded', open);
  });

  function selectPortrait(portrait) {
    frame.querySelectorAll('.map-portrait.selected').forEach(function (other) {
      if (other === portrait) return;
      other.classList.remove('selected');
      other.querySelector('.map-portrait-toggle').setAttribute('aria-expanded', 'false');
    });
    if (!portrait) return;
    portrait.classList.add('selected');
    portrait.querySelector('.map-portrait-toggle').setAttribute('aria-expanded', 'true');
  }

  const panel = frame.querySelector('.map-panel');
  const panelContent = panel.querySelector('.map-panel-content');
  let panelRequest = 0;

  function closePanel() {
    panelRequest++;
    panel.hidden = true;
    panelContent.innerHTML = '';
    selectPortrait(null);
  }

  function openPanel(trigger) {
    if (!trigger || !trigger.dataset.panel) return;
    const current = ++panelRequest;
    const portrait = trigger.closest('.map-portrait');
    selectPortrait(portrait);
    if (!portrait) highlight(trigger.dataset.lieu);
    fetch(trigger.dataset.panel)
      .then(function (response) { return response.ok ? response.text() : Promise.reject(response); })
      .then(function (html) {
        if (current !== panelRequest) return;
        panelContent.innerHTML = html;
        panel.hidden = false;
        panel.scrollTop = 0;
      })
      .catch(function () {
        if (current === panelRequest) closePanel();
      });
  }

  map.addEventListener('click', function (e) {
    const trigger = e.target.closest('.lieu-other-icon[data-panel], .map-portrait-toggle');
    if (!trigger) return;
    e.preventDefault();
    openPanel(trigger);
  });

  panel.querySelector('.map-panel-close').addEventListener('click', closePanel);

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closePanel();
  });

  const search = frame.querySelector('.map-search');
  const input = search.querySelector('.search-bar-input');
  const list = search.querySelector('.search-bar-suggestions');
  const empty = list.querySelector('.search-suggestion-empty');
  const items = Array.from(list.querySelectorAll('.search-suggestion'));

  function normalize(text) {
    return text.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().trim();
  }

  items.forEach(function (item) {
    item.dataset.search = normalize(item.querySelector('.search-suggestion-label').textContent);
  });

  function visibleItems() {
    return items.filter(function (item) { return !item.hidden; });
  }

  function filter() {
    const term = normalize(input.value);
    items.forEach(function (item) {
      item.hidden = !item.dataset.search.includes(term);
      item.classList.remove('active');
    });
    empty.hidden = visibleItems().length > 0;
    list.classList.add('show');
  }

  function openPortrait(id) {
    const portrait = frame.querySelector('.map-portrait[data-personnage="' + id + '"]');
    if (!portrait) return;
    if (frame.classList.contains('party-hidden')) frame.querySelector('[data-map-toggle="party"]').click();
    openPanel(portrait.querySelector('.map-portrait-toggle'));
  }

  function choose(item) {
    input.value = item.querySelector('.search-suggestion-label').textContent;
    list.classList.remove('show');
    input.blur();
    if (!ready) return;
    flyTo(parseFloat(item.dataset.locx), parseFloat(item.dataset.locy));
    highlight(item.dataset.lieu);
    if (item.dataset.personnage) {
      openPortrait(item.dataset.personnage);
    } else {
      openPanel(frame.querySelector('.lieu-other-icon[data-lieu="' + item.dataset.lieu + '"]'));
    }
  }

  function moveActive(step) {
    const visible = visibleItems();
    if (visible.length === 0) return;
    let index = visible.findIndex(function (item) { return item.classList.contains('active'); });
    visible.forEach(function (item) { item.classList.remove('active'); });
    index = index === -1 ? (step > 0 ? 0 : visible.length - 1) : (index + step + visible.length) % visible.length;
    visible[index].classList.add('active');
    visible[index].scrollIntoView({ block: 'nearest' });
  }

  input.addEventListener('input', filter);
  input.addEventListener('focus', filter);

  input.addEventListener('keydown', function (e) {
    if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
      e.preventDefault();
      moveActive(e.key === 'ArrowDown' ? 1 : -1);
    } else if (e.key === 'Enter') {
      e.preventDefault();
      const item = list.querySelector('.search-suggestion.active') || visibleItems()[0];
      if (item) choose(item);
    } else if (e.key === 'Escape') {
      list.classList.remove('show');
    }
  });

  list.addEventListener('click', function (e) {
    const item = e.target.closest('.search-suggestion');
    if (item) choose(item);
  });

  document.addEventListener('click', function (e) {
    if (!search.contains(e.target)) list.classList.remove('show');
  });
})();
