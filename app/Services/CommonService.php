<?php

namespace App\Services;

use App\Facades\BaseFacade;
use App\Models\PlatformConfig;
use App\Models\StockClosed;
use App\Traits\ResultTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Redis;

/**
 * 公共服务
 * @package App\Http\Service
 */
class CommonService
{
    use ResultTrait;

    public function __construct()
    {

    }

    /**
     * 编号生成（前缀+时间戳+4位随机数）
     * @param string $prefix
     * @return string
     */
    public function code($key, $prefix = '')
    {

        $num = Redis::incr('Code:' . $key);
        return $prefix . Carbon::now()->format('YmdHis') . $num;
    }


    /**
     * 判定是否为交易日
     * @param null $date 日期 格式Y-m-d
     * @return array
     */
    public function isTrainingDay($date = null)
    {
        if (!$date) {
            $date = Carbon::now()->toDateString();
        }
        $closed = StockClosed::where('date', $date)->where('status', 0)->first();
        if ($closed) {
            return $this->succeed($closed, '今天是交易日');
        } else {
            return $this->failure(1, '不是交易日');
        }
    }

    /**
     * 获取交易日
     * @param int $days 天数
     * @param string $begin_date 开始日期
     * @param int $status 状态
     * @return mixed
     */
    public function stock_closed($days = 1, $begin_date = '', $status = 0)
    {
        $list = StockClosed::where(function ($qurey) use ($status, $begin_date) {
            $qurey->where('status', $status);
            if (empty($begin_date)) {
                $begin_date = Carbon::now()->toDateString();
            }
            $qurey->where('date', '>', $begin_date);

        })->orderBy('date')->limit($days)->pluck('date')->take($days)->toArray();
        return $list;
    }

    /**
     * 获取平台所有手续费率
     * @return mixed
     */
    public function getPoundage()
    {
        $poundages = PlatformConfig::find(1);
        return $poundages;
    }

    /**
     * 写备注,数据格式化为json
     * @param $remark
     * @param $content
     * @return string
     */
    public function writeRemark($remark, $content)
    {
        $arr = [];
        if (!empty($remark)) {
            $arr = json_decode($remark, true);
        }
        $arr[] = $content;
        return json_encode($arr);
    }

    //php生成GUID
    function getGuid()
    {
        $charid = strtoupper(md5(uniqid(mt_rand(), true)));

        $hyphen = chr(45);// "-"
        $uuid = substr($charid, 0, 8) . $hyphen
            . substr($charid, 8, 4) . $hyphen
            . substr($charid, 12, 4) . $hyphen
            . substr($charid, 16, 4) . $hyphen
            . substr($charid, 20, 12);
        return $uuid;
    }

    /*
	 * 通过日期查询周几
	 *
     */
    public function getWeek($date)
    {
        $weekArray = [0 => '周日', 1 => '周一', 2 => '周二', 3 => '周三', 4 => '周四', 5 => '周五', 6 => '周六'];
        $week = date('w', strtotime($date)); //出团日期为星期几
        return $weekArray[$week];
    }

    /**
     * 获取当前IP
     * @return string
     */
    public function getIP()
    {
        try {
            global $ip;
            if (getenv("HTTP_CLIENT_IP"))
                $ip = getenv("HTTP_CLIENT_IP");
            else if (getenv("HTTP_X_FORWARDED_FOR"))
                $ip = getenv("HTTP_X_FORWARDED_FOR");
            else if (getenv("REMOTE_ADDR"))
                $ip = getenv("REMOTE_ADDR");
            else
                $ip = false;

            return $ip;
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * 获取来源URL
     * @return string
     */
    public function getUrl()
    {
        try {
            return $_SERVER['HTTP_REFERER'];
        } catch (Exception $ex) {
            return '未知来源';
        }
    }

    function uuid($prefix = '')
    {
        $chars = md5(uniqid(mt_rand(), true));
        $uuid = substr($chars, 0, 8) . '-';
        $uuid .= substr($chars, 8, 4) . '-';
        $uuid .= substr($chars, 12, 4) . '-';
        $uuid .= substr($chars, 16, 4) . '-';
        $uuid .= substr($chars, 20, 12);
        return $prefix . $uuid;
    }

    public function getTree($data, $field_id, $field_pid, $pid = 0)
    {
        $arr = array();
        foreach ($data as $k => $v) {
            if ($v->$field_pid == $pid) {
                $v['children'] = self::getTree($data, $field_id, $field_pid, $v->$field_id);
                array_push($arr, $v);
            }
        }
        return $arr;
    }

    public function getPermissionTree()
    {
        $list = BaseFacade::getPermissions();
        return $this->getTreeview($list, 'name', 'code', 'parent_code', 'top');
    }

    public function getTreeview($data, $name, $field_id, $field_pid, $pid)
    {
        $arr = array();
        foreach ($data as $k => $v) {

            $v['text'] = $v->$name;
            if ($v->$field_pid == $pid) {
                $nodes = self::getTreeview($data, $name, $field_id, $field_pid, $v->$field_id);
                if (count($nodes) > 0) {
                    $v['nodes'] = $nodes;
                }
                array_push($arr, $v);
            }
        }
        return $arr;
    }

    /**
     * 通过订单参数生成锁房编号 -在 ota查房和创建订单锁房时使用
     * @param array $params ota_id check_in check_out today
     * @return string
     */
    public function getLockHouseNo($params)
    {
        $no = '';
        foreach ($params as $k => $v) {
            $no .= $k . '_' . $v;
        }
        return $no;
    }


    /**
     *  时间戳 毫秒
     * @return float
     */
    public function getMillisecond()
    {
        list($t1, $t2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
    }

    /**
     * 根据两个经纬度坐标计算距离
     * @param $lat1
     * @param $lng1
     * @param $lat2
     * @param $lng2
     * @return float|int
     */
    function getDistance($longitude1, $latitude1, $longitude2, $latitude2, $unit = 2, $decimal = 2)
    {

        $EARTH_RADIUS = 6370.996; // 地球半径系数
        $PI = 3.1415926;

        $radLat1 = $latitude1 * $PI / 180.0;
        $radLat2 = $latitude2 * $PI / 180.0;

        $radLng1 = $longitude1 * $PI / 180.0;
        $radLng2 = $longitude2 * $PI / 180.0;

        $a = $radLat1 - $radLat2;
        $b = $radLng1 - $radLng2;

        $distance = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
        $distance = $distance * $EARTH_RADIUS * 1000;

        if ($unit == 2) {
            $distance = $distance / 1000;
        }
        return round($distance, $decimal);
    }

    /**
     * php导出csv函数
     * @param $modelArr   格式 : [['名称'=>'张三','年龄'=>33],['名称'=>'李四','年龄'=>77]]
     * @param $countArr   格式 : ['总计:33元','合计:88元']
     * @return bool
     */
    function csv($modelArr, $countArr = null)
    {
        header("Cache-Control: public");
        header("Pragma: public");
        header("Content-type:application/vnd.ms-excel");
        header("Content-Disposition:filename=" . date('Ymd') . ".csv");
        header('Content-Type:APPLICATION/OCTET-STREAM');
        $headerStr = ($modelArr) ? iconv('utf-8', 'gb2312//IGNORE', implode(',', array_keys($modelArr[0])) . "\n") : false;
        if (!$headerStr) return false;
        echo $headerStr;
        foreach ($modelArr as $v) {
            $str = implode(',', $v) . "\n";
            $modelStr = iconv('utf-8', 'gb2312//IGNORE', $str);
            echo $modelStr;
        }
        if (!is_null($countArr)) {
            echo iconv('utf-8', 'gb2312//IGNORE', "\n");
            echo iconv('utf-8', 'gb2312//IGNORE', "\n");
            $str = implode(',', $countArr) . "\n";
            echo iconv('utf-8', 'gb2312//IGNORE', $str);
        }
    }

    /*
     * 随机6位密码
     * */
    function randStr($len = 6, $format = 'ALL')
    {
        switch ($format) {
            case 'ALL':
                $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-@#~';
                break;
            case 'CHAR':
                $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-@#~';
                break;
            case 'NUMBER':
                $chars = '0123456789';
                break;
            default :
                $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-@#~';
                break;
        }
        mt_srand((double)microtime() * 1000000 * getmypid());
        $password = "";
        while (strlen($password) < $len)
            $password .= substr($chars, (mt_rand() % strlen($chars)), 1);
        return $password;
    }

    /*
     * 生成33短链接
     * */
    function short_3url($data, $action = 'add', $timeout = 5)
    {
        try {
            $_apikey = 'OHRnB1';
            $_apisecret = 'b34ec2dc7ea01fff8a1562557d341a19';

            $url = sprintf('https://3url.cn/apis/%s?apikey=%s&apisecret=%s', $action, $_apikey, $_apisecret);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $result = curl_exec($ch);
            curl_close($ch);

            $ret = json_decode($result, true);

            if (!$ret || !isset($ret['result'])) {
                return array(false, '接口结果解析失败');
            }
            if ($ret['result'] != 0) {
                return array(false, $ret['message']);
            }
            return array(true, $ret['data']);

        } catch (\Exception $ex) {
            return array(false, '接口调用异常');
        }
    }

    /*
     * 获取33短链接集合
     * */
    function short_3url_list()
    {
        try {
            $_apikey = 'OHRnB1';
            $_apisecret = 'b34ec2dc7ea01fff8a1562557d341a19';

            $url = sprintf('https://3url.cn/apis/list?apikey=%s&apisecret=%s', $_apikey, $_apisecret);
            $timeout = 5;
            $data = array(
                'order' => 'pv'
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $result = curl_exec($ch);
            curl_close($ch);

            $ret = json_decode($result, true);
            if (!$ret || !isset($ret['result'])) {
                return array(false, '接口结果解析失败');
            }
            if ($ret['result'] != 0) {
                return array(false, $ret['message']);
            }
            return array(true, $ret['data']);
        } catch (\Exception $ex) {
            return array(false, '接口调用异常');
        }

    }

    /*
    * 获取33短链接详情
    * */
    function short_3url_info(array $url)
    {
        try {
            $_apikey = 'OHRnB1';
            $_apisecret = 'b34ec2dc7ea01fff8a1562557d341a19';

            $url = sprintf('https://3url.cn/apis/info?apikey=%s&apisecret=%s', $_apikey, $_apisecret);
            $timeout = 5;
            $data = array(
                'short_keys' => $url
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $result = curl_exec($ch);
            curl_close($ch);


            $ret = json_decode($result, true);
            return array(true, $result);

            if (!$ret || !isset($ret['result'])) {
                return array(false, '接口结果解析失败');
            }
            if ($ret['result'] != 0) {
                return array(false, $ret['message']);
            }
            return array(true, $ret['data']);
        } catch (\Exception $ex) {
            return array(false, '接口调用异常');
        }

    }

    /*
    * 生成猴子短链接
    * */
    function short_monkey_url($oldUrl)
    {
        try {
            $_apikey = 'B36881C966DF86CF694CC1BA90488213';
//            $_ios_img = 'http://oss.hjhp.cn/live/7e3f14ab-714a-fed8-d6d2-18fdff687241';
//            $_ios_img = url('/images/wechat.png');
            $_ios_img = '';
            $url = sprintf('http://apk.boruibj.com/api_admin/insertMoreUrl?appkey=%s&jump_url=%s&ios_img=%s&short_type=6', $_apikey, $oldUrl, $_ios_img);

            // 初始化
            $curl = curl_init();
            // 设置url路径
            curl_setopt($curl, CURLOPT_URL, $url);
            // 将 curl_exec()获取的信息以文件流的形式返回，而不是直接输出。
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回
            curl_setopt($curl, CURLOPT_BINARYTRANSFER, true);
            // 添加头信息
            curl_setopt($curl, CURLOPT_HTTPHEADER, []);
            // CURLINFO_HEADER_OUT选项可以拿到请求头信息
            curl_setopt($curl, CURLINFO_HEADER_OUT, true);
            // 不验证SSL
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
            // 执行
            $data = curl_exec($curl);
            // 打印请求头信息
            // 关闭连接
            curl_close($curl);
            // 返回数据
            $ret = json_decode($data, true);
            if (!$ret) {
                return array(false, '接口结果解析失败');
            }
            if ($ret['code'] != 200) {
                return array(false, $ret['msg']);
            }
            return array(true, $ret['data']);

        } catch (\Exception $ex) {
            return array(false, '接口调用异常');
        }
    }
}
