/*
PluName: 微博 MicroBlog
PluLink: https://www.webersongao.com/tag/microblog
Desc: 将您的WordPress网站用作微博；在小部件中显示微博或使用短代码显示微博。
Author: WebersonGao
AuthorLink: https://www.webersongao.com
Based on simple-microblogging plugin developed by Samuel Coskey, Victoria Gitman(http://boolesrings.org),Thanks to obaby(https://h4ck.org.cn/) Thanks to ChatGPT.
*/

document.addEventListener('DOMContentLoaded', function () {
    var currentPageURL = window.location.href;
    if (currentPageURL.includes('post_type=micropost')) {
        var postTypeLinks = document.querySelectorAll('a[href*="post_type=micropost"]');
        postTypeLinks.forEach(function (link) {
            if (link.textContent && link.textContent.trim() === '写文章') {
                link.textContent = '发微博';
            }
        });
        var pageTitle = document.title;
        if (pageTitle && pageTitle.includes('写文章')) {
            document.title = pageTitle.replace(/写文章/g, '发微博');
        }
    }
});