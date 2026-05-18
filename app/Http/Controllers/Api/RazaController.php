<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RazaResource;
use App\Models\Raza;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RazaController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return RazaResource::collection(Raza::orderBy('nombre')->get());
    }
}
