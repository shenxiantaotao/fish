<?php


namespace app\common\model;


use think\Db;
use think\Exception;
use think\Model;

class Fishrelation
{
    /**
     * 获取单个信息
     * @param $id
     * @return array|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getfishrelation($id){
        return Db::name('fish_relation')->where('id',$id)->find();
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
        if(isset($search['fisherman_id'])&&$search['fisherman_id']!=''){
            $where['fisherman_id']=$search['fisherman_id'];
        }
        if(isset($search['fish_id'])&&$search['fish_id']!=''){
            $where['fish_id']=$search['fish_id'];
        }
        if(isset($search['status'])&&$search['status']!==''){
            $where['status']=$search['status'];
        }
        if($page=='all'){
            return Db::name('fish_relation')->where($where)->order($orderBy)->select();
        }else{
            $list=Db::name('fish_relation')->where($where)->page($page,$page_size)->order($orderBy)->select();
            $count=Db::name('fish_relation')->where($where)->page($page,$page_size)->order($orderBy)->count();
            return ['list'=>$list,'count'=>$count];
        }
    }

    /**
     * 保存数据
     * @param $data
     * @return bool|int|string
     */
    public function add($data,$all=false){
        try{
            //单个新增或修改
            if($all==false){
                if(isset($data['id'])&&$data['id']!=''){
                    return Db::name('fish_relation')->where('id',$data['id'])->update($data);
                }else{
                    if(!isset($data['fisherman_id'])||empty($data['fisherman_id'])){
                        throw new Exception('id不能为空');
                    }
                    if(!isset($data['fish_id'])||empty($data['fish_id'])){
                        throw new Exception('id不能为空');
                    }
                    return Db::name('fish_relation')->insertGetId($data);
                }
            }else{
                //批量增
                if(!isset($data[0]['fisherman_id'])||empty($data[0]['fisherman_id'])){
                    throw new Exception('人不能为空');
                }
                if(!isset($data[0]['fish_id'])||empty($data[0]['fish_id'])){
                    throw new Exception('鱼不能为空');
                }
                return Db::name('fish_relation')->insertAll($data);
            }


        }catch (Exception $e){
            $this->error=$e->getMessage();
            return false;
        }

    }

    /**
     * 增减数量
     * @param $fish_id
     * @param $number
     * @param bool $add
     * @return bool|int|true
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function addNum($id,$number,$add=true){
        if($add){
            return Db::name('fish_relation')->where('id',$id)->setInc('weight',$number);
        }else{
            $r=Db::name('fish_relation')->where('id',$id)->find();
            if(isset($r['weight'])&&$r['weight']>=$number){
                return Db::name('fish_relation')->where('id',$id)->setDec('weight',$number);
            }else{
                return false;
            }
        }
    }

}