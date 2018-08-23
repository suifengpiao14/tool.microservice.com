<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018\8\4 0004
 * Time: 15:51
 */

namespace App\Controllers\v1;

use App\Common\Helpers;
use Da\QrCode\Contracts\ErrorCorrectionLevelInterface;
use Da\QrCode\Format\MeCardFormat;
use Da\QrCode\Format\WifiFormat;
use Da\QrCode\QrCode;
use Slim\Http\Request;
use Slim\Http\Response;
use Zxing\Qrcode\QRCodeReader;
use Zxing\QrReader;

class FormatController
{
    private  $_app=null;
    public function __construct()
    {
        $this->_app=app();
    }

    /**
     * 生成wifi链接二维码
     * @SWG\Post(
     *     path="/api/v1/format/wifi",
     *     tags={"api"},
     *     summary="生成ifi链接二维码",
     *     produces={"application/json"},
     *     consumes={"application/x-www-form-urlencoded"},
     *     @SWG\Parameter(
     *          name="authentication",
     *          type="string",
     *          required=true,
     *          in="formData",
     *          description="加密方法",
     *          default="WPA",
     *     ),
     *     @SWG\Parameter(
     *          name="ssid",
     *          type="string",
     *          required=true,
     *          in="formData",
     *          description="热点名称",
     *          default="wifi",
     *     ),
     *     @SWG\Parameter(
     *          name="password",
     *          type="string",
     *          required=true,
     *          in="formData",
     *          description="密码",
     *          default="88888888",
     *     ),
     *     @SWG\Parameter(
     *          name="hidden",
     *          type="string",
     *          in="formData",
     *          description="热点是否被隐藏",
     *          default="0",
     *     ),
     *     @SWG\Parameter(
     *          name="filename",
     *          type="string",
     *          in="formData",
     *          description="文件名称（svg/png/eps 默认为jpg, svg/eps为矢量图文件）,有文件名输出图片下载地址，否则直接输出图片",
     *          default="wifi.jpg",
     *     ),
     *     @SWG\Response(response=400,description="bad request",ref="#/responses/BadRequest"),
     *     @SWG\Response(response=404,description="bad request",ref="#/responses/NotFound"),
     *     @SWG\Response(response="200", description="ok",),
     *),
     **/
    public function wifi(Request $request,Response $response,$arguments=[]){
        $authentication = $request->getParam('authentication','WPA');
        $ssid=$request->getParam('ssid');
        $password=$request->getParam('password');
        $filename=$request->getParam('filename');
        $hidden=$request->getParam('hidden');
        $format = new WifiFormat([
            'authentication' => $authentication,
            'ssid' => $ssid,
            'password' => $password,
            'hidden'=>$hidden,
        ]);
        $qrCode = (new QrCode($format))->setMargin(20);
        if($filename){
            $filename = '/qrcode/'.$filename;
            $qrCode->writeFile(web_path() . $filename);// writer defaults to PNG when none is specified
            $uri = $request->getUri();
            $urlArr = [
                'scheme'=>$uri->getScheme(),
                'host'=>$uri->getHost(),
                'path'=>$filename,
            ];
            $url = http_build_url($urlArr);
            return $response->write($url);
        }

        $response->withHeader('Content-Type',$qrCode->getContentType());
        $content = $qrCode->writeString();
        return $response->write($content);
    }




    /**
     * 个人名片二维码
     * @SWG\Post(
     *     path="/api/v1/format/my-card",
     *     tags={"api"},
     *     summary="个人名片二维码",
     *     produces={"application/json"},
     *     consumes={"application/x-www-form-urlencoded"},
     *     @SWG\Parameter(
     *          name="name",
     *          type="string",
     *          required=true,
     *          in="formData",
     *          description="名称",
     *          default="name",
     *     ),
     *     @SWG\Parameter(
     *          name="birthday",
     *          type="string",
     *          in="formData",
     *          description="生日格式mm-dd",
     *          default="07-17",
     *     ),
     *     @SWG\Parameter(
     *          name="address",
     *          type="string",
     *          in="formData",
     *          description="住址",
     *          default="住址",
     *     ),
     *     @SWG\Parameter(
     *          name="phone",
     *          type="string",
     *          required=true,
     *          in="formData",
     *          description="手机号",
     *          default="15999646785",
     *     ),
     *     @SWG\Parameter(
     *          name="weixin",
     *          type="string",
     *          in="formData",
     *          description="微信号",
     *          default="weixin_count",
     *     ),
     *     @SWG\Parameter(
     *          name="email",
     *          type="string",
     *          in="formData",
     *          description="邮箱",
     *          default="123@qq.com",
     *     ),
     *     @SWG\Parameter(
     *          name="qq",
     *          type="string",
     *          in="formData",
     *          description="qq号",
     *          default="123456789",
     *     ),
     *     @SWG\Parameter(
     *          name="profile",
     *          type="string",
     *          in="formData",
     *          description="简介",
     *          default="我的简介",
     *     ),
     *     @SWG\Parameter(
     *          name="filename",
     *          type="string",
     *          in="formData",
     *          description="文件名称（svg/png/eps 默认为jpg, svg/eps为矢量图文件）,有文件名输出图片下载地址，否则直接输出图片",
     *          default="my-card.jpg",
     *     ),
     *     @SWG\Response(response=400,description="bad request",ref="#/responses/BadRequest"),
     *     @SWG\Response(response=404,description="bad request",ref="#/responses/NotFound"),
     *     @SWG\Response(response="200", description="ok",),
     *),
     **/
    public function myCard(Request $request,Response $response,$arguments=[]){
        $name=$request->getParam('name');
        $phone = $request->getParam('phone');
        $email=$request->getParam('email');
        $weixin=$request->getParam('weixin');
        $qq=$request->getParam('qq');
        $birthday=$request->getParam('birthday');
        $address=$request->getParam('address');
        $profile=$request->getParam('profile');
        $filename=$request->getParam('filename');
        $data = [];
        $data[] = "MECARD:";
        $data[] = "N:{$name};";
        $data[] = "TEL:{$phone};";
        $data[] = "EMAIL:{$email};";
        $data[] = "WX:{$weixin}";
        $data[] = "BDAY:{$birthday};";
        $data[] = "ADR:{$address};";
        $data[] = "QQ:{$qq};";
        $data[] = "PROFILE:{$profile};\n;";
        $text = implode("\n", $data);

        $qrCode = (new QrCode($text))->setMargin(20);
        if($filename){
            $filename = '/qrcode/'.$filename;
            $qrCode->writeFile(web_path() . $filename);// writer defaults to PNG when none is specified
            $uri = $request->getUri();
            $urlArr = [
                'scheme'=>$uri->getScheme(),
                'host'=>$uri->getHost(),
                'path'=>$filename,
            ];
            $url = http_build_url($urlArr);
            return $response->write($url);
        }

        $response->withHeader('Content-Type',$qrCode->getContentType());
        $content = $qrCode->writeString();
        return $response->write($content);
    }






}