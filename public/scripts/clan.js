// Clan Banner Mon Play Video Icon
$('#zone-mon').hover(playVisible, playInvisible);

function playVisible() {
    var test = $('#icon-play');
    $('#icon-play').removeClass('invisible');
    $('#icon-play').addClass('visible');
};

function playInvisible() {
    var test = $('#icon-play');
    $('#icon-play').removeClass('visible');
    $('#icon-play').addClass('invisible');
};


// Clan Banner Video Focus+Unmute / UnFocus+Mute Top Left Icon

function focusVideo() {
    document.getElementById('banner-video').style.zIndex = 4;
    $("#banner-video").prop('muted', false);
    $('#icon-mute-video').removeClass('invisible');
    $('#icon-mute-video').addClass('visible');
};

function unfocusVideo() {
    document.getElementById('banner-video').style.zIndex = 0;
    $("#banner-video").prop('muted', true);
    $('#icon-mute-video').removeClass('visible');
    $('#icon-mute-video').addClass('invisible');
};


// Territory Map Image Modal Zoom

var modal = document.getElementById("carteZoomModal");
var img = document.getElementById("carte");
var modalImg = document.getElementById("carteModal");
var captionText = document.getElementById("caption");

img.onclick = function(){
  modal.style.display = "block";
  modalImg.src = this.src;
  captionText.innerHTML = this.alt;
}

var span = document.getElementsByClassName("close")[0];

span.onclick = function() {
  modal.style.display = "none";
} 