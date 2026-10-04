$(document).ready(function ()
{
    $('.search-bar').each(function () {
        var form = this;
        var input = form.querySelector('.search-bar-input');
        var list = form.querySelector('.search-bar-suggestions');
        var min = parseInt(input.dataset.min, 10);
        var timer = null;
        var request = 0;

        function isOpen() {
            return list.classList.contains('show');
        }

        function close() {
            list.classList.remove('show');
        }

        function suggest() {
            var term = input.value.trim();
            var current = ++request;

            if (term.length < min) {
                close();
                return;
            }

            fetch(input.dataset.suggest + '?q=' + encodeURIComponent(term))
                .then(function (response) { return response.text(); })
                .then(function (html) {
                    if (current !== request) {
                        return;
                    }
                    list.innerHTML = html;
                    list.classList.add('show');
                });
        }

        function move(step) {
            var items = Array.from(list.querySelectorAll('.search-suggestion'));
            var index = items.findIndex(function (item) { return item.classList.contains('active'); });

            if (items.length === 0) {
                return;
            }

            items.forEach(function (item) { item.classList.remove('active'); });
            index = index === -1 ? (step > 0 ? 0 : items.length - 1) : (index + step + items.length) % items.length;
            items[index].classList.add('active');
        }

        input.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(suggest, 250);
        });

        input.addEventListener('focus', function () {
            if (list.children.length > 0 && input.value.trim().length >= min) {
                list.classList.add('show');
            }
        });

        input.addEventListener('keydown', function (event) {
            if ((event.key === 'ArrowDown' || event.key === 'ArrowUp') && isOpen()) {
                event.preventDefault();
                move(event.key === 'ArrowDown' ? 1 : -1);
            } else if (event.key === 'Enter' && isOpen()) {
                var active = list.querySelector('.search-suggestion.active');
                if (active) {
                    event.preventDefault();
                    window.location = active.href;
                }
            } else if (event.key === 'Escape') {
                close();
            }
        });

        document.addEventListener('click', function (event) {
            if (!form.contains(event.target)) {
                close();
            }
        });
    });

    $('.search-collapse').on('shown.bs.collapse', function () {
        this.querySelector('.search-bar-input').focus();
    });
});
