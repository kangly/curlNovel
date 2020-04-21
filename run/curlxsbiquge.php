<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2020/04/20
 * Time: 21:04
 */
//为crontab设置的路径,服务器上需要执行
//chdir('/www/wwwroot/spider/run');

require_once '../base.php';
require_once '../function.php';

use QL\QueryList;

/**
 * 暂时不记录日志
 * 抓取https://www.xsbiquge.com站点小说
 * Class curlxsbiqugeClass
 */
class curlxsbiqugeClass extends baseClass
{
    function run()
    {
        $this->curl_xsbiquge();
    }

    protected function curl_xsbiquge()
    {
        $url_data = $this->dbm->select('novel', ['id','source_link(url)'], ['source'=>1,'is_curl'=>0]);

        foreach ($url_data as $e)
        {
            //小说章节
            $chapter_data = QueryList::get($e['url'],null,[
                'timeout' => 60,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.130 Safari/537.36',
                ]
            ])
                ->rules([
                    'title' => ['a','text'],
                    'url' => ['a','href','',function($content){
                        return 'https://www.xsbiquge.com'.$content;
                    }]
                ])
                ->range('#list>dl dd')
                ->queryData();

            foreach ($chapter_data as $k=>$v)
            {
                sleep(5);

                //小说章节详情
                $view_data = QueryList::get($v['url'],null,[
                    'timeout' => 60,
                    'headers' => [
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.130 Safari/537.36',
                    ]
                ])
                    ->rules([
                        'content' => ['#content','html'],
                    ])
                    ->queryData();

                if($view_data['content'])
                {
                    $this->dbm->insert('novel_chapter',[
                        'novel_id' => $e['id'],
                        'source_id' => pathinfo($v['url'],PATHINFO_FILENAME),
                        'title' => $v['title'],
                        'create_time' => _time()
                    ]);
                    $chapter_id = $this->dbm->id();

                    if($chapter_id>0)
                    {
                        $this->dbm->insert('novel_content',[
                            'chapter_id' => $chapter_id,
                            'content' => $view_data['content'],
                            'create_time' => _time()
                        ]);
                    }
                }
            }

            $this->dbm->update('novel',['is_curl'=>1],['id'=>$e['id']]);
        }
    }
}

$obj = new curlxsbiqugeClass();
$obj->run();