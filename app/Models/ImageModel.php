<?php

namespace App\Models;

use App\Entities\Image;
use CodeIgniter\Model;

class ImageModel extends Model
{
    protected $table = IMAGES;
    protected $primaryKey = "id";
    protected $returnType = Image::class;

    protected $allowedFields = [
        'id', 'author'
    ];
}


