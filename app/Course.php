<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $appends = ['is_video', 'is_pdf'];
    protected $fillable = [
        'user_id',
        'name',
        'image',
        'video',
        'pdf',
        'details',
        'price',
    ];

    protected $hidden = [
        'video', 'pdf',
    ];

    
    public function getIsVideoAttribute()
    {
        if($this->video != null){
            return 1;
        }else{
            return 0;
        }
    }

    public function getIsPdfAttribute()
    {
        if($this->pdf != null){
            return 1;
        }else{
            return 0;
        }
    }
}
