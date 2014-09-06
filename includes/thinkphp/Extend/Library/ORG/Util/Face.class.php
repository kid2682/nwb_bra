<?php
class Face{
	private $images;		//图片资源
	private $width; 		//新图宽度
	private $height;		//新图高度
	private $left;			//截图小图片从大图片的左边距离
	private $top;			//截图小图片从大图片的上边距离
	private $proportion;	//图片比例
	private $imagewidth;	//新图片高度宽度,此图片是正方形,其他图形可自定义height属性
	public $saveimage;		
	public function __construct($images,$width,$height,$left,$top,$imagewidth,$saveimage){
		$this->images = $images;
		$this->width = $width;
		$this->height = $height;
		$this->left = $left;
		$this->top = $top;
		$this->imagewidth = $imagewidth;
		$this->saveimage = $saveimage;
		$this->calculation();
	}
	private function calculation(){
		//获取图片资源的宽度、高度、类型
		list($yimagewidth, $yimageheight, $imageType) = getimagesize($this->images);
        
        $imageType = image_type_to_mime_type($imageType);
        
    	switch($imageType) {
    		case "image/gif":
    			$im=imagecreatefromgif($this->images); 
    			break;
    	    case "image/pjpeg":
    		case "image/jpeg":
    		case "image/jpg":
    			$im=imagecreatefromjpeg($this->images); 
    			break;
    	    case "image/png":
    		case "image/x-png":
    			$im=imagecreatefrompng($this->images); 
    			break;
      	}
        
		//计算比例
		$this->proportion = $this->imagewidth/$this->width;
		//$im = imagecreatefromjpeg($this->images);
		//计算新图片大小   
		$new_img_width  = ceil($this->width*$this->proportion); 
		$new_img_height = ceil($this->height*$this->proportion); 
		$newim = imagecreatetruecolor($this->width, $this->height);   
		$newims = imagecreatetruecolor($new_img_width, $new_img_height);  
		//复制资源
		imagecopyresampled($newim, $im, 0, 0, $this->left, $this->top, $yimagewidth, $yimageheight, $yimagewidth, $yimageheight);
		imagecopyresampled($newims, $newim, 0, 0, 0, 0, $this->imagewidth, $this->imagewidth, $this->width, $this->height);
		//header('Content-Type: image/jpeg');
		//imagejpeg($newims); 
        
		imagejpeg($newims,$this->saveimage,100); 
		imagedestroy($newim); 
		imagedestroy($newims); 
		imagedestroy($im);
        /**/
	}
}
//$im = new Face("backup/pscb.jpg",220,220,295,95,80,"upload_pic/test.jpg");
?>