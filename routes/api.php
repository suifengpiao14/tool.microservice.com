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

/**
 *  文档json地址
 */
$app->get('/swagger/json',"App\\Controllers\\SwaggerController:json")->setName('swagger_json');
