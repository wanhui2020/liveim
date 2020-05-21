<div>
    @if(isset($merchant->enterprise_name))
        <p> 商户名称: {{ $merchant->enterprise_name }}-{{ $merchant->name }}</p>
    @endif
    <p> 运行环境: {{ $merchant->production==0?"正式环境":"测试环境" }}</p>
    <p> API_Token: {{ $merchant->api_token }}</p>
    <p> API密钥: {{ $merchant->secret_key }}</p>
    <p> 安全码: {{ $merchant->safeCode }}</p>
</div>