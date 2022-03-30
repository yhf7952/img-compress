<?php
    set_time_limit(0);
    //找到所有目录
    function selectdir($dir, $level=0,$return=[]) {
        if(!is_dir($dir)){
            return array($dir);
        }
        if(empty($return)){
            $return[] = $dir;   
        } 
         //首先先读取文件夹
        $temp=scandir($dir);
        $level++;
        //遍历文件夹
        foreach($temp as $v){
            $a=$dir.'/'.$v;
            if(is_dir($a)){//如果是文件夹则执行
                if($v=='.' || $v=='..'){//判断是否为系统隐藏的文件.和..  如果是则跳过
                   continue;
                }
                //echo $a,"<br/>";
                $return[] = $a;
                $return = $return + selectdir($a, $level, $return);//因为是文件夹所以再次调用 selectdir，把这个文件夹下的文件遍历出来
            }
        } 
        return $return;
    }

    function endsWith($string, $endString)
    {
        $string = strtolower($string);
        $len = strlen($endString);
        if ($len == 0) {
            return true;
        }
        return (substr($string, -$len) === $endString);
    }
    function startsWith ($string, $startString)
    {
        $len = strlen($startString);
        return (substr($string, 0, $len) === $startString);
    }

    function selectfiles($dir) {
        //首先先读取文件
       $temp=scandir($dir);
       $return = [];
       //遍历文件夹
       foreach($temp as $v){
           $aa = $dir.'/'.$v;
           $a=strtolower($aa);
           $extList = [".jpg",".jpeg",".png",".gif",".mp4"];
           $i=0; $hasImg=false;
           while($i < count($extList) && !$hasImg)
           {
                $hasImg = !startsWith($v,".") && endswith($a,$extList[$i]);
                $i++;
           }
           //不是文件夹，且包含图片文件
           if(!is_dir($a) && $hasImg){
                $return[] = $aa;
           }
       }
       return $return;
   }


    /**
    * 图片压缩类：通过缩放来压缩。
    * 如果要保持源图比例，把参数$percent保持为1即可。
    * 即使原比例压缩，也可大幅度缩小。数码相机4M图片。也可以缩为700KB左右。如果缩小比例，则体积会更小。
    *
    * 结果：可保存、可直接显示。
    */
    class imgcompress{
        private $src;
        private $image;
        private $imageinfo;
        private $percent = 0.5;
        /**
        * 图片压缩
        * @param $src 源图
        * @param float $percent 压缩比例
        */
        public function __construct($src, $percent=1)
        {
        $this->src = $src;
        $this->percent = $percent;
        }
        /** 高清压缩图片
        * @param string $saveName 提供图片名（可不带扩展名，用源图扩展名）用于保存。或不提供文件名直接显示
        */
        public function compressImg($saveName='')
        {
            $sourseSize;
            $compressSize;
            try{
                $this->_openImage();
                $sourseSize = filesize($this->src);
            }catch(Exception $e){

            }
            if(!empty($saveName)){
                $this->_saveImage($saveName); //保存
                $compressSize = filesize($saveName);
                if($compressSize > $sourseSize){
                    copy($this->src, $saveName);
                }
            } 
            else{
                $this->_showImage();
            }
        }
        /**
        * 内部：打开图片
        */
        private function _openImage()
        {
            list($width, $height, $type, $attr) = getimagesize($this->src);
            $this->imageinfo = array(
                'width'=>$width,
                'height'=>$height,
                'type'=>image_type_to_extension($type,false),
                'attr'=>$attr
            );
            $fun = "imagecreatefrom".$this->imageinfo['type'];
            $this->image = $fun($this->src);
            if($this->imageinfo['type']!="gif"){
                $this->_thumpImage();
            }
        }
        /**
        * 内部：操作图片
        */
        private function _thumpImage()
        {
            //writeLog("------【开始压缩】--------");
            $new_width = $this->imageinfo['width'] * $this->percent;
            $new_height = $this->imageinfo['height'] * $this->percent;

            // 设置最大宽高
            $width = 2000;
            $height = 2000;
            // 获取新尺寸
            if($new_width > $width || $new_height> $height){
                $ratio_orig = $new_width/$new_height;
                if ($width/$height > $ratio_orig) {
                    $new_width = $height*$ratio_orig;
                    $new_height = 2000;
                } else {
                    $new_height = $width/$ratio_orig;
                    $new_width = 2000;
                }
            }

            $image_thump = imagecreatetruecolor($new_width,$new_height);
            //writeLog("------【压缩1】--------");
            //将原图复制带图片载体上面，并且按照一定比例压缩,极大的保持了清晰度
            imagecopyresampled($image_thump,$this->image,0,0,0,0,$new_width,$new_height,$this->imageinfo['width'],$this->imageinfo['height']);
            //writeLog("------【压缩2】--------");
            imagedestroy($this->image);
            $this->image = $image_thump;

            //writeLog("------【结束压缩】--------");
        }
        /**
        * 输出图片:保存图片则用saveImage()
        */
        private function _showImage()
        {
            header('Content-Type: image/'.$this->imageinfo['type']);
            if($this->imageinfo['type']=="gif"){
                imagegif($this->image);
            }else{
                $funcs = "image".$this->imageinfo['type'];
                $funcs($this->image);
            }
        }
        /**
        * 保存图片到硬盘：
        * @param string $dstImgName 1、可指定字符串不带后缀的名称，使用源图扩展名 。2、直接指定目标图片名带扩展名。
        */
        private function _saveImage($dstImgName)
        {
            if(empty($dstImgName)) return false;
            $allowImgs = ['.jpg', '.jpeg', '.png', '.bmp', '.wbmp','.gif']; //如果目标图片名有后缀就用目标图片扩展名 后缀，如果没有，则用源图的扩展名
            $dstExt = strrchr($dstImgName ,".");
            $sourseExt = strrchr($this->src ,".");
            if(!empty($dstExt)) $dstExt =strtolower($dstExt);
            if(!empty($sourseExt)) $sourseExt =strtolower($sourseExt);
            //有指定目标名扩展名
            if(!empty($dstExt) && in_array($dstExt,$allowImgs)){
                $dstName = $dstImgName;
            }elseif(!empty($sourseExt) && in_array($sourseExt,$allowImgs)){
                $dstName = $dstImgName.$sourseExt;
            }else{
                $dstName = $dstImgName.$this->imageinfo['type'];
            }
            $funcs = "image".$this->imageinfo['type'];
            $funcs($this->image,$dstName);
        }
        /**
        * 销毁图片
        */
        public function __destruct(){
            imagedestroy($this->image);
        }
    }

    function udate($format = 'u', $utimestamp = null)
    {
        if (is_null($utimestamp)){
            $utimestamp = microtime(true);
        }
        $timestamp = floor($utimestamp);
        $milliseconds = round(($utimestamp - $timestamp) * 1000);//改这里的数值控制毫秒位数
        return date(preg_replace('`(?<!\\\\)u`', $milliseconds, $format), $timestamp);
    }
    function writeLog($text){
        print_r(udate('Y-m-d H:i:s.u')."$text\r\n");
    }

    function isImage($imgPath){
        $types = '.gif|.jpeg|.png|.bmp'; //定义检查的图片类型
        if(file_exists($imgPath)){
            if($info = getimagesize($imgPath)) return 0;
            $ext = image_type_to_extension($info['2']);
            return stripos($types, $ext);
        }else {
          return false;
        }
    }

    //记个日志
    writeLog("------【开始】--------");

    $path = "/from";
    
    //var_dump(scandir($path));
    //$path = "./img";
    //获取所有目录
    $dirs = [];

    $dirs = selectdir($path);
    writeLog("读取目录成功");

    foreach ($dirs as $key => $dir) {
        //取图片
        $imgs = selectfiles($dir);
        foreach ($imgs as $ke => $img) {  
            try{          
                $save=preg_replace('/^\/from/','/to',$img);  
                $saveDir = substr($save,0,strripos($save,"/"));
                
                if(file_exists($save)) continue;

                //保存位置
                if (!file_exists($saveDir)){
                    writeLog("新建文件夹：$saveDir");
                    mkdir ($saveDir,0777,true);
                }
                
                if(isImage($img)!==false){
                    
                    $percent = 1; #原图压缩，不缩放，但体积大大降低
                             
                    $image = (new imgcompress($img,$percent))->compressImg($save);
                    writeLog("处理完成：$save");
                    
                }else{                    
                    copy($img,$save);
                    writeLog("未处理：$img");
                }
            } catch (Exception $e) {
                writeLog("处理异常：".$e->getMessage());
                continue;
            }
        }
       
    }

    writeLog("------【结束】--------");

?>