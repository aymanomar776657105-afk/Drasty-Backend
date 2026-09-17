<?php

namespace App\Http\Controllers;

use App\Models\ContentType;
use Illuminate\Http\Request;

class ContentTypeController extends Controller
{
    public function index()
    {
        $types = ContentType::all();

        return response()->json([
            'status' => true,
            'count'  => $types->count(),
            'data'   => $types
        ], 200);
    }
}   