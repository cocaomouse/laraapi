<?php

namespace App\Http\Controllers\Api;

use App\Models\Link;
use App\Http\Resources\LinkResource;
use Illuminate\Http\Request;

class LinksController extends Controller
{
    public function index(Link $link)
    {
        $links = $link->getAllCached();

        //LinkResource::wrap('data');
        return LinkResource::collection($links);
    }
}
