<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    /**
     * 指示是否自动维护时间戳
     * @var bool
     */
    public $timestamps = false;

    protected $connection = 'mysql';

    public function __construct(array $attributes = [], $connection = 'mysql')
    {
        parent::__construct($attributes);
        $this->connection = $connection;
    }


    /**
     * @param $where
     * @return array
     */
    public function getInfoFirst($where, $select = ['*']): array
    {
        $rows = self::query()
            ->select($select)
            ->where($where)
            ->first();
        return empty($rows) ? [] : $rows->toArray();
    }

    /**
     * @param $where
     * @return int
     */
    public function getInfoTotal($where): int
    {
        return self::query()
            ->where($where)
            ->count();
    }

    /**
     * @param $where
     * @return array
     */
    public function getInfo($where): array
    {
        return self::query()
            ->where($where)
            ->get()->toArray();
    }


    /**
     * @param $where
     * @param $set
     * @return int
     */
    public function updateData($where, $set): int
    {
        return self::query()->where($where)->update($set);
    }

    /**
     * @param array $insert
     * @return int
     */
    public function createData(array $insert): int
    {
        return self::query()->insertGetId($insert);
    }

    /**
     * @param array $insert
     * @return int
     */
    public function createAll(array $insert): int
    {
        return self::query()->insert($insert);
    }

    /**
     * 批量设置属性
     *
     * @param array $attribute
     *
     * @version 1.0
     * @author  only
     */
    public function batchSetAttribute(array $attribute)
    {
        foreach ($attribute as $key => $value) {
            $this->setAttribute($key, $value);
        }
    }



}
