<?php
namespace Rmcc;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;

class Resize {

  private $imagine;
  
  private $w, $h, $crop;

  public function __construct($w, $h, $crop) {
    $this->imagine = new Imagine();
    $this->w = $w;
    $this->h = $h;
    $allowed_crop_positions = array(
      'default', 'center', 'top', 'bottom', 'left', 'right', 'top-center', 'bottom-center'
    );
    if ( $crop !== false && !in_array($crop, $allowed_crop_positions) ) {
      $crop = $allowed_crop_positions[0];
    }
    $this->crop = $crop;
  }
  
  /**
  * @param string $src_filename the basename of the file (ex: my-awesome-pic.jpg) (include extension for now)
  * @param string $src_extension the extension (ex: .jpg)
  * @return string the new filename to be used (ex: my-awesome-pic-300x200_default.jpg)
  *
  * edit to allow for external urls being used & saved etc
  */
  public function filename($src_filename, $src_extension) {
    
    $new_filename = '';
    
    $width_string = round($this->w, 0);
    if(!$this->h) {
      $new_string = '-'.$width_string;
    } else {
      $height_string = round($this->h, 0);
      $new_string = '-'.$width_string.'x'.$height_string.'_'.$this->crop;
    }
    
    $new_filename = $src_filename.$new_string.'.'.$src_extension;
    
    return $new_filename;
  }
  
  public function run($load_filename, $save_filename) {
    
    if(!file_exists($load_filename)) return;
    
    list($iwidth, $iheight) = getimagesize($load_filename); // get some data from original img with getimagesize() & save as variables using list()
    $ratio = $iwidth / $iheight; // original img ratio
    $photo = $this->imagine->open($load_filename); // open the given file
    
    // no height given; maintain aspect ratio
    if(!$this->h) {
      $height = $this->w / $ratio;
      $new_photo = $photo->resize(new Box($this->w, $height)); // resize the file using the new width & height... (maintain aspect)
    } 
    
    // height given, cropped according to $allowed_crop_positions
    if($this->h) {
      
      switch ($this->crop) {
        case 'top':
          $crop_x = $iwidth / 2 - $this->w / 2;
          $crop_y = 0;
          break;

        case 'bottom':
          $crop_x = $iwidth / 2 - $this->w / 2;
          $crop_y = $iheight - $this->h;
          break;

        case 'top-center':
          $crop_x = $iwidth / 2 - $this->w / 2;
          $crop_y = round(($iheight - $this->h) / 4);
          break;

        case 'bottom-center':
          $crop_x = $iwidth / 2 - $this->w / 2;
          $crop_y = $iheight - $this->h - round(($iheight - $this->h) / 4);
          break;

        case 'left':
          $crop_x = 0;
          $crop_y = ($iheight - $this->h) / 6;
          break;

        case 'right':
          $crop_x = $iwidth - $this->w;
          $crop_y = ($iheight - $this->h) / 6;
          break;

        default:
          $crop_x = round(($iwidth - $this->w) / 2);
          $crop_y = round(($iheight - $this->h) / 2);
      }
      $new_photo = $photo->crop(new Point($crop_x, $crop_y), new Box($this->w, $this->h));
    }
    
    $options = array(
      'resolution-units' => ImageInterface::RESOLUTION_PIXELSPERINCH,
      'resolution-x' => 300,
      'resolution-y' => 300,
      'jpeg_quality' => 100,
      'png_compression_level' => 1,
      'webp_quality' => 100,
      'resampling-filter' => ImageInterface::FILTER_LANCZOS,
    );
    
    $new_photo->save($save_filename, $options); // then save it with the new filename
  }
}