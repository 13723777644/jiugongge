<?php

namespace Api\Controller;

use Think\Controller;

class IndexController extends PublicController
{


    public function index()
    {
        /***********获取首页顶部轮播图************/
        $ggtop = M('guanggao')->where(['position' => 1])->order('sort desc,id asc')->select();

        foreach ($ggtop as $k => $v) {
            $ggtop[$k]['photo'] = __DATAURL__ . $v['photo'];
        }
        M('program')->where(['id' => 1])->setInc('num');
        $num = M("program")->where(['id' => 1])->getField('num');
        $num = 5565 + $num;
        echo json_encode(array('ggtop' => $ggtop, 'num' => $num));
        exit();

    }


    public function getnews()
    {
        $id = I("post.id");
        $data = M('guanggao')->where(['id' => $id])->find();
        $content = str_replace(C('content.dir'), __DATAURL__, $data['content']);
        $data['content'] = html_entity_decode($content, ENT_QUOTES, 'utf-8');
        echo json_encode(array('status' => 1, 'data' => $data));
        exit();
    }


    public function save()
    {
        $uid = I("post.uid");
        $url = I("post.url");
        $type = I("post.type");
        $data['uid'] = $uid;
        $data['url'] = $url;


            //分隔图片并且保持


//分好了记录数据库放在数组里，传回去
            $result = $this->fenge($data['url'], $type);
          $data['photos']=$result;
          $res=M('photo')->add($data);
          if($res){
             $arr= array_filter(explode(',',$result));
             if($type==9){
                 $arrs1[0]['photo']=__DATAURL__.$arr[0];
                 $arrs1[1]['photo']=__DATAURL__.$arr[1];
                 $arrs1[2]['photo']=__DATAURL__.$arr[2];
                 $arrs2[0]['photo']=__DATAURL__.$arr[3];
                 $arrs2[1]['photo']=__DATAURL__.$arr[4];
                 $arrs2[2]['photo']=__DATAURL__.$arr[5];
                 $arrs3[0]['photo']=__DATAURL__.$arr[6];
                 $arrs3[1]['photo']=__DATAURL__.$arr[7];
                 $arrs3[2]['photo']=__DATAURL__.$arr[8];
                 echo json_encode(array('status' => 1,'arrs1'=>$arrs1,'arrs2'=>$arrs2,'arrs3'=>$arrs3));
                 exit();
             }elseif($type==4){
                 $arrs1[0]['photo']=__DATAURL__.$arr[0];
                 $arrs1[1]['photo']=__DATAURL__.$arr[1];
                 $arrs2[0]['photo']=__DATAURL__.$arr[2];
                 $arrs2[1]['photo']=__DATAURL__.$arr[3];
                 echo json_encode(array('status' => 1,'arrs1'=>$arrs1,'arrs2'=>$arrs2));
                 exit();
             }


          }else{
              echo json_encode(array('status' => 0, 'err' => '数据有误'));
              exit();
          }


    }


    public function getchicun($url)
    {

        $image_content = file_get_contents($url);
        $image = imagecreatefromstring($image_content);
        $width = imagesx($image);
        $height = imagesy($image);
        return array('width' => $width, 'height' => $height);


    }




    public function fenge($url,$type = 4)
    {

        $array=explode('/',$url);
            $sa=explode('.',$array[3]);//文件名

        $arrs = explode('.', $url);

        if ($arrs[count($arrs) - 1] != 'jpg') {
            $arrs[count($arrs) - 1] = 'jpg';
            $lurl = implode('.', $arrs);


            $this->zhuan($url, $lurl);

        }


        $url = __DATAURL__ . $lurl;

        $arr = $this->getchicun($url);

        if ($type == 9) {

            $maxW = (int)$arr['width'] / 3;; //准备将图片裁减成的小图的宽
            $maxH = (int)$arr['height'] / 3;; //准备将图片裁减成的小图的高

            $link = $url;//图片路径,自己修改
            $img = imagecreatefromjpeg($link);
            list($width, $height, $type, $attr) = getimagesize($link);
            $widthnum = ceil($width / $maxW);
            $heightnum = ceil($height / $maxH);
            $iOut = imagecreatetruecolor($maxW, $maxH);
//bool imagecopy ( resource dst_im, resource src_im, int dst_x, int dst_y, int src_x, int src_y, int src_w, int src_h )
//将 src_im 图像中坐标从 src_x，src_y 开始，宽度为 src_w，高度为 src_h 的一部分拷贝到 dst_im 图像中坐标为 dst_x 和 dst_y 的位置上。
            for ($i = 0; $i < $heightnum; $i++) {
                for ($j = 0; $j < $widthnum; $j++) {
                    imagecopy($iOut, $img, 0, 0, ($j * $maxW), ($i * $maxH), $maxW, $maxH);//复制图片的一部分
                    imagejpeg($iOut, "./Data/".$array[0]."/".$array[1]."/".$array[2]."/" .$sa[0]. $i  . $j . ".jpg"); //输出成0_0.jpg,0_1.jpg这样的格式
                }
            }

            $str= $array[0]."/".$array[1]."/".$array[2]."/" .$sa[0].'00.jpg'.','.
                $array[0]."/".$array[1]."/".$array[2]."/" .$sa[0].'01.jpg'.','.
                $array[0]."/".$array[1]."/".$array[2]."/" .$sa[0].'02.jpg'.','.
                $array[0]."/".$array[1]."/".$array[2]."/" .$sa[0].'10.jpg'.','.
                $array[0]."/".$array[1]."/".$array[2]."/" .$sa[0].'11.jpg'.','.
                $array[0]."/".$array[1]."/".$array[2]."/" .$sa[0].'12.jpg'.','.
                $array[0]."/".$array[1]."/".$array[2]."/" .$sa[0].'20.jpg'.','.
                $array[0]."/".$array[1]."/".$array[2]."/" .$sa[0].'21.jpg'.','.
                $array[0]."/".$array[1]."/".$array[2]."/" .$sa[0].'22.jpg'.',';

            return $str;

        }elseif($type==4){
            $maxW = (int)$arr['width'] / 2;; //准备将图片裁减成的小图的宽
            $maxH = (int)$arr['height'] / 2;; //准备将图片裁减成的小图的高

            $link = $url;//图片路径,自己修改
            $img = imagecreatefromjpeg($link);
            list($width, $height, $type, $attr) = getimagesize($link);
            $widthnum = ceil($width / $maxW);
            $heightnum = ceil($height / $maxH);
            $iOut = imagecreatetruecolor($maxW, $maxH);
//bool imagecopy ( resource dst_im, resource src_im, int dst_x, int dst_y, int src_x, int src_y, int src_w, int src_h )
//将 src_im 图像中坐标从 src_x，src_y 开始，宽度为 src_w，高度为 src_h 的一部分拷贝到 dst_im 图像中坐标为 dst_x 和 dst_y 的位置上。
            for ($i = 0; $i < $heightnum; $i++) {
                for ($j = 0; $j < $widthnum; $j++) {
                    imagecopy($iOut, $img, 0, 0, ($j * $maxW), ($i * $maxH), $maxW, $maxH);//复制图片的一部分
                    imagejpeg($iOut, "./Data/".$array[0]."/".$array[1]."/".$array[2]."/" .$sa[0]. $i  . $j . ".jpg"); //输出成0_0.jpg,0_1.jpg这样的格式
                }
            }

            $str= $array[0]."/".$array[1]."/".$array[2]."/" .$sa[0].'00.jpg'.','.
                $array[0]."/".$array[1]."/".$array[2]."/" .$sa[0].'01.jpg'.','.
                $array[0]."/".$array[1]."/".$array[2]."/" .$sa[0].'10.jpg'.','.
                $array[0]."/".$array[1]."/".$array[2]."/" .$sa[0].'11.jpg'.',';

            return $str;
        }


    }


    public function zhuan($url,$lurl)
    {
        $url = './Data/' . $url;
        $lurl = './Data/' . $lurl;


        $im = imagecreatefrompng($url);


             imagejpeg($im, $lurl);

    }
}