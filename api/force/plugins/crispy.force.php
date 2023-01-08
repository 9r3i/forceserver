<?php
/* crispy.force.php
 * ~ a ForceServer plugin for crispies plugin and crispy theme
 * started at december 4th 2022
 * @requires ForceData
 */
class crispy{
  public $getMethods=[
    'get',
    'fetch',
  ];
  public $postMethods=[
    'put',
    'unknown',
    'contact',
    'reservation',
    'inbox',
    'order',
  ];
  protected $db=null;
  protected $dir=null;
  protected $file=null;
  function __construct($req,$method,$pre){
    $base=$req['database']??'data';
    $this->db=new ForceData('website_'.$base);
    $this->dir=$this->db->dir();
    $this->file=$this->dir.'crispy.json';
  }
  public function unknown($post,$method,$pre){
    $post['microtime']=microtime(true);
    $data=$this->db->data('crispy_unknown');
    $data[]=$post;
    $put=$this->db->data('crispy_unknown',$data);
    return $put?'Saved.':'Error: Failed to save data.';
  }
  public function reservation($post,$method,$pre){
    $post['microtime']=microtime(true);
    $data=$this->db->data('crispy_reservation');
    $data[]=$post;
    $put=$this->db->data('crispy_reservation',$data);
    return $put?'Saved.':'Error: Failed to save data.';
  }
  public function order($post,$method,$pre){
    return $this->db->data('crispy_reservation');
  }
  public function contact($post,$method,$pre){
    $post['microtime']=microtime(true);
    $data=$this->db->data('crispy_contact');
    $data[]=$post;
    $put=$this->db->data('crispy_contact',$data);
    return $put?'Saved.':'Error: Failed to save data.';
  }
  public function inbox($post,$method,$pre){
    return $this->db->data('crispy_contact');
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
