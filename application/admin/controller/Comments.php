<?php
namespace app\admin\controller;

use think\facade\Request;
use think\facade\Lang;
use think\facade\Hook;
use app\common\model\DocumentComments;
use app\common\model\SiteConfig;
use app\admin\controller\Admin;

class Comments extends Admin
{
    // 分类配置标识
    protected $category = 'block_category';

    public function index()
    {
        $request = Request::param();

        $obj = new DocumentComments;

        // 查询条件
        $map    = [];
        $params = [];
        $search = [];

        // 状态
        if (isset($request['status']) && is_numeric($request['status'])) {
            $status           = ['status','=',$request['status']];
            $params['status'] = $request['status'];
            $search['status'] = $request['status'];
            array_push($map, $status);
        }else {
            $status = [];
            $search['status'] = '';
        }

        // 分页列表
        $list = $obj
            ->where($map)
            ->where('site_id', 'eq', $this->site_id)
            ->order('id desc')
            ->paginate(20, false, [
                'type'     => 'bootstrap',
                'var_page' => 'page',
                'query'    => $params,
            ]);

        $data = [
            'search'   => $search,
            'list'     => $list,
            'page'     => $list->render(),
        
        ];

        return $this->fetch('index', $data);
    }


    public function edit()
    {
        // 处理AJAX提交数据
        if (Request::isAjax()) {
            $request = Request::param();

            // 写入
            $obj = new DocumentComments;
            $obj->isUpdate(true)->allowField(true)->save($request);

            if (is_numeric($obj->id)) {
                return $this->response(200, Lang::get('Success'));
            } else {
                return $this->response(201, Lang::get('Fail'));
            }
        }

        $request = Request::param();
        $obj = new DocumentComments;
        $info = $obj->where('id', $request['id'])->find();

        $data = [
            'info'  => $info,
        ];

        return $this->fetch('edit', $data);
    }

    public function handle()
    {
        $request = Request::instance()->param();

        $obj = new DocumentComments;
        switch ($request['type']) {
            case 'delete':
                foreach ($request['ids'] as $v) {
                    $result = $obj::destroy($v);
                }

                if ($result !== false) {
                    return $this->response(200, Lang::get('Success'));
                }
                break;
            case 'approval':
                foreach ($request['ids'] as $v) {
                    $result = $obj
                        ->where('id', $v)
                        ->setField('status', 1);
                }

                if ($result !== false) {
                    return $this->response(200, Lang::get('Success'));
                }
                break;
            case 'progress':
                foreach ($request['ids'] as $v) {
                    $result = $obj
                        ->where('id', $v)
                        ->setField('status', 0);
                }

                if ($result !== false) {
                    return $this->response(200, Lang::get('Success'));
                }
                break;
        }

        return $this->response(201, Lang::get('Fail'));
    }

    public function remove()
    {
        $id = Request::param('id');

        // 删除
        $des = DocumentComments::destroy($id);

        if ($des !== false) {
            return $this->response(200, Lang::get('Success'));
        } else {
            return $this->response(201, Lang::get('Fail'));
        }
    }
}