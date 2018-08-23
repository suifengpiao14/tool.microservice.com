<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018\8\4 0004
 * Time: 15:36
 */
use Slim\Http\Request;
use Slim\Http\Response;
$app = app();


/**
 * 文档html页面
 */
$app->get('/',function(Request $request,Response $response,$arguments=[]){
    return $response->withRedirect('/swagger',301);
});

// 生成二维码
$app->post('/api/v1/qrcode/create',"App\\Controllers\\v1\\QrcodeController:create")->setName('qrcode_create');
// 解码二维码
$app->post('/api/v1/qrcode/decode',"App\\Controllers\\v1\\QrcodeController:decode")->setName('qrcode_decode');
// 多个二维码合并成一个
$app->post('/api/v1/qrcode/all-in-one',"App\\Controllers\\v1\\QrcodeController:allInOne")->setName('qrcode_all_in_one');

// 生成wifi链接二维码
$app->post('/api/v1/format/wifi',"App\\Controllers\\v1\\FormatController:wifi")->setName('format_wifi');

// 个人名片二维码
$app->post('/api/v1/format/my-card',"App\\Controllers\\v1\\FormatController:myCard")->setName('format_my_card');

/**
 *  文档json地址
 */
$app->get('/swagger/json',"App\\Controllers\\SwaggerController:json")->setName('swagger_json');
