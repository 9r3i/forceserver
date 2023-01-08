<?php
/**
 * website
 * ~ a plugin for ForceServer
 * authored by 9r3i
 * https://github.com/9r3i
 * started at november 13th 2022
 * @requires ForceData
 */
class website{
  protected $db=null;
  protected $pkey=null;
  protected $temp=null;
  public $postMethods=[
    'test',
    'login',
    'dataNew',
    'dataEdit',
    'dataDelete',
    'pictureUpload',
    'pictureDelete',
    'userEdit',
    'upload',
  ];
  public $getMethods=[
    'test',
    'select',
    'all',
    'content',
    'image',
  ];
  public $types=[
    'text',
    'image',
    'audio',
    'video',
    'json',
    'url',
    'ini',
    'base64',
    'gzip',
    'binary',
  ];
  /* constructor */
  function __construct($req,$method,$pre){
    $base=$req['database']??'data';
    $this->db=new ForceData('website_'.$base);
    $this->temp=$this->db->dir().'temp/';
    if(!is_dir($this->temp)){
      @mkdir($this->temp,0755,true);
    }
    if($method=='POST'&&$pre->method!='login'){
      if(!isset($_POST['pkey'])){
        return $pre->output('Error: Require privilege key.');
      }elseif(!$this->db->isValidPkey($_POST['pkey'])){
        return $pre->output('Error: Invalid privilege key.');
      }
      $this->pkey=$_POST['pkey'];
      unset($_POST['pkey']);
    }
  }
  /* =============== get methods =============== */
  /* public image */
  public function image($get){
    if(!isset($get['id'])){
      $text='Error: Require image ID.';
      header('HTTP/1.1 400 Bad Request');
      header('Content-Length: '.strlen($text));
      exit($text);
    }
    $file=$this->db->diri().$get['id'];
    if(!is_file($file)){
      $text='Error: Image is not found.';
      header('HTTP/1.1 404 Not Found');
      header('Content-Length: '.strlen($text));
      exit($text);
    }
    $info=getimagesize($file);
    $mime=isset($info['mime'])?$info['mime']:'image/jpeg';
    $fname=$get['id'].'.'
        .preg_replace('/^[^\/]+\//i','',$mime);
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Cache-Control: public, max-age=604800');
    header('Content-Description: File Transfer');
    header('Content-Disposition: attachment; filename="'.$fname.'"');
    header('Content-Type: '.$mime);
    header('Content-Length: '.filesize($file));
    header('HTTP/1.1 200 OK');
    @readfile($file);
    exit;
  } 
  /* data content only */
  public function content($get){
    if(!isset($get['id'])){
      return 'Error: Require ID.';
    }
    $data=$this->db->findById($get['id']);
    $file=$this->db->dir().$get['id'];
    if(!is_array($data)||count($data)<1||!is_file($file)){
      $text='Error: Not Found.';
      header('Content-Length: '.strlen($text));
      header('HTTP/1.1 404 Not Found');
      exit($text);
    }
    $row=array_values($data)[0];
    $type=$row['type'];
    $mime='text/plain';
    $fname=$row['slug'].'.txt';
    if($type=='image'){
      $info=@getimagesize($file);
      $mime=isset($info['mime'])?$info['mime']:'image/jpeg';
      $fname=$row['slug'].'.'
        .preg_replace('/^[^\/]+\//i','',$mime);
    }elseif($type=='audio'){
      $mime='audio/mpeg';
      $fname=$row['slug'].'.mp3';
    }elseif($type=='video'){
      $mime='video/mp4';
      $fname=$row['slug'].'.mp4';
    }elseif($type=='gzip'){
      $mime='application/gzip';
      $fname=$row['slug'].'.gz';
    }elseif($type=='binary'){
      $mime='application/octet-stream';
      $fname=$row['slug'].'.bin';
    }elseif($type=='json'){
      $mime='application/json';
      $fname=$row['slug'].'.json';
    }elseif($type=='url'){
      $mime='application/x-www-form-urlencoded';
      $fname=$row['slug'].'.url';
    }elseif($type=='ini'){
      $fname=$row['slug'].'.ini';
    }elseif($type=='base64'){
      $fname=$row['slug'].'.b64';
    }
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Description: File Transfer');
    header('Content-Disposition: attachment; filename="'.$fname.'"');
    header('Content-Type: '.$mime);
    header('Content-Length: '.filesize($file));
    header('HTTP/1.1 200 OK');
    @readfile($file);
    exit;
  }
  /* select public data -- with content */
  public function select($get){
    $key=isset($get['key'])?$get['key']:'id';
    if(!isset($get[$key])){
      return 'Error: Require '.$key.'.';
    }
    $data=$this->db->findData($key,$get[$key]);
    $map=array_map(function($v){
      $readable=['text','json','url','ini','base64'];
      if(in_array($v['type'],$readable)){
        $v['content']=$this->db->read($v['id']);
      }else{
        $v['content']="@[content:{$v['id']}[*** {$v['type']} ***]]";
      }
      return $v;
    },$data);
    return array_values($map);
  }
  /* all data -- without content */
  public function all($get){
    return $this->db->data();
  }
  /* =============== post methods =============== */
  /* login */
  public function login($post){
    if(!isset($post['uname'],$post['upass'])){
      return 'Error: Invalid request.';
    }
    $users=$this->db->findData('uname',$post['uname'],'user');
    if(!is_array($users)||count($users)<1){
      return 'Error: Invalid username or password.';
    }
    $user=array_values($users)[0];
    if(!password_verify($post['upass'],$user['upass'])){
      return 'Error: Invalid username or password.';
    }
    $expire=strtotime('+7 days');
    $pkey=$this->db->pkeyCreate($user['uname'],$expire);
    return [
      'uname'=>$user['uname'],
      'expire'=>$expire,
      'expire_date'=>date('Y-m-d H:i:s',$expire),
      'pkey'=>$pkey,
    ];
  }
  /* new data */
  public function dataNew($post){
    if(!isset($post['title'],$post['content'],$post['type'])){
      return 'Error: Invalid request.';
    }
    if(trim($post['title'])==''){
      return 'Error: Empty title field.';
    }
    $slug=$this->db->toSlug($post['title']);
    $find=$this->db->findData('slug',$slug);
    if(count($find)>0){
      return 'Error: Slug has been taken.';
    }
    if(!in_array($post['type'],$this->types)){
      return 'Error: Invalid content type.';
    }
    $data=$this->db->data();
    $length=count($data);
    $id=intval($data[$length-1]['id'])+1;
    $data[]=[
      'id'=>$id,
      'title'=>$post['title'],
      'time'=>date('Y-m-d H:i:s'),
      'slug'=>$slug,
      'type'=>$post['type'],
    ];
    $wd=$this->db->data('data',$data);
    $readable=['text','json','url','ini','base64'];
    $ptrn='/^@\[content:(\d+)\[\*{3}\s([a-z0-9]+)\s\*{3}\]\]$/';
    if(in_array($post['type'],$readable)){
      $wc=$this->db->write((string)$id,$post['content']);
    }elseif(preg_match($ptrn,$post['content'],$akur)
      &&$akur[2]=='temp'){
      $wc=$this->moveTempFile($post['content'],(string)$id);
    }else{
      /* nothing else to do */
    }
    return $wd?'Saved.':'Error: Failed to save data.';
  }
  /* edit data */
  public function dataEdit($post){
    if(!isset($post['title'],$post['content'],$post['id'])
      ||!isset($post['slug'],$post['time'],$post['type'])){
      return 'Error: Invalid request.';
    }
    $tptrn='/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/';
    if(!preg_match($tptrn,$post['time'])){
      return 'Error: Invalid time format.';
    }
    if(!preg_match('/^[0-9a-z-]+$/',$post['slug'])){
      return 'Error: Invalid slug format.';
    }
    $find=$this->db->findData('slug',$post['slug']);
    $find=array_values($find);
    if(count($find)>0&&$find[0]['id']!=$post['id']){
      return 'Error: Slug has been taken.';
    }
    if(!in_array($post['type'],$this->types)){
      return 'Error: Invalid content type.';
    }
    $data=$this->db->data();
    $old=null;
    $key=null;
    $found=false;
    foreach($data as $k=>$v){
      if($v['id']==$post['id']){
        $old=$v;
        $key=$k;
        $data[$k]=[
          'id'=>intval($post['id']),
          'title'=>$post['title'],
          'time'=>$post['time'],
          'slug'=>$post['slug'],
          'type'=>$post['type'],
        ];
        $found=true;
        break;
      }
    }
    if(!$found){
      return 'Error: Data is not found.';
    }
    $ptrn='/^@\[content:(\d+)\[\*{3}\s([a-z0-9]+)\s\*{3}\]\]$/';
    if(preg_match($ptrn,$post['content'],$akur)
      &&$akur[2]!='temp'){
      $data[$key]['type']=$old['type'];
    }
    $wd=$this->db->data('data',array_values($data));
    $readable=['text','json','url','ini','base64'];
    if(in_array($post['type'],$readable)){
      $wc=$this->db->write($post['id'],$post['content']);
    }elseif(preg_match($ptrn,$post['content'],$akur)
      &&$akur[2]=='temp'){
      $wc=$this->moveTempFile($post['content'],$post['id']);
    }else{
      /* nothing else to do */
    }return $wd?'Saved.':'Error: Failed to save data.';
  }
  /* delete data */
  public function dataDelete($post){
    if(!isset($post['id'])){
      return 'Error: Invalid request.';
    }
    $data=$this->db->data();
    $found=false;
    foreach($data as $k=>$v){
      if($v['id']==$post['id']){
        unset($data[$k]);
        $found=true;
      }
    }
    if(!$found){
      return 'Error: Data is not found.';
    }
    $file=$this->db->dir().$post['id'];
    $image=$this->db->diri().$post['id'];
    if(is_file($file)){@unlink($file);}
    if(is_file($image)){@unlink($image);}
    $wd=$this->db->data('data',array_values($data));
    return $wd?'Saved.':'Error: Failed to save data.';
  }
  /* upload picture */
  public function pictureUpload($post){
    if(!isset($post['id'],$post['data'])){
      return 'Error: Invalid request.';
    }
    $find=$this->db->findById($post['id']);
    if(count($find)<1){
      return 'Error: Data is not found.';
    }
    $ptrn='/^data:image\/([0-9a-z]+);base64,/';
    $file=$this->db->diri().$post['id'];
    $base=preg_replace($ptrn,'',$post['data']);
    $data=base64_decode($base);
    $wd=@file_put_contents($file,$data);
    return $wd?'Uploaded.':'Error: Failed to upload picture.';
  }
  /* delete picture */
  public function pictureDelete($post){
    if(!isset($post['id'])){
      return 'Error: Invalid request.';
    }
    $find=$this->db->findById($post['id']);
    if(count($find)<1){
      return 'Error: Data is not found.';
    }
    $file=$this->db->diri().$post['id'];
    if(!is_file($file)){
      return 'Error: File is not found.';
    }
    $wd=@unlink($file);
    return $wd?'Deleted.':'Error: Failed to delete picture.';
  }
  /* edit user */
  public function userEdit($post){
    if(!isset($post['uname'],$post['upass'],$post['opass'])){
      return 'Error: Invalid request.';
    }
    if($post['upass']==''){
      return 'Error: Empty new password.';
    }
    if($post['uname']=='demo'){
      return 'Error: Cannot change user in demo version.';
    }
    $data=$this->db->data('user');
    $key=false;
    foreach($data as $k=>$v){
      if($v['uname']==$post['uname']){
        $key=$k;
        break;
      }
    }
    if($key===false){
      return 'Error: User is not found.';
    }
    $user=$data[$key];
    if(!password_verify($post['opass'],$user['upass'])){
      return 'Error: Invalid old password.';
    }
    $data[$key]['upass']=password_hash($post['upass'],1);
    $wd=$this->db->data('user',array_values($data));
    return $wd?'Saved.':'Error: Failed to save data.';
  }
  /* upload */
  public function upload($post,$method,$pre){
    if(!isset($_FILES['data'])){
      return 'Error: Invalid request.';
    }
    $file=$_FILES['data'];
    $error=$file['error'];
    $errors=[
      'UPLOAD_ERR_OK',
      'UPLOAD_ERR_INI_SIZE',
      'UPLOAD_ERR_FORM_SIZE',
      'UPLOAD_ERR_PARTIAL',
      'UPLOAD_ERR_NO_FILE',
      'UPLOAD_ERR_UNKNOWN_5',
      'UPLOAD_ERR_NO_TMP_DIR',
      'UPLOAD_ERR_CANT_WRITE',
      'UPLOAD_ERR_EXTENSION',
    ];
    if($error){
      $message=$errors[$error];
      return "Error: {$error} - {$message}.";
    }
    $tid=$this->getTempID();
    $target=$this->temp.$tid;
    $moved=@move_uploaded_file($file['tmp_name'],$target);
    if(!$moved){
      return 'Error: Failed to upload file.';
    }return "@[content:{$tid}[*** temp ***]]";
  }
  /* =============== testing methods =============== */
  /* test */
  public function test($req,$method,$pre){
    return [
      'method'=>$method,
      'request'=>$req,
      'pre'=>$pre,
    ];
  }
  /* =============== protected methods =============== */
  /* move temp file to content */
  protected function moveTempFile(string $content,$id){
    $ptrn='/^@\[content:(\d+)\[\*{3}\stemp\s\*{3}\]\]$/';
    if(!preg_match($ptrn,$content,$akur)){
      return false;
    }
    $tid=$akur[1];
    $file=$this->temp.$tid;
    if(!is_file($file)){
      return false;
    }
    $target=$this->db->dir().$id;
    $moved=@rename($file,$target);
    return $moved?true:false;
  }
  /* get random temp id */
  protected function getTempID(){
    $tid=mt_rand(1000,9999);
    $file=$this->temp.$tid;
    return is_file($file)?$this->getTempID():$tid;
  }
}
