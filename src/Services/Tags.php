<?php
/**
 * Created by PhpStorm.
 * User: Ufuk
 * Date: 01.03.2017
 * Time: 12:32
 */

namespace Yaranliu\Tags\Services;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

use Yaranliu\Tags\BaseTag as Tag;
use Yaranliu\Tags\BaseTaggable as Taggable;
use Yaranliu\Tags\BasePendingTag as PendingTag;
use Yaranliu\Tags\BasePendingTaggable as PendingTaggable;
use Yaranliu\Tags\Contracts\TagsContract;

class Tags implements TagsContract
{

    /**
     * Adds tags to entity. Entity is the exact class name of the corresponding model
     *
     * @param $tags
     * @param $entity
     * @param $id
     * @param int $max
     * @param bool $requires
     * @return array|\Illuminate\Support\Collection
     */
    public function addTagToEntity($tags, $entity, $id, $max = 30, $requires = true)
    {
        $result = array('success' => array(), 'pending' => array(), 'illegal' => array());
//        $tags = trim($tags);
        if (is_null($tags) || $tags == '') return $result;
        $tags = $this->cArray($tags);
        foreach ($tags as $tag) {
            {
                $tag = trim($tag);
                if (is_null($tag) || $tag === '') $return = ['status' => 'illegal', 'tag' => null];
                else {
                    $tag = $this->lowercase($tag);
                    if ($existingTag = Tag::where('name', $tag)->first()) {
                        if (Taggable::where(['taggable_type' => $entity, 'taggable_id' => $id])->count() > $max)
                            $return = ['status' => 'limit', 'tag' => $tag];
                        else {
                            $data = Taggable::firstOrCreate(
                                [
                                    'tag_id' => $existingTag->id,
                                    'taggable_type' => $entity,
                                    'taggable_id' => $id
                                ]
                            );
                            $data = array_add($data, 'name', $tag);
                            $return = ['status' => 'success', 'tag' => $data];
                        }
                    } else {
                        if (!$requires)
                        {
                            $newTag = Tag::firstOrCreate(['name' => $tag]);
                            Taggable::firstOrCreate(
                                [
                                    'tag_id' => $newTag->id,
                                    'taggable_type' => $entity,
                                    'taggable_id' => $id
                                ]
                            );
                            // Delete from PendingTaggables if a pending tag
                            if ($pendingTag = PendingTag::where('name', $tag)->first())
                            {
                                PendingTaggable::where([
                                        'pending_tag_id' => $pendingTag->id,
                                        'taggable_type' => $entity,
                                        'taggable_id' => $id])->delete();
                            }
                            $return = ['status' => 'success', 'tag' => $newTag];
                        }
                        else {
                            $pendingTag = PendingTag::firstOrCreate(['name' => $tag]);
                            $pendingTag ->hit = $pendingTag ->hit + 1;
                            $pendingTag ->save();
                            PendingTaggable::firstOrCreate(
                                [
                                    'pending_tag_id' => $pendingTag->id,
                                    'taggable_type' => $entity,
                                    'taggable_id' => $id
                                ]
                            );
                            $return = ['status' => 'pending', 'tag' => $pendingTag ];
                        }
                    }

                }
            }
            $result[$return['status']][] = $tag;
        }
        unset($result['illegal']);
        return collect($result);
    }

    /**
     * Removes tags from entity. Entity is the exact class name of the corresponding model
     * @param $tags
     * @param $entity
     * @param $id
     * @return array|\Illuminate\Support\Collection
     */
    public function removeTagFromEntity($tags, $entity, $id)
    {
        $result = array('success' => array(), 'not_found' => array(), 'illegal' => array());
//        $tags = trim($tags);
        if (is_null($tags) || $tags == '') return $result;
        $tags = $this->cArray($tags);
        foreach ($tags as $tag) {
            $tag = trim($tag);
            if (is_null($tag) || $tag === '') $removeTag = ['status' => 'illegal', 'tag' => null];
                else {
                    $tag = $this->lowercase($tag);
                    if ($existingTag = Tag::where('name', $tag)->first()) {
                        if ($data = Taggable::where(['tag_id' => $existingTag->id, 'taggable_type' => $entity, 'taggable_id' => $id])->delete())
                            $removeTag = ['status' => 'success', 'tag' => $data];
                        else $removeTag = ['status' => 'not_found', 'tag' => $tag];

                    } else $removeTag = ['status' => 'not-found', 'tag' => $tag];
                }
            $result[$removeTag['status']][] = $tag;
        }
        unset($result['illegal']);
        return collect($result);
    }

    /**
     * Adds tags to entity. Entity is the short-name found in the TAGS.PHP configuration file.
     * maxTags and requiresConfirmation options are retrieved from TAGS.PHP configuration file.
     *
     * @param $tags
     * @param $entity
     * @param $id
     * @return array|bool|\Illuminate\Support\Collection
     */
    public function tagEntity($tags, $entity, $id)
    {
        $a = Config::get('tags.entities', null);
        if (is_null($a)) return false;
        else {
            $index = array_search($entity, array_column($a,'name'));
            if (!is_bool($index)) {
                $entityClassName = array_keys($a)[$index];
                return $this->addTagToEntity($tags, $entityClassName, $id,
                    Config::get('tags.entities.'.$entityClassName.'.max', Config::get('tags.maxTags', 30)),
                    Config::get('tags.entities.'.$entityClassName.'.requiresConfirmation', Config::get('tags.requiresConfirmation', true))
                );
            }
            else return false;
        }
    }

    /**
     * Removes tags from entity. Entity is the short-name found in the TAGS.PHP configuration file.
     *
     * @param $tags
     * @param $entity
     * @param $id
     * @return array|bool|\Illuminate\Support\Collection
     */
    public function untagEntity($tags, $entity, $id)
    {
        $a = Config::get('tags.entities', null);
        if (is_null($a)) return false;
        else {
            $index = array_search($entity, array_column($a,'name'));
            if (!is_bool($index)) {
                $entityClassName = array_keys($a)[$index];
                return $this->removeTagFromEntity($tags, $entityClassName, $id);
            }
            else return false;
        }
    }

    /**
     * @param bool $withTaggables
     * @param string $sort
     * @param string $order
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAll($withTaggables = false, $sort = 'updated_at', $order = 'desc')
    {
        if ($withTaggables) return Tag::with('taggables')->orderBy($sort, $order)->get();
        else return Tag::orderBy($sort, $order)->get();
    }

    /**
     * @param bool $withTaggables
     * @param string $sort
     * @param string $order
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getPendingAll($withTaggables = false, $sort = 'updated_at', $order = 'desc')
    {
        if ($withTaggables) return PendingTag::with('taggables')->orderBy($sort, $order)->get();
        else return PendingTag::orderBy($sort, $order)->get();
    }

    /**
     * @param bool $withEntity
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAllTaggables($withEntity = false)
    {
        if ($withEntity) return Taggable::with('entity')->get();
        else return Taggable::get();
    }

    /**
     * @param bool $withEntity
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAllPendingTaggables($withEntity = false)
    {
        if ($withEntity) return PendingTaggable::with('entity')->get();
        else return Taggable::get();
    }

    /**
     * @param $entity
     * @param $id
     * @return bool
     */
    public function getByEntity($entity, $id)
    {
        $a = Config::get('tags.entities', null);
        if (is_null($a)) return false;
        else {
            $index = array_search($entity, array_column($a, 'name'));
            if (!is_bool($index)) {
                $entityClassName = array_keys($a)[$index];
                $tags = DB::table('uy_tags')
                    ->join('uy_taggables', function ($join) use($entityClassName, $id) {
                        $join->on('uy_tags.id', '=', 'uy_taggables.tag_id')
                            ->where('uy_taggables.taggable_type', '=', $entityClassName)
                            ->where('uy_taggables.taggable_id', '=', $id);
                    })->select('name')->get();
                return $tags;
            } else return false;
        }
    }

    /**
     * @param $entity
     * @param $id
     * @return bool
     */
    public function getPendingByEntity($entity, $id)
    {
        $a = Config::get('tags.entities', null);
        if (is_null($a)) return false;
        else {
            $index = array_search($entity, array_column($a, 'name'));
            if (!is_bool($index)) {
                $entityClassName = array_keys($a)[$index];
                $tags = DB::table('uy_pending_tags')
                    ->join('uy_pending_taggables', function ($join) use($entityClassName, $id) {
                        $join->on('uy_pending_tags.id', '=', 'uy_pending_taggables.pending_tag_id')
                            ->where('uy_pending_taggables.taggable_type', '=', $entityClassName)
                            ->where('uy_pending_taggables.taggable_id', '=', $id);
                    })->select('name')->get();
                return $tags;
            } else return false;
        }
    }

    /**
     * @param $tag
     * @return mixed
     */
    public function isPending($tag)
    {
        return PendingTag::where('name', $tag)->first();
    }

    /**
     * @param $tag
     * @return mixed
     */
    public function isConfirmed($tag)
    {
        return Tag::where('name', $tag)->first();
    }

    /**
     * @param $tags
     * @return \Illuminate\Support\Collection
     */
    public function confirm($tags)
    {
        $tags = $this->cArray($tags);
        $created = array();
        foreach ($tags as $tagToCheck) {
            $tag = $this->lowercase($tagToCheck);
            if ($pendingTag = PendingTag::where('name', $tag)->first()) {
                $confirmedTag = Tag::firstOrCreate(['name' => $pendingTag->name]);
                $i = 0;
                foreach ($pendingTag->taggables as $pendingTaggable) {
                    Taggable::firstOrCreate(
                        [
                            'tag_id' => $confirmedTag->id,
                            'taggable_type' => $pendingTaggable->taggable_type,
                            'taggable_id' => $pendingTaggable->taggable_id
                        ]
                    );
                    $i++;
                }
                $created[] = ['name' => $confirmedTag->name, 'count' => $i];
                $pendingTag->delete();
            }
        }
        return collect($created);
    }

    /**
     * @param $tags
     * @return \Illuminate\Support\Collection
     */
    public function revert($tags)
    {
        $tags = $this->cArray($tags);
        $reverted = array();
        foreach ($tags as $tagToCheck) {
            $tag = $this->lowercase($tagToCheck);
            if ($confirmedTag = Tag::where('name', $tag)->first()) {
                $pendingTag = PendingTag::firstOrCreate(['name' => $confirmedTag->name]);
                $i = 0;
                foreach ($confirmedTag->taggables as $taggable) {
                    PendingTaggable::firstOrCreate(
                        [
                            'pending_tag_id' => $pendingTag->id,
                            'taggable_type' => $taggable->taggable_type,
                            'taggable_id' => $taggable->taggable_id,
                        ]
                    );
                    $i++;
                }
                $pendingTag->hit = $i;
                $pendingTag->save();
                $reverted[] = ['name' => $pendingTag->name, 'count' => $i];
                $confirmedTag->delete();
            }
        }

        return collect($reverted);
    }

    /**
     * @param $tags
     * @return \Illuminate\Support\Collection
     */
    public function delete($tags)
    {
        $tags = $this->cArray($tags);
        $deleted = [];
        foreach ($tags as $tag) {
            if ((Tag::where('name', $tag)->delete()) || (PendingTag::where('name', $tag)->delete()))
                $deleted[] = $tag;
        }
        return collect($deleted);
    }

    public function deleteConfirmed($tags)
    {
        $tags = $this->cArray($tags);
        $deleted = [];
        foreach ($tags as $tag) {
            if (Tag::where('name', $tag)->delete())
                $deleted[] = $tag;
        }
        return collect($deleted);
    }

    public function deletePending($tags)
    {
        $tags = $this->cArray($tags);
        $deleted = [];
        foreach ($tags as $tag) {
            if (PendingTag::where('name', $tag)->delete())
                $deleted[] = $tag;
        }
        return collect($deleted);
    }


    /**
     * @param $like
     * @param string $type
     * @param string $array
     * @return array|null
     */
    public function like($like, $type = 'confirmed', $array = 'plain')
    {
        $like = $this->lowercase(trim($like));
        if (strlen($like) < 3) return null;
        else
            if (!in_array($type, ['confirmed', 'pending', 'both'])) $type = 'confirmed';
            if (!in_array($array, ['plain', 'keyed'])) $array = 'plain';
            $confirmed = (in_array($type, ['confirmed', 'both'])) ?
                (Tag::select('name')->where('name', 'like', '%'.$like.'%')->take(1000)->get()->toArray()) : [];
            $pending = (in_array($type, ['pending', 'both'])) ?
                (PendingTag::select('name')->where('name', 'like', '%'.$like.'%')->take(1000)->get()->toArray()) : [];
            if ($array == 'plain')
                return array_flatten(array_merge($confirmed, $pending));
            else {
                $return = [];
                if (!empty($confirmed)) array_add($return, 'confirmed', $confirmed);
                if (!empty($pending)) array_add($return, 'pending', $pending);

            }
        return ['confirmed' => array_flatten($confirmed), 'pending' => array_flatten($pending)];
    }

    /**
     * @param $match
     * @param bool $withTaggables
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    public function match($match, $withTaggables = false)
    {
        $match = $this->lowercase(trim($match));
        if ($withTaggables) return Tag::with('taggables')->where('name', $match)->first();
        else return Tag::where('name', $match)->first();
    }

    /**
     * @param $match
     * @param bool $withTaggables
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    public function matchPending($match, $withTaggables = false)
    {
        $match = $this->lowercase(trim($match));
        if ($withTaggables) return PendingTag::with('taggables')->where('name', $match)->first();
        else return PendingTag::where('name', $match)->first();
    }

    public function search($search)
    {
        if (is_null($search)) return null;
        $searchItems = $this->cExplode($search);
        $builder = Tag::select('*');
        foreach ($searchItems as $item) {
            $builder = $builder->orWhere('name', 'like', '%'.$item.'%');
        }
        return $builder->take(1000)->get();
    }

    public function searchPending($search)
    {
        if (is_null($search)) return null;
        $searchItems = $this->cExplode($search);
        $builder = PendingTag::select('*');
        foreach ($searchItems as $item) {
            $builder = $builder->orWhere('name', 'like', '%'.$item.'%');
        }
        return $builder->take(1000)->get();
    }

    public function cArray($input)
    {
        if (is_null($input)) return array();
        if (is_array($input)) return $input;
        if (is_string($input)) return explode(Config::get('tags.delimiter', '|'), $input);
        else return array($input);
    }

    public function tr_strtolower($text)
    {
        return mb_strtolower(str_replace('I', 'ı', $text), 'UTF-8');
    }

    public function tr_strtoupper($text)
    {
        return mb_strtoupper(str_replace('i', 'İ', $text), 'UTF-8');
    }

    public function lowercase($text)
    {
        if (App::isLocale('tr')) return $this->tr_strtolower($text);
        else return mb_strtolower($text);
    }

    public function uppercase($text)
    {
        if (App::isLocale('tr')) return $this->tr_strtoupper($text);
        else return mb_strtoupper($text);
    }

    public function cExplode($input)
    {
        $input = (is_string($input)) ? explode(Config::get('tags.delimiter', '|'), $input) : $input;
        return $input;
    }
}