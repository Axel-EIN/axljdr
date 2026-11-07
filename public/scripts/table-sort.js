document.addEventListener('DOMContentLoaded', function () {
    var collator = new Intl.Collator('fr', { numeric: true, sensitivity: 'base' });

    function key(cell, type) {
        var text = cell ? cell.textContent.trim() : '';

        if (text === '') {
            return null;
        }

        if (type === 'number') {
            var number = parseFloat(text.replace(',', '.'));
            return isNaN(number) ? null : number;
        }

        if (type === 'date') {
            var parts = text.match(/(\d{2})\/(\d{2})\/(\d{4})(?:\s+(\d{2}):(\d{2}))?/);
            return parts ? parts[3] + parts[2] + parts[1] + (parts[4] || '00') + (parts[5] || '00') : null;
        }

        return text;
    }

    function compare(a, b, type) {
        if (type === 'text') {
            return collator.compare(a, b);
        }

        return a < b ? -1 : a > b ? 1 : 0;
    }

    document.querySelectorAll('.admin-element-list').forEach(function (table) {
        var header = table.rows[0];
        var headers = Array.from(header.cells);

        headers.forEach(function (th, column) {
            var type = th.dataset.sort;

            if (!type) {
                return;
            }

            th.tabIndex = 0;

            function sort() {
                var descending = th.getAttribute('aria-sort') === 'ascending';
                var rows = Array.from(table.rows).slice(1).filter(function (row) { return row.cells.length > 1; });

                headers.forEach(function (other) { other.removeAttribute('aria-sort'); });
                th.setAttribute('aria-sort', descending ? 'descending' : 'ascending');

                rows
                    .map(function (row) { return { row: row, key: key(row.cells[column], type) }; })
                    .sort(function (a, b) {
                        if (a.key === null || b.key === null) {
                            return (a.key === null) - (b.key === null);
                        }

                        var order = compare(a.key, b.key, type);
                        return descending ? -order : order;
                    })
                    .forEach(function (item) { item.row.parentNode.appendChild(item.row); });
            }

            th.addEventListener('click', sort);
            th.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    sort();
                }
            });
        });
    });
});
