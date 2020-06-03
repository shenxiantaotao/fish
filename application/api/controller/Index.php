<?php


namespace app\api\controller;


use app\common\model\Fish;
use app\common\model\Fisherman;
use think\Controller;

class Index
{
    public function index(){
        $fm=new Fisherman();
        $data['fisherman_name']=rand(1,100).'shen';
        $r=$fm->getList([],1,3);
        var_dump($r);
    }

    /**
     * 放
     */
    public function raise(){
        $fishman_id=input('fishman_id');
        $fish_id=input('fish_id');
        $number=input('number');
        $remark=input('remark');
        $fish=new Fish();
        $res=$fish->addNum(1,2,false);
    }



}