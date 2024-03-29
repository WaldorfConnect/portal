<?php

namespace App\Models;

use App\Entities\Mail;
use App\Entities\Notification;
use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table = NOTIFICATIONS;
    protected $primaryKey = "id";
    protected $returnType = Notification::class;

    protected $allowedFields = [
        'user_id', 'subject', 'body', 'created_at', 'read_at', 'mail_at'
    ];
}


