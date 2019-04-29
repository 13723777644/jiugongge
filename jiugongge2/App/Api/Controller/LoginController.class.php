<?php
namespace Api\Controller;
use Think\Controller;
class LoginController extends PublicController {


  /*
   * 登录接口返回1是没有用户头像，返回2是有用户头像
   */
    public function authlogin(){
        $openid = $_POST['openid'];
        if (!$openid) {
            echo json_encode(array('status'=>0,'err'=>'授权失败！'.__LINE__));
            exit();
        }

        $con = array();
        $con['openid']=trim($openid);
        $uid = M('user')->where($con)->getField('id');
        if ($uid) {
            $userinfo = M('user')->where('id='.intval($uid))->find();
            if (intval($userinfo['del'])==1) {
                echo json_encode(array('status'=>0,'err'=>'账号状态异常！'));
                exit();
            }

            if($userinfo['photo']){
                //新用户
                $arr['photo']=$userinfo['photo'];
                $arr['nickname']=$userinfo['uname'];
                echo json_encode(array('status'=>2,'uid'=>$uid,'arr'=>$arr,));//有用户
                exit();
            }else{
                echo json_encode(array('status'=>1,'uid'=>$uid));//无用很详细
                exit();
            }

        }else{

            $data = array();

            $data['openid'] = $openid;
            $data['source'] = 'wx';

            $data['addtime'] = time();
            if (!$data['openid']) {
                echo json_encode(array('status'=>0,'err'=>'授权失败！'.__LINE__));
                exit();
            }

            $res = M('user')->add($data);
            if ($res) {
                echo json_encode(array('status'=>1,'uid'=>$res));//
                exit();
            }else{
                echo json_encode(array('status'=>0,'err'=>'授权失败！'.__LINE__));
                exit();
            }
        }
    }

    /*
     * 授权存
     * 需要photo,uname
     * 返回  status  0 和1
     */
    public function login(){

        $data['photo']=I("post.photo");

        $data['uname']=  $this->filterEmoji(I("post.uname"));

        $uid=I("post.uid");
        if(empty($uid)){
            echo json_encode(array('status'=>0,'err'=>'网络慢'));
            exit();
        }
        $res=M('user')->where(['id'=>$uid])->save($data);
        if($res){
            echo json_encode(array('status'=>1,));
            exit();
        }

    }

/*
 * 过滤特殊名字
 */
    function filterEmoji($str)
    {
        $str = preg_replace_callback( '/./u',
            function (array $match) {
                return strlen($match[0]) >= 4 ? '' : $match[0];
            },
            $str);

        return $str;
    }


   /*
    * 获取openid
    */
    //***************************
    //  获取sessionkey 接口
    //***************************
    public function getsessionkey(){
        $wx_config = C('weixin');
        $appid = $wx_config['appid'];
        $secret = $wx_config['secret'];

        $code = trim($_POST['code']);
        if (!$code) {
            echo json_encode(array('status'=>0,'err'=>'非法操作！'));
            exit();
        }

        if (!$appid || !$secret) {
            echo json_encode(array('status'=>0,'err'=>'非法操作！'.__LINE__));
            exit();
        }

        $get_token_url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.$appid.'&secret='.$secret.'&js_code='.$code.'&grant_type=authorization_code';
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$get_token_url);
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  //跳过sll证书
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        $res = curl_exec($ch);
        curl_close($ch);
        echo $res;
        exit();
    }



}