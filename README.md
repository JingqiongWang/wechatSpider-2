# wechatSpider
是一个微信文章抓取爬虫,可以作为参考

> wechatSpider.php  没有进行封装 ，只是作为临时使用 ,但是参考价值很大
测试的话 ，把header替换为最新的header，把token替换为最新的token，如果不懂可以联系wx：wanghui119c


> NewsQQSpider 腾讯新闻爬取(使用puppeteer,使用composer install就好) (临时爬取，只做参考) 
> 新增NewsIfengSpider（凤凰网）/NewsQQSpider(腾讯新闻)/NewsSinaSpider（新浪网）/

需要安装（puppeteer）

```
composer require jaeger/querylist-puppeteer
npm install @nesk/puphpeteer
```

使用：

```
php NewsFactory.php sina  
php NewsFactory.php ifeng
php NewsFactory.php qq
```

	