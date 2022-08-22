$(document).ready(function() {
var myTimer;
var copyBtn = document.querySelector('.js-copybtn');
if (copyBtn != null) {
  copyBtn.addEventListener('click', function(event) {
    var secretText = document.getElementById('devinfo_secret');
    secretText.select();
    secretText.setSelectionRange(0, 99999); /* For mobile devices */

    try {
      var successful = document.execCommand('copy');
      if (successful) {
            copyBtn.innerHTML='&#10003;';
      } else {
            copyBtn.innerHTML='&#10007;';
      }
    } catch(err) {
      console.log('Oops, unable to copy');
    }

    window.getSelection().removeAllRanges();
    secretText.blur();
    myTimer = setInterval(myReturn, 1200);
  });
}
function myReturn() {
    copyBtn.innerHTML='&#10064;';
    clearInterval(myTimer);
}
});
