<?php
/**
 * Created by PhpStorm.
 * User: Ufuk
 * Date: 28.02.2017
 * Time: 04:49
 */

namespace Yaranliu\Tags;

use Illuminate\Database\Eloquent\Model;

class BasePendingTag extends Model
{
    protected $table = 'uy_pending_tags';

    protected $fillable = ['name'];

    protected $hidden = ['pivot'];

    public function taggables()
    {
        return $this->hasMany('Yaranliu\Tags\BasePendingTaggable', 'pending_tag_id');
    }

}