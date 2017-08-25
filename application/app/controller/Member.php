<?php

namespace app\app\controller;

use app\app\model\MemberDO;

class Member extends BaseController
{

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
        return $this->buildMemberListResult($requestPage, $pageSize);
    }

    private function buildMemberListResult($requestPage, $pageSize)
    {
        $member = new MemberDO();
        $totalCount = $member->count();
        $dataList = $member->limit(($requestPage - 1) * $pageSize, $pageSize)->select();
        return $this->buildSuccessResult([
            'totalCount' => $totalCount,
            'data' => $dataList
        ]);

    }
}
