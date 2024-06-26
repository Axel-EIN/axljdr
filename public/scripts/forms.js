var input_image_file = document.getElementsByClassName('input-image')[0]; // get the input HTML by searching by classnames and taking the first element of the array

input_image_file.addEventListener(
        "change", // track the event on change
        function ()
        {
            if ($(this).val()) { // if the input HTML has a value

                var filename = $(this).val().split("\\"); // split the file name
            
                filename = filename[filename.length-1]; // choose the last part of the splitted filename

                $('#fileName').text(filename); // print the filename

                readURL(this); // Read the image file and put it in IMG src attributes

                $('#preview-label').removeClass('d-none');
            }
        }
    );

function readURL(input) {

    if (input.files && input.files[0]) { // check if the arraw exists and if the first element exist

        var reader = new FileReader(); // creating a new FileReader object

        reader.onload = function (e) {
            $('#image-preview').attr('src', e.target.result);
        }

        reader.readAsDataURL(input.files[0]);
    }
}


// ===

if (document.getElementsByClassName('input-icon')[0]) {

    var input_icon_file = document.getElementsByClassName('input-icon')[0]; // get the input HTML by searching by classnames and taking the first element of the array

    input_icon_file.addEventListener(
        "change", // track the event on change
        function ()
        {
            if ($(this).val()) { // if the input HTML has a value

                var filename_icon = $(this).val().split("\\"); // split the file name
            
                filename_icon = filename_icon[filename_icon.length-1]; // choose the last part of the splitted filename

                $('#fileName_icon').text(filename_icon); // print the filename

                readURL_icon(this); // Read the image file and put it in IMG src attributes

                $('#preview-label-icon').removeClass('d-none');
            }
        }
    );

    function readURL_icon(input) {

        if (input.files && input.files[0]) { // check if the arraw exists and if the first element exist

            var reader = new FileReader(); // creating a new FileReader object

            reader.onload = function (e) {
                $('#icon-preview').attr('src', e.target.result);
            }

            reader.readAsDataURL(input.files[0]);
        }
    }
}
