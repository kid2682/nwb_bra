<?php
class verifyAction extends Action{
	//验证码
    function verify(){
        import("ORG.Util.Image");
        Image::buildImageVerify();
    }
}
?>