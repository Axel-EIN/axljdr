$(document).ready(function ()
{   
    $("#filter-select").change(function() {
        var optionValue = $(this).val();
        var url = window.location.href.split("&filter=")[0];
        window.location = url + "&filter=" + optionValue;
    });

    $("#keyword-select").change(function() {
        var optionValue = $(this).val();
        var url = window.location.href.split("&keyword=")[0];
        window.location = url + "&keyword=" + optionValue;
    });

    var target = document.getElementById(window.location.hash.slice(1));
    if (target && target.classList.contains("collapse")) {
        $(target).one("shown.bs.collapse", function () {
            target.parentElement.scrollIntoView({block: "center"});
        }).collapse("show");
    }

});