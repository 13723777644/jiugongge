<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/11
 * Time: 14:27
 */

namespace Ht\Controller;


class PayController extends PublicController
{

    public function index(){
       $res= M('Chongzhi')->select();
       foreach ($res as $k=>$v){
          $res[$k]['name']= M('user')->where(['id'=>$v['uid']])->getField('name');
          $res[$k]['addtime']=date('Y-m-d H:i:s',$v['addtime']);
       }
       $this->assign('list',$res);
       $this->display();
    }

}