<?php

return [
    'alipay' => [
        // 支付宝分配的 APPID
        'app_id' => env('ALI_APP_ID', '2019112969503657'),

        // 支付宝异步通知地址
        'notify_url' => 'http://120.24.250.152/common/alipaycallback',

        // 支付成功后同步通知地址
        'return_url' => 'http://120.24.250.152/common/alipaycallback',

        // 阿里公共密钥，验证签名时使用
        'ali_public_key' => env('ALI_PUBLIC_KEY', 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAoH4oz/1qdWOlPxRvEtMNfVMEsCsyIKOqAtAoDKAJ9Ufb5EGfYZgUoz//4EP4awKbzuXtOYjc1/Wzw4GUUbkWqNCe0T7KqBZSnTfc+ZG8Mjsm9dJXfTHLa60AQNFYFBtmQ4G5gIgr70iowMFfjMvX4w7j/UbwRDKtZ49y2qOEiZ9yeceS6odf5Gcmm8g4IEhpacPBVZKp4RdR/Fp6EVJ6vb05yPAjB8JtJ9y8mFzUF8KCYoZk8RiBHQvvoc9o/GmytqepsB1SP+49YluEt3AsNaho6SZyidYl11AqoMgN7n4UQEZ/RV6AHHxPtQLaBUDj+KRHR/lsGxzNnSCAu6C44wIDAQAB'),

        // 自己的私钥，签名时使用
        'private_key' => env('ALI_PRIVATE_KEY', 'MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQC2oX6Rj/Q5lLjIQQ0LLtWXdSpUilI8Bfw5PnSMnJC2WWC1aktK/3EVTyl+U2xNk5wzY6GM8/PBrOaDErLPyM7184Zvuw/gqgbnFO0rhr02smMChitxXMjQD9VuNisWXn7jAqSpKNzHN+8hgWXWGzLk9tqYqHp3OtvGCSaS7oPE2q68rUsu9SwBF9ELr3tC1AFmDX5EAVKratpzCYBZSMIdlxnhK2QiiQEYuXiyX/8q7NDlqvUuXYGNlZ4njyCsnkpjSR23ue2FzBSU5e/yTXhWUZcEHMXJgLpLcoqyVWAe2EkOI3BtCcORa7NEw+J9nUiY1Oi9akLJg/pVzWhDbfavAgMBAAECggEBAIjA8PeqpaQgWyWIoG3JSiM5ltLrKJzNlop+2+MWsb35u1LdHzgvgswEFPwFF8oXSBWq+yOC4PobsBhh/gddRKwjCH3Nwxvl9Xk/0ECRiknU1hLjyNAAPFagJJ98bFM5uyXdqtMISBndJnEeZdUnVLripdk9/0YciskFvR60/3J9USsvLLzjA1ld5EiX9hF09oeT/h7EOaZz0nGJu1P049nHSf62PhyGaj2T9F2mNSqa0ux0Mmvv8AbN4m5Q4lj57zGqrVWdYIUYgwzgx0MpQNxTC9k4UpDJNWc1PXj1u01adGM6IcrliAqueLj5T/pnb+W1KT51hWk6QcUjZ8PHKzECgYEA6Vm3tmwMb9p3Tzlfxxo+31Vzto5vPWo6F31r/CV0VqysHK2KmeVxAyqOKyYsSDFj8/CQOp4EBkIfzrbcZ4MAt8bbqkysOEG2RHCRtY0AUcTi1e+pDTfdD1VdiFNjUOhdxjv8tKElw5ChVmEw3SrszitN8qqZ0S3z/kt7dio3Z50CgYEAyFt/bN1JHahmPUh8uDsDPtDkiSXPODPRXa1SlJroq+zj1UNm4Z2RJiEtGQS5Psz2xOb2J98+YwCaLzyugTsUSp0LNNaTG0eQhHZlzAM0nJHvFPuEB3riuajUxLL1QCjqqLn5jWpX8ITiAfS0mbICphMQzM04fAwjfqLZAm5FM7sCgYABZGajMPvWAwCpOVdn8bEkVfctxKXHQQX9s+LcC0EpbzhLp4FnL6Y/9ZYJjd2/xIOrJelGYED35JcZ/Z9NAswTTJ1s4643W7UBrYZBBglVc45C5n4ktQudls3LDWxPREOi9iKo1TlLdGZyBHxtJV9qT3nUk839IIAPKVYgO1o/RQKBgQCffga/2J+9ljJdEnkGByQcOxnR+2ErskQ2OCUG2xHg2qC7Qf6CF3ZWTg6iXdpBHdRLBjhpoRL+qJoUhE+93BPZGY60LyPHkX5/k+iRMPQOtzUFFPsTIUWe2cEWHAeTXX7/dZwscL00X/Ox7uRdfREStxamka8nQFp5EpWBk9pOxQKBgGg5KhKY0uf1elRZZYtPJ2/Pu+0oTo+iNy3fPKWF5TRH+u5HOz9p5l9/YLYOAn9Co23YTQcN0PHTWJYhnDdVszmG4BmiNAwFeRf4xWqxjMJXwN1V0/reAEG1y1sAxJnyJFkqM69B+Z46VN4o+ahgWlScKUAu1K19rKIYKc+taqow'),

        // optional，默认 warning；日志路径为：sys_get_temp_dir().'/logs/yansongda.pay.log'
        'log' => [
            'file' => storage_path('logs/alipay.log'),
        //  'level' => 'debug'
        //  'type' => 'single', // optional, 可选 daily.
        //  'max_file' => 30,
        ],

        // optional，设置此参数，将进入沙箱模式
        // 'mode' => 'dev',
    ],

    'wechat' => [
        // 公众号 APPID
        'app_id' => env('WECHAT_APP_ID', ''),

        // 小程序 APPID
        'miniapp_id' => env('WECHAT_MINIAPP_ID', ''),

        // APP 引用的 appid
        'appid' => env('WECHAT_APPID', ''),

        // 微信支付分配的微信商户号
        'mch_id' => env('WECHAT_MCH_ID', ''),

        // 微信支付异步通知地址
        'notify_url' => '',

        // 微信支付签名秘钥
        'key' => env('WECHAT_KEY', ''),

        // 客户端证书路径，退款、红包等需要用到。请填写绝对路径，linux 请确保权限问题。pem 格式。
        'cert_client' => '',

        // 客户端秘钥路径，退款、红包等需要用到。请填写绝对路径，linux 请确保权限问题。pem 格式。
        'cert_key' => '',

        // optional，默认 warning；日志路径为：sys_get_temp_dir().'/logs/yansongda.pay.log'
        'log' => [
            'file' => storage_path('logs/wechat.log'),
        //  'level' => 'debug'
        //  'type' => 'single', // optional, 可选 daily.
        //  'max_file' => 30,
        ],

        // optional
        // 'dev' 时为沙箱模式
        // 'hk' 时为东南亚节点
        // 'mode' => 'dev',
    ],
];
