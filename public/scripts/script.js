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
    const buttons = scroller.querySelectorAll('.scroller-btn');

    function update() {
        const max = grid.scrollWidth - grid.clientWidth;
        scroller.classList.toggle('overflowing', max > 1);
        buttons.forEach(function (button) {
            button.disabled = button.dataset.direction < 0 ? grid.scrollLeft <= 1 : grid.scrollLeft >= max - 1;
        });
    }

    buttons.forEach(function (button) {
        button.addEventListener('click', function () {
            grid.scrollBy({ left: button.dataset.direction * grid.clientWidth });
        });
    });

    grid.addEventListener('scroll', update, { passive: true });
    new ResizeObserver(update).observe(grid);

});
