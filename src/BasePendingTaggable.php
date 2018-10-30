<?php
/**
 * Created by PhpStorm.
 * User: Ufuk
 * Date: 28.02.2017
 * Time: 04:33
 */

namespace Yaranliu\Tags;

use Illuminate\Database\Eloquent\Model;

class BasePendingTaggable extends Model
{
    protected $table = 'uy_pending_taggables';

    protected $fillable = ['pending_tag_id', 'taggable_id', 'taggable_type'];
}