<?php

namespace App\Models;

use App\Entities\Organisation;
use App\Entities\Mail;
use CodeIgniter\Model;

class MailModel extends Model
{
    protected $table = MAILS;
    protected $primaryKey = "id";
    protected $returnType = Mail::class;

    protected $allowedFields = [
        'recipient_id', 'subject', 'body'
    ];
}


