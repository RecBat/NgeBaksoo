<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index(Request $request){
        $categoryId = $request->query('category');
        $search = $request->query('q');

        $categories = Category::orderBy('id')->get();

        $menus = Menu::with('category', 'optionGroups.choices')
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('id')
            ->get();

        return view('menu.index', compact('menus', 'categories', 'categoryId', 'search'));
    }
}
