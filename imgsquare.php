<?php 
class cropImage{
var $imgSrc,$myImage,$cropHeight,$cropWidth,$x,$y,$thumb;  
function setImage($image)
{

//Your Image
   $this->imgSrc = $image; 
                     
//getting the image dimensions
   list($this->width, $this->height) = getimagesize($this->imgSrc); 
                     
//create image from the jpeg
   if ($this->myImage = @imagecreatefromjpeg($this->imgSrc) ){
            
       if($this->width > $this->height) $biggestSide = $this->width; //find biggest length
       else $biggestSide = $this->height; 

//The crop size will be half that of the largest side 
   $cropPercent = .5; // This will zoom in to 50% zoom (crop)
   $this->cropWidth   = $biggestSide*$cropPercent; 
   $this->cropHeight  = $biggestSide*$cropPercent; 

//getting the top left coordinate
   $this->x = ($this->width-$this->cropWidth)/2;
   $this->y = ($this->height-$this->cropHeight)/3;
   }else{
   $this->myImage ='http://g-ecx.images-amazon.com/images/G/01/x-locale/detail/thumb-no-image._V47060337_.gif';
   }  
}  

function createThumb($thsize)
{
  if ($this->myImage !='http://g-ecx.images-amazon.com/images/G/01/x-locale/detail/thumb-no-image._V47060337_.gif') {                
  $thumbSize = $thsize; // will create a 250 x 250 thumb
  $this->thumb = imagecreatetruecolor($thumbSize, $thumbSize); 
imagecopyresampled($this->thumb, $this->myImage, 0, 0,$this->x, $this->y, $thumbSize, $thumbSize, $this->cropWidth, $this->cropHeight); 
  //imagecopy($this->thumb, $this->myImage, 0, 0,$this->x-32, $this->y-32, $this->width, $this->height); 
  }
}  

function renderImage($thsize)
{
if ($this->myImage !='http://g-ecx.images-amazon.com/images/G/01/x-locale/detail/thumb-no-image._V47060337_.gif') {                
  
	$prodid=$_GET['prodid'];
   header('Content-type: image/jpeg');
   imagejpeg($this->thumb,$_SERVER['DOCUMENT_ROOT']."/wp-content/cache/".$prodid."-".$thsize.".jpg");
   imagedestroy($this->thumb); 
   $loc="http://".$_SERVER["SERVER_NAME"]."/wp-content/cache/".$prodid."-".$thsize.".jpg";
	header('Location: '.$loc);
	}else {
	header('Location: http://g-ecx.images-amazon.com/images/G/01/x-locale/detail/thumb-no-image._V47060337_.gif');
	}
}
}  


$src=$_GET['imgurl'];
$prodid=$_GET['prodid'];
$thsize=$_GET['thsize'];
$phlink=$_SERVER['DOCUMENT_ROOT']."/wp-content/cache/".$prodid."-".$thsize.".jpg";

if (file_exists($phlink)){
$loc="http://".$_SERVER["SERVER_NAME"]."/wp-content/cache/".$prodid."-".$thsize.".jpg";
header('Location: '.$loc);
}else{

$image = new cropImage;
$image->setImage($src);
$image->createThumb($thsize);
$image->renderImage($thsize);  
}