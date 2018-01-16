<?php

namespace Core;

class LayoutProcessor
{
    private $viewPath = null;

    static private $instance = null;

    private function __clone()
    {
    }

    private function __construct()
    {
        $this->viewPath = realpath($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR."view");
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function getTPL(string $template, string $contentFileName, array $data = [])
    {
        return LayoutProcessor::getInstance()->_getTPL($template, $contentFileName, $data);
    }

    public function _getTPL(string $template,string $contentFileName, array $data = [])
    {
        try{

            $tplPath = $this->viewPath . DIRECTORY_SEPARATOR . $template;
            if(!is_dir (realpath($tplPath)) )
                throw new \Exception("There is no such template");

            if(!$layout = file_get_contents (realpath($tplPath."/layout.html") ) )
                throw new \Exception("Error with template");

            if(!$content = file_get_contents (realpath($tplPath."/".$contentFileName.".html") ) )
                throw new \Exception("Error with content template");

            foreach ($data as $key => $data){
                $content = str_replace( "###".$key."###" , $data , $content);
            }

            return str_replace( "###content###" , $content , $layout);

        } catch ( \Exception $exception ){

            return null;
        }
    }
}