<?php

namespace App\core;

use App\Core\Exceptions\GDResizeException;
use GdImage;

/**
 * Image helper class for handling image resizing and modification.
 * @author Viggo Lagestedt Ekholm
 */
class ImageHandler
{

    /**
     * Resize a given image.
     * @param GdImage
     * @return GdImage|bool
     */
    function resize($image): GdImage|bool
    {
        return imagescale($image, IMAGE_UPLOAD_WIDTH, IMAGE_UPLOAD_HEIGHT);
    }

    /**
     * Check the file extension and choose the right resize option.
     * @param mixed
     * @return false|string|null
     * @throws GDResizeException
     */
    public function handleUploadResizing($image_object): bool|string|null
    {
        $path = $image_object['name'];
        $ext = pathinfo($path, PATHINFO_EXTENSION);

        return match ($ext) {
            "jpg" => $this->createResizedJPEG($image_object),
            "gif" => file_get_contents($image_object['tmp_name']),
            "png" => $this->createResizedPNG($image_object),
            default => null,
        };
    }

    /**
     * Creates a resized PNG image and returns it.
     * @param mixed
     * @return false|string
     * @throws GDResizeException
     */
    private function createResizedPNG($image_object): bool|string
    {
        $uploadedImage = imagecreatefrompng($image_object['tmp_name']);
        if (!$uploadedImage) {
            throw new GDResizeException();
        } else {
            $resizedImage = $this->resize($uploadedImage);
            ob_start();
            imagepng($resizedImage);
            return ob_get_clean();
        }
    }

    /**
     * Creates a resized JPEG image and returns it.
     * @param mixed
     * @return false|string
     * @throws GDResizeException
     */
    private function createResizedJPEG($image_object): bool|string
    {
        $uploadedImage = imagecreatefromjpeg($image_object['tmp_name']);
        if (!$uploadedImage) {
            throw new GDResizeException();
        } else {
            $resizedImage = $this->resize($uploadedImage);
            ob_start();
            imagejpeg($resizedImage);
            return ob_get_clean();
        }
    }

    /**
     * Creates a customized image with text using the GD graphics library.
     * @param string $text
     * @return false|string
     */
    public function createImageFromText(string $text): bool|string
    {
        ob_start();

        $img = imagecreate(IMAGE_UPLOAD_WIDTH, IMAGE_UPLOAD_HEIGHT);

        $background = imagecolorallocate($img, 54, 57, 64);
        $text_color = imagecolorallocate($img, 255, 255, 255);

        //Draw the background.
        imagefilledrectangle($img, 0, 0, 300, 300, $background);

        //Draw the text using the image and font size/text/color.
        imagestring($img, 20, 130, 150, $text, $text_color);

        //Save the image to the server.
        imagepng($img);

        return ob_get_clean();
    }
}
