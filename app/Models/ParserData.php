<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParserData extends Model
{
    /**
     * @var string Table name
     */
    protected $table  = 'parser_data';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'url',
        'img_tag_count',
        'page_processing_time',
    ];


}
