<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidBeerXmlException;
use App\Services\BeerXmlImporter;
use Illuminate\Http\Request;

class FileController extends Controller
{
    public function index()
    {
        return view('recipes.uploadFile');
    }

    public function store(Request $request, BeerXmlImporter $importer)
    {
        if ($request->action == 'cancel') {
            return redirect()->route('recipes.index');
        }

        $request->validate([
            'file' => 'required|file|mimes:xml|max:2048',
        ]);

        try {
            $recipe = $importer->import($request->file('file')->get(), $request->user());
        } catch (InvalidBeerXmlException) {
            return back()->withErrors(['file' => __('Invalid BeerXML file')]);
        }

        return redirect()->route('recipes.show', ['recipe' => $recipe->id]);
    }
}
