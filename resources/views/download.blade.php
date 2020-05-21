<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>微游-下载</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <link href="https://res.cdn.openinstall.io/ipa_icon/uv7atq/2046501096002034779-1569211876792.png"
          rel="apple-touch-icon-precomposed">
    <!-- 以下为openinstall集成代码，建议在html文档中尽量靠前放置，加快初始化过程 -->
    <!-- 强烈建议直接引用下面的cdn加速链接，以得到最及时的更新，我们将持续跟踪各种主流浏览器的变化，提供最好的服务；不推荐将此js文件下载到自己的服务器-->
    <link rel="preconnect" href="https://openinstall.io" crossorigin="use-credentials">
    <script type="text/javascript" charset="utf-8" src="https://res.cdn.openinstall.io/openinstall.js"></script>
    <script type="text/javascript">
        //openinstall初始化时将与openinstall服务器交互，应尽可能早的调用
        /*web页面向app传递的json数据(json string/js Object)，应用被拉起或是首次安装时，通过相应的android/ios api可以获取此数据*/
        var data = OpenInstall.parseUrlParams();//openinstall.js中提供的工具函数，解析url中的所有查询参数
        new OpenInstall({
            /*appKey必选参数，openinstall平台为每个应用分配的ID*/
            appKey: "uv7atq",
            /*可选参数，自定义android平台的apk下载文件名，只有apk在openinstall托管时才有效；个别andriod浏览器下载时，中文文件名显示乱码，请慎用中文文件名！*/
            //apkFileName : 'com.runner.taohuayuan.pkg-v1.99.apk',
            /*可选参数，是否优先考虑拉起app，以牺牲下载体验为代价*/
            //preferWakeup:true,
            /*自定义遮罩的html*/
            //mask:function(){
            //  return "<div id='openinstall_shadow' style='position:fixed;left:0;top:0;background:rgba(0,255,0,0.5);filter:alpha(opacity=50);width:100%;height:100%;z-index:10000;'></div>"
            //},
            /*openinstall初始化完成的回调函数，可选*/
            onready: function () {
                var m = this, button = document.getElementById("downloadButton");
                button.style.visibility = "visible";

                /*在app已安装的情况尝试拉起app*/
                m.schemeWakeup();
                /*用户点击某个按钮时(假定按钮id为downloadButton)，安装app*/
                button.onclick = function () {
                    m.wakeupOrInstall();
                    return false;
                }
            }
        }, data);

    </script>

    <link rel="stylesheet" href="https://res.cdn.openinstall.io/api_res/css/style.css">
    <style type="text/css">
        * {
            -webkit-tap-highlight-color: rgba(0, 0, 0, 0);
        }
    </style>
</head>
<body>
<div class="content channel-page">
    <div class="page-index">
{{--        <div style="padding-top: 20px;padding-left:10px">--}}
{{--            <p style="line-height:1.3rem;font-size:1.6rem;">--}}
{{--                javascript api测试页-<span style="color: green">wy</span>--}}
{{--            </p>--}}
{{--        </div>--}}
        <div class="udid-content channel-content">
            <img id="prizeInfo" style="width: 72px; height: 72px" alt=""
                 src="http://oss.hjhp.cn/live/7e3f14ab-714a-fed8-d6d2-18fdff687241">
            <p style="font-size:1.6rem;margin:2px auto;">wy</p>
        </div>
        <div style="padding-top: 1em">
        </div>
        <div class="channel-title">
            <p>微游交友平台，欢迎下载！</p>
        </div>
        <div class="content-block">
            <p style="text-align: center">
                <a id="downloadButton" href="javascript:;" style="visibility: visible;"
                   class="button button-big udid-bt channel-bt">立即下载</a>
            </p>
        </div>

    </div>
</div>
</body>
<script type="text/javascript">
    window.onload = function(){
        if(isWeiXin()){
            document.getElementById("prizeInfo").src = "{{ url('/images/wechat.png') }}";
        }
    }
    function isWeiXin(){
        var u = navigator.userAgent;
        if(u.indexOf('MicroMessenger') != -1 ){
            return true;
        }else{
            return false;
        }
    }
</script>
</html>
