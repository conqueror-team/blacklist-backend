<?php
/**
 * Created by PhpStorm.
 * User: jiangchaoren
 * Date: 2017/8/25
 * Time: 11:20
 */

namespace app\app\model;


use think\Db;
use think\Model;

class MemberDO extends Model
{
    protected static $tableName = 'member';
    protected $table = 'member';

    public static function queryByPagination($requestPage, $pageSize)
    {
        $member = new MemberDO();
        $totalCount = $member->count();
        if ($totalCount - $requestPage * $pageSize < 0) {
            $offset = 0;
            $pageSize = $totalCount - ($requestPage - 1) * $pageSize;
        } else {
            $offset = $totalCount - $requestPage * $pageSize;
        }
        $subQuery = Db::table(self::$tableName)->limit($offset, $pageSize)->buildSql(false);
        $dataList = Db::table('(' . $subQuery . ') a')
            ->order('id', 'desc')
            ->select();
        return [
            'totalCount' => $totalCount,
            'data' => $dataList
        ];
    }

    public static function searchMember($keyword)
    {
        $dataList = Db::table(self::$tableName)->where('name', 'like', '%' . $keyword . '%')
            ->limit(10)->select();
        return [
            'totalCount' => count($dataList),
            'data' => $dataList
        ];
    }

    public static function insertMember($data){
        Db::table(self::$tableName)->insert($data);
    }


}