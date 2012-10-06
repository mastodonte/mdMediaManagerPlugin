<?php

/*
 * This file is part of the sfImageTransform package.
 * (c) 2007 Stuart Lowes <stuart.lowes@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * sfImageResizeSimple class.
 *
 * Resizes image.
 *
 * Resizes the image to the set size.
 *
 * @package sfImageTransform
 * @subpackage transforms
 * @author Stuart Lowes <stuart.lowes@gmail.com>
 * @version SVN: $Id$
 */
class sfImageResizecropGD extends sfImageTransformAbstract {

  /**
   * Image width.
   * var integer width of the image is to be reized to
   */
  protected $width = 0;

  /**
   * Image height.
   * var integer height of the image is to be reized to
   */
  protected $height = 0;

  /**
   * Construct an sfImageCrop object.
   *
   * @param integer
   * @param integer
   */
  public function __construct($width, $height) {
    $this->setWidth($width);
    $this->setHeight($height);
  }

  /**
   * Set the images new width.
   *
   * @param integer
   */
  public function setWidth($width) {
    $this->width = (int) $width;
  }

  /**
   * Gets the images new width
   *
   * @return integer
   */
  public function getWidth() {
    return $this->width;
  }

  /**
   * Set the images new height.
   *
   * @param integer
   */
  public function setHeight($height) {
    $this->height = (int) $height;
  }

  /**
   * Gets the images new height
   *
   * @return integer
   */
  public function getHeight() {
    return $this->height;
  }

  /**
   * Apply the transform to the sfImage object.
   *
   * @param sfImage
   * @return sfImage
   */
  protected function transform(sfImage $image) {
    $resource = $image->getAdapter()->getHolder();

    $w = imagesx($resource);
    $h = imagesy($resource);

    // If the width or height is not valid then enforce the aspect ratio
    if (!is_numeric($this->width) || $this->width < 1) {
      $this->width = round(($x / $y) * $this->height);
    } else if (!is_numeric($this->height) || $this->height < 1) {
      $this->height = round(($y / $x) * $this->width);
    }

    //$dest_resource = $image->getAdapter()->getTransparentImage($this->width, $this->height);    

    $width = $this->width;
    $height = $this->height;

    if (($w == $width) && ($h == $height)) {
      return $image;
    } //no resizing needed

    //try max width first...
    $ratio = $width / $w;
    $new_w = $width;
    $new_h = $h * $ratio;

    //if that created an image smaller than what we wanted, try the other way
    if ($new_h < $height) {
      $ratio = $height / $h;
      $new_h = $height;
      $new_w = $w * $ratio;
    }

    $image2 = imagecreatetruecolor($new_w, $new_h);
    
    // Preserving transparency for alpha PNGs
    if($image->getMIMEType() == 'image/png')
    {
      imagealphablending($image2, false);
      imagesavealpha($image2, true);
    }
    
    imagecopyresampled($image2, $resource, 0, 0, 0, 0, $new_w, $new_h, $w, $h);

    //check to see if cropping needs to happen
    if (($new_h != $height) || ($new_w != $width)) {
      
      $image3 = imagecreatetruecolor($width, $height);
      
      if ($new_h > $height) { //crop vertically
        $extra = $new_h - $height;
        $x = 0; //source x
        $y = round($extra / 2); //source y
        imagecopyresampled($image3, $image2, 0, 0, $x, $y, $width, $height, $width, $height);
      } else {
        $extra = $new_w - $width;
        $x = round($extra / 2); //source x
        $y = 0; //source y
        imagecopyresampled($image3, $image2, 0, 0, $x, $y, $width, $height, $width, $height);
      }
      
      imagedestroy($image2);
      
      $result_image = $image3;

    } else {
      
      $result_image = $image2;
      
    }
    
    $image->getAdapter()->setHolder($result_image);
    
    return $image;
  }

}
