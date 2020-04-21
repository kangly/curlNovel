<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2020/04/21
 * Time: 09:54
 */
//为crontab设置的路径,服务器上需要执行
//chdir('/www/wwwroot/spider/run');

require_once '../base.php';
require_once '../function.php';

use QL\QueryList;

/**
 * 更新https://www.xsbiquge.com站点小说
 * Class curlupxsbiqugeClass
 */
class curlupxsbiqugeClass extends baseClass
{
    function run()
    {
        $this->curl_up_xsbiquge();
    }

    protected function curl_up_xsbiquge()
    {
        $url_data = $this->dbm->select('novel', ['id','source_link(url)'], ['source'=>1]);

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

            $new_chapter_data = array_reverse($chapter_data);
            $curl_chapter_data = [];

            foreach ($new_chapter_data as $k=>$v)
            {
                $source_id = pathinfo($v['url'],PATHINFO_FILENAME);

                $is_exist = $this->dbm->select('novel_chapter', ['id'], ['novel_id'=>$e['id'],'source_id'=>$source_id]);
                if($is_exist){
                    break;
                }else{
                    $curl_chapter_data[$k] = $v;
                    $curl_chapter_data[$k]['source_id'] = $source_id;
                }
            }

            if($curl_chapter_data)
            {
                $new_chapter_data = array_reverse($curl_chapter_data);

                foreach ($new_chapter_data as $v)
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
                            'source_id' => $v['source_id'],
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
            }
        }
    }
}

$obj = new curlupxsbiqugeClass();
$obj->run();