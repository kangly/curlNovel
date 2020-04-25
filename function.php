<?php
/**
 * Created by PhpStorm.
 * User: kangly
 * Date: 2018/4/22
 * Time: 16:41
 */

/**
 * 输出信息
 * @param $str
 */
function de($str){
    echo "\n";
    print_r($str);
    echo "\n";
}

/**
 * 当前时间
 * @return bool|string
 */
function _time(){
    return date('Y-m-d H:i:s');
}

/**
 * 写入数据到文件
 * @param $log
 * @param string $file
 */
function _log($log,$file='test'){
    file_put_contents('../log/'.$file.'.txt', $log."\n", FILE_APPEND);
}