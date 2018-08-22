<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018\8\4 0004
 * Time: 15:51
 */

namespace App\Controllers\v1;

use App\Common\Helpers;
use App\Services\QrcodeService;
use App\Services\WeatherService;
use Da\QrCode\QrCode;
use Slim\Http\Request;
use Slim\Http\Response;
use Zxing\Qrcode\QRCodeReader;
use Zxing\QrReader;

class QrcodeController
{
    private  $_app=null;
    public function __construct()
    {
        $this->_app=app();
    }

    /**
     * 生成二维码
     * @SWG\Post(
     *     path="/api/v1/qrcode/create",
     *     tags={"api"},
     *     summary="生成二维码",
     *     produces={"application/json"},
     *     consumes={"application/x-www-form-urlencoded"},
     *     @SWG\Parameter(
     *          name="text",
     *          type="string",
     *          required=true,
     *          in="formData",
     *          description="内容",
     *          default="test",
     *     ),
     *     @SWG\Parameter(
     *          name="filename",
     *          type="string",
     *          in="formData",
     *          description="文件名称（svg/png/eps 默认为jpg, svg/eps为矢量图文件）,有文件名输出图片下载地址，否则直接输出图片",
     *          default="qrcode.jpg",
     *     ),
     *     @SWG\Response(response=400,description="bad request",ref="#/responses/BadRequest"),
     *     @SWG\Response(response=404,description="bad request",ref="#/responses/NotFound"),
     *     @SWG\Response(response="200", description="ok",),
     *),
     **/
    public function create(Request $request,Response $response,$arguments=[]){
        $text = $request->getParam('text');
        $filename=$request->getParam('filename');
        $qrCode = (new QrCode($text))
            ->setSize(334)
            ->setMargin(20);
            //->useForegroundColor(51, 153, 255);
        if($filename){
            $filename = '/qrcode/'.$filename;
            $qrCode->writeFile(web_path() . $filename);// writer defaults to PNG when none is specified
            $uri = $request->getUri();
            $url = strtr('{scheme}://{host}{path}',[
                '{scheme}'=>$uri->getScheme(),
                '{host}'=>$uri->getHost(),
                '{path}'=>$filename,
            ]);
            return $response->write($url);
        }

        $response->withHeader('Content-Type',$qrCode->getContentType());
        $content = $qrCode->writeString();
        return $response->write($content);
    }

    /**
     * 解析二维码文件
     * @SWG\Post(
     *     path="/api/v1/qrcode/decode",
     *     tags={"api"},
     *     summary="解析二维码文件",
     *     produces={"application/json"},
     *     consumes={"multipart/form-data"},
     *   @SWG\Parameter(
     *     in="formData",
     *     name="img",
     *     description="上传二维码",
     *     required=true,
     *     type="file"
     *   ),
     *     @SWG\Response(response=400,description="bad request",ref="#/responses/BadRequest"),
     *     @SWG\Response(response=404,description="bad request",ref="#/responses/NotFound"),
     *     @SWG\Response(response="200", description="ok",),
     *),
     **/
    public function decode(Request $request,Response $response,$arguments=[]){
        $files=$request->getUploadedFiles();
        /** @var \Slim\Http\UploadedFile $qrcodeFile */
        $qrcodeFile = reset($files);
        $qrReader = new QrReader($qrcodeFile->file);
        $text = $qrReader->text();
        return $response->write($text);
    }



}