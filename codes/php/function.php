<?php
class Helper
{
    /**
     * 后台执行php命令
     * @param $command
     */
    public static function execInBackground($command)
    {
        if (substr(php_uname(), 0, 7) == "Windows") {
            pclose(popen("start /B $command", "r"));
        } else {
            exec("$command > /dev/null &");
        }
    }

    /**
     * 执行一条命令
     * @param $command
     * @return null|string
     * @throws \Exception
     */
    public static function execCommand($command)
    {
        $output = null;
        if (function_exists('exec')) {
            exec($command, $result, $return);
            $output = implode(PHP_EOL, $result);
        } elseif (function_exists('passthru')) {
            ob_start();
            passthru($command, $return);
            $output = ob_get_contents();
            ob_end_clean();
        } elseif (function_exists('system')) {
            ob_start();
            system($command, $return);
            $output = ob_get_contents();
            ob_end_clean();
        }

        if ($return !== 0) {
            throw new \Exception("Can't run command: " . $command . ". result: " . $output);
        }

        return $output;
    }

    /**
     * 删除目录
     * @param $dir
     * @return bool
     */
    public static function rmdir($dir)
    {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? static::rmdir("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    //随机IP
    public static function Rand_IP()
    {

        $ip2id = round(rand(600000, 2550000) / 10000); //第一种方法，直接生成
        $ip3id = round(rand(600000, 2550000) / 10000);
        $ip4id = round(rand(600000, 2550000) / 10000);
        //下面是第二种方法，在以下数据中随机抽取
        $arr_1 = ["218", "218", "66", "66", "218", "218", "60", "60", "202", "204", "66", "66", "66", "59", "61", "60", "222", "221", "66", "59", "60", "60", "66", "218", "218", "62", "63", "64", "66", "66", "122", "211"];
        $randarr = mt_rand(0, count($arr_1) - 1);
        $ip1id = $arr_1[$randarr];
        return $ip1id . "." . $ip2id . "." . $ip3id . "." . $ip4id;
    }

    /**
     * 下载文件
     * @param $url
     * @param $filename
     * @param $retry
     * @param $curlOptions
     * @return string
     * @throws Exception
     */
    public static function downloadFile($url, $filename = null, $retry = 3, $curlOptions = [])
    {
        if ($filename === null)
            $filename = sys_get_temp_dir() . '/cache_dl_' . md5($url);

        if (!is_dir($path = pathinfo($filename, PATHINFO_DIRNAME)))
            mkdir($path, 0777, true);
        elseif (is_file($filename))
            unlink($filename);

        $fp = fopen($filename, 'w+');

        $ch = curl_init();
        curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_FILE => $fp,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_ENCODING => '',
                CURLOPT_CONNECTTIMEOUT => 10,
            ] + $curlOptions);

        curl_exec($ch);
        $success = curl_errno($ch) ? false : true;
        $error = curl_error($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        fclose($fp);

        if (!$success) {
            if ($retry > 0) {
                sleep(1);
                return static::downloadFile($url, $filename, $retry - 1);
            }
            file_exists($filename) && @unlink($filename);
            throw new Exception('request url error:' . $url . ' error:' . var_export($error, true) . ' info:' . var_export($info, true), $info['http_code']);
        }

        if (in_array($info['http_code'], [301, 302])) {
            return static::downloadFile($info['redirect_url'], $filename);
        } elseif ($info['http_code'] != 200) {
            file_exists($filename) && @unlink($filename);
            throw new Exception('request url error:' . $url . ' info:' . var_export($info, true), $info['http_code']);
        }

        return $filename;
    }

    /**
     * 批量下载文件
     * @param array $urls
     * @param array $filenames 默认为空自动生成，若指定key需与$urls对应
     * @param int $retry 重试次数
     * @param array $curlOptions 附加curl选项
     * @return array []["error"=>0,"message"=>"","url"=>"","file"=>""] 0成功,1失败
     */
    public static function downloadFiles(array $urls, array $filenames = [], $retry = 3, $curlOptions = [])
    {
        /* @var []resource $fpArr */
        /* @var []resource $chArr */
        $fpArr = $chArr = [];
        $mh = curl_multi_init();
        foreach ($urls as $key => $url) {
            if (($filename = $filenames[$key]) === null) {
                $filename = sys_get_temp_dir() . '/cache_dl_' . md5($url);
                $filenames[$key] = $filename;
            }

            if (!is_dir($path = pathinfo($filename, PATHINFO_DIRNAME)))
                mkdir($path, 0777, true);
            elseif (is_file($filename))
                unlink($filename);

            $fpArr[$key] = fopen($filename, 'w+');
            $chArr[$key] = curl_init();
            curl_setopt_array($chArr[$key], [
                    CURLOPT_URL => $url,
                    CURLOPT_FILE => $fpArr[$key],
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS => 2,
                    CURLOPT_ENCODING => '',
                    CURLOPT_CONNECTTIMEOUT => 10,
                ] + $curlOptions);

            curl_multi_add_handle($mh, $chArr[$key]);
        }

        do {
            $status = curl_multi_exec($mh, $active);
            if ($active) {
                curl_multi_select($mh);
            }
        } while ($active && $status == CURLM_OK);

        $errorUrls = [];
        $result = [];
        foreach ($urls as $key => $url) {
            $success = curl_errno($chArr[$key]) ? false : true;
            $error = curl_error($chArr[$key]);
            $info = curl_getinfo($chArr[$key]);
            curl_multi_remove_handle($mh, $chArr[$key]);
            fclose($fpArr[$key]);

            if ($info['http_code'] != 200) {
                file_exists($filenames[$key]) && @unlink($filenames[$key]);
                if (!in_array($info['http_code'], [404])) {
                    $errorUrls[$key] = $url;
                }
            }

            $result[md5($url)] = [
                'key'     => $key,
                "error"   => $success ? 0 : 1,
                "message" => $success ? "" : "request $url error: $error",
                "url"     => $url,
                "file"    => $filenames[$key],
            ];
        }
        curl_multi_close($mh);

        if ($errorUrls && $retry > 0) {
            $result = array_merge($result, static::downloadFiles($errorUrls, $filenames, $retry - 1, $curlOptions));
        }

        return array_values($result);
    }


    /**
     * 获取base64json
     * @param $params
     * @return string
     */
    public static function base64Json($params)
    {
        return base64_encode(json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }


    /**
     * 计算匹配进程数
     * @param string $match
     * @return integer
     */
    public static function countProcesses($match)
    {
        $count = 0;
        foreach (explode("\n", Helper::execCommand("ps aux | grep '$match'")) as $string) {
            if (strpos($string, 'grep') === false && strpos($string, 'bin/bash') === false && strpos($string, 'bin/sh') === false) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * 使用curl请求数据
     * @param string $url
     * @param array $post
     * @param string $method
     * @return array|string
     */
    public static function curl_request($url, $post = [], $method = 'GET', $headerArr = [], $is_json_decode = true)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOBODY, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        if ($headerArr) curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArr);

        //需要post数据
        if ($post) {
            if (is_array($post)) {
                $post = http_build_query($post);
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }

        $data = curl_exec($ch);
        //var_dump($data, json_decode($data, true));exit;

        $errno = curl_errno($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($errno) {
            return $is_json_decode ? [
                'error' => 1,
                'message' => "CURL请求发生错误：$error"
            ] : "CURL请求发生错误：$error";
        }

        return $is_json_decode ? json_decode($data, true) : $data;
    }

    /**
     * 读取内容
     * @param $fileName
     * @param $headerMaps
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public static function readFile($fileName, $headerMaps, $unLink = true)
    {
        ini_set('memory_limit', '512M');
        /*$simpleCache = new Cache();
        $simpleCache->baseCache = 'tmpCache';
        //$simpleCache->defaultTtl = 1800;

        \PhpOffice\PhpSpreadsheet\Settings::setCache($simpleCache);*/

        $spreadsheet = IOFactory::load($fileName);

        $data = $headers = [];
        $originalData = $originalHeaders = [];
        foreach ($spreadsheet->setActiveSheetIndex(0)->toArray(null, false, true, false) ?: [] as $i => $row) {
            $tmpData = $tmpOriginal = [];
            foreach ($row as $key => $v) {
                $tmpOriginal[$key] = $v;
                // 需要先读取一行头信息
                if ($i == 0) {
                    $originalHeaders[$key] = $v;
                    if (isset($headerMaps[$v])) {
                        $headers[$key] = $v;
                    }
                } elseif (isset($headers[$key])) {
                    $header = $headerMaps[$headers[$key]];
                    $tmpData[$header] = $v;
                }
            }
            $data[] = $tmpData;
            $originalData[] = $tmpOriginal;
        }
        //是否要删除文件
        if ($unLink) {
            unlink($fileName);
        }

        return ['data' => $data, 'original' => ['headers' => $originalHeaders, 'data' => $originalData]];
    }

    /**
     * 上传文件
     * @param $file
     * @return string
     */
    public static function uploadFile($file)
    {
        if ($file['name']) {
            $filename = $file['name'];
            $ext = strtolower(substr($filename, strrpos($filename, '.') + 1)); // 获取后缀

            $destination = sys_get_temp_dir() . '/' . md5($file['tmp_name']) . '.' . $ext;
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                return $destination;
            }
        }
        return '';
    }

    /**
     * 获取文件信息
     * @author jiajin 2020-01-17
     */
    public static function getFileData($headers)
    {
        $uploadData = static::uploadAndReadFile($_FILES['file'], array_flip($headers));
        if ($uploadData['error']) {
            return static::returnJson($uploadData['message'], 1);
        }

        $data = [];
        foreach ($uploadData['data'] as $i => $row) {
            $row = array_filter(array_map('trim', $row));
            if ($i==0 || $row) {
                $data[] = $row;
            }
        }
        $uploadData['data'] = $data;
        $data = array_values(array_filter($data));

        return [$uploadData, $data];
    }

    /**
     * 读取文件内容
     * @param $file
     * @param $headMaps
     * @param $unLink //是否删除临时文件
     * @return array
     */
    public static function uploadAndReadFile($file, $headMaps, $unLink=true)
    {
        if ($file['name']) {
            $filename = $file['name'];
            $ext = strtolower(substr($filename, strrpos($filename, '.') + 1)); // 获取后缀

            $destination = sys_get_temp_dir() . '/' . md5($file['tmp_name']) . '.' . $ext;
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $fileName = $destination;
            }
        }

        if (!$fileName) {
            return ['error' => 1, 'message' => Yii::t('ec', 'Please upload a file')];
        }

        try {
            $readData = static::readFile($fileName, $headMaps, $unLink);
            return ['data' => $readData['data'], 'original' => $readData['original'], 'fileName' => str_replace('.' . $ext, '', $file['name']), 'error' => 0];
        } catch (\Exception $e) {
            Yii::error((string)$e);
            return ['error' => 1, 'message' => Yii::t('ec', 'Read file error, please try again later')];
        }
    }

    // 上传文件后的提示信息
    public static function getUploadHandleMsg($fileData, $errorMsgs)
    {
        $totalCount = count($fileData['data']);
        $ignoreKeysCount = count($errorMsgs);

        // 需要提供下载错误数据的方法
        if ($errorMsgs && $totalCount != $ignoreKeysCount) {
            $original = $fileData['original'];
            $headers = $original['headers'];
            $headers[] = '出错原因';

            $data = [];
            foreach ($errorMsgs as $row => $msg) {
                $data[$row] = $original['data'][$row - 1] ?: [];
                $data[$row][] = $msg;
            }

            $cacheKey = substr(md5('ec:upload:ignore:userId:' . Yii::$app->getUser()->getId() . "time:" . time()), 8, 16);
            Yii::$app->getCache()->set($cacheKey, ['fileName' => $fileData['fileName'] . '_失败数据', 'headers' => $headers, 'data' => $data], 1800); // 缓存半个钟？
        }

        $message = $errorMsgs ?
            ($totalCount == $ignoreKeysCount ?
                Yii::t('ec', 'Processing failed, all data is filtered out, please re-check the data before uploading!') :
                Yii::t('ec', 'Successfully processed') . ($totalCount - $ignoreKeysCount - 1) . Yii::t('ec', 'data') .
                ", " . Yii::t('ec', 'failed') . $ignoreKeysCount . Yii::t('ec', 'data') .
                ", <a href='/site/export?key={$cacheKey}'>" . Yii::t('ec', 'Click here to download the failed data') . "</a>")
            : Yii::t('ec', 'All processing is successful, total processing') . ($totalCount - 1) . Yii::t('ec', 'data');

        return $message;
    }

    //字符串解密加密
    public static function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0)
    {
        $operation = strtoupper($operation);
        $ckey_length = 4;

        // 随机密钥长度 取值 0-32;
        // 加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。
        // 取值越大，密文变动规律越大，密文变化 = 16 的 $ckey_length 次方
        // 当此值为 0 时，则不产生随机密钥

        $key = md5($key ? $key : 'lpp$#@!');
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

        $cryptkey = $keya . md5($keya . $keyc);
        $key_length = strlen($cryptkey);

        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
        $string_length = strlen($string);

        $result = '';
        $box = range(0, 255);

        $rndkey = [];
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }

        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }

        if ($operation == 'DECODE') {
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            return $keyc . str_replace('=', '', base64_encode($result));
        }
    }

    /**
     * 获取顶级域名
     */
    public static function topdomain($domain)
    {
        static $domainExt = [
            'com.cn', 'org.cn', 'net.cn', 'com.tw', 'idv.tw', 'com.hk', 'cn.com', 'cn.im', 'com.jp',
            'com.ag', 'net.ag', 'org.ag', 'com.br', 'net.br', 'com.bz', 'net.bz', 'com.co', 'net.co', 'nom.co',
            'com.es', 'net.es', 'nom.es', 'co.in', 'firm.in', 'gen.in', 'ind.in', 'net.in', 'org.in', 'com.mx',
            'co.nz', 'net.nz', 'org.nz', 'co.uk', 'me.uk', 'org.uk', 'xn--fiqs8s'
        ];

        if (empty($domain) || preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $domain)) {
            return $domain;
        }

        $domain = strtolower($domain);
        $arr = explode('.', $domain);
        $len = count($arr);
        $ext = $arr[$len - 2] . '.' . $arr[$len - 1];
        return in_array($ext, $domainExt) ? $arr[$len - 3] . '.' . $ext : $ext;
    }

    /**
     * 根据比重随机获取一个值 -- 通过填充数组来随机抽取
     *
     * key => rate 每个值对应的权重，数字越大代表被抽中的几率越高
     *
     * @param $data [key => rate]
     * @param int $decimal 精度，3位，代表千分之几
     * @return mixed
     */
    public static function randByRate($data, $decimal = 3)
    {
        $base = pow(10, $decimal);
        // 重新计算比重，按百分比来算 按照概率填充key，概率大的占有数量多，被抽中的概率更大
        $sum = array_sum($data);
        $ableKeys = [];
        foreach ($data as $k => $rate) {
            $end = round($rate / $sum * $base);
            if (!$end || $end < 1) continue;

            foreach (range(1, $end) as $_v) {
                $ableKeys[] = $k;
            }
        }

        return $ableKeys[mt_rand(0, count($ableKeys) - 1)];
    }

    /**
     * 根据比重随机获取一个值 -- 通过比对百分率大小来抽取
     *
     * key => rate 每个值对应的权重，数字越大代表被抽中的几率越高
     *
     * @param $data [key => rate, ]
     * @param int $decimal 精度，3位，代表千分之几
     * @return int|string|bool
     */
    public static function randByRate2($data, $decimal = 3)
    {
        $base = pow(10, $decimal);
        // 根据权重计算比例，必须是百分百
        $sum = array_sum($data);
        foreach ($data as &$rate) {
            $rate = $rate / $sum;
        }
        // 随机获取一个百分比 (0, 1]
        $randRatio = mt_rand(1, $base) / $base;

        //随机数<=(概率+前面所有概率)*总数
        $before = 0;
        foreach ($data as $k => $v) {
            if ($randRatio <= $v + $before) {
                return $k;
            }
            $before += $v;
        }

        return false;
    }

    /**
     * 本程序已锁的key，避免错误解锁
     * @var array
     */
    private static $lockKeys = [];

    /**
     * 利用redis加锁
     * @param $key
     * @param int $times
     * @param int $expire
     * @return bool
     */
    public static function lockKey($key, $times = 100, $expire = 300)
    {
        if (isset(static::$lockKeys[$key]))
            return true;

        /* @var $redis \Redis */
        $redis = Yii::$app->getCache()->getRedis();
        $value = $redis->incr($key); // 加一
        if ($value == 1) {
            static::$lockKeys[$key] = 1;
            $redis->expire($key, $expire);
            return true;
        } else {
            $redis->incrBy($key, -1);
            if ($times - 1 > 0) {
                usleep(20000); // 0.02s
                return static::lockKey($key, $times - 1, $expire);
            } else { // 后面删除
                Yii::error($key . ' try times is over', 'lockKey');
            }
        }
        return false;
    }

    /**
     * 利用redis解锁
     * @param $key
     * @return bool
     */
    public static function unlockKey($key)
    {
        if (!isset(static::$lockKeys[$key]))
            return false;

        /* @var $redis \Redis */
        $redis = Yii::$app->getCache()->getRedis();
        $redis->incrBy($key, -1);
        unset(static::$lockKeys[$key]);
        return true;
    }

    /**
     * 根据公式计算结果
     * @param $exp
     * @param $data
     * @return float|int
     */
    public static function computeExpression($exp, $data)
    {
        //将格式化后的公式缓存起来
        static $maps = [];
        if (!isset($maps[$exp])) {
            $pattern = '/[a-z][a-z_\d]+/i';
            $nexp = preg_replace($pattern, '$${0}', $exp);
            preg_match_all($pattern, $exp, $keys);

            //保存当前格式化后的表达式，还有表达式内的变量
            $maps[$exp] = [
                'exp' => $nexp,
                'keys' => $keys[0]
            ];
        }

        $keys = $maps[$exp]['keys'];
        foreach ($keys as $k) {
            $$k = $data[$k];
        }

        $val = @eval("return {$maps[$exp]['exp']};");
        return is_nan($val) || is_infinite($val) ? 0 : floatval($val);
    }

    /**
     * 检测输入字符中是否包含汉字 【针对utf-8字符集】
     * @param $input
     * @return false|int
     */
    public static function hasChinese($input)
    {
        return preg_match('/[\x{4e00}-\x{9fa5}]{1,}/u', $input);
    }

    /**
     * 从h5代码里匹配出所有图片
     *
     * @author pp 2019-08-16
     *
     * @param $html
     * @return array
     */
    public static function getImagesFromHtml($html)
    {
        $images = [];
        if (preg_match_all("/(<img .*?src=\")(.*?)(\".*?>)/is", $html, $matches)) {
            return $matches[2];
        }
        return $images;
    }

    /**
     * 打包文件列表，并导出
     *
     * fileList => [['导出的文件名' => '当前文件路径']]
     *
     * @author pp 2019-08-16
     *
     * @param $fileList [] 待压缩文件列表
     * @param $fileName string 压缩后文件名，不带后缀
     * @param $unlink boolean 是否需要把待压缩文件删除
     */
    public static function outputZip($fileList, $fileName, $unlink = true)
    {
        $fileList = is_array($fileList) ? $fileList : [];
        if (empty($fileList)) exit("it's empty.");

        $tmpName = tempnam(sys_get_temp_dir(), 'outputZip');
        $zipFile = $tmpName . '.zip';
        $zip = new \ZipArchive();
        $zip->open($zipFile, \ZipArchive::CREATE);

        $isSingle = count($fileList) == 1;
        foreach ($fileList as $name => $file) {
            $exportName = $fileName . '/' . (is_numeric($name) ? $file : $name);
            // $file资源路径，$exportName导出的文件名
            $zip->addFile($file, $exportName);
        }
        $zip->close();
        // 把本地文件删除
        if ($unlink) {
            foreach ($fileList as $name => $file) {
                unlink($file);
            }
        }

        header("Content-type: application/zip");
        header('Content-Disposition: attachment;filename="' . $fileName . '.zip"');
        header('Expires:0');
        header('Pragma:public');
        header('Cache-Control: max-age=0');
        header('Content-Length: ' . filesize($zipFile));
        readfile($zipFile);
        unlink($zipFile);
    }


    /**
     * 导入的二维数组，是否为空行，导入的完全空行数据需要直接跳过，无需处理
     *
     * @author pp 2019-09-02
     *
     * @param $row
     * @return bool
     */
    public static function uploadEmpty($row)
    {
        if (!is_array($row)) return false;

        if (empty($row)) return true;

        foreach ($row as $value) {
            if (isset($value)) return false;
        }

        return true;
    }

    public static function returnJson($message = '', $error = 0, $data = [])
    {
        Yii::$app->getResponse()->format = Response::FORMAT_JSON;
        return array_merge(['message' => Yii::t('ec', $message), 'error' => $error], $data);
    }


    /**
     * 读取文件内容（多个sheet）
     * @param $file
     * @param $headMaps
     * @return array
     * author taihao 2019-09-24
     */
    public static function uploadAndReadFileSheets($file, $headMaps)
    {
        if ($file['name']) {
            $filename = $file['name'];
            $ext = strtolower(substr($filename, strrpos($filename, '.') + 1)); // 获取后缀

            $destination = sys_get_temp_dir() . '/' . md5($file['tmp_name']) . '.' . $ext;
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $fileName = $destination;
            }
        }

        if (!$fileName) {
            return ['error' => 1, 'message' => Yii::t('ec', 'Please upload a file')];
        }

        try {
            $readData = static::readFileSheets($fileName, $headMaps);
            return ['data' => $readData['data'], 'sheetNames' => $readData['sheetNames'], 'original' => $readData['original'], 'fileName' => str_replace('.' . $ext, '', $file['name']), 'error' => 0];
        } catch (\Exception $e) {
            Yii::error((string)$e);
            return ['error' => 1, 'message' => Yii::t('ec', 'Read file error, please try again later')];
        }
    }

    /**
     * 读取内容
     * @param $fileName
     * @param $headerMaps
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public static function readFileSheets($fileName, $headerMaps, $unLink = true)
    {
        ini_set('memory_limit', '512M');
        /*$simpleCache = new Cache();
        $simpleCache->baseCache = 'tmpCache';
        //$simpleCache->defaultTtl = 1800;

        \PhpOffice\PhpSpreadsheet\Settings::setCache($simpleCache);*/

        $spreadsheet = IOFactory::load($fileName);
        $sheetCount = $spreadsheet->getSheetNames();

        $data = $headers = [];
        $originalData = $originalHeaders = [];
        foreach ($sheetCount as $index => $sheet) {
            foreach ($spreadsheet->setActiveSheetIndex($index)->toArray(null, false, true, false) ?: [] as $i => $row) {
                $tmpData = $tmpOriginal = [];
                foreach ($row as $key => $v) {
                    $tmpOriginal[$key] = $v;
                    // 需要先读取一行头信息
                    if ($i == 0) {
                        $originalHeaders[$index][$key] = $v;
                        if (isset($headerMaps[$v])) {
                            $headers[$index][$key] = $v;
                        }
                    } elseif (isset($headers[$index][$key])) {
                        $header = $headerMaps[$headers[$index][$key]];
                        $tmpData[$header] = $v;
                    }
                }

                $data[$index][] = $tmpData;
                $originalData[$index] = $tmpOriginal;
            }
        }

        //是否要删除文件
        if ($unLink) {
            unlink($fileName);
        }

        //处理空格
        foreach ($sheetCount as &$item) {
            $item = trim($item);
        }

        return ['data' => $data, 'sheetNames' => $sheetCount, 'original' => ['headers' => $originalHeaders, 'data' => $originalData]];
    }

    /**
     * PDF有些显示有问题，先简单替换一下，方便正常展示
     *
     * @author pp 2019-10-14
     *
     * @param $string
     * @return mixed
     */
    public static function formatForPDF($string)
    {
        $search = ['【', '】', '（', '）'];
        $replacement = ['[', ']', '[', ']'];

        return $string ? str_replace($search, $replacement, $string) : $string;
    }

    /**
     * 返回指定时区零点时间戳
     * @param $timestamp
     * @param $timezone
     * @return int
     */
    public static function zeroTimestamp($timestamp, $timezone = 'Asia/Shanghai')
    {
        return (new \DateTime())->setTimezone(new \DateTimeZone($timezone))
            ->setTimestamp($timestamp)->setTime(0, 0)->getTimestamp();
    }

    /**
     * 获取cdn资源仅供展示使用
     * @param string $url
     * @return string
     */
    public static function getCDNUrl($url)
    {
        // 需要通过token访问
        if (strpos($url, 'file-ec.youcdn.net') !== false) {
            $etime = time() + 300;
            $urlInfo = parse_url($url);
            $sign = md5(Yii::$app->params['upyun']['cdnToken'] . '&' . $etime . '&' . $urlInfo['path']);
            return $urlInfo['scheme'] . ($urlInfo['scheme'] ? '://' : '//') . $urlInfo['host'] . $urlInfo['path'] .
                '?' . $urlInfo['query'] . (isset($urlInfo['query']) ? '&' : '') . '_upt=' . substr($sign, 12, 8) . $etime;
        }

        return $url; //str_replace('//img-ec.youcdn.net', '//cn-img-ec.youcdn.net', $url);
    }

    /**
     * 加密的url保存之前需要清掉参数
     *
     * @param $url
     * @return string
     */
    public static function cleanUrlParam($url)
    {
        $urlInfo = parse_url($url);
        $tmpStr = $urlInfo['scheme'] . ($urlInfo['scheme'] ? '://' : '//') . $urlInfo['host'] . $urlInfo['path'];

        return $tmpStr;
    }

    /**
     * 格式化用','间隔的字段值为相应中文值
     * @author jiajin
     *
     * @param string $values 字段值
     * @param $list
     * @param $name string 标题
     * @return string
     */
    public static function formatMultipleValues($values, $list, $name = '', $operator = ',')
    {
        if ($values == 'all') {
            return "所有{$name}";
        }
        $parts = [];
        foreach ($values ? explode(',', trim($values, ',')) : [] as $id) {
            $list[$id] && $parts[] = $list[$id];
        }
        return implode($operator . ' ', $parts) ?: '';
    }

    /**
     * 验证时间范围是否有效
     * @author jiajin 2019-11-04
     *
     * @param $dateStart
     * @param $dateEnd
     * @return string
     */
    public static function verifyTimeRange($dateStart, $dateEnd)
    {
        $timeStart = strtotime($dateStart);
        $timeEnd = strtotime($dateEnd);
        if (!ctype_digit($timeStart) || !ctype_digit($timeEnd) || $timeEnd <= $timeStart) {
            return '生效时间段无效';
        }
        return '';
    }

    /**
     * 验证IP段是否合法
     * @author jiajin 2019-11-04
     */
    public static function verifyIP($stratIP, $endIP)
    {
        if (!filter_var($stratIP, FILTER_VALIDATE_IP) || !filter_var($endIP, FILTER_VALIDATE_IP)) {
            return 'IP段不合法';
        }
        return '';
    }

    /**
     * 用,隔开的字符串，trim成有效值的数组
     * @author jiajin 2020-01-17
     *
     * @param string $multiString
     * @return array
     */
    public static function getTrimArray($multiString) : array
    {
        return array_filter(static::explode($multiString, ','));
    }

    /**
     * 类似fputcsv，返回csv字符串
     *
     * @edit pp 2019-10-31 需要处理一下分行问题，替换成空格，避免出现多行
     *
     * @author owen
     *
     * @param array $fields
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escape_char
     * @return bool|string
     */
    public static function strPutCsv(array $fields, $delimiter = ",", $enclosure = '"', $escape_char = "\\")
    {
        $fp = fopen('php://memory', 'r+');
        foreach ($fields as &$value) {
            $value = str_replace(["\r\n", "\n", PHP_EOL], ' ', $value);
        }
        fputcsv($fp, $fields, $delimiter, $enclosure, $escape_char);
        rewind($fp);
        $string = fgets($fp);
        fclose($fp);
        return $string;
    }

    /**
     * 将秒转为x天x时x分格式
     * author: august 2019/10/26
     * @param $seconds int
     * @return string
     */
    public static function second2Minutes($seconds)
    {
        if (!$seconds) return 0;

        $return = '';
        $times = [
            '天' => 86400,
            '时' => 3600,
            '分' => 60
        ];
        foreach ($times as $name => $_interval) {
            if ($seconds > $_interval) {
                $return .= ($name == '分' ? ceil($seconds / $_interval) : floor($seconds / $_interval)) . $name;
            } elseif ($name == '分') {
                $return .= ceil($seconds / $_interval) . $name;
            }
            $seconds = $seconds % $_interval;
        }
        return $return;
    }

    /**
     * php命令行运行脚本基础部分
     *
     * author: august 2019/11/8
     * @return string '/usr/bin/php /path/to/yii '
     */
    public static function getPhpCommandPrefix()
    {
        return Yii::$app->params['phpBin'] . ' ' . Yii::$app->getBasePath() . '/../yii ';
    }

    /**
     * 将回车换行输入的多行内容，分割为数组
     *
     * author: august 2019/11/21
     * @param $input
     * @return array
     */
    public static function splitMultiLinesIntoArray($input) : array
    {
        return static::explode(str_replace("\r", '', trim($input)), "\n", 'trim');
    }

    /**
     * 获取字符串的长度。如果mb_strlen()可用，则使用mb_strlen()返回字符串长度
     *
     * author: august 2019/11/29
     * @param $string
     * @param $encoding
     * @return int
     */
    public static function getStrLength($string, $encoding = 'UTF-8')
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($string, $encoding);
        }
        return strlen($string);
    }

    /**
     * 格式化时间 xx天xx小时xx分钟
     *
     * @author pp 2019-12-20
     *
     * @param $seconds
     * @return string
     */
    public static function formatDayHourMinutes($seconds)
    {
        $result = '';
        if (!$seconds) return '';

        $times = [
            86400 => '天',
            3600 => '小时',
            60 => '分钟',
        ];

        $mod = 0;
        foreach ($times as $key => $val) {
            if ($seconds < $key) continue;

            $result .= floor($mod ? $seconds % $mod / $key : $seconds / $key) . $val;
            $mod = $key;
        }

        return $result ?: '<1分钟';
    }

    /**
     * 检查字符串中是否存在某字符串
     *
     * author: august 2019/12/23
     *
     * @param $haystack 要查找的字符串
     * @param $needle 子串
     * @return bool 存在，true, 否则为false
     */
    public static function hasString($haystack, $needle)
    {
        return strpos($haystack, $needle) !== false;
    }

    /**
     * 取数组指定的keys，md5对应的值，返回一个字符串
     *
     * author: august 2020/1/17
     * @param array $data
     * @param array $keys
     * @return string
     */
    public static function md5ArrayByKeys(array $data, $keys=[])
    {
        if (!empty($keys)) {
            $data = array_intersect_key($data, array_flip($keys));
        }
        ksort($data);
        return md5(join('-', $data));
    }

    /**
     * 获取当前访问权限
     *
     * @author pp 2020-02-24
     *
     * @param $user
     * @param $controller \yii\web\Controller
     * @param null $permissionName
     * @return bool
     */
    public static function checkAccess($user, $controller, $permissionName=null)
    {
        if ($user['status']!=AdminUser::STATUS_NORMAL) {
            Yii::$app->getUser()->logout();
            return false;
        }
        if ($permissionName===null) {
            if (in_array($controller->module->id, ['widget']))
                return true;
            if (in_array($controller->id, ['site','auth','ueditor']))
                return true;
            $permissionName = $controller->getRoute();
        }
        // 超级管理员拥有所有权限
        if ($user['is_admin'] && $user['company_id']==0)
            return true;
        return Yii::$app->getUser()->can($permissionName);
    }

    /**
     * 把拼接的字符串分割成数组
     *
     * @author pp 2020-03-16
     *
     * @param $value
     * @param string $delimiter
     * @param string $mapCallback
     * @return array
     */
    public static function explode($value, $delimiter = ',', $mapCallback = 'intval') : array
    {
        $value = trim($value, $delimiter);
        return strlen($value) ? array_map($mapCallback, explode($delimiter, $value)) : [];
    }

    /**
     *
     * 将字符串按长度切割成数组
     *
     * @author rodman
     * @param $string
     * @param int $length
     * @return array|false|string[]
     */
    public static function strSplitUnicode($string, $length = 0)
    {
        if ($length > 0) {
            $ret = [];
            $len = mb_strlen($string, "UTF-8");
            for ($i = 0; $i < $len; $i += $length) {
                $ret[] = mb_substr($string, $i, $length, "UTF-8");
            }

            return $ret;
        }

        return preg_split("//u", $string, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * 渲染管理导航
     * @author jiajin 2020-03-25
     */
    public static function renderNavs($auths)
    {
        $html = '';
        foreach ($auths as $row) {
            $html .= '<a href="#/'.$row['route'].'" class="btn btn-default '.($row['is_active']?'blue':'').'">'.$row['name'].'</a>';
        }
        return $html;
    }

    /**
    * @author archie 2020.03.28 同步原始数据，获取处理完的数据
     * @param  $orginalData  // 原始数据
     * @param $headerMaps //表头，作为返回数组的key
     */
    public static function getHaveKeyData($orginalData, $headerMaps)
    {
        $data = [];
        $headers = [];
        $j=0;
        foreach($orginalData as $i=>$row) {
            $j++;
            $tmpData = [];
            foreach($row as $key => $v) {
                if($i == 0) {
                    if(isset($headerMaps[$v])){
                        $headers[$key] = $v;
                    }
                }elseif (isset($headers[$key])) {
                    $tmpKey = $headerMaps[$headers[$key]];
                    $tmpData[$tmpKey] = $v;
                }
            }

            if($tmpData) {
                $tmpData['upload_key'] = $j;
                $data[] = $tmpData;
            }
        }

        return $data;

    }


}