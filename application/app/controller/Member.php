<?php

namespace app\app\controller;

use app\app\model\MemberDO;
use think\Cookie;

class Member extends BaseController
{
    private static $ADMIN_FILE_PATH = APP_PATH."AdminUserId.txt";
    private static $COOKIE_KEY_NAME = 'wQ4c_e043_lastcheckfeed';

    public function index()
    {
        return view("/index");
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

    public function search(){
        $keyword = input('keyword');
        if ($keyword==null){
            return $this->buildErrorResult('keyword can not be null');
        }
        $this->recordLog('search() methods called searchKeyword='.$keyword);
        return $this->buildSuccessResult(MemberDO::searchMember($keyword));
    }

    private function getDiscuzLoggedUser()
    {
        $bbsCookie = Cookie::get(self::$COOKIE_KEY_NAME);
        if ($bbsCookie != null) {
            $userId = explode("|", $bbsCookie)[0];
            return $userId;
        } else {
            return "";
        }
    }

    private function validateUserPrivilege($userId)
    {
        if (!file_exists(self::$ADMIN_FILE_PATH)) {
            return false;
        }
        $fp = fopen(self::$ADMIN_FILE_PATH,"r");
        $content = fread($fp, filesize(self::$ADMIN_FILE_PATH));
        $adminList = explode(",", $content);
        fclose($fp);
        return in_array($userId,$adminList);
    }


}
