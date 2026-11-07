document.querySelectorAll('input[type="password"]').forEach(function (input) {
    var wrapper = document.createElement('div');
    wrapper.className = 'password-field';
    input.parentNode.insertBefore(wrapper, input);
    wrapper.appendChild(input);

    var button = document.createElement('button');
    button.type = 'button';
    button.className = 'password-toggle';
    button.setAttribute('aria-label', 'Afficher le mot de passe');
    button.innerHTML = '<i class="fa-solid fa-eye" aria-hidden="true"></i>';
    wrapper.appendChild(button);

    button.addEventListener('click', function () {
        var shown = input.type === 'text';
        input.type = shown ? 'password' : 'text';
        button.setAttribute('aria-label', shown ? 'Afficher le mot de passe' : 'Masquer le mot de passe');
        button.firstElementChild.className = 'fa-solid ' + (shown ? 'fa-eye' : 'fa-eye-slash');
    });
});
