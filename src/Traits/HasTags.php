<?php
/**
 * Created by PhpStorm.
 * User: Ufuk
 * Date: 21.02.2017
 * Time: 22:16
 */

namespace Yaranliu\Tags\Traits;

use Illuminate\Support\Facades\Config;
use Yaranliu\Tags\Facades\Tags;

trait HasTags
{
    /**
     * To be read from Configuration File: Tags.php -> maxTags
     *
     * @var int
     */
    protected $maxTags = 30;

    /**
     * To be read from Configuration File: Tags.php -> requiresConfirmation
     *
     * @var bool
     */
    protected $newTagRequiresConfirmation = false;

    protected $className = null;

    public function instantiateHasTags()
    {
        $this->maxTags = Config::get('tags.entities.'.get_class().'.max', Config::get('tags.maxTags', 30));
        $this->newTagRequiresConfirmation = Config::get('tags.entities.'.get_class().'.requiresConfirmation', Config::get('tags.requiresConfirmation', false));
        $this->className = get_class();
    }

    public function newTagRequiresConfirmation($status = true)
    {
        $this->newTagRequiresConfirmation = $status;
        return $this;
    }

    public function addTag($tags)
    {
        return Tags::addTagToEntity($tags, get_class($this), $this->getKey(), $this->maxTags, $this->newTagRequiresConfirmation);
    }

    public function removeTag($tags)
    {
        return Tags::removeTagFromEntity($tags, get_class($this), $this->getKey());
    }

    public function tags()
    {
        return $this->morphToMany('Yaranliu\Tags\BaseTag', 'taggable', 'uy_taggables', 'taggable_id', 'tag_id');
    }

    public function pendingTags()
    {
        return $this->morphToMany('Yaranliu\Tags\BasePendingTag', 'taggable', 'uy_pending_taggables', 'taggable_id', 'pending_tag_id');
    }

    public function getTagsArrayAttribute()
    {
        return array_flatten($this->tags()->select('name')->get()->toArray());
    }

    public function getPendingTagsArrayAttribute()
    {
        return array_flatten($this->pendingTags()->select('name')->get()->toArray());
    }

}

