<?php
namespace App\Core;
use App\Core\Exceptions\GDResizeException;

class ImageHandler{

  function resize($image)
  {
      return imagescale($image, IMAGE_UPLOAD_WIDTH, IMAGE_UPLOAD_HEIGHT);
  }

  public function handleUploadResizing($image_object){
    $path = $image_object['name'];
    $ext = pathinfo($path, PATHINFO_EXTENSION);

    switch($ext){
      case "jpg": return $this->createResizedJPEG($image_object);
      break;
      case "gif": return file_get_contents($image_object['tmp_name']); //TODO resize gifs...
      break;
      case "png": return $this->createResizedPNG($image_object);
      break;
      default: return null;
    }
  }

  private function createResizedPNG($image_object){
    $uploadedImage = imagecreatefrompng($image_object['tmp_name']);
    if (!$uploadedImage) {
        throw new GDResizeException();
    } else {
        $resizedImage = $this->resize($uploadedImage);
        ob_start();
        imagepng($resizedImage);
        return $rawImageBytes = ob_get_clean();
    }
  }

  private function createResizedJPEG($image_object){
    $uploadedImage = imagecreatefromjpeg($image_object['tmp_name']);
    if (!$uploadedImage) {
        throw new GDResizeException();
    } else {
        $resizedImage = $this->resize($uploadedImage);
        ob_start();
        imagejpeg($resizedImage);
        return $rawImageBytes = ob_get_clean();
    }
  }

  public function createImageFromText($text){
    // Begin capturing the byte stream
    $x1 = 0;
    $x2 = 0;
    $x3 = 300;
    $x4 = 300;
    $width = 300;
    $height = 300;

    ob_start();

    $img = imagecreate(IMAGE_UPLOAD_WIDTH, IMAGE_UPLOAD_HEIGHT);

    $background = imagecolorallocate($img, 54, 57, 64);
    $text_color = imagecolorallocate($img, 255, 255, 255);

    //Draw the background.
    imagefilledrectangle($img, 0, 0, 300, 300, $background);

    //Draw the text using the image and font size/text/color.
    imagestring($img, 20, 130, 150, $text , $text_color);

    //Save the image to the server.
    imagepng($img);

    $rawImageBytes = ob_get_clean();
    return $rawImageBytes;
  }
}
