<?php
namespace Ht\Controller;
use Think\Controller;
class UserController extends PublicController{

    public function normal(){
        $user=M("user");
        @$list=$user->where("del=0 AND qx=6")->field('id,name,uname,realname,addtime,openid,photo,tel,outtime')->select();
        foreach ($list as $k=>$v){
            if($v['outtime']==0){
                $list[$k]['outtime']='无';
            }elseif ($v['outtime']<=time()){
                $list[$k]['outtime']='无';
            }else{
                $list[$k]['outtime']=date('Y-m-d H:i:s',$v['outtime']);
            }
        }


        $this->assign("list",$list);
        $this->assign("type",I("get.type"));
        $this->display();
    }
}
