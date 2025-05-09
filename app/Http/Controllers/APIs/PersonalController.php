<?php

namespace App\Http\Controllers\APIs;

use Exception;
use App\Models\Personal;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PersonalResource;

class PersonalController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(Personal::all());
        // try {
        //     $limit = (int) $request->limit;
        //     $search = $request->search;

        //     $query = Personal::orderBy('id', 'desc');

        //     if ($search) {
        //         $query->where('name', 'LIKE', $search . '%');
        //     }

        //     $data = $limit ? $query->paginate($limit) : $query->get();

        //     $data = PersonalResource::collection($data);

        //     $total = Personal::count();

        //     return response()->json([
        //         "status" => "OK! The request was successful",
        //         "total" => $total,
        //         "data" => $data
        //     ], 200);
        // } catch (Exception $e) {
        //     return response()->json([
        //         'status' => 'Bad Request!. The request is invalid.',
        //         'message' => $e->getMessage()
        //     ],400);
        // }
    }

}
