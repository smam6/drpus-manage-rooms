<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class WoowUp_ACW_Logger{
    public function __construct(){}
    private static function logger($data,$level='DEBUG')
    {
        if(is_array($data) || is_object($data)){
            $data=print_r($data,true);
        }
        $dir = dirname(WOOWUP_ABANDONEDCART_PLUGIN_FILE);
        $folder = "$dir/logs";
        if(!is_dir($folder)){
            mkdir($folder,0775,true);
        }
        $file = 'woowup-log-'.date('Y-m-d').'.log';
        $data = date('Y-m-d H:i:s').'  '.$level.'  '.$data."\n";
        @file_put_contents("$folder/$file", $data, FILE_APPEND);
    }
    public static function info($message){
        self::logger($message,'INFO');
    }
    public static function debug($message){
        self::logger($message);
    }
}
