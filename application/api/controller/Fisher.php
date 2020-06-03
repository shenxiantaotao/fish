<?php


namespace app\api\controller;

use app\common\model\Fish;
use app\common\model\Fisherman;
use app\common\model\Fishrelation;
use app\common\model\Foodrelation;
use app\common\model\Record;

class Fisher
{
    /**
     * 保存
     */
    public function post(){
        $fm=new \app\common\model\Fisherman();
        $data['fisherman_name']=input('fm_name');
        $data['create_time']=time();
        $data['phone_number']=input('phone_number');
        $data['open_id']=input('open_id');
        $data['id']=input('fm_id');
        $r=$fm->add($data);
        if($r===false){
            showJson('',1,$fm->getError());
        }else{
            showJson($r);
        }
    }

    /**
     * 获取人列表
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAllMan(){
        $page=input('page');
        $fm=new Fisher();
        $r=$fm->getList([],$page,20);
        showJson($r);

    }
    /**
     * 获取池塘所有鱼列表
     */
    public function getAllfish(){
        $fr=new Fishrelation();
        $fr_list=$fr->getList(['status'=>1],input('page'),20);
        showJson($fr_list);
    }
    /**
     * 获取系统可放养的鱼列表
     */
    public function getraisefishlist(){
        $f=new Fish();
        $f_list=$f->getList([],'all');
        showJson($f_list);
    }

    /**
     * 放
     */
    public function raise(){
        $fishman_id=input('fishman_id');
        if(empty($fishman_id)){
            showJson('',1,'捕鱼人是谁');
        }
        $fish_id=input('fish_id');
        $number=input('number')?:1;
        $remark=input('remark');
        if(empty($fish_id)||empty($number)){
            showJson('',1,'请选择鱼和数量');
        }
        $alldata=[];
        for($i=0;$i<$number;$i++){
            $alldata[$i]['fisherman_id']=$fishman_id;
            $alldata[$i]['fish_id']=$fish_id;
            $alldata[$i]['remark']=$remark;
            $alldata[$i]['weight']=1;
            $alldata[$i]['create_time']=time();
        }
        $fr=new Fishrelation();
        $res=$fr->add($alldata,true);
        if($res==false){
            showJson('',1,'放养失败');
        }else{
            //增加记录
            $record=new Record();
            $data['fish_id']=$fish_id;
            $data['number']=$number;
            $data['type']=2;
            $data['fisherman_id']=$fishman_id;
            $data['create_time']=time();
            $data['remark']=$remark;
            $r=$record->add($data);
            showJson($res);
        }
    }
    /**
     * 捕
     */
    public function fishing(){
        $fishman_id=input('fishman_id');
        $number=rand(0,3);//捕获数量
        $fr=new Fishrelation();
        $record=new Record();
        $fr_list=$fr->getList(['status'=>1],'all');
        $fr_id_list=array_column($fr_list,'id');
        if(empty($fr_id_list)){
            showJson('',1,'没有鱼了，放几条吧');
        }
        $data['status']=2;
        $res=[];
        $rdata=[
            'fish_id'=>0,
            'type'=>1,
            'number'=>1,
            'fisherman_id'=>$fishman_id,
            'create_time'=>time(),
            'remark'=>''
        ];
        $number=count($fr_id_list)>=$number?:count($fr_id_list);
        echo $number;
        for($n=0;$n<$number;$n++){
            $data['id']=$fr_id_list[array_rand($fr_id_list)];
            $r=$fr->add($data);//修改状态为被捕
            $res[]=$data['id'];
            //添加捕鱼记录
            $frone=$fr->getfishrelation($data['id']);
            $rdata['fish_id']=isset($frone['fish_id'])?$frone['fish_id']:0;
            $record->add($rdata);
        }
        showJson($res);
    }

    /**
     * 喂
     */
    public function feed(){
        $fishman_id=input('fishman_id');
        if(empty($fishman_id)){
            showJson('',1,'是谁在喂食哦');
        }
        $food_id=input('food_id');
        $number=input('number')?:1;
        $weight=input('weight')?:1;
        $remark=input('remark');
        if(empty($food_id)||empty($number)){
            showJson('',1,'请选择食物哦');
        }
        $alldata=[];
        for($i=0;$i<$number;$i++){
            $alldata[$i]['fisherman_id']=$fishman_id;
            $alldata[$i]['food_id']=$food_id;
            $alldata[$i]['remark']=$remark;
            $alldata[$i]['weight']=$weight;
            $alldata[$i]['create_time']=time();
        }
        $fr=new Foodrelation();
        $res=$fr->add($alldata,true);
        if($res==false){
            showJson('',1,'喂食失败');
        }else{
            //增加记录
            $record=new Record();
            $data['food_id']=$food_id;
            $data['number']=$number;
            $data['type']=3;
            $data['fisherman_id']=$fishman_id;
            $data['create_time']=time();
            $data['remark']=$remark;
            $r=$record->add($data);
            showJson($res);
        }
    }

    /**
     * 获取自己捕获的鱼
     */
    public function getMyfish(){
        $fishman_id=input('fishman_id');
        $type=input('type')?:1;
        $r=new Record();
        $search=[
            'fisherman_id'=>$fishman_id,
            'type'=>$type,
        ];
        $fm=new Fisherman();
        $list=$r->getList($search,input('page'),3);
        foreach($list['list'] as $k=>&$v){
            $v['fishman_name']=$fm->getFisherman($v['fisherman_id'])['fisherman_name'];
            $v['create_time']=date('Y-m-d H:i:s',$v['create_time']);
        }
        showJson($list);
    }

}