<?php
/**
* Created by 代码生成器
*/ 
class DoerError extends Exception
{
	/**
	*正式服url
	**/
	const PHPROOTURL = "http://server.zdoer.net/";
	/**
	*测试服url
	**/
	const PHPTESTURL = "http://test-server.zdoer.net/";
	/**
	*md5 key
	**/
	const MD5_KEY = "8f38780480386fcdf5978bd41ef13bce";
	/**
	*成功
	**/
	const CODE_SUCCESS = 1;
	/**
	*失败
	**/
	const CODE_FAIL = 0;
	/**
	*服务器维护
	**/
	const CODE_SERVER_REBOOT = -1;
	/**
	*鉴权失败
	**/
	const CODE_AUTH_FAIL = -2;
	/**
	*该用户已经被拉黑
	**/
	const CODE_BLACK_USER = -3;
	/**
	*男
	**/
	const SEX_MAN = 0;
	/**
	*女
	**/
	const SEX_WOMAN = 1;
	/**
	*优惠劵校验失败
	**/
	const COUPON_CODE_FAIL = -1;
	/**
	*优惠劵建议使用
	**/
	const COUPON_CODE_USE = 1;
	/**
	*优惠劵建议不使用
	**/
	const COUPON_CODE_NOT_USE = 0;
	/**
	*正常订单
	**/
	const ORDER_CODE_NORMAL = 0;
	/**
	*取消订单
	**/
	const ORDER_CODE_CANCEL = -1;
	/**
	*已支付订单
	**/
	const ORDER_CODE_PAY = 1;
	/**
	*待支付
	**/
	const ORDER_CODE_WAIT_PAY = 2;
	/**
	*线下支付
	**/
	const ORDER_PAY_TYPE_UNONLINE = 1;
	/**
	*支付宝支付
	**/
	const ORDER_PAY_TYPE_ALIPAY = 2;
	/**
	*微信支付
	**/
	const ORDER_PAY_TYPE_WECHAT = 3;
	/**
	*收货地址个数
	**/
	const USER_ADDR_COUNT = 10;
	/**
	*默认学校
	**/
	const DEFAULT_COLLEGE = "北京航空航天科技大学";
	/**
	*默认学校id
	**/
	const DEFAULT_COLLEGE_ID = 1;
	/**
	*短信验证码
	**/
	const VERIFY_TYPE_MESSAGE = 1;
	/**
	*语音验证码
	**/
	const VERIFY_TYPE_VOICE = 2;
	/**
	*类型的键
	**/
	const CONDITION_TYPE_KEY = "type";
	/**
	*超市
	**/
	const CONDITION_TYPE_SUPER_MARKET = "1";
	/**
	*条件类型夜宵常量值
	**/
	const CONDITION_TYPE_YEXIAO = "2";
	/**
	*条件类型帮带饭常量值
	**/
	const CONDITION_TYPE_BANG_DAI_FANG = "3";
	/**
	*条件类型取快递常量值
	**/
	const CONDITION_TYPE_TAKE_EXPRESS = "9";
	/**
	*跳商品
	**/
	const BANNER_JUMP_GOODS = "1";
	/**
	*跳店铺
	**/
	const BANNER_JUMP_SHOP = "2";
	/**
	*跳转到url网页
	**/
	const BANNER_JUMP_URL = "3";
    public $others;
    public function __construct($message = "", $code = 0, $others = null)
    {
        parent::__construct($message, $code);
        $this->others = $others;
    }
}

