<?php
/**
 * Created by PhpStorm.
 * User: jiangchaoren
 * Date: 2017/8/25
 * Time: 14:01
 */

namespace app\app\controller;


use think\Controller;
use think\Log;
use think\Request;

class BaseController extends Controller
{

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        header('Server:nginx');
        header('X-Powered-By:Conqueror');
    }

    protected function recordLog($message)
    {
        Log::record($message, 'info');
        Log::save();
    }

    protected function recordError($message)
    {
        Log::record($message, 'error');
        Log::save();
    }

    protected function buildCommonResult($code, $message, $result = null)
    {
        $common = [
            'code' => $code,
            'message' => $message,
        ];
        if ($result != null) {
            $common['result'] = $result;
        }
        return json($common);

    }

    protected function buildErrorResult($message){
        return $this->buildCommonResult('-1',$message);
    }

    protected function buildSuccessResult($result){
        return $this->buildCommonResult('0','',$result);
    }
}