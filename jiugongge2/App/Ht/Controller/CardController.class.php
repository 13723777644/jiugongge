<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/11
 * Time: 9:31
 */

namespace Ht\Controller;


class CardController extends PublicController
{ public function _initialize(){
    $this->news = M('Card');

    $this->category = M('shicard');
}


    public function add()
    {
        $id = I("get.id");
        if (IS_POST) {

            //构建数组
            $this->news->create();
            //上传新闻图标
            if (!empty($_FILES["photo"]["tmp_name"])) {
                //文件上传
                $info = $this->upload_images($_FILES["photo"],array('jpg', 'png', 'jpeg'), "news/" . date(Ymd));
                if (!is_array($info)) {// 上传错误提示错误信息
                    $this->error($info);
                } else {// 上传成功 获取上传文件信息
                    $this->news->photo = 'UploadFiles/' . $info['savepath'] . $info['savename'];
                }
            }

            //保存数据
            if ($id > 0) {
                $result = $this->news->where("id=$id")->save();
            } else {
                //保存添加时间
                $this->news->addtime = time();
                $result = $this->news->add();
            }
            //判断数据是否更新成功
            if ($result) {
                $this->success('操作成功');
            } else {
                $this->error('操作失败');
            }
        } else {
            if ($id > 0) {
                $info = $this->news->where("id=$id")->find();
                if (!$info) {
                    $this->error('没有找到相关信息.');
                    exit;
                }
                $this->assign("info", $info);
            }

            $this->assign("id", $id);
            $this->display();
        }
    }


    public function index(){
        $result = M('Card')->select();

        foreach ($result as $k => $v) {
           $result[$k]['addtime']=date('Y-m-d H:i:s' ,$v['addtime']);
        }


        $this->assign('result', $result);
        $this->display();

    }

    public function del(){
        $id=I('get.id');
       $res= M("Card")->where(['id'=>$id])->delete();
       if($res){
        echo json_encode(1);
        die;
       }else{
           echo json_encode(0);
           die;
       }

    }

    public function news_cat_add(){
    $res=M('cardinfo')->where(['status'=>1])->select();

                foreach ($res as $k=>$v){
                    $res[$k]['addtime']=date('Y-m-d H:i:s',$v['addtime']);
                    $res[$k]['shixiao']=date('Y-m-d H:i:s',$v['shixiao']);
                    $res[$k]['shengri']=date('Y-m-d H:i:s',$v['shengri']);
                    $res[$k]['cardid']=M("card")->where(['id'=>$v['cardid']])->getField('name');
                }
                $this->assign('res',$res);
$this->display();
    }

    public function news_cat(){


         $res=M('Cardinfo')->where(['status'=>2])->select();

        foreach ($res as $k=>$v){
            $res[$k]['addtime']=date('Y-m-d H:i:s',$v['addtime']);
            $res[$k]['shixiao']=date('Y-m-d H:i:s',$v['shixiao']);
            $res[$k]['shengri']=date('Y-m-d H:i:s',$v['shengri']);
            $res[$k]['cardid']=M("card")->where(['id'=>$v['cardid']])->getField('name');
        }
        $this->assign('res',$res);
        $this->display();
    }


    public function order_shenhe(){
        $id=I('post.id');
        $res= M('cardinfo')->where(['id'=>$id])->save(['status'=>2]);

            echo 1;

    }


    public function order_return(){
        $id=I('post.id');
        $res= M('cardinfo')->where(['id'=>$id])->save(['status'=>3]);

        if($res){
            echo 1;
        }else{
            echo 0;
        }
    }


public function baoming(){
        $res=M("baoyue")->order('status asc,addtime desc')->select();

        foreach ($res as $k=>$v){

            $res[$k]['scid']=M('shicard')->where(['id'=>$v['scid']])->getField('name');

            $res[$k]['addtime']=date('Y-m-d H:i:s',$res['addtime']);
        }
        $this->assign('res',$res);
    $this->display();
}

 public function news_cat_tjs(){
        $id=I('post.id');
        $scid=I('post.act');

        if($scid==1){
            $res=M("baoyue")->where(['id'=>$id])->save(['status'=>2]);
        }
        if($res){
            echo 1;
        }else{
            echo 0;
        }
 }

    public function news_cat_adds(){
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

    public function news_cats(){

        //获取分类表里所有新闻分类
        $list = $this->category->order('sort desc,id desc')->select();
        $this->assign('list',$list);
        $this->display(); // 输出模板

    }


    public function news_cat_dels(){
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


    public function news_cat_delss(){
        if(IS_AJAX){
            $id=I("post.id");
            $check_info = M('baoyue')->where("id=$id")->find();
            if (!$check_info) {
                $this->error('非法操作.');
            }
            if($id){
                $re=M('baoyue')->where("id=$id")->delete();
                if($re){
                    unlink('./Data/'.$check_info['photo']);
                    $this->ajaxReturn(1);
                }else{
                    $this->ajaxReturn(0);
                }
            }
        }
    }



















}