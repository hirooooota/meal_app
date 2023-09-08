<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Meal;
use App\Models\Like;
use App\Http\Requests\StoreMealRequest;
use App\Http\Requests\UpdateMealRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MealController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $meals = Meal::with('user')->latest()->paginate(4);
        return view('meals.index', compact('meals'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        return view('meals.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMealRequest $request)
    {
        $meal = new Meal($request->all());
        $meal->user_id = $request->user()->id;

        $file = $request->file('image');
        $meal->image = self::createFileName($file);


        // トランザクション開始
        DB::beginTransaction();
        try {
            // 登録
            $meal->save();

            // 画像アップロード
            if (!Storage::putFileAs('images/meals', $file, $meal->image)) {
                // 例外を投げてロールバックさせる
                throw new \Exception('画像ファイルの保存に失敗しました。');
            }

            // トランザクション終了(成功)
            DB::commit();
        } catch (\Exception $e) {
            // トランザクション終了(失敗)
            DB::rollback();
            return back()->withInput()->withErrors($e->getMessage());
        }

        return redirect()
            ->route('meals.show', $meal)
            ->with('notice', '記事を登録しました');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $meal = Meal::find($id);
        $is_favorited_by_logged_in_user = Like::where('user_id', auth()->id())
            ->where('meal_id', $meal->id)
            ->exists();


        return view('meals.show', compact('meal', 'is_favorited_by_logged_in_user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $meal = Meal::find($id);
        $categories = Category::all();

        return view('meals.edit', compact('meal', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreMealRequest $request, string $id)
    {
        $meal = Meal::find($id);

        if ($request->user()->cannot('update', $meal)) {
            return redirect()->route('meals.show', $meal)
                ->withErrors('自分の記事以外は更新できません');
        }

        $file = $request->file('image');
        if ($file) {
            $delete_file_path = $meal->image_path;
            $meal->image = self::createFileName($file);
        }
        $meal->fill($request->all());

        // トランザクション開始
        DB::beginTransaction();
        try {
            // 更新
            $meal->save();
            if ($file) {
                // 画像アップロード
                if (!Storage::putFileAs('images/meals', $file, $meal->image)) {
                    // 例外を投げてロールバックさせる
                    throw new \Exception('画像ファイルの保存に失敗しました。');
                }

                // 画像削除
                if (!storage::delete($delete_file_path)) {
                    // アップロードした画像を削除する
                    Storage::delete($meal->image_path);
                    // 例外を投げてロールバックさせる
                    throw new \Exception('画像ファイルの削除に失敗しました。');
                }
            }

            // トランザクション終了(成功)
            DB::commit();
        } catch (\Exception $e) {
            // トランザクション終了(失敗)
            DB::rollback();
            return back()->withInput()->withErrors($e->getMessage());
        }

        return redirect()
            ->route('meals.show', $meal)
            ->with('notice', '記事を更新しました');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $meal = Meal::find($id);

        // トランザクション開始
        try {
            $meal->delete();

            // 画像削除
            if (!Storage::delete($meal->image_path)) {
                // 例外を投げてロールバックさせる
                throw new \Exception('画像ファイルの削除に失敗しました。');
            }

            // トランザクション終了(成功)
            DB::commit();
        } catch (\Exception $e) {
            // トランザクション終了(失敗)
            DB::rollBack();
            return back()->withInput()->withErrors($e->getMessage());
        }

        return redirect()->route('meals.index')
            ->with('notice', '記事を削除しました');
    }

    private static function createFileName($file)
    {
        return date('YmdHis') . '_' . $file->getClientOriginalName();
    }

    public function like(Meal $meal)
    {
        $like = new Like();
        $like->user_id = auth()->id();
        $like->meal_id = $meal->id;
        $like->save();

        return redirect()->back();
    }

    public function unlike(Meal $meal)
    {
        $like = Like::where('user_id', auth()->id())->where('meal_id', $meal->id)->first();
        if ($like) {
            $like->delete();
        }

        return redirect()->back();
    }
}
