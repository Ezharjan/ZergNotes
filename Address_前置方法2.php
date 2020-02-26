<?php


namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\model\User as UserModel;
use app\api\service\Token as TokenService;
use app\api\validate\AddressNew;
use app\lib\enum\ScopeEnum;
use app\lib\exception\ForbiddenException;
use app\lib\exception\SuccessMessage;
use app\lib\exception\TokenException;
use app\lib\exception\UserException;

class Address extends BaseController
{
    protected $beforeActionList = [
        'checkPrimaryScope' => ['only' => 'createOrUpdateAddress']
    ];

    protected function checkPrimaryScope()
    {
        $scope = TokenService::getCurrentTokenVar('scope');
        if ($scope) {
            if ($scope >= ScopeEnum::User) {
                return true;
            } else {
                throw new ForbiddenException();
            }
        } else {
            throw new TokenException();
        }
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