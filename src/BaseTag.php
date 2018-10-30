<?php

namespace Yaranliu\Tags;

use Illuminate\Database\Eloquent\Model;

class BaseTag extends Model
{
    protected $table = 'uy_tags';

    protected $fillable = ['name'];

    protected $hidden = ['pivot'];

    public function taggables()
    {
        return $this->hasMany('Yaranliu\Tags\BaseTaggable', 'tag_id');
    }

}
