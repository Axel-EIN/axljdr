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

function previewHandler(inputClassNameString, prefixName) {
    var input_file = document.getElementsByClassName(inputClassNameString)[0];
    input_file.addEventListener( "change",
        function () {
            if ($(this).val()) {

                var filename = $(this).val().split("\\");
                filename = filename[filename.length-1];
                $('#' + prefixName + '-filename').text(filename);

                if (prefixName == 'video') {
                    var media = URL.createObjectURL(this.files[0]);
                    var video = document.getElementById("video");
                    video.src = media;
                    video.style.display = "block";
                    video.controls = true;
                    video.play();
                } else
                    readURL(this, '#' + prefixName + '-preview');
            }
        }
    );
}

function readURL(input, imgPreviewIDnameString) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) { $(imgPreviewIDnameString).attr('src', e.target.result); }
        reader.readAsDataURL(input.files[0]);
    }
}
