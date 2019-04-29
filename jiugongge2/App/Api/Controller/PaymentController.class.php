<?php
// 本类由系统自动生成，仅供测试用途
namespace Api\Controller;

use Think\Controller;

class PaymentController extends PublicController
{


    /*Payment/payment
     * 下单  只需要kid
     */
    public function payment()
    {

        $product = M("product");
        $kid=I("post.kid");
        $kanjia=M('kanjia')->where(['id'=>$kid,'status'=>0])->find();
        if(empty($kanjia)){
            echo json_encode(array('status' => 0, 'err' => '已经下过单'));
            exit();
        }
        $uid=$kanjia['uid'];

        $goods = $product->where(['del' => 0, 'id' => $kanjia['pid']])->find();//2
        if (empty($goods)) {
            echo json_encode(array('status' => 0, 'err' => '数据异常.'));
            exit();
        }//库存


        //生成订
        try {

            $data['amount'] =$goods['price']-M('kanjialist')->where(['kid'=>$kid,'status'=>1])->sum('price');
            $data['order_sn'] = $this->build_order_no();//生成唯一订单号
            $data['uid'] = $uid;
            $data['cid'] =1;
            $data['addtime'] = time();
            $data['del'] = 0;
            $data['status'] = 10;
            $data['pid'] = $kanjia['pid'];
            $data['kid'] =$kid;

            $results = M("order")->add($data);
        if($results){
            M('kanjia')->where(['id'=>$kid])->save(['status'=>1]);
            M('kanjialist')->where(['kid'=>$kid,'status'=>0])->delete();
        }

        } catch (Exception $e) {
            echo json_encode(array('status' => 0, 'err' => $e->getMessage()));
            exit();
        }


        echo json_encode(array('status' => 1));
        exit();
    }




}