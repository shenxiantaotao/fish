<?php


namespace app\api\controller;


use app\common\model\Fisherman;

class Login
{
    /**
     * 小程序登录
     */
    public function getOpenId(){
        $code=input('code');
        if(empty($code)){
            showJson('',1,'登录失败');
        }
        $appid='wx6e1d2d004ac78b0d';
        $secret='91225874b2fb70e49ab160e6da1a01df';
        $url='https://api.weixin.qq.com/sns/jscode2session?appid='.$appid.'&secret='.$secret.'&js_code='.$code.'&grant_type=authorization_code';
        $res=$res_arr=[];
        if(empty(session('session_key'))){
            $res=file_get_contents($url);
            $res_arr=json_decode($res,true);
        }

        if(isset($res_arr['session_key'])){
            //存用户
            $userinfo=json_decode(input('user_info'),true);
            session('session_key',$res_arr['session_key']);
            session('open_id',$res_arr['openid']);
            $fm=new Fisherman();
            $data['open_id']=$res_arr['openid'];
            $data['session_key']=$res_arr['session_key'];
            $data['wechat_pic']=$userinfo['avatarUrl'];
            $data['fisherman_name']=$userinfo['nickName'];
            $data['create_time']=time();
            $data['phone_number']='';
            $data['city']=$userinfo['city'];
            $fmone=$fm->getByopenid($res_arr['openid']);
            if($fmone){
                showJson();
            }
            $r=$fm->add($data);
            showJson($r);
        }else{
            showJson('',1,$res_arr);
        }
    }

}