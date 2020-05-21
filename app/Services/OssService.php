<?php

namespace App\Service;

use App\Facades\CommonFacade;
use App\Traits\ResultTrait;
use App\Utils\Result;
use DateTime;
use OSS\Core\OssException;
use OSS\OssClient;

/**
 * 文件服务
 * @package App\Http\Service
 */
class OssService
{
    use ResultTrait;
    private $OssClient;

    public function __construct()
    {
        $accessKeyId = env('OSS_ACCESS_KEY_ID');
        $accessKeySecret = env('OSS_ACCESS_KEY_SECRET');
        $endpoint = env('OSS_URL');
        try {
            $this->OssClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint, true);
            $this->OssClient->setTimeout(3600);
            $this->OssClient->setConnectTimeout(10);
        } catch (OssException $e) {
            $this->exception($e);
            print $e->getMessage();
        }
    }

    public function getBucket()
    {
        return env('OSS_BUCKET');
    }

    public function putObject($file)
    {
        try {

            $fileName = $file->getClientOriginalName();
            $extend = strtolower(substr(strrchr($fileName, "."), 1));

            $object = env('OSS_DIRECTORY') . '/' . CommonFacade::uuid();
            $options = array(
                OssClient::OSS_HEADERS => array(
                    'Content-Type' => $file->getMimeType(),
                    'fileName' => $object,
                    'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                ));
            $content = file_get_contents($file);
            $res = $this->OssClient->putObject($this->getBucket(), $object, $content, $options);
            $res['src'] = $res['info']['url'];
            return $this->succeed($res['src'], '上传成功');
        } catch (OssException $e) {
            return $this->exception($e);
        }
    }

    /**
     * 转存远程图片
     * @param $url
     * @return array|string
     */
    public function putUrl($url)
    {
        try {
            $OssClient = new OssService();
            $oss = $OssClient->OssClient;
            $object = env('OSS_DIRECTORY') . '/' . date('Y/m/d') . '/' . CommonFacade::uuid();
            $content = file_get_contents($url, true);
            $res = $oss->putObject($this->getBucket(), $object, $content);
            if (isset($res['info'])) {
                $info = $res['info'];
                return ['status' => true, 'code' => 0, 'src' => $info['url'], 'data' => $object];
            }
            return $this->failure(1, '上传成功', $res);
        } catch (OssException $e) {
            return $e->getMessage();
        }
    }

    public function putImage($file)
    {
        try {

            $fileName = $file->getClientOriginalName();
            $extend = strtolower(substr(strrchr($fileName, "."), 1));

            $object = env('OSS_DIRECTORY') . '/' . CommonFacade::uuid();
            $options = array(
                OssClient::OSS_HEADERS => array(
                    'Content-Type' => $file->getMimeType(),
                    'fileName' => $object,
                    'Content-Disposition' => 'inline"',
                ));
            $content = file_get_contents($file);
            $res = $this->OssClient->putObject($this->getBucket(), $object, $content, $options);
            $res['src'] = $res['info']['url'];
            return $this->succeed($res['src'], '上传成功');
        } catch (OssException $e) {
            return $this->exception($e);
        }
    }

    public function putBytes($uploadData, $fileName = '')
    {
        try {
            $object = $fileName ?: (env('OSS_DIRECTORY') . '/' . CommonFacade::uuid());

            $res = $this->OssClient->putObject($this->getBucket(), $object, $uploadData);
            $res['src'] = $res['info']['url'];
            return $this->succeed($res, '上传成功');
        } catch (OssException $e) {
            return $e->getMessage();
        }
    }

    public function putFile($file)
    {
        try {
            $object = CommonFacade::uuid();

//             $content = file_get_contents($file);
            $res = $this->OssClient->uploadFile($this->getBucket(), $object, $file);
            return $this->succeed($res, '上传成功');
        } catch (OssException $e) {
            return $this->exception($e);
        }
    }

}
