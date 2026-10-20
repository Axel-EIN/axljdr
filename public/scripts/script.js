$('.cacher, .voir').each(function () {

        const $button = $(this);

        $($button.data('target'))
            .on('show.bs.collapse', function (e) {
                if (e.target !== this) return;
                $button.removeClass('voir btn-primary-style').addClass('cacher btn-tertiary-style').html('<i class="fa-solid fa-eye-slash" aria-hidden="true"></i>&nbsp;&nbsp;Cacher');
            })
            .on('hide.bs.collapse', function (e) {
                if (e.target !== this) return;
                $button.removeClass('cacher btn-tertiary-style').addClass('voir btn-primary-style').html('<i class="fa-solid fa-scroll fa-bounce" aria-hidden="true"></i>&nbsp;&nbsp;Voir les Épisodes');
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

    const grid = scroller.querySelector('.picture-grid');
    const items = grid.querySelectorAll('.picture-item');
    const buttons = scroller.querySelectorAll('.scroller-btn');
    const dots = scroller.id ? document.querySelector('.scroller-dots[data-scroller="' + scroller.id + '"]') : null;
    let page = 0;

    function perPage() {
        return parseInt(getComputedStyle(grid).getPropertyValue('--cols'), 10) || 1;
    }

    function pageWidth() {
        const cols = perPage();
        return items.length > cols ? items[cols].offsetLeft - items[0].offsetLeft : grid.clientWidth;
    }

    function goTo(target) {
        grid.scrollTo({ left: target * pageWidth() });
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
        const max = grid.scrollWidth - grid.clientWidth;
        const pages = max > 1 ? Math.ceil(items.length / perPage()) : 0;
        page = grid.scrollLeft >= max - 1 ? pages - 1 : Math.round(grid.scrollLeft / pageWidth());

        scroller.classList.toggle('overflowing', max > 1);
        buttons.forEach(function (button) {
            button.disabled = button.dataset.direction < 0 ? grid.scrollLeft <= 1 : grid.scrollLeft >= max - 1;
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

    grid.addEventListener('scroll', update, { passive: true });
    new ResizeObserver(update).observe(grid);

});
