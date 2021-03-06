<?php
/**
 * Created by PhpStorm.
 * User: huizi
 * Date: 2019/5/23
 * Time: 18:11
 */
require "./vendor/autoload.php";

use http\Env\Response;
use Medoo\Medoo;
use Nesk\Rialto\Data\JsFunction;
use QL\QueryList;

/**
 * 新闻qq
 * Class NewsQQSpider
 */
class NewsQQSpider111
{
    private $urls;
    private $headers;
    private $db;

    public function __construct($urls, $headers)
    {
        $this->urls = $urls;
        $this->headers = $headers;
        $this->db = new Medoo([
            'database_type' => 'mysql',
            'database_name' => 'topai',
            'server' => '',
            'username' => '',
            'password' => ''
        ]);
    }

    /**
     * 运行数据
     * Created by PhpStorm.
     * Author:huizi
     * Date: 2019/5/23-18:20
     */
    public function run()
    {
        foreach ($this->urls as $key => $val) {
            echo "抓取url:".$val."----------------";
            $list = $this->getQueryData($val);
            $time = time();
            foreach ($list as $item) {
                $data['title'] = isset($item['title']) ? $item['title'] : "";
                $data['cover'] = isset($item['cover']) ? $item['cover'] : "";
                $data['source'] = isset($item['source']) ? $item['source'] : "";
                $data['summary'] = isset($item['summary']) ? $item['summary'] : "";
                $data['url'] = isset($item['href']) ? $item['href'] : "";
                if(isset($item['href'])){
                    $data['message'] = $this->getQueryContent($item['href']);
                }else{
                    continue;
                }
                $data['uuid'] = md5($item['href']);
                $data['catch_type'] = 2;
                $data['createtime'] = $time;
                $data['updatetime'] = $time;
                $this->writeSql($data);
            }
            sleep(mt_rand(10, 15));
        }
    }


    public function getQueryContent($url)
    {
        sleep(mt_rand(5, 10));
        $ql = QueryList::get($url);
        $html = $ql->encoding('UTF-8', 'GB2312')
            ->removeHead()
            ->find(".content.clearfix")->html();
        return $html;
    }


    public function getQueryData($url)
    {
        $ql = QueryList::getInstance();
        $ql->use(\QL\Ext\Chrome::class);
        $rules = [
            'title' => ['.channel_mod .list .detail h3', 'text'],
            'href' => [".channel_mod .list .detail h3 a", "href"],
            'cover' => ['.channel_mod .list a.picture img', 'src'],
            'source' => ['.channel_mod .list .binfo a.source', 'text']
        ];
        $html = $ql->chrome(function ($page, $browser) use ($url) {
            $page->goto($url);
            $page->evaluate(JsFunction::createWithBody("
                 var i = 0 ;
                 var timer = setInterval(function(){
                    if(i>=20){
                        clearInterval(timer);
                    }
                    window.scrollBy(0, i*100);
                    i++;
                 },3000);
            "));
            sleep(55);
            $html = $page->content();
            $browser->close();
            return $html;
        })->rules($rules)->queryData();
        return $html;
    }

    /**
     * 写入数据库
     * @param $data
     * Created by PhpStorm.
     * Author:huizi
     * Date: 2019/3/21-15:40
     */
    public function writeSql($data)
    {
        echo "写入".$data['title']."----------------";

        if ($this->db->get('data_temp_article', "id", [
            'uuid' => $data['uuid']
        ])) {
            $this->db->update("data_temp_article", [
                'title' => $data['title'],
                'cover' => $data['cover'],
                'source' => $data['source'],
                'message' => $data['message'],
                'url' => $data['url'],
                'summary' => $data['summary'],
                'catch_type' => $data['catch_type'],
                'updatetime' => $_SERVER["REQUEST_TIME"]
            ], [
                'uuid' => $data['uuid']
            ]);
        } else {
            $data = $this->db->insert('data_temp_article', [
                'title' => $data['title'],
                'cover' => $data['cover'],
                'source' => $data['source'],
                'message' => $data['message'],
                'url' => $data['url'],
                'summary' => $data['summary'],
                'uuid' => $data['uuid'],
                'catch_type' => $data['catch_type'],
                'createtime' => $_SERVER["REQUEST_TIME"],
                'updatetime' => $_SERVER["REQUEST_TIME"]
            ]);
        }
    }


}


$urls = [
    "https://new.qq.com/ch2/ai",
    "https://new.qq.com/ch2/internet",
    "https://new.qq.com/tag/276813",
    "https://new.qq.com/ch2/hgjj",
    "https://new.qq.com/ch2/jinr"
];

$cache_path = __DIR__ . '/temp/';

$headers = [
//    'Referer' => 'https://querylist.cc/',
//    'User-Agent' => 'testing/1.0',
//    'Accept' => 'application/json',
//    'X-Foo' => ['Bar', 'Baz'],
//    // 携带cookie
//    'Cookie' => 'abc=111;xxx=222',
//    'cache' => $cache_path,
//    'cache_ttl' => 600
];


$newsQQ = new NewsQQSpider($urls, $headers);
$newsQQ->run();