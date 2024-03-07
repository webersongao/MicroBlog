/*
PluName: 微博 MicroBlog
PluLink: https://www.webersongao.com/tag/microblog
Desc: 将您的WordPress网站用作微博；在小部件中显示微博或使用短代码显示微博。
Author: WebersonGao
AuthorLink: https://www.webersongao.com
Based on simple-microblogging plugin developed by Samuel Coskey, Victoria Gitman(http://boolesrings.org),Thanks to obaby(https://h4ck.org.cn/) Thanks to ChatGPT.
*/

document.addEventListener('DOMContentLoaded', function () {
    // Function to update links text and target
    function updateLinksTextAndTarget(selector, searchText, newText, newTarget) {
        var links = document.querySelectorAll(selector);
        links.forEach(function (link) {
            if (link.textContent && link.textContent.trim() === searchText) {
                link.textContent = newText;
                if (newTarget) {
                    link.setAttribute('target', newTarget);
                }
            }
        });
    }

    // Update links and page title based on the current page URL
    var currentPageURL = window.location.href;
    if (currentPageURL.includes('post_type=micropost')) {
        updateLinksTextAndTarget('a[href*="post_type=micropost"]', '写文章', '发微博');
        updateLinksTextAndTarget('li#wp-admin-bar-archive a.ab-item', '查看文章', '微博列表', '_blank');

        var pageTitle = document.title;
        if (pageTitle && pageTitle.includes('写文章')) {
            document.title = pageTitle.replace(/写文章/g, '发微博');
        }
    } else {
        updateLinksTextAndTarget('a[href*="post_type=micropost"]', '写文章', '发微博');
    }
});