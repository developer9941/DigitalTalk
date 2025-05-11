<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Translation;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Translations",
 *     description="API Endpoints for Managing Translations"
 * )
 */
class TranslationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/translations",
     *     tags={"Translations"},
     *     summary="List translations",
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="Successful response")
     * )
     */
    public function index()
    {
        return response()->json(Translation::with('tags')->simplePaginate(50), 200);
    }

    /**
     * @OA\Post(
     *     path="/api/translations",
     *     tags={"Translations"},
     *     summary="Create a new translation",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"key","content","locale"},
     *             @OA\Property(property="key", type="string"),
     *             @OA\Property(property="content", type="string"),
     *             @OA\Property(property="locale", type="string"),
     *             @OA\Property(property="tags", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(response=201, description="Created")
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'key' => 'required|string',
            'content' => 'required|string',
            'locale' => 'required|string',
            'tags' => 'array'
        ]);

        $translation = Translation::create($data);

        if (!empty($data['tags'])) {
            $tagIds = collect($data['tags'])->map(function ($tagName) {
                return Tag::firstOrCreate(['name' => $tagName])->id;
            });
            $translation->tags()->sync($tagIds);
        }
        Cache::flush();
        return response()->json($translation->load('tags'), 201);
    }

    /**
     * @OA\Put(
     *     path="/api/translations/{id}",
     *     tags={"Translations"},
     *     summary="Update a translation",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="key", type="string"),
     *             @OA\Property(property="content", type="string"),
     *             @OA\Property(property="locale", type="string"),
     *             @OA\Property(property="tags", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(response=200, description="Updated")
     * )
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'key' => 'sometimes|string',
            'content' => 'sometimes|string',
            'locale' => 'sometimes|string',
            'tags' => 'array'
        ]);

        $translation = Translation::findOrFail($id);
        $translation->update($data);

        if (isset($data['tags'])) {
            $tagIds = collect($data['tags'])->map(function ($tagName) {
                return Tag::firstOrCreate(['name' => $tagName])->id;
            });
            $translation->tags()->sync($tagIds);
        }

        return response()->json($translation->load('tags'), 200);
    }

    /**
     * @OA\Get(
     *     path="/api/translations/search",
     *     tags={"Translations"},
     *     summary="Search translations",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="key", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="content", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="tag", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="locale", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Search results")
     * )
     */
    public function search(Request $request)
    {
        $query = Translation::query()->with('tags');

        if ($request->has('key')) {
            $query->where('key', 'like', '%' . $request->key . '%');
        }

        if ($request->has('content')) {
            $query->where('content', 'like', '%' . $request->content . '%');
        }

        if ($request->has('tag')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('name', $request->tag);
            });
        }

        if ($request->has('locale')) {
            $query->where('locale', $request->locale);
        }

        return response()->json($query->paginate(50), 200);
    }

    /**
     * @OA\Get(
     *     path="/api/translations/export/{locale}",
     *     tags={"Translations"},
     *     summary="Export translations by locale",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="locale", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Exported JSON translations")
     * )
     */
    public function export($locale)
    {
        $translations = Cache::remember("translations_json_{$locale}", 30, function () use ($locale) {
            return Translation::where('locale', $locale)
                ->get()
                ->pluck('content', 'key')
                ->toArray();
        });

        return response()->json($translations, 200);
    }
}
