<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AanvraagTaskQuestion extends Model
{
    protected $table = 'aanvraag_task_questions';

    protected $fillable = [
        'aanvraag_task_id',
        'question',
        'answer',
        'required',
        'order',
    ];

    public function task()
    {
        return $this->belongsTo(AanvraagTask::class, 'aanvraag_task_id');
    }
}
