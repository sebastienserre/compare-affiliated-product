document.addEventListener("DOMContentLoaded", function(event) {
    var classname = document.getElementsByClassName("atc");
    for (var i = 0; i < classname.length; i++) {
        classname[i].addEventListener('click', myFunction, false);
    }
});
var myFunction = function(event) {
    var attribute = this.getAttribute("data-atc");
    if(event.ctrlKey) {
        var newWindow = window.open(decodeURIComponent(window.atob(attribute)), '_blank');
        newWindow.focus();
    } else {
        document.location.href= decodeURIComponent(window.atob(attribute));
    }
};