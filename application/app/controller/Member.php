<?php

namespace app\app\controller;

use app\app\model\MemberDO;
use app\app\utils\COSUtils;
use think\Cookie;
use think\Loader;
use QCloud\Cos\Api;
use think\View;

class Member extends BaseController
{
    private static $ADMIN_FILE_PATH = APP_PATH . "AdminUserId.txt";
    private static $COOKIE_KEY_NAME = 'wQ4c_e043_lastcheckfeed';

    public function index()
    {
        return view("/index");
    }

    public function admin()
    {
        return view("/admin/index");
    }

    public function showImage()
    {
        $href = input('href', '');
        if ($href == '') {
            $this->redirect('app/member/index');
            return;
        }
        $view = new View();
        $view->assign('href', $href);
        return $view->fetch('/showImage');
    }

    public function getAll()
    {
        $requestPage = input('requestPage');
        $pageSize = input('pageSize');
        if ($requestPage == null) {
            return $this->buildErrorResult('requestPage can not be null');
        } elseif ($requestPage <= 0) {
            return $this->buildErrorResult('requestPage need >=1');
        } elseif ($pageSize == null) {
            return $this->buildErrorResult('pageSize can not be null');
        } elseif ($pageSize <= 0 || $pageSize > 100) {
            return $this->buildErrorResult('pageSize need 1-100');
        }
        $this->recordLog('getAll() methods called requestPage=' . $requestPage . ' pageSize=' . $pageSize);
        return $this->buildSuccessResult(MemberDO::queryByPagination($requestPage, $pageSize));
    }

    public function search()
    {
        $keyword = input('keyword');
        if ($keyword == null) {
            return $this->buildErrorResult('keyword can not be null');
        }
        $this->recordLog('search() methods called searchKeyword=' . $keyword);
        return $this->buildSuccessResult(MemberDO::searchMember($keyword));
    }

    public function checkUser()
    {
        $this->recordLog('checkUser() methods called');
        $userId = $this->getDiscuzLoggedUser();
        if ($userId != '') {
            if ($this->validateUserPrivilege($userId)) {
                return $this->buildSuccessResult(['state' => 'Allow']);
            } else {
                return $this->buildSuccessResult(['state' => 'Denied']);
            }
        } else {
            return $this->buildSuccessResult(['state' => 'Unauthorized']);
        }
    }

//    public function test()
//    {
//        $cosUtils = new COSUtils('conqueror');
//        $ret = $cosUtils->uploadFile(ROOT_PATH . 'public' . DS . 'uploads' . DS . '20170831' . DS . '4f6449ccfbad456881c9a18c9ce5b086.jpg',
//            '/blacklist/1.jpg');
//
//    }

    public function submit()
    {
        $name = input('post.name', null);
        $comment = input('post.comment', null);
        $area = input('post.area', null);
        if ($name == null) {
            return $this->buildErrorResult('name can not be null');
        } elseif ($comment == null) {
            return $this->buildErrorResult('comment can not be null');
        } elseif ($area == null) {
            return $this->buildErrorResult('area can not be null');
        }
        $lastData = ['name' => $name,
            'comment' => $comment,
            'area' => $area,
            'create_time' => time()];
        $this->recordLog('submit() methods called.name=' . $name . 'comment=.' . $comment . 'area=' . $area);
        $privilege = $this->checkUser()->getData();
        if ($privilege['result']['state'] != 'Allow') {
            return $this->buildErrorResult('access denied');
        }
        $file = request()->file('image');
//         移动到框架应用根目录/public/uploads/ 目录下
        if ($file != null) {
            $info = $file->rule('unique')->move(ROOT_PATH . 'public' . DS . 'uploads' . DS);
            if ($info) {
//                echo $info->getExtension();
//                echo $info->getFilename();
                $cosUtil = new COSUtils('conqueror');
                $ret = $cosUtil->uploadFile(ROOT_PATH . 'public' . DS . 'uploads' . DS . $info->getSaveName(),
                    '/blacklist/' . $info->getFilename());
                if ($ret) {
                    $lastData['picture'] = $ret;
                    //删除服务器目录的临时文件
                    unlink(ROOT_PATH . 'public' . DS . 'uploads' . DS . $info->getSaveName());
                } else {
                    $this->recordLog('upload COS File failed!' . 'tempFilePath=' . $info->getSaveName());
                    return $this->buildErrorResult('upload COS File failed');
                }
            } else {
                // 上传失败获取错误信息
                $this->recordLog('upload File failed!error=' . $file->getError());
                return $this->buildErrorResult('upload File failed');
            }
        }
        MemberDO::insertMember($lastData);
        return $this->buildSuccessResult('');
    }

    private function getDiscuzLoggedUser()
    {
        $bbsCookie = Cookie::get(self::$COOKIE_KEY_NAME);
        if ($bbsCookie != null) {
            $userId = explode("|", $bbsCookie)[0];
            return $userId;
        } else {
            return '';
        }
    }

    private function validateUserPrivilege($userId)
    {
        if (!file_exists(self::$ADMIN_FILE_PATH)) {
            return false;
        }
        $fp = fopen(self::$ADMIN_FILE_PATH, "r");
        $content = fread($fp, filesize(self::$ADMIN_FILE_PATH));
        $adminList = explode(",", $content);
        fclose($fp);
        for ($i = 0; $i < count($adminList); $i++) {
            if ($userId == $adminList[$i]) {
                return true;
            }
        }
        return false;
    }


}
