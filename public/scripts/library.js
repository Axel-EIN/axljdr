$(document).ready(function ()
{   
    function reloadWith(removedParams, param, value) {
        var url = new URL(window.location.href);
        removedParams.forEach(function(removedParam) {
            url.searchParams.delete(removedParam);
        });
        if (value)
            url.searchParams.set(param, value);
        url.hash = "";
        window.location = url;
    }

    $("#filter-select").change(function() {
        reloadWith(["filter"], "filter", $(this).val());
    });

    $("#keyword-select").change(function() {
        reloadWith(["keyword"], "keyword", $(this).val());
    });

    $(".discount-select").change(function() {
        var discountParams = $(".discount-select").map(function() { return $(this).data("param"); }).get();
        reloadWith(discountParams, $(this).data("param"), $(this).val());
    });

    var target = document.getElementById(window.location.hash.slice(1));
    if (target && target.classList.contains("collapse")) {
        $(target).one("shown.bs.collapse", function () {
            target.parentElement.scrollIntoView({block: "center"});
        }).collapse("show");
    }

});