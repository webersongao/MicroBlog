/* MicroBlog */

document.addEventListener('DOMContentLoaded', function () {

    if (window.location.pathname === '/wp-admin/index.php') {
        var urlParams = new URLSearchParams(window.location.search);
        var message = urlParams.get('micropost_pub_alert');
        if (message) {
            var messageLink = document.querySelector('#quick-micropost-message a');
            if (messageLink) {
                messageLink.textContent = message;
            }
        }
    }
});
