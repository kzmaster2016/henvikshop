<?php
namespace Web\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
        $this->display();
    }
    public function about(){
        $this->display();
    }
    public function services(){
        $this->display();
    }
    public function project(){
        $this->display();
    }
    public function contact(){
        $this->display();
    }
}