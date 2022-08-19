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
            copyBtn.innerHTML='<i class="fa fa-check-circle has-text-success fa-fw"></i>';
      } else {
            copyBtn.innerHTML='<i class="fa fa-exclamation-circle has-text-danger fa-fw"></i>';
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
    copyBtn.innerHTML='<i class="fa fa-clipboard fa-fw"></i>';
    clearInterval(myTimer);
}
});
