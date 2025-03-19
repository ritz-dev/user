<?php

namespace App\Http\Controllers\APIs;

use Exception;
use Carbon\Carbon;
use App\Models\Role;
use App\Models\Teacher;
use App\Models\Employee;
use App\Models\Personal;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\TeacherRequest;
use App\Http\Resources\TeacherResource;
use App\Http\Requests\UpdateTeacherRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class TeacherController extends Controller
{
    public function list(Request $request)
    {
        try {
            $limit = (int) $request->limit;
            $search = $request->search;

            $query = Teacher::orderBy('id', 'desc');

            if ($search) {
                $query->where('name', 'LIKE', $search . '%');
            }

            $data = $limit ? $query->paginate($limit) : $query->get();

            $data = TeacherResource::collection($data);

            $total = Teacher::count();

            return response()->json([
                "status" => "OK! The request was successful",
                "total" => $total,
                "data" => $data
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'Bad Request!. The request is invalid.',
                'message' => $e->getMessage()
            ],400);
        }
    }
    public function create(Request $request)
    {
        try{
            $request->validate([
                'personalId' => 'nullable|exists:personals,slug',
                'email' => 'required|email',
                'teacherCode' => 'required|string',
                'phonenumber' => 'required',
                'department' => 'required|string',
                'salary' => 'required|numeric',
                'hireDate' => 'required|date',
                'status' => 'required|in:active, inactive, supspened, disabled',
                'employmentType' => 'required|in:full-time, part-time, contract',
                'specialization' => 'required',
                'designation' => 'required'
            ]);

            if($request->personalId){
                $personal = Personal::where('slug',$request->personalId)->firstOrFail();
            }else{
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
            }

            $teacher = new Teacher;
            $teacher->slug = Str::uuid();
            $teacher->personal_id = $personal->id;
            $teacher->email = $request->email;
            $teacher->teacher_code = $request->teacherCode;
            $teacher->phonenumber = $request->phonenumber;
            $teacher->department = $request->department;
            $teacher->salary = $request->salary;
            $teacher->hire_date = $request->hireDate;
            $teacher->status = $request->status;
            $teacher->employment_type = $request->employmentType;
            $teacher->specialization = $request->specialization;
            $teacher->designation = $request->designation;
            $teacher->save();

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
                'slug' => 'required|exists:teachers,slug'
            ]);

            $data = Teacher::where('slug',$request->slug)->firstOrFail();
            $teacher = new TeacherResource($data);
            return response()->json($teacher,200);

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
                'email' => 'required|email',
                'teacherCode' => [
                    Rule::unique('teachers','teacher_code')->ignore($request->slug,'slug'),
                ],
                'phonenumber' => 'required',
                'department' => 'required|string',
                'salary' => 'required|numeric',
                'hireDate' => 'required|date',
                'status' => 'required|in:active, inactive, supspened, disabled',
                'employmentType' => 'required|in:full-time, part-time, contract',
                'specialization' => 'required',
                'designation' => 'required'
            ]);

            $teacher = Teacher::where('slug',$request->slug)->firstOrFail();
            $teacher->email = $request->email;
            $teacher->teacher_code = $request->teacherCode;
            $teacher->phonenumber = $request->phonenumber;
            $teacher->department = $request->department;
            $teacher->salary = $request->salary;
            $teacher->hire_date = $request->hireDate;
            $teacher->status = $request->status;
            $teacher->employment_type = $request->employmentType;
            $teacher->specialization = $request->specialization;
            $teacher->designation = $request->designation;
            $teacher->save();

            $teacher = new TeacherResource($teacher);

            return response()->json($teacher,200);

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
    public function delete(Request $request)
    {
        try {
            $teacher = Teacher::where('slug',$request->slug)->firstOrFail();
            $teacher->delete();

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
