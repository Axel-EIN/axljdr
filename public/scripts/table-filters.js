$(document).ready(function ()
{
    $('.table-filters').each(function () {
        var form = this;
        var section = form.closest('section');
        var table = section.querySelector('.admin-element-list');
        var create = section.querySelector('[data-table-filters-create]');
        var createPath = create.getAttribute('href');
        var selects = Array.from(form.querySelectorAll('select'));
        var rows = Array.from(table.querySelectorAll('tr[data-filter-0]'));
        var key = 'table-filters-' + form.dataset.key;

        function read() {
            try {
                return JSON.parse(sessionStorage.getItem(key)) || [];
            } catch (e) {
                return [];
            }
        }

        function save() {
            try {
                sessionStorage.setItem(key, JSON.stringify(selects.map(function (select) { return select.value; })));
            } catch (e) {}
        }

        function matches(row, skipped) {
            return selects.every(function (select, index) {
                return index === skipped || select.value === '' || select.value === row.getAttribute('data-filter-' + index);
            });
        }

        function available(index) {
            return rows
                .filter(function (row) { return matches(row, index); })
                .map(function (row) { return row.getAttribute('data-filter-' + index); });
        }

        function narrow(changed) {
            selects.forEach(function (select, index) {
                if (index !== changed && select.value !== '' && available(index).indexOf(select.value) === -1) {
                    select.value = '';
                }
            });

            selects.forEach(function (select, index) {
                var values = available(index);

                Array.from(select.options).forEach(function (option) {
                    option.hidden = option.disabled = option.value !== '' && values.indexOf(option.value) === -1;
                });
            });
        }

        function link() {
            var params = new URLSearchParams();

            selects.forEach(function (select, index) {
                if (select.value !== '') {
                    params.set('filter[' + index + ']', select.value);
                }
            });

            create.setAttribute('href', createPath + (params.toString() ? '?' + params : ''));
        }

        function apply(changed) {
            narrow(changed);

            rows.forEach(function (row) {
                row.hidden = !matches(row, -1);
            });

            link();
            save();
        }

        var saved = read();

        selects.forEach(function (select, index) {
            select.value = saved[index] || '';
            if (select.selectedIndex === -1) {
                select.value = '';
            }

            select.addEventListener('change', function () {
                apply(index);
            });
        });

        apply(-1);
    });
});
