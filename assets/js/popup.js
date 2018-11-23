document.addEventListener('DOMContentLoaded', function(){
    var loginButton = document.querySelector('#login');
    var popup = document.querySelector('#popup');
    var popupBg = document.querySelector('#popup-bg');
    var close = document.querySelector('#close');

    loginButton.addEventListener('click', function(){
        show(popup);
    });

    popupBg.addEventListener('click', function(){
        hide(popup);
    });

    close.addEventListener('click', function(){
        hide(popup);
    });

});

function show(el){
    el.style.display = 'block';
}

function hide(el){
    el.style.display = 'none';
}
