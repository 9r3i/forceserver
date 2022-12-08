<?php
/* like.force.php
 * ~ a ForceServer plugin for like storage
 * started at november 14th 2022
 * @requires ForceData
 */
class like{
  public $getMethods=[
    'get',
    'put',
  ];
  public $postMethods=[
  ];
  protected $db=null;
  protected $dir=null;
  function __construct($req,$method,$pre){
    $base=$req['database']??'data';
    $this->db=new ForceData('website_'.$base);
    $this->dir=$this->db->dir();
  }
  public function get($get,$method,$pre){
    if(!isset($get['id'])){
      return '0';
    }
    $id=$get['id'];
    $data=$this->db->data('like');
    return isset($data[$id])?$data[$id]:'0';
  }
  public function put($get,$method,$pre){
    if(!isset($get['id'])){
      return 'Error: Invalid request.';
    }
    $id=$get['id'];
    $data=$this->db->data('like');
    $like=isset($data[$id])?intval($data[$id]):0;
    $data[$id]=$like+1;
    $put=$this->db->data('like',$data);
    return $put?'OK':'Error: Failed to save data.';
  }
}
