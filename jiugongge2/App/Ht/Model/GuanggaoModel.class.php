<?php
namespace Ht\Model;
use Think\Model;
class GuanggaoModel extends Model{
	public	$optimLock=true;
	 Protected $pk = 'id';
	  	protected $fields=array(
		'name','photo','addtime','sort','shopid','position','uniacid','lock_version'
	);
	}
?>
