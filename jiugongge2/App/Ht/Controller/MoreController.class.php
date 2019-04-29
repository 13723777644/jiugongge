<?php
// |++++++++++++++++++++++++++++++++++++++++
// |-综合管理
// |---单页管理(lr_web)
// |---用户反馈(lr_fankui)
// |---首页设置
// |------首页banner(lr_guanggao)
// |------新闻栏目设置(lr_config)
// |------推荐分类(lr_category)
// |------推荐产品(lr_product)
// |------推荐商家(lr_shangchang)
// |---城市管理(lr_china_city)
// |+++++++++++++++++++++++++++++++++++++++++
namespace Ht\Controller;
use Think\Controller;
class MoreController extends PublicController{
	//*************************
	//单页设置
	//*************************
	public function pweb_gl(){
		//获取web表的数据进行输出
		$model=M('web');
		$list=$model->where(['pid'=>0])->select();
		foreach ($list as $k=>$v){
		   $list[$k]['list2']= $model->where(['pid'=>$v['id']])->select();
        }

		//=================
		//将变量进行输出
		//=================
		$this->assign('list',$list);	
		$this->display();
	}

    public function pweb_gls(){
	    $model=M('zhanggui');
        $shopid=$_SESSION['admininfo']['shop_id'];
        if($shopid){
            $list=$model->where(['shopid'=>$shopid])->select();
        }else{
            $list=$model->select();
        }
        foreach ($list as $k=>$v){
            $list[$k]['shop'] = M('shangchang')->where(['id' => $v['shopid']])->getField('name');
            if ($list[$k]['shop'] == '') {
                $list[$k]['shop'] = "本店";
            }
        }

        $this->assign('list',$list);
        $this->display();
    }

    public function pwebs(){
        if(IS_POST){

            if(intval($_POST['id'])){
                $id=I("post.id");
                $data = array();
                $data['name']=$_POST['name'];
                $data['ptype']=$_POST['ptype'];
                $data['content'] = $_POST['concent'];
                $data['sort'] = intval($_POST['sort']);

                if (!empty($_FILES["file"]["tmp_name"])) {
                    //文件上传
                    $info = $this->upload_images($_FILES["file"],array('jpg','png','jpeg'),"category/indeximg");
                    if(!is_array($info)) {// 上传错误提示错误信息
                        $this->error($info);
                        exit();
                    }else{// 上传成功 获取上传文件信息

                        $data['photo'] = 'UploadFiles/'.$info['savepath'].$info['savename'];
                        $xt = M('indeximg')->where('id='.intval($id))->field('photo')->find();
                        if (intval($id) && $xt['photo']) {
                            $img_url = "Data/".$xt['photo'];
                            if(file_exists($img_url)) {
                                @unlink($img_url);
                            }
                        }
                    }
                }


                $up = M('zhanggui')->where('id='.intval($_POST['id']))->save($data);

                if ($up) {
                    $this->success('保存成功！');
                    exit();
                }else{
                    $this->error('操作失败！');
                    exit();
                }

            }else{
                $this->error('系统错误！');
                exit();
            }
        }else{
            $id=I('get.id');
          $data=  M('zhanggui')->where(['id'=>$id])->find();

            $this->assign('datas',$data);
            $this->display();
        }
    }


    //*************************
	//单页设置修改
	//*************************
	public function pweb(){
		if(IS_POST){

			if(intval($_POST['id'])){
				$data = array();
				$data['uname']=$_POST['uname'];
				$data['concent'] = $_POST['concent'];
				$data['sort'] = intval($_POST['sort']);
				$data['addtime'] = time();
				$up = M('web')->where('id='.intval($_POST['id']))->save($data);
				if ($up) {
					$this->success('保存成功！');
					exit();
				}else{
					$this->error('操作失败！');
					exit();
				}

			}else{
				$this->error('系统错误！');
				exit();
			}
		}else{
			$this->assign('datas',M('web')->where(M('web')->getPk().'='.I('get.id'))->find());
			$this->display();
		}
	}






	//*************************
	// 小程序配置 设置页面
	//*************************
	public function setup(){

        if (IS_POST) {

            //构建数组

            //上传产品分类缩略图
            if (!empty($_FILES["logo"]["tmp_name"])) {
                //文件上传
                $info2 = $this->upload_images($_FILES["logo"], array('jpg', 'png', 'jpeg'), "logo");
                if (!is_array($info2)) {// 上传错误提示错误信息
                    $this->error($info2);
                } else {// 上传成功 获取上传文件信息
                    $data['logo'] = 'UploadFiles/' . $info2['savepath'] . $info2['savename'];
                }
            }

                $data['title'] = I('post.title');
                $data['tel'] = I('post.tel');
                $data['people']=I('post.people');
                $data['phone'] = I('post.phone');
                $data['address'] = I('post.address');
                $data['copyright'] = I('post.copyright');
                $data['suppot'] = I('post.suppot');
                $data['fuwu'] = I('post.fuwu');
                $data['hours'] = I('post.hours');
                $data['num'] = I('post.num');
                $data['uptime'] = time();
                    $data['key'] = I('post.key');
                $data['mchid'] = I('post.mchid');
                $data['appsecret'] = I('post.appsecret');
                $data['appid'] = I('post.appid');

                $data['is_show'] = I('post.is_show');
                $data['isshows'] = I('post.isshows');
                $data['describe'] = trim(I('post.describe'));
                $data['content']=I("post.content");

                $check = M('program')->where('id=1')->getField('id');
                if (intval($check)) {

                    $up = M('program')->where('id=1')->save($data);
                } else {
                    $data['id'] = 1;
                    $up = M('program')->add($data);
                }

                if ($up) {
                    $this->success('保存成功！');
                    exit();
                } else {
                    $this->error('操作失败！');
                    exit();
                }

            } else {
                $this->assign('info', M('program')->where('id=1')->find());
                $this->display();
            }

    }

	//*************************
	// 首页图标 设置
	//*************************
	public function indeximg(){
		$list = M('indeximg')->where('1=1')->order('sort asc')->select();

		$this->assign('list',$list);
		$this->display();
	}

	//*************************
	// 首页图标 设置
	//*************************
	public function addimg(){
	    if(I('get.id')){
            $info = M('indeximg')->where('id='.intval($_REQUEST['id']))->find();

            //获取所有二级分类
            $procat = M('category')->where('tid=1')->field('id,name')->select();
            foreach ($procat as $k => $v) {
                $procat[$k]['list'] = M('category')->where('tid='.intval($v['id']))->field('id,name')->select();
            }
            $this->assign('info',$info);
            $this->assign('procat',$procat);
        }



		$this->display();
	}

	//*************************
	// 首页图标 设置
	//*************************
	public function saveimg(){
		$id = intval($_REQUEST['id']);


		$data = array();

		//上传产品分类缩略图
		if (!empty($_FILES["file"]["tmp_name"])) {
			//文件上传
			$info = $this->upload_images($_FILES["file"],array('jpg','png','jpeg'),"category/indeximg");
			if(!is_array($info)) {// 上传错误提示错误信息
				$this->error($info);
				exit();
			}else{// 上传成功 获取上传文件信息
				$data['photo'] = 'UploadFiles/'.$info['savepath'].$info['savename'];
				$xt = M('indeximg')->where('id='.intval($id))->field('photo')->find();
				if (intval($id) && $xt['photo']) {
					$img_url = "Data/".$xt['photo'];
					if(file_exists($img_url)) {
						@unlink($img_url);
					}
				}
			}
		}
		if (trim($_POST['name'])) {
			$data['name'] = trim($_POST['name']);
		}
		if (intval($_POST['ptype'])) {
			$data['ptype'] = intval($_POST['ptype']);
		}

		$data['sort'] = intval($_POST['sort']);
		if($id){
            $res = M('indeximg')->where('id='.intval($id))->save($data);
        }else{
            $res = M('indeximg')->add($data);
        }

		if ($res) {
			$this->success('保存成功！','indeximg');
			exit();
		}else{
			$this->error('操作失败！');
			exit();
		}
	}

}