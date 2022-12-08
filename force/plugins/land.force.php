<?php
/* land.force.php
 * ~ a ForceServer plugin for landing server
 * started at november 9th 2022
 * @requires ForceData
 */
class land{
  public $getMethods=[
    'get',
    'trade',
  ];
  public $postMethods=[
    'iniGet',
    'iniPut',
  ];
  protected $dir=null;
  function __construct($req,$method,$pre){
    $base=$req['database']??'data';
    $db=new ForceData('website_'.$base);
    $this->dir=$db->dir();
  }
  public function iniPut($post,$method,$pre){
    if(!isset($post['content'])){
      return false;
    }
    $data=$this->parse($this->dir);
    $file=$this->dir.'land.ini';
    return @file_put_contents($file,$post['content']);
  }
  public function iniGet($post,$method,$pre){
    $data=$this->parse($this->dir);
    $file=$this->dir.'land.ini';
    return @file_get_contents($file);
  }
  public function trade($get,$method,$pre){
    if(!isset($get['key'])){
      return false;
    }$key=$get['key'];
    $file=$this->dir($this->dir).$key;
    if(!is_file($file)){
      return false;
    }
    $url=@file_get_contents($file);
    @unlink($file);
    return $url;
  }
  public function get($get,$method,$pre){
    if(!isset($get['id'])){
      return false;
    }
    $data=$this->parse($this->dir);
    if(!array_key_exists($get['id'],$data)){
      return false;
    }
    $url=$data[$get['id']];
    $hash=base64_encode(md5(microtime(true)*mt_rand(),true));
    $key=preg_replace('/[^0-9a-z]+/i','',$hash);
    $file=$this->dir($this->dir).$key;
    @file_put_contents($file,$url);
    return $key;
  }
  private function dir($dir){
    $dd=dirname($dir).'/land-key/';
    if(!is_dir($dd)){
      @mkdir($dd,0755,true);
    }
    $indexArray=[
      "<?php",
      "header('HTTP/1.1 401 Unauthorized');",
      "exit('Error: 401 Unauthorized');",
    ];
    $index=implode("\r\n",$indexArray);
    $ifile=$dd.'index.php';
    if(!is_file($ifile)){@file_put_contents($ifile,$index);}
    return $dd;
  }
  private function parse($dir){
    $file=$dir.'land.ini';
    if(!is_file($file)){
      @file_put_contents($file,"9302=http://127.0.0.1:9302/\n");
    }return @parse_ini_file($file);
  }
}
