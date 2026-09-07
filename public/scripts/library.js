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

});