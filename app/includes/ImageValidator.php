<?php


namespace App\includes;


use Google\ApiCore\ApiException;
use Google\ApiCore\ValidationException;
use JetBrains\PhpStorm\ArrayShape;
use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use Google\Cloud\Vision\V1\Feature\Type;
use Google\Cloud\Vision\V1\Likelihood;

class ImageValidator
{
    /**
     * This method checks if a file has a valid extension and file size.
     * @param string $global
     * @return array
     * @throws ApiException
     * @throws ValidationException
     */
    #[ArrayShape(['adult' => "int", 'medical' => "int", 'spoof' => "int", 'violence' => "int", 'racy' => "int"])]
    public static function checkImageForFeatures(string $global): array
    {
        $imageResource = file_get_contents( $_FILES['file']['tmp_name']);
        $image = base64_encode($imageResource);

        putenv("GOOGLE_APPLICATION_CREDENTIALS=C:/xampp/htdocs/UniShare/auth.json");

        $client = new ImageAnnotatorClient();

        $annotation = $client->annotateImage(
            fopen("C:/xampp/htdocs/UniShare/images/books.png", "r"),
            [Type::SAFE_SEARCH_DETECTION]
        );

        $safeSearch = $annotation->getSafeSearchAnnotation();

        return [
            'adult' => $safeSearch->getAdult(),
            'medical' => $safeSearch->getMedical()
        ];
    }

    /**
     * This method checks if a valid image extension has been uploaded by the user.
     * @param string $global
     * @return bool
     */
    public static function hasValidImageExtension(string $global): bool
    {
        $result = false;
        if(isset( $_FILES[$global])){
            $fileType = $_FILES[$global]['type'];

            $allowed = array("image/jpeg", "image/gif", "image/png");
            if (in_array($fileType, $allowed)) {
                $result = true;
            } else {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * This method checks if a file has been uploaded by the user.
     * @param mixed
     * @return bool
     */
    public static function hasValidUpload(string $global): bool
    {
        if(isset($_FILES[$global])){
            $file = $_FILES[$global]['tmp_name'];
            if (!file_exists($file) || !is_uploaded_file($file)) {
                return false;
            }
        }

        return true;
    }
}