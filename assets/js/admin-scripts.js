/*
PluName: 微博 MicroBlog
PluLink: https://www.webersongao.com/tag/microblog
Desc: 将您的WordPress网站用作微博；在小部件中显示微博或使用短代码显示微博。
Author: WebersonGao
AuthorLink: https://www.webersongao.com
Based on simple-microblogging plugin developed by Samuel Coskey, Victoria Gitman(http://boolesrings.org),Thanks to obaby(https://h4ck.org.cn/) Thanks to ChatGPT.
*/

document.addEventListener('DOMContentLoaded', function () {

    // ======= 快速发微博 状态提示
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