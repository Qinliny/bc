<?php
namespace app\Admin\controller;

class IndexController extends BaseController
{
    public function index() {

        return view('index/index');
    }
}