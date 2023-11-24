<?php

namespace App\Helpers;

use CodeIgniter\Files\File;

/**
 * @param $inputName string the name attribute value of the <input type="file" name="...">
 * @return array an image validation rule, ensuring it is a common image format
 * Docs: https://codeigniter.com/user_guide/libraries/validation.html?highlight=validation#rules-for-file-uploads
 *
 * Having omitted the 'uploaded[$inputName]' rule, it is optional that with the given input a file as been uploaded.
 * Size is limited to 2MB, which is also the `upload_max_filesize` of the Apache (see `/etc/php/8.2/apache2/php.ini`)
 */
function createImageValidationRule(string $inputName, int $maxFileSizeKb = 2000, int $maxWidth = 3840, int $maxHeight = 2160): array
{
    return [
        $inputName => [
            'label' => 'Image File',
            'rules' => [
                "is_image[$inputName]",
                "mime_in[$inputName,image/png,image/jpg,image/jpeg,image/gif,image/webp]",
                "max_size[$inputName,$maxFileSizeKb]",
                "max_dims[$inputName,$maxWidth,$maxHeight]",
            ],
        ],
    ];
}

/**
 * @param File $file an image file of either one of the allowed formats (see above function)
 * @param string $outputPath
 * @param int $compressionQuality
 * @return void
 *
 * Convert the image to WEBP format and save it under the specified outputPath
 * Docs: https://www.php.net/manual/de/function.exif-imagetype.php | https://www.php.net/manual/de/function.imagewebp.php
 */
function convertToWebp(File $file, string $outputPath, int $compressionQuality = 90): void
{
    $filePath = $file->getTempName();
    $fileType = exif_imagetype($filePath);

    switch ($fileType) {
        case IMAGETYPE_GIF:
            $image = imagecreatefromgif($file);
            break;
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($file);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($file);
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
            break;
        case IMAGETYPE_WEBP:
            $image = imagecreatefromwebp($file);
            break;
    }

    // Convert the image to Webp and save (create/overwrite) it
    imagewebp($image, $outputPath, $compressionQuality);

    // Free up memory
    imagedestroy($image);
}