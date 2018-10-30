<?php
/**
 * Created by PhpStorm.
 * User: Ufuk
 * Date: 28.02.2017
 * Time: 08:00
 */

namespace Yaranliu\Tags\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Yaranliu\Tags\Facades\Tags;

trait TagsControllerTrait
{
    public function tagEntity(Request $request, $entity, $id)
    {
        $data = Tags::tagEntity($request->input('tags'), $entity, $id);
        return response()->json($data);
    }

    public function untagEntity(Request $request, $entity, $id)
    {
        $data = Tags::untagEntity($request->input('tags'), $entity, $id);
        return response()->json($data);
    }

    public function confirm(Request $request)
    {
        $data = Tags::confirm($request->input('tags'));
        return response()->json(['confirmed' => $data]);
    }

    public function revert(Request $request)
    {
        $data = Tags::revert($request->input('tags'));
        return response()->json(['reverted' => $data]);
    }

    public function delete(Request $request)
    {
        $data = Tags::delete($request->input('tags'));
        return response()->json(['deleted' => $data]);

    }

    public function deleteConfirmed(Request $request)
    {
        $data = Tags::deleteConfirmed($request->input('tags'));
        return response()->json(['deleted' => $data]);

    }

    public function deletePending(Request $request)
    {
        $data = Tags::deletePending($request->input('tags'));
        return response()->json(['deleted' => $data]);

    }

    public function all(Request $request)
    {
        $data = Tags::getAll();
        return response()->json(['tags' => $data]);
    }

    public function pendingAll(Request $request)
    {
        $data = Tags::getPendingAll();
        return response()->json(['pending_tags' => $data]);
    }

    public function allTaggables(Request $request)
    {
        $data = Tags::getAllTaggables();
        return response()->json(['taggables' => $data]);
    }

    public function allPendingTaggables(Request $request)
    {
        $data = Tags::getAllPendingTaggables();
        return response()->json(['pending_taggables' => $data]);
    }

    public function getByEntity(Request $request, $entity, $id)
    {
        $data = Tags::getByEntity($entity, $id);
        return response()->json($data);
    }

    public function getPendingByEntity(Request $request, $entity, $id)
    {
        $data = Tags::getPendingByEntity($entity, $id);
        return response()->json($data);
    }

    public function check(Request $request)
    {
        $check = 'none';
        if (Tags::isPending($request->query('tag'))) $check = 'pending';
        else if (Tags::isConfirmed($request->query('tag'))) $check = 'confirmed';

        return response()->json(['status' => $check]);
    }

    public function like(Request $request)
    {
        $type = ($request->has('type')) ? $request->query('type') : 'confirmed';
        $array = ($request->has('array')) ? $request->query('array') : 'plain';

        $data = Tags::like($request->query('tag'), $type, $array);
        return response()->json(['tags' => $data]);
    }

    public function match(Request $request)
    {
        $data = Tags::match($request->query('tag'), false);
        return response()->json(['tag' => $data]);
    }

    public function matchPending(Request $request)
    {
        $data = Tags::matchPending($request->query('tag'), false);
        return response()->json(['pending_tag' => $data]);
    }

    public function search(Request $request)
    {
        $data = $search = Tags::search($request->query('search'));
        return response()->json(['tags' => $data]);
    }

    public function searchPending(Request $request)
    {
        $data = $search = Tags::searchPending($request->query('search'));
        return response()->json(['tags' => $data]);
    }

}