//modals.js

document.addEventListener('DOMContentLoaded', function() {
    let modals = document.querySelectorAll('.modal');

    modals.forEach(function(modal) {

        modal.querySelector('.modal-close').addEventListener('click', function() {
            modal.classList.remove('show');
            modal_container_hide();
        });

    });
});

function modal_container_hide() {
    let modal_container = document.querySelector('.modal-container');
    modal_container.classList.remove('show');
}

function modal_container_show() {
    let modal_container = document.querySelector('.modal-container');
    modal_container.classList.add('show');
}