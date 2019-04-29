<?php
namespace Ht\Controller;
use Think\Controller;
class GuanggaoController extends PublicController{

	/*
	*
	* 构造函数，用于导入外部文件和公共方法
	*/
	public function _initialize(){
		$this->guanggao = M('guanggao');
	}

	/*
	*
	* 获取、查询广告表数据
	*/
	public function index(){
        //搜索，根据广告标题搜索
        $adv_name = I('post.adv_name');
        $condition = array();

        if ($adv_name) {
            $condition['name'] = array('LIKE', '%' . $adv_name . '%');
            $this->assign('name', $adv_name);
        }
        $shopid = I('get.shopid');
        if ($shopid) {

            $condition['shopid'] = array('eq', $shopid);
        }

        $adv_list = $this->guanggao->where($condition)->order('addtime desc')->select();


        $this->assign('adv_list', $adv_list);

      $this->display(); // 输出模板


    }


	/*
	*
	* 跳转添加或修改广告数据页面
	*/
	public function add(){

        $shopid=$_SESSION['admininfo']['shop_id'];
        $this->assign('shopid',$shopid);
		//如果是修改，则查询对应广告信息

        if (intval(I('get.adv_id'))) {
            $adv_id = intval(I('get.adv_id'));

            $adv_info = $this->guanggao->where('id=' . intval($adv_id))->find();
            if (!$adv_info) {
                $this->error('没有找到相关信息.');
                exit();
            }

            $this->assign('adv_info', $adv_info);
        }

      $this->display();
	}


	/*
	*
	* 添加或修改广告信息
	*/
	public function save(){


        $position=I("post.position");
        if(empty($position)){
            $this->error('数据有误');
        }
		$this->guanggao->create();


		//上传广告图片
		if (!empty($_FILES["file"]["tmp_name"])) {
			//文件上传
			$info = $this->upload_images($_FILES["file"],array('jpg','png','jpeg'),'adv/'.date(Ymd));
		    if(!is_array($info)) {// 上传错误提示错误信息
		        $this->error($info);
		    }else{// 上传成功 获取上传文件信息
			    $this->guanggao->photo = 'UploadFiles/'.$info['savepath'].$info['savename'];
			    //生成国定大小的缩略图
			    /*$path_url = './Data/UploadFiles/'.$info['savepath'].$info['savename'];
			    $image = new \Think\Image();
			    $image->open($path_url);
			    $image->thumb(310, 120,\Think\Image::IMAGE_THUMB_FIXED)->save($path_url);*/
			    if (intval($_POST['adv_id'])) {
					$check_url = $this->guanggao->where('id='.intval($_POST['adv_id']))->getField('photo');
					$url = "Data/".$check_url;
					if (file_exists($url) && $check_url) {
						@unlink($url);
					}
				}
		    }
		}


		//保存数据
		if (intval($_POST['adv_id'])) {
			$result = $this->guanggao->where('id='.intval($_POST['adv_id']))->save();
		}else{
			//保存添加时间
			$this->guanggao->addtime = time();
			$result = $this->guanggao->add();
		}
		//判断数据是否更新成功
		if ($result) {
			$this->success('操作成功.');
		}else{
			$this->error('操作失败.');
		}
	}

	/*
	*
	* 广告删除
	*/
	public function del(){
		//获取广告id，查询数据库是否有这条数据
		$adv_id = I('did');
		$check_info = $this->guanggao->where('id='.intval($adv_id))->find();
		if (!$check_info) {
			$this->error('系统繁忙，请时候再试！');
			exit();
		}

		//修改对应的删除状态
		$up = $this->guanggao->where('id='.intval($adv_id))->delete();
		if ($up) {
			$url = "Data/".$check_info['photo'];
			if (file_exists($url)) {
				@unlink($url);
			}
            echo json_encode(1);
			die;
		}else{
            echo json_encode(0);
            die;
		}
	}
}