<?php

namespace Ht\Controller;

use Think\Controller;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
class ProductController extends PublicController
{
    //***********************************************
    public static $Array;//这个给检查产品的字段用 
    public static $PRO_FENLEI; //这个给产品分类打勾用
    //**************************************************
    //**********************************************
    //说明：产品列表管理 推荐 修改 删除 列表 搜索
    //**********************************************
    public function _initialize(){

        $this->category = M('goodsfenlei');

    }



    public function index()
    {



        $id = (int)$_GET['id'];


        $where = "1=1 AND del<1";


        $productlist = M('product')->where($where)->order('sort desc,id desc')->select();


        //==========================
        // 将GET到的数据再输出
        //==========================
        $this->assign('id', $id);



        $this->assign('productlist', $productlist);

        $this->display();
    }

    public function news_cat(){

        //获取分类表里所有新闻分类
        $list = $this->category->order('sort desc,id desc')->select();
        $this->assign('list',$list);
        $this->display(); // 输出模板

    }
    public function news_cat_del(){
        if(IS_AJAX){
            $id=I("post.id");
            $check_info = $this->category->where("id=$id")->find();
            if (!$check_info) {
                $this->error('非法操作.');
            }
            if($id){
                $re=$this->category->where("id=$id")->delete();
                if($re){
                    unlink('./Data/'.$check_info['photo']);
                    $this->ajaxReturn(1);
                }else{
                    $this->ajaxReturn(0);
                }
            }
        }
    }

    /**
     * [news_cat_add 新闻分类添加]
     * @return [type] [description]
     */
    public function news_cat_add(){
        //如果是修改，则查询对应分类信息
        $id=I("get.id");
        if(IS_POST){
            //判断是否已经存在该栏目
            if (!$id) {
                $check_id = $this->category->where('name="'.I("post.name").'"')->getField('id');
                if (is_int($check_id)) {
                    $this->error('分类已存在');exit;
                }
            }

            //构建数组
            $this->category->create();
            //上传新闻分类图标
            if (!empty($_FILES["photo"]["tmp_name"])) {
                //文件上传
                $info = $this->upload_images($_FILES["photo"],array('png'),"category/".date(Ymd));
                if(!is_array($info)) {// 上传错误提示错误信息
                    $this->error($info);
                }else{// 上传成功 获取上传文件信息
                    $this->category->photo = 'UploadFiles/'.$info['savepath'].$info['savename'];
                }
            }

            //保存数据
            if ($id>0) {
                $result = $this->category->where("id=$id")->save();
            }else{
                //保存添加时间
                $this->category->addtime = time();
                $result = $this->category->add();
            }
            //判断数据是否更新成功
            if ($result) {
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }else{
            if($id>0){
                $info=$this->category->where("id=$id")->find();
                if (!$info) {
                    $this->error('没有找到相关信息.');exit;
                }
                $this->assign("info",$info);
            }
            $this->assign("id",$id);
            $this->display();
        }
    }
    public function set()
    {
        $id = I('get.id');
        $hot = M("product")->where(['id' => $id])->getField('ishot');

        if ($hot == 1) {
            $data['ishot'] = 2;
        } else {
            $data['ishot'] = 1;
        }

        $up = M('product')->where(['id' => $id])->save($data);

        if ($up) {
            $this->redirect('index');
            exit();
        }
    }


    /*
     * 轮播图表
     */
    public function photoindex()
    {
        $result = M('product')->where(['del' => 0])->order('sort desc,addtime desc')->select();
        $this->assign('result', $result);
        $this->display();
    }


    public function photoadd()
    {
        if (I('get.id')) {
            $pid = I('get.id');
            $list = M("photo")->where(['pid' => $pid])->order("id asc")->select();
            $this->assign("pid", $pid);
            $this->assign("list", $list);
            $this->display();
        } else {
            $this->error('数据有误');
        }

    }







    public function upvideo($file)
    {

        Vendor('qiniuyun.autoload');

        $accessKey = C('qiniuyun.ak');
        $secretKey = C('qiniuyun.sk');
        // 初始化签权对象
        $auth = new Auth($accessKey, $secretKey);
        $bucket = C('qiniuyun.bucket');
// 生成上传Token

        $token = $auth->uploadToken($bucket);


// 构建 UploadManager 对象
        $uploadMgr = new UploadManager();

        $arr = explode('.', $file['name']);
        $filename = time() . '.' . $arr[1];

        list($ret, $err) = $uploadMgr->putFile($token, $filename, $file['tmp_name']);

        if ($err !== null) {//失败
            $url = 0;
            return $url;
            die;
        } else {//成功
            $url = C('qiniuyun.url') . $filename;

            return $url;
            die;
        }
    }

    /*
     * 增加视频
     */
    public function addvideo(){
        $pid=I("get.id");
        if(empty($pid)){
            $this->error("数据有误");
        }
        if(IS_POST){

            if (!empty($_FILES["file"]["tmp_name"])) {
                $arr = explode('.', $_FILES['file']['name']);
                $array = ['mp4', "avi"];

                if($_FILES['file']['size']>=23744790){
                    $this->error('请上传小于20M的视频');
                }


                if (in_array($arr[1], $array)) {

                    $url = $this->upvideo($_FILES["file"]);
                    if (!empty($url)) {
                        $data['url']=$url;


                        $in=M("video")->where(['pid'=>$pid])->find();
                        if($in){//有的花就保存
                            $info= M("video")->where(['pid'=>$pid])->save($data);
                        }else{//没有就增加
                            $data['pid']=$pid;
                            $info= M("video")->add($data);
                        }
                        if($info){
                            $this->success('成功');
                        }else{
                            $this->error('失败');
                        }
                    } else {
                        $this->error('视频上传失败');
                    }
                } else {
                    $this->error('格式不正确');
                }
            }

        }else{
            $url=M("video")->where(['pid'=>$pid])->getField('url');
            $this->assign("url",$url);
            $this->display();
        }
    }





    public function videodel(){
        $id=I("get.id");
        $url=M("video")->where(['pid'=>$id])->getField('url');
        if($url){
            Vendor('qiniuyun.autoload');
            $accessKey = C('qiniuyun.ak');
            $secretKey = C('qiniuyun.sk');
            $bucket = C('qiniuyun.bucket');
            $arr=explode('/',$url);

            $auth = new Auth($accessKey, $secretKey);
            $config = new \Qiniu\Config();
            $bucketManager = new \Qiniu\Storage\BucketManager($auth, $config);
            $err = $bucketManager->delete($bucket, $arr[3]);
            $url=M("video")->where(['pid'=>$id])->delete();
            $this->success('成功');
        }else{
            $this->error("失败");
        }




    }



















    public function shop_photo_add()
    {
        $id = I('get.id');
        $this->assign('id', $id);
        $this->display();
    }

    public function webuploader()
    {

        $id = I("get.id");
        if (!empty($_FILES["file"]["tmp_name"])) {
            //文件上传
            $info = $this->upload_images($_FILES["file"], array('jpg', 'png', 'jpeg'), "product/photo/" . date(Ymd));
            if (!is_array($info)) {// 上传错误提示错误信息
                $this->error($info);
            } else {// 上传成功 获取上传文件信息
                $photo = 'UploadFiles/' . $info['savepath'] . $info['savename'];
            }
            $data["pid"] = $id;
            $data["photo"] = $photo;

            M("photo")->add($data);
            echo json_encode(array("status" => 1, "err" => $photo));
        } else {
            echo json_encode(array("status" => 0, "err" => "上传失败!"));
        }

    }

    public function dels()
    {
        $id = I('get.id');
        if ($id) {
            $result = M("photo")->where(['id' => $id])->delete();
            if ($result) {
               echo json_encode(1);
            } else {
                echo json_encode(0);
            }
        }
    }

    public function shop_photo_del()
    {
        if (IS_AJAX) {
            $id = I("post.id");
            if ($id) {
                $re = M("photo")->where("id=$id")->delete();
                if ($re) {
                    $this->ajaxReturn(1);
                } else {
                    $this->ajaxReturn(0);
                }
            }
        }
    }

    public function roomnum()
    {
        $result = M('product')->where(['del' => 0])->order('sort desc,id')->select();
        foreach ($result as $k => $v) {

            $result[$k]['room'] = M('roomnum')->where(['pid' => $v['id']])->select();

            if ($result[$k]['room']) {

                foreach ($result[$k]['room'] as $k1 => $v1) {

                    $result[$k]['rooms'] .= $v1['room'] . ',';

                }
            } else {
                $result[$k]['rooms'] = '';
            }

        }

        $this->assign('result', $result);
        $this->display();
    }

    public function roomadd()
    {
        if (IS_GET) {
            $result = M('product')->where(['del' => 0])->field('id,name')->select();
            $rooms = M('roomnum')->where(['pid' => $pid])->select();
            $this->assign('result', $result);
            $this->assign('rooms', $rooms);
            $this->display();
        } else {
            $pid = I('post.ptype');
            $rooms = trim(I('post.rooms'));
            $arr = array_unique(explode(',', $rooms));

            $count = M("roomnum")->where(['pid' => $pid])->count();
            $length = count($arr) + $count;
            $num = M('product')->where(['del' => 0, 'id' => $pid])->getField('num');
            if ($length > $num) {
                $this->error('房间号数量大于设置的房间数');
            } else {
                foreach ($arr as $v) {
                    $data['room'] = $v;
                    $data['pid'] = $pid;
                    $res = M('roomnum')->where(['room' => $data['room']])->find();
                    if ($res) {
                        $this->error('房间号有重复');
                    } else {
                        M('roomnum')->add($data);
                    }
                }
                $this->success('批量插入成功');
            }
        }
    }

    /*
     * 改变某时房间数量
     */
    public function changenum()
    {
        $shopid = I('get.shopid');
        $where['del'] = array('eq', 0);


        if (!empty($shopid)) {
            $where['shopid'] = array('eq', $shopid);
        }
        $id = I("get.id");
        if ($id) {
            $res = M('changenum')->where(['id' => $id])->find();
            $res['starttime'] = date('Y-m-d', $res['starttime']);
            $res['endtime'] = date('Y-m-d', $res['endtime']);
            $res['endtimes'] = date('Y-m-d', $res['endtimes']);
            $this->assign('changenum', $res);
        }
        $product = M('product')->where($where)->field('name,id')->select();
        $this->assign('product', $product);
        $this->display();
    }



    public function delss()
    {
        $id = I("get.did");

        M('changenum')->where(['id' => $id])->delete();
        $this->success('删除成功');
    }







    //说明：产品 添加修改
    //注意：cid 分类id  shop_id店铺id
    //**********************************************
    public function add()
    {


        $id = (int)$_GET['id'];
        $page = (int)$_GET['page'];
        $name = $_GET['name'];
        $type = $_GET['type'];

        if (IS_POST) {

                $lei=I('post.lei');
            $feng=I('post.feng');
            $kong=I('post.kong');
            $se=I('post.se');
    if(empty($lei)){
        $this->error('类别必须填写');
    }
            if(empty($feng)){
                $this->error('风格必须填写');
            }
            if(empty($kong)){
                $this->error('空间必须填写');
            }
            if(empty($se)){
                $this->error('颜色必须填写');
            }
      $da=array_merge(array_merge(array_merge($lei,$feng),$kong),$se);




            try {
                $id = intval($_POST['pro_id']);
                $array = array(
                    'name' => $_POST['name'],
                    'intro' => $_POST['intro'],
                    'sort' => (int)$_POST['sort'],
                    'keywords' =>I('post.keywords'),
                    'isnew'=>I('post.isnew'),
                    'ishot'=>I('post.ishot'),
                    'url'=>I('post.url'),
                      //库存
                    'content' =>I('post.content'),

                );

                if (!empty($_FILES["photo1"]["tmp_name"])) {

                    $info = $this->upload_images($_FILES["photo1"], array('jpg', 'png', 'jpeg'), "product/" . date(Ymd));
                    if (!is_array($info)) {// 上传错误提示错误信息
                        $this->error($info);
                        exit();
                    } else {// 上传成功 获取上传文件信息
                        $array['photo1'] = 'UploadFiles/' . $info['savepath'] . $info['savename'];

                    }
                }


                if (!empty($_FILES["photo2"]["tmp_name"])) {

                    $info = $this->upload_images($_FILES["photo2"], array('jpg', 'png', 'jpeg'), "product/" . date(Ymd));
                    if (!is_array($info)) {// 上传错误提示错误信息
                        $this->error($info);
                        exit();
                    } else {// 上传成功 获取上传文件信息
                        $array['photo2'] = 'UploadFiles/' . $info['savepath'] . $info['savename'];

                    }
                }


                if (!empty($_FILES["photo3"]["tmp_name"])) {

                    $info = $this->upload_images($_FILES["photo3"], array('jpg', 'png', 'jpeg'), "product/" . date(Ymd));
                    if (!is_array($info)) {// 上传错误提示错误信息
                        $this->error($info);
                        exit();
                    } else {// 上传成功 获取上传文件信息
                        $array['photo3'] = 'UploadFiles/' . $info['savepath'] . $info['savename'];

                    }
                }




                //执行添加
                if (intval($id) > 0) {
                         M('guanxi')->where(['pid'=>$id])->delete();
                    $sql = M('product')->where('id=' . intval($id))->save($array);



                        $dataarr=[];

                        foreach ($da as $k=>$v){

                            $dataarr[$k]['pid']=$id;
                            $dataarr[$k]['cid']=$v;
                        }

                        M('guanxi')->addAll($dataarr);



                } else {
                    $array['addtime'] = time();


                    $sql = M('product')->add($array);
                    if($sql){
                        $dataarr=[];

                        foreach ($da as $k=>$v){

                            $dataarr[$k]['pid']=$sql;
                            $dataarr[$k]['cid']=$v;
                        }
                    }

                 M('guanxi')->addAll($dataarr);


                }

                //规格操作
                if ($sql > 0) {
                    echo "<script>alert('success')</script>";
                } else {
                    throw new \Exception("操作失败");
                }

            } catch (\Exception $e) {
                echo "<script>alert('" . $e->getMessage() . "');location='{:U('index')}?shop_id=" . $shop_id . "';</script>";
            }
        }

        $goodsfenlei= M("goodsfenlei")->select();
        $this->assign('goodsfenlei',$goodsfenlei);
        $pro_allinfo = $id > 0 ? M('product')->where('id=' . $id)->find() : "";

$this->assign('ll',$pro_allinfo);
        //获取所有商品轮播图
        if ($pro_allinfo['photo_string']) {
            $img_str = explode(',', trim($pro_allinfo['photo_string'], ','));

            $this->assign('img_str', $img_str);
        }

        $datas=M('category')->select();




        // 将GET到的数据再输出
        //==========================
        $this->assign('id', $id);
        $this->assign('name', $name);
        $this->assign('type', $type);
        $this->assign('shop_id', $shop_id);
        $this->assign('page', $page);
        //=============
        // 将变量输出
        //=============

        // 获取所有分类，进行关系划分
            $leibie=M('category')->where(['tid'=>28])->select();

            foreach ($leibie as $k=>$v){
                $arr[$k]=M('category')->where(['tid'=>$v['id']])->select();
                foreach ($arr[$k] as $k1=>$v1){
                    $arr[$k][$k1]['title']=$v['name'];
                }

            }
            $arrs=[];
            foreach ($arr as $k=>$v){
                    foreach ($v as $k=>$v){
                        $arrs[]=$v;
                    }
            }

$this->assign('arr',$arrs);
    $feng=M('category')->where(['tid'=>36])->select();
        $this->assign('feng',$feng);

        $kong=M('category')->where(['tid'=>39])->select();
        $this->assign('kong',$kong);


        $cids=M('guanxi')->where(['pid'=>$id])->field('cid')->select();
        $as=[];
        foreach ($cids as $k=>$v){
            $as[]=$v['cid'];
        }

        $se=M('category')->where(['tid'=>42])->select();
        $this->assign('se',$se);
        $this->assign('as',$as);
        $this->assign('pro_allinfo', $pro_allinfo);

        $this->display();


        //执行添加


    }




    public function news_cat_tj(){
        if(IS_AJAX){
            $id=I("post.id");
            $act=I("post.act");
            if(!$id){
                $this->ajaxReturn("参数有误!");
            }
            $re=M("goodsfenlei")->where("id=$id")->setField("is_tj",$act);
            if($re){
                $returnid= $act==0? 1 :0 ;
                $this->ajaxReturn($returnid);
            }else{
                $this->ajaxReturn("修改失败!");
            }
        }
    }


    public function goodsadd()
    {
        $shopid = I('get.shopid');
        $this->assign('shopid', $shopid);
        $id = (int)$_GET['id'];
        $page = (int)$_GET['page'];
        $name = $_GET['name'];
        $type = $_GET['type'];

        if ($_POST['submit'] == true) {

            try {
                //如果不是管理员则查询商家会员的店铺ID
                $id = intval($_POST['pro_id']);
                $array = array(
                    'name' => $_POST['name'],
                    'intro' => $_POST['intro'],
                    'shop_id' => intval($_POST['shop_id']),//所属店铺

                    'pro_number' => $_POST['pro_number'],    //产品编号
                    'sort' => (int)$_POST['sort'],
                    'price' => (float)$_POST['price'],
                    'price_yh' => (float)$_POST['price_yh'],
                    'price_jr' => (float)$_POST['price_jr'],//赠送积分
                    'updatetime' => time(),
                    'cid' => 3,
                    'num' => (int)$_POST['num'],            //库存
                    'content' => $_POST['content'],
                    'param' => $_POST['param'],
                    'company' => $_POST['company'],  //产品单位
                    'pro_type' => 1,

                    'renqi' => intval($_POST['renqi'])
                );
                dump($shopid);
                if ($shopid) {
                    $array['shopid'] = $shopid;
                } else {
                    $array['shopid'] = $_POST['fendian'];
                }


                if (!empty($_FILES["photo_string"]["tmp_name"])) {
                    $info = $this->upload_images($_FILES["photo_string"], array('jpg', 'png', 'jpeg'), "product/" . date(Ymd));
                    if (!is_array($info)) {// 上传错误提示错误信息
                        $this->error($info);
                        exit();
                    } else {// 上传成功 获取上传文件信息
                        $array['photo_string'] = 'UploadFiles/' . $info['savepath'] . $info['savename'];
                        $dt = M('product')->where('id=' . intval($id))->field('photo_string')->find();

                        if ($id && $dt['photo_string']) {
                            $img_url2 = "Data/" . $dt['photo_string'];
                            if (file_exists($img_url2)) {
                                @unlink($img_url2);
                            }
                        }
                    }
                }

                //执行添加
                if (intval($id) > 0) {

                    $sql = M('product')->where('id=' . intval($id))->save($array);
                } else {
                    $array['addtime'] = time();
                    $sql = M('product')->add($array);
                    $id = $sql;
                }

                //规格操作
                if ($sql) {//name="guige_name[]
                    $this->success('操作成功.');
                    exit();
                } else {
                    throw new \Exception('操作失败.');
                }

            } catch (\Exception $e) {
                echo "<script>alert('" . $e->getMessage() . "');location='{:U('index')}?shop_id=" . $shop_id . "';</script>";
            }
        }

        //=========================
        // 查询所有一级产品分类
        //=========================
//		$cate_list = M('category')->where('tid=1')->field('id,name')->select();
//		$this->assign('cate_list',$cate_list);

        //=========================
        // 查询产品信息
        //=========================
        $pro_allinfo = $id > 0 ? M('product')->where('id=' . $id)->find() : "";
        //商场信息
//		$shangchang= $pro_allinfo ? M('shangchang')->where('id='.intval($pro_allinfo['shop_id']))->find() : "";
//		//产品分类
//		$tid = M('category')->where('id='.intval($pro_allinfo['cid']))->getField('tid');
//		$pro_allinfo['tid'] = intval($tid);
//		if ($tid) {
//			$catetwo = M('category')->where('tid='.intval($tid))->field('id,name')->select();
//			$this->assign('catetwo',$catetwo);
//		}

        //获取所有商品轮播图
        if ($pro_allinfo['photo_string']) {
            $img_str = explode(',', trim($pro_allinfo['photo_string'], ','));

            $this->assign('img_str', $img_str);
        }

        //=========================
        // 查询所分类
        //=========================
        $brand_list = M('web')->where(['pid' => 0])->field('id,uname')->select();

        unset($brand_list[0]);
        $this->assign('brand_list', $brand_list);

        //==========================
        // 将GET到的数据再输出
        //==========================
        $this->assign('id', $id);
        $this->assign('name', $name);
        $this->assign('type', $type);

        $this->assign('page', $page);
        $shangchang = M('shangchang')->field('id,name')->select();

        $this->assign('shang', $shang);
        //=============
        // 将变量输出
        //=============
        $this->assign('pro_allinfo', $pro_allinfo);
        $this->assign('shangchang', $shangchang);
        $this->display();

    }


    /*
    * 商品获取二级分类
    */
    public function getcid()
    {
        $cateid = intval($_REQUEST['cateid']);
        $catelist = M('category')->where('tid=' . intval($cateid))->field('id,name')->select();
        echo json_encode(array('catelist' => $catelist));
        exit();
    }

    /*
    * 商品单张图片删除
    */
    public function img_del()
    {
        $img_url = trim($_REQUEST['img_url']);
        $pro_id = intval($_REQUEST['pro_id']);
        $check_info = M('product')->where('id=' . intval($pro_id) . ' AND del=0')->find();
        if (!$check_info) {
            echo json_encode(array('status' => 0, 'err' => '产品不存在或已下架删除！'));
            exit();
        }

        $arr = explode(',', trim($check_info['photo_string'], ','));
        if (in_array($img_url, $arr)) {
            foreach ($arr as $k => $v) {
                if ($img_url === $v) {
                    unset($arr[$k]);
                }
            }
            $data = array();
            $data['photo_string'] = implode(',', $arr);
            $res = M('product')->where('id=' . intval($pro_id))->save($data);
            if (!$res) {
                echo json_encode(array('status' => 0, 'err' => '操作失败！' . __LINE__));
                exit();
            }
            //删除服务器上传文件
            $url = "Data/" . $img_url;
            if (file_exists($url)) {
                @unlink($url);
            }

            echo json_encode(array('status' => 1));
            exit();
        } else {
            echo json_encode(array('status' => 0, 'err' => '操作失败！' . __LINE__));
            exit();
        }
    }

    //***************************
    //说明：产品 设置推荐
    //***************************
    public function set_tj()
    {
        $pro_id = intval($_REQUEST['pro_id']);
        $tj_update = M('product')->field('shop_id,type')->where('id=' . intval($pro_id) . ' AND del=0')->find();
        if (!$tj_update) {
            $this->error('产品不存在或已下架删除！');
            exit();
        }

        $shopinfo = M('shangchang')->where('id=' . intval($tj_update['shop_id']))->find();
        //查status,不符合条件不给通过
        if (intval($shopinfo['status']) != 1) {
            $this->error('商家未通过审核，产品不能设置推荐.');
            exit;
        }

        //查推荐type
        //dump($tj_update);
        $data = array();
        $data['type'] = $tj_update['type'] == 1 ? 0 : 1;
        $up = M('product')->where('id=' . intval($pro_id))->save($data);
        if ($up) {
            $this->redirect('index', array('page' => intval($_REQUEST['page'])));
            exit();
        } else {
            $this->error('操作失败！');
            exit();
        }
    }

    //***************************
    //说明：产品 设置新品
    //***************************
    public function set_new()
    {
        $pro_id = intval($_REQUEST['pro_id']);
        $tj_update = M('product')->field('shop_id,is_show')->where('id=' . intval($pro_id) . ' AND del=0')->find();
        if (!$tj_update) {
            $this->error('产品不存在或已下架删除！');
            exit();
        }

        //查推荐type
        $data = array();
        $data['is_show'] = $tj_update['is_show'] == 1 ? 0 : 1;
        $up = M('product')->where('id=' . intval($pro_id))->save($data);
        if ($up) {
            $this->redirect('index', array('page' => intval($_REQUEST['page'])));
            exit();
        } else {
            $this->error('操作失败！');
            exit();
        }
    }



    //***************************
    //说明：产品 删除
    //***************************
    public function del()
    {
        $id = intval($_REQUEST['did']);
        $info = M('product')->where('id=' . intval($id))->find();
        if (!$info) {
          echo json_encode(0);
        }

        if (intval($info['del']) == 1) {
            echo json_encode(1);
        }

        $data = array();
        $data['del'] = $info['del'] == '1' ? 0 : 1;
        $data['del_time'] = time();
        $up = M('product')->where('id=' . intval($id))->save($data);
        if ($up) {
            M('guanxi')->where(['pid'=>$id])->delete();
            echo json_encode(1);
        } else {
            echo json_encode(0);
        }
    }



    //**************************************
    // 说明：产品字段检测
    //**************************************
    public function check()
    {
        try {

            if (self::$Array['name'] == '') {
                throw new \Exception('产品名字不能为空！');
            }

            if (self::$Array['shop_id'] == '') {
                throw new \Exception('请选择所属店铺！');
            }


            return 1;

        } catch (\Exception $e) {
            $this->error_message = $e->getMessage();
            return false;
        }
    }

    //**********************************************
    //说明：商品商品规格
    //**********************************************
    public function pro_guige()
    {
        $pro_id = intval($_REQUEST['pid']);
        $check_info = M('product')->where('id=' . intval($pro_id) . ' AND del=0')->field('id,pro_buff')->find();
        if (!$check_info) {
            $this->error('产品不存在或已下架删除！');
            exit();
        }


        $guige_list = M('guige')->where('pid=' . intval($pro_id))->order('attr_id asc')->select();
        foreach ($guige_list as $k => $v) {
            $guige_list[$k]['attr_name'] = M('attribute')->where('id=' . intval($v['attr_id']))->getField('attr_name');
        }

        $this->assign('pro_id', $pro_id);
        $this->assign('pro_info', $check_info);
        $this->assign('guige_list', $guige_list);
        $this->display();
    }

    //**********************************************
    //说明：设置单产品不同规格不同价格不同库存
    //**********************************************
    public function set_attr()
    {
        $pro_id = intval($_REQUEST['pid']);
        $check_info = M('product')->where('id=' . intval($pro_id) . ' AND del=0')->field('id,name,price_yh,num,company,pro_buff')->find();
        if (!$check_info) {
            $this->error('产品不存在或已下架删除！');
            exit();
        }

        //已有属性
        $guige = M('guige')->where('pid=' . intval($pro_id))->select();
        foreach ($guige as $k => $v) {
            $guige[$k]['attr_name'] = M('attribute')->where('id=' . intval($v['attr_id']))->getField('attr_name');
        }

        //获取已有规格
        if ($check_info['pro_buff'] != '') {
            $pro_buff = explode(',', trim($check_info['pro_buff'], ','));
            $buff = array();
            $buffs = array();
            foreach ($pro_buff as $k => $v) {
                $guige_list = array();
                $buff['attr_id'] = $v;
                $buff['attr_name'] = M('attribute')->where('id=' . intval($v))->getField('attr_name');
                $guige_list = M('guige')->where('attr_id=' . intval($v) . ' AND pid=' . intval($pro_id))->field('id,name')->select();
                $guige = array();
                $guige2 = array();
                foreach ($guige_list as $key => $val) {
                    $guige['id'] = $val['id'];
                    $guige['name'] = $val['name'];
                    $guige2[] = $guige;
                }
                $buff['guige_list'] = $guige2;
                $buffs[] = $buff;
            }
        }

        //产品属性管理
        $attr_list = M('attribute')->select();
        $this->assign('pro_id', $pro_id);
        $this->assign('pro_info', $check_info);
        $this->assign('guige', $guige);
        $this->assign('attr_list', $attr_list);
        $this->assign('guige_list', $buffs);
        $this->display();
    }

    //********************************
    //说明：保存产品规格
    //********************************
    public function save_guide()
    {

        $pro_id = intval($_POST['pro_id']);
        $check_info = M('product')->where('id=' . intval($pro_id) . ' AND del=0')->field('id,name,price_yh,num,company,pro_buff')->find();
        if (!$check_info) {
            $this->error('产品不存在或已下架删除！');
            exit();
        }

        if ($_POST['attribute']) {
            //产品规格
            $attribute = $_POST['attribute'];
            foreach ($attribute as $k => $v) {
                $guige_name = $_POST['gg_name'][$v];
                if ($guige_name) {
                    foreach ($guige_name as $key => $val) {
                        $data = array();
                        $data['pid'] = $pro_id;
                        $data['attr_id'] = $v;
                        $data['name'] = $val;
                        $data['price'] = $check_info['price_yh'];
                        $data['stock'] = intval($check_info['num']);
                        $data['addtime'] = time();
                        $res = M('guige')->add($data);
                        if (!$res) {
                            $this->error('部分规格添加失败，请核对过后再补充！', U('pro_guige', array('pid' => $pro_id)));
                            exit();
                        }
                    }
                }
            }
            $str = implode(',', $_POST['attribute']);
            $up_pro = M('product')->where('id=' . intval($pro_id))->save(array('pro_buff' => $str));

            $this->success('操作成功！', U('pro_guige', array('pid' => $pro_id)));
            exit();
        }
        $this->error('没有获取到属性信息！');
        exit();
    }

    //********************************
    //说明：ajax修改价格库存
    //********************************、
    public function ajax_up()
    {
        $pro_id = intval($_POST['pro_id']);
        $id = intval($_POST['id']);
        $vals = trim($_POST['vals']);
        $type = trim($_POST['type']);
        $check = M('guige')->where('id=' . intval($id) . ' AND pid=' . intval($pro_id))->find();
        if (!$check) {
            echo json_encode(array('status' => 0, 'err' => '系统错误.' . __LINE__));
            exit();
        }

        $data = array();
        if ($type == 'pro_price') {
            if ($check['price'] == $vals) {
                echo json_encode(array('status' => 1));
                exit();
            }
            $data['price'] = floatval(sprintf("%.2f", $vals));
        } elseif ($type == 'pro_stock') {
            if ($check['stock'] == $vals) {
                echo json_encode(array('status' => 1));
                exit();
            }
            $data['stock'] = intval($vals);
        }

        if ($data) {
            $res = M('guige')->where('id=' . intval($id) . ' AND pid=' . intval($pro_id))->save($data);
            if ($res) {
                echo json_encode(array('status' => 1));
                exit();
            } else {
                echo json_encode(array('status' => 0, 'err' => '网络异常，请稍后再试.' . __LINE__));
                exit();
            }
        } else {
            echo json_encode(array('status' => 0, 'err' => '没有找到要修改的数据.' . __LINE__));
            exit();
        }

    }

    //********************************
    //说明：规格图片上传
    //********************************、
    public function guige_upload()
    {
        $id = intval($_POST['gg_id']);
        $check_info = M('guige')->where('id=' . intval($id))->find();
        if (!$check_info) {
            $this->error('参数错误.' . __LINE__);
            exit();
        }
        $array = array();
        if (!empty($_FILES['file_' . $id]['tmp_name'])) {
            //文件上传
            $info = $this->upload_images($_FILES['file_' . $id], array('jpg', 'png', 'jpeg'), "attribute/" . date(Ymd));
            if (!is_array($info)) {// 上传错误提示错误信息
                $this->error($info);
                exit();
            } else {// 上传成功 获取上传文件信息
                $array['img'] = 'UploadFiles/' . $info['savepath'] . $info['savename'];
            }
        }
        if ($array) {
            $res = M('guige')->where('id=' . intval($id))->save($array);
            if (!$res) {
                $this->error('上传失败，请稍后再试.' . __LINE__);
                exit();
            }

            //删除之前的图片
            if ($check_info['img']) {
                $img_url = "Data/" . $xt['img'];
                if (file_exists($img_url)) {
                    @unlink($img_url);
                }
            }
        }

        $this->redirect('pro_guige', array('pid' => intval($check_info['pid'])));
    }

    //********************************
    //说明：产品单个规格删除
    //********************************
    public function del_guige()
    {
        $id = intval($_REQUEST['gg_id']);
        $check_info = M('guige')->where('id=' . intval($id))->find();
        if (!$check_info) {
            $this->error('参数错误.' . __LINE__);
            exit();
        }

        $res = M('guige')->where('id=' . intval($id))->delete();
        if ($res) {
            //删除之前的图片
            if ($check_info['img']) {
                $img_url = "Data/" . $check_info['img'];
                if (file_exists($img_url)) {
                    @unlink($img_url);
                }
            }
            $this->success('操作成功！');
            exit();
        } else {
            $this->error('删除失败.');
            exit();
        }

    }

    //**********************************************
    //说明：设置单产品不同规格不同价格不同库存 公共方法
    //**********************************************
    public function set_pro_attr($pro_id)
    {
        //查询产品是否存在
        $pro_info = M('product')->where('id=' . intval($pro_id))->find();
        if (!$pro_info) {
            return false;
        }

        //获取产品所有规格属性组合，没有就添加
        $proAttrid = M('pro_attr')->where('pid=' . intval($pro_id))->getField('id');
        //遍历查询到的属性名称
        $d = array();
        $pro_buff = array();
        $buff = M('product')->where('id=' . intval($pro_id))->getField('pro_buff');
        $pro_buff = explode(',', $buff);
        foreach ($pro_buff as $k => $v) {
            $a = M('guige')->where('pid=' . intval($pro_id) . ' AND attr_id=' . intval($v))->field('name')->select();
            foreach ($a as $key => $val) {
                $b[$k][] = $val['name'];
            }
        }

        //组合所有规格属性
        foreach ($b[0] as $k => $v) {
            if ($b[1]) {
                foreach ($b[1] as $k1 => $v1) {
                    if ($b[2]) {
                        foreach ($b[2] as $k2 => $v2) {
                            if ($b[3]) {
                                foreach ($b[3] as $k3 => $v3) {
                                    $d[] = $v . ',' . $v1 . ',' . $v2 . ',' . $v3;
                                }
                            } else {
                                $d[] = $v . ',' . $v1 . ',' . $v2;
                            }
                        }
                    } else {
                        $d[] = $v . ',' . $v1;
                    }
                }
            } else {
                $d[] = $v;
            }
        }

        //把所有组合存入一个数组
        $arr = array();
        $arr1 = array();
        foreach ($d as $k => $v) {
            $arr1['gg_name'] = $v;
            $arr1['price'] = $pro_info['price_yh'];//价格：默认为添加产品时的优惠价格
            $arr1['stock'] = $pro_info['num'];//库存：默认为添加产品时的数量
            $arr[] = $arr1;
        }

        //存入产品属性规格表
        $data = array();
        $data['pid'] = intval($pro_id);
        $data['name'] = serialize($arr);
        $data['addtime'] = time();
        if ($proAttrid) {
            $res = M('pro_attr')->where('id=' . intval($proAttrid))->save($data);
        } else {
            $res = M('pro_attr')->add($data);
        }

        if ($res) {
            return true;
        } else {
            return false;
        }
    }

    public function nums()
    {
        $id = I('get.id');
        if (empty($id)) {
            $this->error('数据有误');
        }
        $this->assign('id', $id);
        $this->display();


    }

    /*拿到开始时间和结束时间，以及商品的编号，可以判断每天算不算节假日，算就价格不易
    然后判断有没有房间
     *
     */
    public function ggsmd()
    {
        $shopid = $_SESSION['admininfo']['shop_id'];

        $id = $_REQUEST['id'];
        if (empty($id)) {
            $this->error('参数有误');
        }

        $today = strtotime(date("Y-m-d"), time());
        $endtime = strtotime('+3 month', $today);
        $goods = M('product')->where(['del' => 0, 'id' => $id, 'cid' => 2])->find();
        if (empty($goods)) {
            echo json_encode(array('status' => 0, 'err' => "没有这个房型"));
            exit();
        }

        $vacation = M('vacation')->where(['shopid' => $shopid])->select();

        $ggs = $this->timer($vacation, $goods, $today, $endtime);//获取到是否是节假日了和价格

        $goods = $this->manfang($goods, $today, $endtime);  //房间数量
        $goods = $this->kucunss($goods, $today, $endtime);//获取到库存

        $ggs = $this->creatdata($ggs, $today);

        $data = $this->merge($ggs, $goods['status']);

//
//        $data = [
//            "2017-10-11" => ['price' => 538, 'roomNum' => '5'],
//            "2017-10-12" => ['price' => 538, 'roomNum' => '5'],
//            "2017-10-13" => ['price' => 538, 'roomNum' => '5'],
//        ];

//
        $callback = $_REQUEST['callback'];
        echo $callback . '(' . json_encode($data) . ')';
        exit;
    }

    public function merge($ggs, $goods)
    {//合并两个数组

        foreach ($ggs as $k1 => $v1) {
            $ggs[$k1]['roomNum'] = '';

            foreach ($goods as $k => $v) {
                if ($k == $k1) {
                    $ggs[$k1]['roomNum'] = $v;
                }
            }
        }
        //做成类似data的数据
        $arrs = '';
        foreach ($ggs as $k => $v) {
            $arrs[$ggs[$k]['time']] = $v;

        }
        foreach ($arrs as $k => $v) {
            unset($arrs[$k]['staus']);
            unset($arrs[$k]['time']);
        }

        return $arrs;
    }

    public function creatdata($ggs, $today)
    {

        foreach ($ggs as $k => $v) {
            if ($k == 1) {
                $ggs[$k]['time'] = $today;
            } else {
                $ggs[$k]['time'] = $today + ($k - 1) * 86400;
            }

        }
        foreach ($ggs as $k => $v) {
            $ggs[$k]['time'] = date('Y-m-d', $v['time']);
        }

        return $ggs;

    }

    public function manfang($goods, $today, $endtime, $ks = '1')
    {

        if ($today < $endtime) {
            $where['pid'] = ['eq', $goods['id']];
            $where['checkindate'] = ['elt', $today + 1];
            $where['checkoutdate'] = ['egt', $today + 1];
            $where['status'] = ['gt', 10];
            $goods['status'][$ks] = M('order')->where($where)->sum('product_num');

            return $this->manfang($goods, $today + 86400, $endtime, $ks + 1);
        } else {

            return $goods;
        }

    }

    public function kucunss($goods, $start_time, $end_time, $ks = '1')
    {
        if ($start_time < $end_time) {
            //算现在的时间是否在数据表如果在就用表里的房间数，
            foreach ($goods['status'] as $k1 => $v1) {
                $where['starttime'] = ['elt', $start_time + 1];
                $where['endtime'] = ['egt', $start_time + 1];
                $where['pid'] = ['eq', $goods['id']];
                $dd = M('changenum')->where($where)->find();

                if ($dd) {
                    $goods['status'][$ks] = $dd['num'] - $v1;
                } else {
                    //当前时间不在设置的表里就取本地的
                    if ($v1 < $goods['num']) {

                        $goods['status'][$ks] = $goods['num'] - $v1;//有房间

                    } else {
                        $goods['status'][$ks] = 0;
                    }
                }
            }


            return $this->kucunss($goods, $start_time + 86400, $end_time, $ks + 1);
        } else {

            return $goods;
        }


//
//        foreach ($goods['status'] as $k => $v) {
//            if ($v < $goods['num']) {
//                $goods['status'][$k] = $goods['num'] - $v;//有房间
//            } else {
//
//                $goods['status'][$k] = 0;
//            }
//        }
//
//        return $goods;
    }

    public function timer($vacation, $goods, $today, $endtime, $ks = '1')
    {
        if ($today < $endtime) {
            foreach ($vacation as $k => $v) {
                $goods['timer'][$ks] .= ',' . $this->isMixTimes($today, $today, $v['start_time'], $v['end_time']);

            }

            return $this->timer($vacation, $goods, $today + 86400, $endtime, $ks + 1);
        } else {

            return $this->chuli($goods);//拿到进而


        }
    }


    function isMixTimes($begintime1, $endtime1, $begintime2, $endtime2)
    {
        $status = $begintime2 - $begintime1;//节日开始时间减去普通开始时间
        if ($status > 0) {      //如果大于0则  节日时间大于普通开始时间  就要看普通结束时间了
            $status2 = $begintime2 - $endtime1;  //节日开始时间减去普通结束时间
            if ($status2 > 0) {
                return 0;                //	如果大于0  则两个时间段没有交际
            } else {
                return 1;
                //如小与0   则两个时间段有交际  交际为   $endtime1- $begintime2
            }
        } else { //如果小于0则  节日开时间小于普通开始时间    就要看节日结束时间可普通开始时间了
            $status2 = $begintime1 - $endtime2;    /// 普通开始时间减去节日结束·时间
            if ($status2 > 0) {
                return 0;                // 如果大于则  普通时间大于节日时间
            } else {
                return 1;     // 如果小于则  交际为  $kk= $endtime1-$endtime2  if大于0交及为 $endtime2-$bgintime1
                //如果小于0  $bgintime1-$endtime1
            }
        }

    }

    public function chuli($goods)
    {

        foreach ($goods['timer'] as $k => $v) {
            $goods['timer'][$k] = max(explode(',', $v));
        }

        foreach ($goods['timer'] as $k => $v) {

            $ggs[$k]['status'] = $v;
        }

        foreach ($ggs as $k => $v) {
            if ($v['status'] == 1) {
                $ggs[$k]['price'] = $goods['price_jr'];

            } else {
                $ggs[$k]['price'] = $goods['price_yh'];
            }
        }
        return $ggs;


    }
}