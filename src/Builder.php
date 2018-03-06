<?php

namespace thinkphp5\validator;

use think\Controller;
use think\facade\Url;
use think\Request;

class Builder extends Controller
{

    public function index()
    {
        $helper = new BuilderHelper();
        $tables = $helper->getTables();

        $this->assign('tables', $tables);
        $this->assign('action', Url::build('/validator_builder/add', '', false));
        return $this->fetch(VALIDATOR_VIEW_PATH . 'index.html');
    }

    public function add(Request $request)
    {
        $data = $request->post();

        if (empty($data['tables'])) {
            $this->error('没有选中的数据表');
        }

        $helper = new BuilderHelper();
        $result = $helper->generate($data['tables']);

        $this->assign('result', $result);
        $this->assign('index', Url::build('/validator_builder', '', false));
        return $this->fetch(VALIDATOR_VIEW_PATH . 'result.html');
    }

}