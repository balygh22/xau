<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // GET /settings/categories
    public function index()
    {
        $categories = Category::orderBy('CategoryName')->get();
        return view('settings.categories.index', compact('categories'));
    }

    // GET /settings/categories/create
    public function create()
    {
        return view('settings.categories.create');
    }

    // POST /settings/categories
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:100'],
        ], [], [
            'name' => 'اسم الفئة',
        ]);

        Category::create([
            'CategoryName' => $data['name'],
        ]);

        return redirect()->route('settings.categories.index')->with('success','تمت إضافة الفئة بنجاح');
    }

    // GET /settings/categories/{category}/edit
    public function edit(Category $category)
    {
        return view('settings.categories.edit', compact('category'));
    }

    // PUT /settings/categories/{category}
    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => ['required','string','max:100'],
        ], [], [
            'name' => 'اسم الفئة',
        ]);

        $category->update([
            'CategoryName' => $data['name'],
        ]);

        return redirect()->route('settings.categories.index')->with('success','تم تحديث الفئة بنجاح');
    }

    // DELETE /settings/categories/{category}
    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('settings.categories.index')->with('success','تم حذف الفئة بنجاح');
    }
}