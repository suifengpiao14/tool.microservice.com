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

    /**
     * 将多个二维码信息合并到一个二维码中
     * @SWG\Post(
     *     path="/api/v1/qrcode/all-in-one",
     *     tags={"api"},
     *     summary="多合一二维码",
     *     produces={"application/json"},
     *     consumes={"multipart/form-data"},
     *   @SWG\Parameter(
     *     in="formData",
     *     name="ali",
     *     description="二维码1（如支付宝二维码）",
     *     type="file"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="wx",
     *     description="二维码2（如微信二维码）",
     *     type="file"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="logo",
     *     description="生成二维码的logo图片",
     *     type="file"
     *   ),
     *   @SWG\Parameter(
     *     in="formData",
     *     name="label",
     *     type="string",
     *     description="文字说明",
     *     default="说明",
     *   ),
     *   @SWG\Parameter(
     *        name="url",
     *        type="string",
     *        in="formData",
     *        description="扫码后调用地址",
     *        default="http://baidu.com",
     *   ),
     *   @SWG\Parameter(
     *        name="filename",
     *        type="string",
     *        in="formData",
     *        description="生成的二维码文件名称（svg/png/eps 默认为jpg, svg/eps为矢量图文件）,有文件名输出图片下载地址，否则直接输出图片",
     *        default="qrcode.jpg",
     *   ),
     *     @SWG\Response(response=400,description="bad request",ref="#/responses/BadRequest"),
     *     @SWG\Response(response=404,description="bad request",ref="#/responses/NotFound"),
     *     @SWG\Response(response="200", description="ok",),
     *),
     **/
    public function allInOne(Request $request,Response $response,$arguments=[]){
        $files=$request->getUploadedFiles();
        /** @var \Slim\Http\UploadedFile $logo */
        $logo = $files['logo']??null;
        $filename = $request->getParam('filename');
        $url = $request->getParam('url');
        $extraQuery = $request->getParam('query');
        $label = $request->getParam('label');
        unset($files['logo']);
        $data=[];
        $extraQuery and parse_str($extraQuery,$data);
        /** @var \Slim\Http\UploadedFile $file */
        foreach($files as $key=>$file){
            $qrReader = new QrReader($file->file);
            $value=  $qrReader->text();
            $data[$key] = $value;
        }
        $text = http_build_url($url,['query'=>$data]);
        $qrCode = (new QrCode($text,ErrorCorrectionLevelInterface::HIGH))->setMargin(20)->useEncoding('UTF-8');
        $logo and  $qrCode = $qrCode->useLogo($logo->file)->setLogoWidth(60);
        $label and $qrCode = $qrCode->setLabel(mb_convert_encoding($label, "html-entities", "UTF-8"));////传入html编码，防止中文乱码
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