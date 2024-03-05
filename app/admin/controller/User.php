<?php
/**
 *  Created by www.kuleiman.com.
 *  User: YaoHongQiang
 *  Date: 2021/5/11   14:05
 *  description:
 */

namespace app\admin\controller;


use app\admin\AdminBase;
use think\facade\View;
use think\facade\Db;
use  app\admin\model\User as UserModel;
use app\admin\model\Auth;
use think\Validate;
class User extends AdminBase
{
    public function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub
        $this->UserModel=new UserModel();
    }
    function index(){
        $keyword=input("get.keyword/s","");
        $status=input("get.status/d",0);
        $return_data=$this->UserModel->user_list($keyword,$status);
        $list=$return_data["list"]->toarray();
        View::assign("status",$status);
        View::assign("count",$list["total"]);
        View::assign("list",$list["data"]);
        View::assign("page",$return_data["page"]);
        return View::fetch();
    }

    /**
     * 获取权限组
     */
    function auth_group(){
        $auth=new Auth();
        $data=$auth->auth_group("id,title");
        return $this->success("",["data"=>$data]);
    }

    /**
     * 添加/修改用户
     * @return bool|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function add(){
        $id=input("post.id/d","");
        $name=input("post.name/s","");
        $phone=input("post.phone/d","");
        $password=input("post.password/s","");
        $group=input("post.group/d","");
        $avatar=input("post.avatar/s","");
        $status=input("post.status/d","");
        $res_validate = $this->validateList(input(), 'User.UserAdd');
        if ($res_validate !== true) {
            return $res_validate;
        }else{
            if(!verify_phone($phone)){
                return $this->error("10000","手机号格式错误");
            }
            $group_info=Db::name("auth_group")->where(["status"=>1,"id"=>$group])->find();
            if(empty($group_info)){
                return $this->error("10000","请选择正确的分组");
            }
            $save_data=[
                "group_id"=>$group,
                "name"=>$name,
                "phone"=>$phone,
                "avatar"=>$avatar,
                "status"=>$status,
            ];
            $where=" phone=$phone";
            if($id){
                $where.=" and id!=$id";
            }
            $has_phone=Db::name("user")->where($where)->find();
            if(!empty($has_phone)){
                return $this->error("10000","该手机号码已被注册");
            }
            if($id){
                Db::name("user")->where(["id"=>$id])->update($save_data);
                Db::name("auth_group_access")->where(["uid"=>$id])->update(["group_id"=>$group]);
            }else{
                $Validate = new Validate();
                $Validate->rule('password', 'require|min:6|max:15')
                    ->message(['password.require' => '请填写密码', 'password.min' => '密码不能少于6位', 'password.max' => '密码不能多于15位']);
                if (!$Validate->check(['password' => $password])) {
                    return $this->validateError($Validate->getError());
                }
                $save_data["password"]=password_hash($password,PASSWORD_DEFAULT);
                $id=Db::name("user")->insertGetId($save_data);
                Db::name("auth_group_access")->insert(["uid"=>$id,"group_id"=>$group]);
            }
            return $this->success('操作成功');
        }
    }

    /**
     * 管理员详情
     * @return bool|\think\response\Json
     */
    function user_info(){
        $id=input("post.id/d","");
        $res_validate = $this->validateList(input(), 'User.UserInfo');
        if ($res_validate !== true) {
            return $res_validate;
        }
        $user_info=$this->UserModel->user_info($id);
        if(!empty($user_info)){
            unset($user_info['password']);
        }
        return $this->success('',["user_info"=>$user_info]);
    }

    /**
     * 用户禁用/启用
     * @return bool|\think\response\Json
     */
    function user_disable(){
        $id=input("post.id/d","");
        $res_validate = $this->validateList(input(), 'User.UserInfo');
        if ($res_validate !== true) {
            return $res_validate;
        }
        $return_data=$this->UserModel->user_disable($id);
        if($return_data["error"]){
            return $this->error($return_data["error"],$return_data["msg"]);
        }
        return $this->success("操作成功");
    }

    /**
     * 用户删除
     * @return bool|\think\response\Json
     */
    function user_del(){
        $id=input("post.id/d","");
        $res_validate = $this->validateList(input(), 'User.UserInfo');
        if ($res_validate !== true) {
            return $res_validate;
        }
        $return_data=$this->UserModel->user_del($id);
        if($return_data["error"]){
            return $this->error($return_data["error"],$return_data["msg"]);
        }
        return $this->success("操作成功");
    }
}