<?php


namespace app\common\model;


use think\Db;
use think\Exception;
use think\Model;

class Fish extends Model
{
    /**
     * 获取单个信息
     * @param $id
     * @return array|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getfish($id){
        return Db::name('fish')->where('id',$id)->find();
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
        if(isset($search['fish_name'])&&$search['fish_name']!=''){
            $where['fish_name']=$search['fish_name'];
        }
        if(isset($search['type'])&&$search['type']!=''){
            $where['type']=$search['type'];
        }

        if($page=='all'){
            return Db::name('fish')->where($where)->order($orderBy)->select();
        }else{
            $list=Db::name('fish')->where($where)->page($page,$page_size)->order($orderBy)->select();
            $count=Db::name('fish')->where($where)->page($page,$page_size)->order($orderBy)->count();
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
                return Db::name('fish')->where('id',$data['id'])->update($data);
            }else{
                if(!isset($data['fish_name'])||empty($data['fish_name'])){
                    throw new Exception('名称不能为空');
                }
                return Db::name('fish')->insertGetId($data);
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
    public function addNum($fish_id,$number,$add=true){
        if($add){
            return Db::name('fish')->where('id',$fish_id)->setInc('number',$number);
        }else{
            $r=Db::name('fish')->where('id',$fish_id)->find();
            if(isset($r['number'])&&$r['number']>=$number){
                return Db::name('fish')->where('id',$fish_id)->setDec('number',$number);
            }else{
                return false;
            }
        }
    }

    
}