<?php


namespace app\common\model;


use think\Db;
use think\Exception;
use think\Model;

class Fisherman extends Model
{
    /**
     * 获取单个信息
     * @param $id
     * @return array|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getFisherman($id)
    {
        return Db::name('fisherman')->where('id', $id)->find();
    }

    /**
     * 获取list
     * @param array $search
     * @param int $page
     * @param int $page_size
     * @param string $orderBy
     * @param $other
     * @return array|false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList($search = [], $page = 1, $page_size = 10, $orderBy = 'create_time DESC', $other = '')
    {
        $where = [];
        if (isset($search['open_id']) && $search['open_id'] != '') {
            $where['open_id'] = $search['open_id'];
        }
        if (isset($search['fisherman_name']) && $search['fisherman_name'] != '') {
            $where['fisherman_name'] = $search['fisherman_name'];
        }
        if (isset($search['phone_number']) && $search['phone_number'] != '') {
            $where['phone_number'] = $search['phone_number'];
        }

        if ($page == 'all') {
            return Db::name('fisherman')->where($where)->order($orderBy)->select();
        } else {
            $list = Db::name('fisherman')->where($where)->page($page, $page_size)->order($orderBy)->select();
            $count = Db::name('fisherman')->where($where)->page($page, $page_size)->order($orderBy)->count();
            return ['list' => $list, 'count' => $count];
        }
    }

    /**
     * 保存数据
     * @param $data
     * @return bool|int|string
     */
    public function add($data)
    {
        try {
            if (isset($data['id']) && $data['id'] != '') {
                return Db::name('fisherman')->where('id', $data['id'])->update($data);
            } else {
                if (!isset($data['open_id']) || empty($data['open_id'])) {
                    throw new Exception('名称不能为空');
                }
                return Db::name('fisherman')->insertGetId($data);
            }
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }

    }

    /**
     * 根据open_id查询
     */
    public function getByopenid($open_id)
    {
        return Db::name('fisherman')->where('open_id',$open_id)->find();
    }
}