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
        $filename = 'novel_'.date('Y-m-d');
        _log('start curl novel',$filename);

        $url_data = $this->dbm->select('novel', ['id','source_link(url)'], ['source'=>1,'is_curl'=>0]);

        foreach ($url_data as $e)
        {
            _log('start curl novel '.$e['url'],$filename);

            //小说章节
            $chapter_data = $this->curl_chapter($e['url']);
            if($chapter_data['code']!=200){
                _log($chapter_data['msg'],$filename);
                exit;
            }
            $chapter_data = $chapter_data['data'];

            foreach ($chapter_data as $k=>$v)
            {
                _log('start curl novel chapter '.$v['url'],$filename);

                $source_id = pathinfo($v['url'],PATHINFO_FILENAME);

                $is_exist = $this->dbm->select('novel_chapter', ['id'], ['novel_id'=>$e['id'],'source_id'=>$source_id]);
                if($is_exist){
                    _log('novel chapter '.$v['url'].' exist',$filename);
                    continue;
                }

                sleep(3);

                //小说章节详情
                $view_data = $this->curl_view($v['url']);
                if($view_data['code']!=200){
                    _log($view_data['msg'],$filename);
                    exit;
                }
                $view_data = $view_data['data'];

                if($view_data['content'])
                {
                    $this->dbm->insert('novel_chapter',[
                        'novel_id' => $e['id'],
                        'source_id' => $source_id,
                        'title' => $v['title'],
                        'create_time' => _time()
                    ]);
                    $chapter_id = $this->dbm->id();

                    if($chapter_id>0)
                    {
                        $this->dbm->insert('novel_content',[
                            'novel_id' => $e['id'],
                            'chapter_id' => $chapter_id,
                            'content' => $view_data['content'],
                            'create_time' => _time()
                        ]);
                    }
                }

                _log('end curl novel chapter '.$v['url'],$filename);
            }

            $this->dbm->update('novel',['is_curl'=>1],['id'=>$e['id']]);

            _log('end curl novel '.$e['url'],$filename);
        }

        _log('end curl novel',$filename);
    }

    /**
     * @param string $url
     * @return array
     */
    protected function curl_chapter($url='')
    {
        if($url){
            try{
                $chapter_data = QueryList::get($url,null,[
                    'timeout' => 300,
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
                return [
                    'code' => 200,
                    'msg' => 'success',
                    'data' => $chapter_data
                ];
            }
            catch (Exception $e){
                return [
                    'code' => 1001,
                    'msg' => $e->getMessage(),
                    'data' => []
                ];
            }
        }else{
            return [
                'code' => 1002,
                'msg' => 'curl chapter url is empty',
                'data' => []
            ];
        }
    }

    /**
     * @param string $url
     * @return array
     */
    protected function curl_view($url='')
    {
        if($url){
            try{
                $view_data = QueryList::get($url,null,[
                    'timeout' => 300,
                    'headers' => [
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.130 Safari/537.36',
                    ]
                ])
                    ->rules([
                        'content' => ['#content','html'],
                    ])
                    ->queryData();
                return [
                    'code' => 200,
                    'msg' => 'success',
                    'data' => $view_data
                ];
            }
            catch(Exception $e){
                return [
                    'code' => 1001,
                    'msg' => $e->getMessage(),
                    'data' => []
                ];
            }
        }else{
            return [
                'code' => 1002,
                'msg' => 'curl view url is empty',
                'data' => []
            ];
        }
    }
}

$obj = new curlxsbiqugeClass();
$obj->run();