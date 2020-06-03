<?php


namespace app\common\model;


use think\Db;
use think\Exception;
use think\Model;

class FishFood
{
    /**
     * 获取单个信息
     * @param $id
     * @return array|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getfishfood($id){
        return Db::name('fish_food')->where('id',$id)->find();
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
    public function getList($search=[],$page=1,$page_size=10,$orderBy='create_time DESC',$other=''){
        $where=[];
        if(isset($search['food_name'])&&$search['food_name']!=''){
            $where['food_name']=$search['food_name'];
        }
        if(isset($search['type'])&&$search['type']!=''){
            $where['type']=$search['type'];
        }

        if($page=='all'){
            return Db::name('fish_food')->where($where)->order($orderBy)->select();
        }else{
            $list=Db::name('fish_food')->where($where)->page($page,$page_size)->order($orderBy)->select();
            $count=Db::name('fish_food')->where($where)->page($page,$page_size)->order($orderBy)->count();
            return ['list'=>$list,'count'=>$count];
        }
    }

    /**
     * 保存数据
     * @param $data
     * @return bool|int|string
     */
    public function add($data){
        try{
            if(isset($data['id'])&&$data['id']!=''){
                return Db::name('fish_food')->where('id',$data['id'])->update($data);
            }else{
                if(!isset($data['food_name'])||empty($data['food_name'])){
                    throw new Exception('名称不能为空');
                }
                return Db::name('fish_food')->insertGetId($data);
            }
        }catch (Exception $e){
            $this->error=$e->getMessage();
            return false;
        }

    }
}