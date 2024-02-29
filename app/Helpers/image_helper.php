<?php

namespace App\Helpers;

use App\Entities\Image;
use App\Models\ImageModel;
use CodeIgniter\Files\File;
use Ramsey\Uuid\Uuid;

/**
 * @param $inputName string the name attribute value of the <input type="file" name="...">
 * @param int $maxFileSizeKb int the maximum file size of the image
 * @param bool $svgAllowed whether the svg format is allowed or not
 * @return array an image validation rule, ensuring it is a common image format
 * Docs: https://codeigniter.com/user_guide/libraries/validation.html?highlight=validation#rules-for-file-uploads
 *
 * Having omitted the 'uploaded[$inputName]' rule, it is optional that with the given input a file as been uploaded.
 * Size is limited to 2MB, which is also the `upload_max_filesize` of the Apache (see `/etc/php/8.2/apache2/php.ini`)
 */
function createImageValidationRule(string $inputName, int $maxFileSizeKb = 2000, bool $svgAllowed = false): array
{
    return [
        $inputName => [
            'label' => 'Image File',
            'rules' => [
                "is_image[$inputName]",
                "mime_in[$inputName,image/png,image/jpg,image/jpeg,image/gif,image/webp" . ($svgAllowed ? ',image/svg+xml' : '') . "]",
                "max_size[$inputName,$maxFileSizeKb]",
            ],
        ],
    ];
}

/**
 * @param File $file an image file of either one of the allowed formats (see above function)
 * @param string $outputDir
 * @param string $newName the new name of the file without extension
 * @param int $quality of the resulting image
 * @return void
 *
 * Convert the image to WEBP format and save it under the specified imageId
 * Docs: https://www.php.net/manual/de/function.exif-imagetype.php | https://www.php.net/manual/de/function.imagewebp.php
 */
function saveImage(File $file, string $author, int $quality = 100): string
{
    $imageId = Uuid::uuid4()->toString();
    insertImage($imageId, $author);

    $inputFilePath = $file->getTempName();

    // Create the output directory if it doesn't exist
    if (!is_dir(UPLOADED_IMAGES_DIR)) mkdir(UPLOADED_IMAGES_DIR);

    // If it's a logo file in SVG format, move it to its destination and quit
    if (mime_content_type($inputFilePath) == 'image/svg+xml') {
        rename($inputFilePath, UPLOADED_IMAGES_DIR . $imageId . '.svg');
        return $imageId;
    }

    $outputFilePath = UPLOADED_IMAGES_DIR . $imageId . '.webp';

    // Determine image format from Exif data
    $fileType = exif_imagetype($inputFilePath);

    // Load the image - or if it's a WEBP already just move it to its destination and quit
    switch ($fileType) {
        case IMAGETYPE_GIF:
            $image = imagecreatefromgif($file);
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
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
            rename($inputFilePath, $outputFilePath);
            return $imageId;
        default:
            return $imageId;
    }

    // Convert the image to WEBP and save (create/overwrite) it
    imagewebp($image, $outputFilePath, $quality);

    // Free up memory
    imagedestroy($image);

    return $imageId;
}

function getImageUrlById(?string $id, string $placeholder): string
{
    if (is_null($id)) {
        return base_url($placeholder);
    }

    $path = UPLOADED_IMAGES_DIR . $id . '.svg';
    if (is_file($path)) {
        return base_url(UPLOADED_IMAGE_URL . $id . '.svg');
    }

    $path = UPLOADED_IMAGES_DIR . $id . '.webp';
    if (is_file($path)) {
        return base_url(UPLOADED_IMAGE_URL . $id . '.webp');
    }

    return base_url($placeholder);
}

function getImageAuthorById(?string $id): string
{
    if (is_null($id)) {
        return "";
    }

    $image = getImageModel()->find($id);
    return $image->getAuthor();
}

function insertImage(string $id, string $author): void
{
    $image = new Image();
    $image->setId($id);
    $image->setAuthor($author);

    getImageModel()->insert($image);
}

function getImageModel(): ImageModel
{
    return new ImageModel();
}