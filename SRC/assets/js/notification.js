function notification(message, type) {
    let notification_banner = document.createElement('div');
    notification_banner.classList.add('notification-banner');
    
    let notification_icon = document.createElement('ion-icon');
    if (type == 'success') {
        notification_banner.classList.add('success');
        notification_icon.setAttribute('name', 'checkmark-circle-outline');
    } else if (type == 'error') {
        notification_banner.classList.add('error');
        notification_icon.setAttribute('name', 'alert-circle-outline');
    } else {
        notification_banner.classList.add('info');
        notification_icon.setAttribute('name', 'information-circle-outline');
    }
    notification_icon.classList.add('notification-icon');

    let notification_message = document.createElement('div');
    notification_message.classList.add('notification-message');
    notification_message.innerHTML = message;

    let close_icon = document.createElement('ion-icon');
    close_icon.setAttribute('name', 'close-outline');
    close_icon.classList.add('close-icon');
    close_icon.addEventListener('click', function() {
        notification_banner.remove();
    });

    notification_banner.appendChild(notification_icon);
    notification_banner.appendChild(notification_message);
    notification_banner.appendChild(close_icon);

    let notification_container = document.querySelector('#notification-container');

    notification_container.appendChild(notification_banner);

    setTimeout(function() {
        notification_banner.remove();
    }, 5000);

}