<?php
/**
 * Created by PhpStorm.
 * User: jiangchaoren
 * Date: 2017/8/31
 * Time: 18:31
 */

namespace app\app\utils;

use QCloud\Cos\Api;
use think\Loader;

class COSUtils
{
    private $config = array(
        'app_id' => '1251711161',
        'secret_id' => 'AKIDPi7SdcyHz52CKMkG7rkYBcu9Qce4rZdW',
        'secret_key' => 'rX2LUSF4nLhuxpV5UjneKsERTJzjLm38',
        'region' => 'cd',   // bucket所属地域：华北 'tj' 华东 'sh' 华南 'gz'
        'timeout' => 60
    );

    private $bucket = 'conqueror';
    private $cosApi;

    public function __construct($bucket)
    {
        Loader::import('include', EXTEND_PATH);
        date_default_timezone_set('PRC');
        $this->bucket = $bucket;
        $this->cosApi = new Api($this->config);
    }

    public function createFolder($folder)
    {

        // Create folder in bucket.
        $ret = $this->cosApi->createFolder($this->bucket, $folder);
        if ($ret['code'] == 0) {
            return true;
        } else {
            return false;
        }
    }

    public function uploadFile($src, $dest)
    {
        $ret = $this->cosApi->upload($this->bucket, $src, $dest);
        if ($ret['code'] == 0) {
            return $ret['data']['access_url'];
        } else {
            return false;
        }
    }

}