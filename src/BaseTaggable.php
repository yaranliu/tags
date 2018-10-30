<?php
/**
 * Created by PhpStorm.
 * User: Ufuk
 * Date: 28.02.2017
 * Time: 04:33
 */

namespace Yaranliu\Tags;

use Illuminate\Database\Eloquent\Model;

class BaseTaggable extends Model
{
    protected $table = 'uy_taggables';

    protected $fillable = ['tag_id', 'taggable_id', 'taggable_type'];

    public function entity()
    {
        return $this->morphTo('taggable');
    }

    public function tag()
    {
        return $this->belongsTo('Yaranliu\Tags\BaseTag', 'tag_id');
    }
}