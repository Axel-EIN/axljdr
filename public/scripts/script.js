// Page Aventure : Gestion du bouton Voir les Episodes
$('.cacher, .voir').click(function(){

        if ($(this).hasClass('voir')) {
            $(this).removeClass('voir').addClass('cacher').parents('section').removeClass('header-bg-img').addClass('header-bg-img-session');
            $(this).removeClass('btn-primary-style').addClass('btn-tertiary-style').html('<i class="fa-solid fa-eye-slash" alt="Icône cacher" aria-hidden="true"></i>&nbsp;&nbsp;Cacher');
        } else {
            $(this).removeClass('cacher').addClass('voir').parents('section').removeClass('header-bg-img-session').addClass('header-bg-img');
            $(this).removeClass('btn-tertiary-style').addClass('btn-primary-style').html('<i class="fa-solid fa-scroll fa-bounce" alt="Icône Voir" aria-hidden="true"></i>&nbsp;&nbsp;Voir les Sessions');
        }

});

// Page Aventure : Activation/Desactivation de l'animation bounce sur le bouton lire des cartes episodes lors d'un mouseHover / mouseLeave
$('.session-card-spacing').hover(mouseEnter, mouseLeave);

function mouseEnter() {
    $(this).find('.btn-primary-style').addClass('fa-bounce');
};

function mouseLeave() {
    $(this).find('.btn-primary-style').removeClass('fa-bounce');
};

// Capture
$('#caption').click(function (e) {

    const x = e.offsetX,
    y = e.offsetY;

    // alert(x + ',' + y);

    const xPourcent = x / 280 * 100;
    const yPourcent = y / 400 * 100;

    alert(xPourcent + '% ,' + yPourcent + '%');

});