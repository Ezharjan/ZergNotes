<?php


namespace app\api\controller\v1;


use app\api\validate\AddressNew;
use app\api\service\Token as TokenService;
use app\api\model\User as UserModel;
use app\lib\exception\SuccessMessage;
use app\lib\exception\UserException;
use think\Controller;
use think\response\Json;

class Address extends Controller
{
    protected $beforeActionList = [
        'first' => ['only' => 'second,third']//只有控制器对应的Second方法才需要执行此操作
    ];

    protected function first()//不是接口，只是前置方法
    {
        echo 'first';
    }

    public function third()
    {
        echo 'third';
    }

    public function second()//是API接口
    {
        echo 'second';
    }

    public function createOrUpdateAddress()
    {
        $validate = new AddressNew();
        $validate->goCheck();
//        (new AddressNew())->goCheck();
        //根据Token获取uid
        //根据uid查找用户数据以判断其是否存在，不存在则抛出异常
        //获取用户从客户端提交的信息
        //根据用户地址信息是否存在来判断添加地址还是更新地址
        $uid = TokenService::getCurrentUid();
        $user = UserModel::get($uid);
        if (!$user) {
            throw new UserException();
        }

        //获取post中的所有内容——获取用户数据
        $dataArray = $validate->getDataByRule(input('post.'));

        $userAddress = $user->address;
        if (!$userAddress) {//如果用户地址信息不存在
            $user->address()->save($dataArray);//新增
        } else {//如果存在则更新
            $user->address->save($dataArray);//直接获取值，覆盖保存
        }
//        return $user;//把最新的资源状态返回到客户端去
//        return 'success';//返回更新成功的消息
        return Json(new SuccessMessage(), 201);//抛出成功信息，用json进行序列化使返回码保持一致
    }
}