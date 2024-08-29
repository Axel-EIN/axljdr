if (document.getElementsByClassName('input-image')[0])
    previewHandler('input-image', 'image');

if (document.getElementsByClassName('input-icon')[0])
    previewHandler('input-icon', 'icon');

if (document.getElementsByClassName('input-map')[0])
    previewHandler('input-map', 'map');

if (document.getElementsByClassName('input-plan')[0])
    previewHandler('input-plan', 'plan');

if (document.getElementsByClassName('input-video')[0])
    previewHandler('input-video', 'video');

// IMAGE PREVIEW HANDLER
function previewHandler(inputClassNameString, prefixName) {
    var input_file = document.getElementsByClassName(inputClassNameString)[0]; // get the input HTML by searching by classnames and taking the first element of the array
    input_file.addEventListener( "change", // track the event on change
        function () {
            if ($(this).val()) { // if the input HTML has a value

                var filename = $(this).val().split("\\"); // split the file name
                filename = filename[filename.length-1]; // choose the last part of the splitted filename
                $('#' + prefixName + '-filename').text(filename); // print the filename

                if (prefixName == 'video') { // if it is a video
                    var media = URL.createObjectURL(this.files[0]);
                    var video = document.getElementById("video");
                    video.src = media;
                    video.style.display = "block";
                    video.controls = true;
                    video.play();
                } else
                    readURL(this, '#' + prefixName + '-preview'); // Read the image file and put it in IMG src attributes
            }
        }
    );
}

// FILE READER AND PREVIEW LOADER
function readURL(input, imgPreviewIDnameString) {
    if (input.files && input.files[0]) { // check if the arraw exists and if the first element exist
        var reader = new FileReader(); // creating a new FileReader object
        reader.onload = function (e) { $(imgPreviewIDnameString).attr('src', e.target.result); }
        reader.readAsDataURL(input.files[0]);
    }
}
