<?php
namespace Ht\Controller;
use Think\Controller;

class IndexController extends PublicController{
	/**
	 * [index 首页框架]
	 * @return [type] [description]
	 */
	public function index(){
         

        $shopid=$_SESSION['admininfo']['shop_id'];

        $this->assign('shopid',$shopid);
	   $this->display();
	}
	/**
	 * [indexpage 首页]
	 * @return [type] [description]
	 */
	public function indexpage(){
        $todaytime=mktime(0,0,0,date('m'),date('d'),date('Y'));
        $beginThismonth=mktime(0,0,0,date('m'),1,date('Y'));
        // 模型

        $user=M('user');

        //订单数、用户数、交易额

        $usernum=$user->where("del=0")->count();

        $money=0;
        //拿充值的钱
        $today_usernum=$user->where("del=0 AND addtime>=".$todaytime)->count();
        $today_money=0;
        $today_moneys=0;
        $today_money=$today_money+$today_moneys;


        $thismonthmoney=0;


        $this->assign("usernum",$usernum);
        $this->assign("money",$money);


        $this->assign("today_usernum",$today_usernum);
        $this->assign("today_money",$today_money);



        $this->assign("thismonthmoney",$thismonthmoney);//本月的销售额

		$this->display();
	}




}