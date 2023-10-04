// Page Aventure : Gestion du bouton Voir les Episodes
$('.cacher, .voir').click(function(){

        if ($(this).hasClass('voir')) {
            $(this)
                .removeClass('voir').addClass('cacher').find('i').removeClass('fa-bounce');
                // .removeClass('btn-primary-style').addClass('text-white')
                // .html('<i class="fa-solid fa-eye-slash" alt="Icône cacher"></i>');
        } else {
            $(this)
                .removeClass('cacher').addClass('voir').find('i').addClass('fa-bounce');
                // .addClass('btn-primary-style').removeClass('text-white')
                // .html('<i class="fa-solid fa-scroll fa-bounce" alt="Icône Voir" ></i>&nbsp;&nbsp;Voir les Sessions');
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