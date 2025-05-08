<?php

namespace App\Http\Controllers\APIs;

use Exception;
use App\Models\Personal;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Resources\PersonalResource;
use Illuminate\Validation\ValidationException;


class PersonalController extends Controller
{
    public function list(Request $request)
    {
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

        return response()->json(Personal::all());
    }
    public function create(Request $request)
    {
        try{
            $request->validate([
                "name" => "required|string",
                "gender" => "required|string",
                "dob" => "required|date|before:today",
                "address" => "required|string",
                "state" => "nullable",
                "district" => "nullable",
                "registerCode" => "nullable",
            ]);

            $data = $request->only(['state', 'district', 'registerCode']);
            $filled = array_filter($data);

            if (count($filled) > 0 && count($filled) < count($data)) {
                return response()->json([
                    'errors' => [
                        'state' => ['All fields (state, district, register_code) must be null or filled together.']
                    ]
                ], 422);
            }

            $personal = new Personal;
            $personal->slug = Str::uuid();
            $personal->name = $request->name;
            $personal->gender = $request->gender;
            $personal->dob = $request->dob;
            $personal->address = $request->address;
            $personal->state = $request->state;
            $personal->district = $request->district;
            $personal->register_code = $request->registerCode;
            $personal->save();

            return response()->json([
                "status" => "OK! The request was successful",
            ],200);

        }catch (ValidationException $e) {
            return response()->json([
                'status' => 'Validation error.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'An error occurred while adding.',
                'message' => $e->getMessage()
            ], 500);
        }

    }
    public function detail(Request $request)
    {
        try {
            $request->validate([
                'slug' => 'required|exists:personals,slug'
            ]);

            $data = Personal::where('slug',$request->slug)->firstOrFail();
            $personal = new PersonalResource($data);
            return response()->json($personal,200);

        }catch (Exception $e) {
            return response()->json([
                'status' => 'An error occurred while showing.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function update(Request $request)
    {
        try{
            $request->validate([
                "name" => "required|string",
                "gender" => "required|string",
                "dob" => "required|date|before:today",
                "address" => "required|string",
                "state" => "nullable",
                "district" => "nullable",
                "registerCode" => "nullable",
            ]);

            $data = $request->only(['state', 'district', 'registerCode']);
            $filled = array_filter($data);

            if (count($filled) > 0 && count($filled) < count($data)) {
                return response()->json([
                    'errors' => [
                        'state' => ['All fields (state, district, register_code) must be null or filled together.']
                    ]
                ], 422);
            }

            $personal = Personal::where('slug',$request->slug)->firstOrFail();
            $personal->name = $request->name;
            $personal->gender = $request->gender;
            $personal->dob = $request->dob;
            $personal->address = $request->address;
            $personal->state = $request->state;
            $personal->district = $request->district;
            $personal->register_code = $request->registerCode;
            $personal->save();

            $personal = new PersonalResource($personal);

            return response()->json($personal,200);

        }catch (ValidationException $e) {
            return response()->json([
                'status' => 'Validation error.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'An error occurred while updating.',
                'message' => $e->getMessage()
            ], 500);
        }

    }
    public function delete(Request $request)
    {
        try {
            $personal = Personal::where('slug',$request->slug)->firstOrFail();
            $personal->delete();

            return response()->json([
                "status" => "OK! Deleting Successfully."
            ],200);
        }catch (Exception $e) {
            return response()->json([
                'status' => 'An error occurred while deleting.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
