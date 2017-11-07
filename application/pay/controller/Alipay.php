<?php
/**
 *  黄志成 支付宝支付类
 *  时间 2017-10-23
 */

namespace pay;

class Alipay
{
    public function __construct()
    {
        vendor('alipay.aop.AopClient');

        vendor('alipay.aop.request.AlipayTradeAppPayRequest');
    }

    /**
     * 创建支付请求
     *
     * 返回请求链接
     */
    public function createPayAsk(){

        // 加载配置项
        $conf = config('alipay');
        $aop = new \AopClient;
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        $aop->appId = $conf['appId'];
        $aop->rsaPrivateKey = $conf['rsaPrivateKey'] ;
        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = "RSA2";
        $aop->alipayrsaPublicKey = $conf['alipayrsaPublicKey'];//对应填写
        //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        $request = new \AlipayTradeAppPayRequest();
        //SDK已经封装掉了公共参数，这里只需要传入业务参数
        $bizcontent = json_encode(array(
            'body'=>'商品名称',
            'subject' => '支付标题',//支付的标题，
            'out_trade_no' => '商户唯一订单号',
            'timeout_express' => '1d',//過期時間（分钟）
            'total_amount' => '0.01',//金額最好能要保留小数点后两位数
            'product_code' => 'QUICK_MSECURITY_PAY'
        ),JSON_UNESCAPED_UNICODE);
        $request->setNotifyUrl($conf['NotifyUrl']);//你在应用那里设置的异步回调地址
        $request->setBizContent($bizcontent);
        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);
        return $response;
    }

    /**
     * 接收支付宝推送的支付结果,并按照其要求返回需求内容
     */
    public function rebackPayResult(){
        $conf = config('alipay');
        $data = $_POST;
        $aop = new \AopClient;
        $aop->alipayrsaPublicKey = $conf['alipayrsaPublicKey'];
        $flag = $aop->rsaCheckV1($data, NULL, "RSA2");
        if($flag){
            //验证成功
            //这里可以做一下你自己的订单逻辑处理
            echo 'success';//这个必须返回给支付宝，响应个支付宝，
               

        } else {
            //验证失败
            echo "fail";
        }
        //$flag返回是的布尔值，true或者false,可以根据这个判断是否支付成功
    }

}