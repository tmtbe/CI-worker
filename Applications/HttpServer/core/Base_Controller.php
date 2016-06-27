<?php
/**
 * Created by PhpStorm.
 * User: tmtbe
 * Date: 16-6-23
 * Time: 上午9:39
 */
class Base_Controller extends CI_Controller  {
    protected $projectName = null;
    protected $action = null;
    public    $uid = null;
    public    $token = null;
    public    $channel = null;//渠道号
    public    $version = null;//版本号 3.0.0
    public    $imei = null;//ios 传idfa号码
    public    $collegeid = 0;//学校id
    public    $base_url = null;
    public    $root_url = null;
    public    $key="8f38780480386fcdf5978bd41ef13bce";//md5("Youwoxing2014")签名使用的key
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('cool');
        $this->load->model('Account');
        $this->load->library('Doer/DoerError');
        $this->load->config('config');
        $this->base_url      = $this->config->item('base_url');
        $this->root_url      = $this->config->item('root_url');
    }
    public function __CI_construct()
    {
        parent::__CI_construct();
        $this->uid           = $this->input->get_request_header('uid');
        $this->token         = $this->input->get_request_header('token');
        $this->channel       = $this->input->get_request_header('channel');
        $this->version       = $this->input->get_request_header('version');
        $this->imei          = $this->input->get_request_header('imei');
        $this->collegeid     = $this->input->get_request_header('collegeid');
        $this->ip_address    = $this->input->ip_address();
    }

    /**
     * 进行token验证
     * @return $accountInfo
     */
    public function authToken(){
        if(empty($this->uid)){
            throw new DoerError('token验证失败',DoerError::CODE_AUTH_FAIL);
        }
        $accountInfo = $this->Account->getAccountInfoByUid($this->uid);
        if(empty($this->token)||$this->token != $accountInfo['token']|| (dateMinusCus($accountInfo['lasttime']) > 15)){
            throw new DoerError('token验证失败',DoerError::CODE_AUTH_FAIL);
        }
        return $accountInfo;
    }

    /**
     * json输出
     * @param number $code 状态码
     * @param string $msg  状态说明
     * @param string $ext  json内容
     */
    public function output_json($code = DoerError::CODE_SUCCESS, $msg="", $ext=null)
    {
        $this->output->code = $code;
        $this->output->msg = $msg;
        $this->output->set_content_type('application/json; charset=UTF-8');
        $result = array("code" => $code);
        if($msg != "")
        {
            $result["msg"] =  $msg;
        }
        if(is_array($ext))
        {
            $result = array_merge($result,$ext);
        }
        $content = json_encode($result,JSON_UNESCAPED_UNICODE);
        $this->output->append_output($content);
        unset($content);
    }

    public function blackListUser(){
        //判断用户是否为黑名单用户
        if(!empty($this->uid)){
            $bool = $this->isBlackAccount($this->uid);
            if($bool){
                throw new DoerError('亲爱的用户:由于您违反平台相关规定,该账号被封停,如有疑问请联系平台客服QQ:3214823723,谢谢.',DoerError::CODE_BLACK_USER);
            }
        }
    }
    /**
     * 黑名单
     * @param int $uid
     * @return boolean
     */
    public function isBlackAccount($uid){
        $this->db->select('a_status');
        $this->db->where('a_id',$uid);
        $res = $this->db->get('account')->row_array(0);
        if(empty($res['a_status'])){
            return false;
        }else{
            return true;
        }
    }

    /**
     * 关闭服务
     */
    public function shutDownServer() {
        throw new DoerError('因服务器架构调整，为了您更好体验有我，小我建议您今晚（1月12号）21点后再进行操作，给您带来不便深表歉意。',DoerError::CODE_SERVER_REBOOT);
    }

    /**
     * 验证参数是否存在
     */
    public function authParamExists(){
        $params = func_get_args();
        if(empty($params)) return;
        foreach ($params as $param){
            if($param===null||$param===''){
                throw new DoerError('缺少参数',DoerError::CODE_FAIL);
            }
        }
    }
    /**
     * 验证参数是否合理
     */
    public function authParamReasonable($params,$reasons){
        if(!in_array($params, $reasons)){
            throw new DoerError('参数错误');
        }
    }
}