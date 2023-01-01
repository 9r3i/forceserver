<?php
/**
 * visitor
 * ~ a ForceServer plugin for archiving visitors
 * started at november 11th 2022
 * @requires ForceData
*/
class visitor{
  public $getMethods=[
    'total',
  ];
  /* get site visitors */
  public function total($get,$method,$pre){
    $base=$get['base']??'data';
    $db=new ForceData('website_'.$base);
    return visitor::get($db->dir(),$base);
  }
  /* get (read/write) site visitor */
  public static function get(string $dir='.',$base='data'){
    $f=$dir.'/'.$base.'.visitor';
    $d=0x01;
    $o=fopen($f,is_file($f)?'rb+':'wb');
    if(!is_resource($o)){
      fclose($o);
      return $d;
    }
    if(!is_file($f)){
      $w=fwrite($o,@gzencode($d));
      flock($o,LOCK_UN);
      fclose($o);
      return $d;
    }
    $r=fread($o,1024);
    $g=@gzdecode($r);
    $n=intval($g)+$d;
    fseek($o,0);
    $w=fwrite($o,@gzencode($n));
    flock($o,LOCK_UN);
    fclose($o);
    return $n;
  }
}
