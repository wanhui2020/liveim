<?php

namespace App\Http\Controllers\Common;

use App\Facades\OssFacade;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

/**
 * 图片上传
 * Class OssController
 * @package App\Http\Controllers\Common
 */
class OssController extends Controller
{
    public function putObject(Request $request)
    {
        if ($request->isMethod('POST')) {
            $file = $request->file('file');

            $resp = OssFacade::putImage($file);
            return $resp;
        }
        return view('common.oss.put');
    }
}
