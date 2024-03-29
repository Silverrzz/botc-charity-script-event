function loading_screen(message) {
    let loading_screen = document.querySelector('#loading-screen');
    if (message) {
        loading_screen.querySelector('.message').innerHTML = message;
        loading_screen.classList.add('active');
    } else {
        loading_screen.classList.remove('active');
    }
}