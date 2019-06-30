<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Annotation\AutoController;
use Logic\Form\DemoForm;

/**
 * Class LogicDemoController
 * @AutoController(prefix="logic")
 * @package App\Controller
 */
class LogicDemoController extends Controller
{
    /**
     * 所有参数为非必填
     * @return array
     */
    public function index()
    {
        $requestData = $this->request->all();
        $form = DemoForm::instance($requestData);

        if ($form->validate()) {
            $result = $form->handle();
            return $this->success('ok', $result);
        }

        return $this->error($form->getError());
    }

    /**
     * 所有参赛为必填
     * @return array
     */
    public function required()
    {
        $requestData = $this->request->all();
        $form = DemoForm::instance($requestData, true);

        if ($form->validate()) {
            $result = $form->handle();
            return $this->success('ok', $result);
        }

        return $this->error($form->getError());
    }


    /**
     * 请求成功响应格式
     * @param $msg
     * @param array $data
     * @param int $code
     * @return array
     */
    private function success($msg, $data = [], $code = 200) {
        return compact('msg', 'data', 'code');
    }

    /**
     * 请求失败响应格式
     * @param $msg
     * @param int $code
     * @param array $data
     * @return array
     */
    private function error($msg, $code = 0, $data = []) {
        return compact('msg', 'data', 'code');
    }
}
