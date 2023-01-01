<?php
/* config.force.php
 * ~ a ForceServer plugin for setting web configuration
 * started at december 4th 2022
 * @requires ForceData
 */
class config{
  public $getMethods=[
    'get',
    'fetch',
  ];
  public $postMethods=[
    'put',
  ];
  protected $db=null;
  protected $dir=null;
  protected $file=null;
  function __construct($req,$method,$pre){
    $base=$req['database']??'data';
    $this->db=new ForceData('website_'.$base);
    $this->dir=$this->db->dir();
    $this->file=$this->dir.'config.json';
  }
  public function fetch($get,$method,$pre){
    if(!is_file($this->file)){
      $content='{}';
      @file_put_contents($this->file,$content);
      return [];
    }
    $get=@file_get_contents($this->file);
    return @json_decode($get,true);
  }
  public function get($get,$method,$pre){
    if(!is_file($this->file)){
      $content='{}';
      @file_put_contents($this->file,$content);
      return $content;
    }return @file_get_contents($this->file);
  }
  public function put($post,$method,$pre){
    if(!isset($post['content'])){
      return 'Error: Invalid request.';
    }
    $decode=@json_decode($post['content'],true);
    if(!is_array($decode)){
      return 'Error: Invalid content.';
    }
    $json=@json_encode($decode,JSON_PRETTY_PRINT);
    $put=@file_put_contents($this->file,$json);
    return $put?$json:'Error: Failed to save data.';
  }
}
