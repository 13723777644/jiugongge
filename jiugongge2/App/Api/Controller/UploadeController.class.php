<?php
/**
 * Created by PhpStorm.
 * User: 1
 * Date: 2018/8/23
 * Time: 15:10
 */

namespace Api\Controller;


class UploadeController extends PublicController
{
    public function upload(){
        //文件上传
        $width= I("request.width") ? I("request.width") : 400;
        $height= I("request.height") ? I("request.height") : 400;
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =  1145720 ;// 设置附件上传大小
        $upload->exts      =  array('jpg', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath  =  './Data/UploadFiles/brand/'; // 设置附件上传根目录
        $upload->savePath  =  ''; // 设置附件上传（子）目录
        $upload->saveName = time().mt_rand(100000,999999); //文件名称创建时间戳+随机数
        $upload->autoSub  = true; //自动使用子目录保存上传文件 默认为true
        $upload->subName  = array('date','Ymd'); //子目录创建方式，采用数组或者字符串方式定义
        // 上传文件
        $info = $upload->upload();
        if($info){
            foreach($info AS $k=>$v){
                $img='UploadFiles/brand/'.$info[$k]['savepath'].$info[$k]['savename'];
            }
//            $image = new \Think\Image();
//            $image->open('./Data/'.$img);
//            $image->thumb($width, $height)->save('./Data/'.$img);

            echo json_encode(array('status' => 1, 'img' =>$img));
            exit();

        }else{

            echo json_encode(array('status' => 0, 'err' => $upload->getError()));
            exit();
        }



    }



    /*
 * 通用识别
 */
    public function ocr(){
        $img='./Data/'.I("post.img");
        $url="https://api.ai.qq.com/fcgi-bin/ocr/ocr_generalocr";

        $data   = file_get_contents($img);
        $base64 = base64_encode($data);
        $params = array(
            'app_id'     => C('orc.aid'),
            'image'      => $base64,
            'time_stamp' => time(),
            'nonce_str'  => 'abckd'.mt_rand(1,9),
            'sign'       => '',
        );


        $params['sign']= $this->getReqSign($params,C('orc.ak'));


      $da=  json_decode($this->doHttpPost($url,$params),true);
      if($da['ret']==0){
          $arr=[];
         foreach ($da['data']['item_list'] as $k=>$v){
             $arr[]=$v['itemstring'];
         }

            $data='';
         foreach ($arr as $k=>$v){
             $data.=$v;
         }

          echo json_encode(array('status' => 1, 'data' => $data));
          exit();

      }else{
          echo json_encode(array('status' => 0, 'err' => '识别失败'));
          exit();
      }

    }


    /*
     * 保存
     */
    public function save(){
        $data['uid']=I('post.uid');
        $data['content']=I("post.str");
        if(empty($data['content'])){
            echo json_encode(array('status' => 0, 'err' => '请选择内容'));
            exit();
        }
        $data['addtime']=time();
        $res=M('content')->add($data);
        if($res){
            echo json_encode(array('status' => 1));
            exit();
        }else{
            echo json_encode(array('status' => 0, 'err' => '复制失败'));
            exit();
        }


    }


    /*str  翻译的文字
     * 翻译
     */
public function translate(){

    $strs="深开票资料名称:深圳市安顺祥科技有限公司地址:深圳市南山区桃源街道新屋村工业区第力电话:0755-8321 7873税号:91440300 6658 6291 66开户行:中国工商银行深圳西丽支行帐号:40000 27409 20055 9527快递地址:深圳市南山区沙河街道香年广场南收件人:黄俊琼联系电话13266773316这";
    $str=trim($strs);
dump($str);
    $str=$this->filterEmoji($str);  //过滤下特殊符号

    dump($str);

    $salt=mt_rand(1,9);
    $sign= md5('7c1535286b048f6b'.$str.$salt.'hyY21wTln8WP8IzmnyiW6bS3KtKXlZgu');
    $url="http://openapi.youdao.com/api?q=".$str."&from=auto&to=en&appKey=7c1535286b048f6b&salt=".$salt."&sign=".$sign;


$da=json_decode($this->dos($url),true);

    if($da['errorCode']==0){

        dump($da);die;
        echo json_encode(array('status' => 1, 'data' => $da['translation']['0']));
        exit();

    }else{
        echo json_encode(array('status' => 0, 'err' => '识别失败'));
        exit();
    }



}

    function filterEmoji($str)
    {
        $str = preg_replace_callback( '/./u',
            function (array $match) {
                return strlen($match[0]) >= 4 ? '' : $match[0];
            },
            $str);
        $regex = "/\/|\～|\，|\。|\！|\？|\“|\”|\【|\】|\『|\』|\：|\；|\《|\》|\’|\‘|\ |\·|\~|\!|\@|\#|\\$|\%|\^|\★|\&|\*|\(|\)|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\.|\/|\;|\'|\`|\-|\=|\\\|\|/";
        return preg_replace($regex,"",$str);

    }





        // getReqSign ：根据 接口请求参数 和 应用密钥 计算 请求签名
// 参数说明
//   - $params：接口请求参数（特别注意：不同的接口，参数对一般不一样，请以具体接口要求为准）
//   - $appkey：应用密钥
// 返回数据
//   - 签名结果
    function getReqSign($params /* 关联数组 */, $appkey /* 字符串*/)
    {
        // 1. 字典升序排序
        ksort($params);

        // 2. 拼按URL键值对
        $str = '';
        foreach ($params as $key => $value)
        {
            if ($value !== '')
            {
                $str .= $key . '=' . urlencode($value) . '&';
            }
        }

        // 3. 拼接app_key
        $str .= 'app_key=' . $appkey;

        // 4. MD5运算+转换大写，得到请求签名
        $sign = strtoupper(md5($str));
        return $sign;
    }


    function doHttpPost($url, $params)
    {


        $curl = curl_init();

        $response = false;
        do {
            // 1. 设置HTTP URL (API地址)
            curl_setopt($curl, CURLOPT_URL, $url);

            // 2. 设置HTTP HEADER (表单POST)
            $head = array(
                'Content-Type: application/x-www-form-urlencoded'
            );
            curl_setopt($curl, CURLOPT_HTTPHEADER, $head);

            // 3. 设置HTTP BODY (URL键值对)
            $body = http_build_query($params);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);

            // 4. 调用API，获取响应结果
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_NOBODY, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($curl);
            if ($response === false) {
                $response = false;
                break;
            }

            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($code != 200) {
                $response = false;
                break;
            }
        } while (0);

        curl_close($curl);
        return $response;


    }

    public function dos($url){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);//执行最长秒数
        curl_setopt($curl, CURLOPT_URL, $url);//url地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,FALSE);//不检查证书
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,FALSE);//不检查证书名
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');//post还是get

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);//流的形势不直接输出
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
            )
        );
        $res = curl_exec ($curl);
        curl_close($curl);

        return $res;

    }

}