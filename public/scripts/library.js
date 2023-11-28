$(document).ready(function ()
{ // quand la page est prête
    $("#filter-select").change(function() { // sur l'objet DOM dont l'ID est filter-select, on ajoute un écouteur on change dont la fonction call back fait :
        var optionValue = $(this).val(); // crée une variable optionValue et stock la valeur selectionné dans le dropdown
        var url = window.location.href.split("&filter=")[0]; // prend l'url window.location.href puis applique une division sur le caractère ?, puis prend la premiere partie de l'array
        // var url = window.location.href; // prend l'url window.location.hret puis applique une division sur le caractère ?, puis prend la premiere partie de l'array
        window.location = url + "&filter=" + optionValue; // s'il n'y a pas de ? on ajoute le caractère ? en plus de l'option
    });

});