<?php

namespace Api\Controller;

use Think\Controller;

class IndexController extends PublicController
{

    //***************************
    //  首页数据接口2为单店
    //***************************
    public function index()
    {

        $cate = M("category");
        $data = $cate->where(['tid' => 0])->order('sort desc')->select();

        foreach ($data as $k => $v) {
            $data[$k]['status'] = false;
            $data[$k]['data1'] = $cate->where(['tid' => $v['id']])->order('sort desc')->select();
            foreach ($data[$k]['data1'] as $k1 => $v1) {
                $data[$k]['data1'][$k1]['status'] = false;
                $data[$k]['data1'][$k1]['data2'] = $cate->where(['tid' => $v1['id']])->order('sort desc')->select();
                foreach ($data[$k]['data1'][$k1]['data2'] as $k2 => $v2) {

                    $data[$k]['data1'][$k1]['data2'][$k2]['did'] = $v['id'];
                    $data[$k]['data1'][$k1]['data2'][$k2]['status'] = false;
                }

            }
        }

        $new = M('product')->where(['isnew' => 2, 'del' => 0])->order('sort  desc')->field('id,name,photo1,photo2')->limit(2)->select();
        foreach ($new as $k => $v) {
            $new[$k]['photo1'] = __DATAURL__ . $v['photo1'];
            $new[$k]['photo2'] = __DATAURL__ . $v['photo2'];
        }
        $hot = M('product')->where(['ishot' => 2, 'del' => 0])->order('sort  desc')->field('id,name,photo1,photo2')->limit(2)->select();
        foreach ($hot as $k => $v) {
            $hot[$k]['photo1'] = __DATAURL__ . $v['photo1'];
            $hot[$k]['photo2'] = __DATAURL__ . $v['photo2'];
        }

        $title = M('category')->where(['tid' => 28])->select();

        foreach ($title as $k => $v) {
            $title[$k]['data'] = $cate->where(['tid' => $v['id']])->select();
            foreach ($title[$k]['data'] as $k1 => $v1) {
                $title[$k]['data'][$k1]['bz_1'] = __DATAURL__ . $v1['bz_1'];
            }
        }

        echo json_encode(array('data' => $data, 'new' => $new, 'hot' => $hot, 'title' => $title));
        exit();
    }

    /*
     * 新品推荐
     */
    public function getNews()
    {

        $page = I('post.page') ? I("post.page") : 1;

        $pages = $page * 10 - 10;

        $new = M('product')->where(['isnew' => 2, 'del' => 0])->order('sort desc')->field('id,name,photo1,photo2')->limit($pages . '10')->select();
        foreach ($new as $k => $v) {
            $new[$k]['photo1'] = __DATAURL__ . $v['photo1'];
            $new[$k]['photo2'] = __DATAURL__ . $v['photo2'];
        }

        echo json_encode(array('new' => $new));
        exit();
    }

    /*
     * 新品推荐
     */
    public function getHots()
    {
        $page = I('post.page') ? I("post.page") : 1;
        $pages = $page * 10 - 10;
        $hot = M('product')->where(['ishot' => 2, 'del' => 0])->order('sort  desc')->field('id,name,photo1,photo2')->limit($pages . '10')->select();
        foreach ($hot as $k => $v) {
            $hot[$k]['photo1'] = __DATAURL__ . $v['photo1'];
            $hot[$k]['photo2'] = __DATAURL__ . $v['photo2'];
        }
        echo json_encode(array('hot' => $hot));
        exit();
    }


    /*
     * 产品专区
     */
    public function data()
    {
        $page = I('post.page') ? I("post.page") : 1;
        $pages = $page * 10 - 10;
        $data = M('product')->where(['del' => 0])->order('sort  desc')->field('id,name,photo1,photo2')->limit($pages . '10')->select();
        foreach ($data as $k => $v) {
            $data[$k]['photo1'] = __DATAURL__ . $v['photo1'];
            $data[$k]['photo2'] = __DATAURL__ . $v['photo2'];
        }
        echo json_encode(array('data' => $data));
        exit();
    }

    /*
     * 二级分类下的
     */
    public function getDatas()
    {
        $page = I('post.page') ? I("post.page") : 1;
        $pages = $page * 10 - 10;
        $id = I('post.id');
        $Model = M();
        $data = $Model->query("select p.id,p.name,p.photo1 from zx_jianshen_guanxi as g LEFT JOIN  zx_jianshen_product as p on g.pid=p.id where p.del=0 and g.cid=" . $id . " order by p.sort desc limit " . $pages . ",10");

        foreach ($data as $k => $v) {
            $data[$k]['photo1'] = __DATAURL__ . $v['photo1'];
        }

        echo json_encode(array('data' => $data));
        exit();
    }


    /*
     * 产品详情
     */
    public function getContent()
    {
        $id = I("post.id");
        $data = M('product')->where(['id' => $id])->find();
        $data['photo3'] = __DATAURL__ . $data['photo3'];
        $content = str_replace(C('content.dir'), __DATAURL__, $data['content']);
        $data['content'] = html_entity_decode($content, ENT_QUOTES, 'utf-8');
$data['videoUrl']=M('video')->where(['pid'=>$data['id']])->getField('url');
        echo json_encode(array('status' => 1, 'data' => $data));
        exit();
    }


    /*
     * 匹配关键字
     */
    public function getSize()
    {
        $str = trim(I("post.str"));
        if(empty($str)){
            echo json_encode(array('status' => 0, 'err' => '匹配不到'));
            exit();
        }
        $page = I('post.page') ? I('post.page') : 1;
        $pages = $page * 10 - 10;
        $where['keywords'] = array('like', '%' . $str . '%');
        $where['del'] = array('eq', 0);
        $data = M('product')->where($where)->field('id,name,photo1,photo2,keywords')->limit($pages . '10')->select();
        foreach ($data as $k => $v) {
            $data[$k]['photo1'] = __DATAURL__ . $v['photo1'];
            $data[$k]['photo2'] = __DATAURL__ . $v['photo2'];
        }

        echo json_encode(array('status' => 1, 'data' => $data));
        exit();
    }


    /*
     *匹配筛选
     */
    public function filtrate()
    {
        $page = I('post.page') ? I('post.page') : 1;
        $pages = $page * 30 - 30;
        $str=I('post.str');
        $data=explode(',',$str);

        $aid=$data[0]?$data[0]:0;
        $bid=$data[1]?$data[1]:0;
        $cid=$data[2]?$data[2]:0;
        $did=$data[3]?$data[3]:0;
            $guanxi=M('guanxi');
            $data=$guanxi->where(['cid'=>$aid])->limit($pages . '30')->select();
            if($bid){
                foreach ($data as$k=>$v){
                    if(!$guanxi->where(['cid'=>$bid,'pid'=>$v['pid']])->find()){
                        unset($data[$k]);
                    }
                }
            }

            if($cid){
                foreach ($data as$k=>$v){
                    if(!$guanxi->where(['cid'=>$cid,'pid'=>$v['pid']])->find()){
                        unset($data[$k]);
                    }
                }
            }
        if($did){
            foreach ($data as$k=>$v){
                if(!$guanxi->where(['cid'=>$did,'pid'=>$v['pid']])->find()){
                    unset($data[$k]);
                }
            }
        }
            $arr=[];
                    foreach ($data as$k=>$v){
                    $arr[]= M('product')->where(['id'=>$v['pid'],'del'=>0])->field('id,name,photo1,photo2')->find();

                    }
            foreach ($arr as $k=>$v){
                $arr[$k]['photo1'] = __DATAURL__ . $v['photo1'];
                $arr[$k]['photo2'] = __DATAURL__ . $v['photo2'];
            }

        echo json_encode(array('status' => 1, 'data' => $arr));
        exit();
    }


}