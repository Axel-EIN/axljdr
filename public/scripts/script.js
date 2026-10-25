$('.cacher, .voir').each(function () {

        const $button = $(this);

        $($button.data('target'))
            .on('show.bs.collapse', function (e) {
                if (e.target !== this) return;
                $button.removeClass('voir').addClass('cacher hue-gray').html('<i class="fa-solid fa-eye-slash" aria-hidden="true"></i>&nbsp;&nbsp;Cacher');
            })
            .on('hide.bs.collapse', function (e) {
                if (e.target !== this) return;
                $button.removeClass('cacher hue-gray').addClass('voir').html('<i class="fa-solid fa-scroll fa-bounce" aria-hidden="true"></i>&nbsp;&nbsp;Voir les Épisodes');
            });

});

$('.session-card-spacing').hover(mouseEnter, mouseLeave);

function mouseEnter() {
    $(this).find('.btn-primary-style').addClass('fa-bounce');
};

function mouseLeave() {
    $(this).find('.btn-primary-style').removeClass('fa-bounce');
};

$('#caption').click(function (e) {

    const x = e.offsetX,
    y = e.offsetY;

    const xPourcent = x / 280 * 100;
    const yPourcent = y / 400 * 100;

    alert(xPourcent + '% ,' + yPourcent + '%');

});

$('.modal').on('shown.bs.modal', function () {
    $(this).find('[autofocus]').trigger('focus');
});

document.querySelectorAll('.picture-scroller').forEach(function (scroller) {

    const track = scroller.querySelector('.scroller-track');
    const buttons = scroller.querySelectorAll('.scroller-btn');
    const dots = scroller.id ? document.querySelector('.scroller-dots[data-scroller="' + scroller.id + '"]') : null;
    let page = 0;

    function visibleItems() {
        return Array.from(track.children).filter(function (item) { return item.offsetParent !== null; });
    }

    function perPage() {
        return parseInt(getComputedStyle(track).getPropertyValue('--cols'), 10) || 1;
    }

    function pageWidth(items) {
        const cols = perPage();
        return items.length > cols ? items[cols].offsetLeft - items[0].offsetLeft : track.clientWidth;
    }

    function goTo(target) {
        track.scrollTo({ left: target * pageWidth(visibleItems()) });
    }

    function renderDots(pages) {
        if (dots.children.length === pages) return;
        dots.replaceChildren();
        for (let i = 0; i < pages; i++) {
            const dot = document.createElement('button');
            dot.type = 'button';
            dot.className = 'scroller-dot';
            dot.setAttribute('aria-label', 'Page ' + (i + 1));
            dot.addEventListener('click', function () { goTo(i); });
            dots.append(dot);
        }
    }

    function update() {
        const items = visibleItems();
        const max = track.scrollWidth - track.clientWidth;
        const pages = max > 1 ? Math.ceil(items.length / perPage()) : 0;
        page = track.scrollLeft >= max - 1 ? pages - 1 : Math.round(track.scrollLeft / pageWidth(items));

        scroller.classList.toggle('overflowing', max > 1);
        buttons.forEach(function (button) {
            button.disabled = button.dataset.direction < 0 ? track.scrollLeft <= 1 : track.scrollLeft >= max - 1;
        });

        if (dots) {
            renderDots(pages);
            Array.from(dots.children).forEach(function (dot, i) {
                dot.classList.toggle('active', i === page);
            });
        }
    }

    buttons.forEach(function (button) {
        button.addEventListener('click', function () {
            goTo(page + Number(button.dataset.direction));
        });
    });

    track.addEventListener('scroll', update, { passive: true });
    const observer = new ResizeObserver(update);
    observer.observe(track);
    Array.from(track.children).forEach(function (item) { observer.observe(item); });

});
