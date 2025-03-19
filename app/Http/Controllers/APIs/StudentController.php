<?php

namespace App\Http\Controllers\APIs;

use Exception;
use Carbon\Carbon;
use App\Models\Student;
use App\Models\Personal;
use App\Models\ParentInfo;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\StudentSection;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\StudentRequest;
use App\Http\Resources\StudentResource;
use App\Http\Requests\UpdateStudentRequest;
use App\Http\Resources\StudentSectionResource;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class StudentController extends Controller
{
    public function list(Request $request)
    {
        try {
            $limit = (int) $request->limit;
            $search = $request->search;

            $query = Student::orderBy('id', 'desc');

            if ($search) {
                $query->where('name', 'LIKE', $search . '%');
            }

            $data = $limit ? $query->paginate($limit) : $query->get();

            $data = StudentResource::collection($data);

            $total = Student::count();

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
                'personalId' => 'nullable|exists:personals,id',
                'name' => 'required|string',
                'studentCode' => 'required|string',
                'address' => 'required|string',
                'email' => 'required|email|unique:students,email',
                'phonenumber' => 'required|unique:students,phonenumber',
                'pob' => 'required|string',
                'nationality' => 'required|string',
                'religion' => 'required|string',
                'bloodType' => 'required|in:A,B,AB,O',
                'status' => 'required|in:active,graduated,suspended,dropped',
                'academicLevel' => 'required',
                'academicYear' => 'required',
                'enrollmentDate' => 'required|date',
                'graduationDate' => 'required|date'
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

            $student = new Student;
            $student->slug = Str::uuid();
            $student->personal_id = $personal->id;
            $student->name = $request->name;
            $student->student_code = $request->studentCode;
            $student->address = $request->address;
            $student->email = $request->email;
            $student->phonenumber = $request->phonenumber;
            $student->pob = $request->pob;
            $student->nationality =$request->nationality;
            $student->religion =$request->religion;
            $student->blood_type =$request->bloodType;
            $student->status =$request->status;
            $student->academic_level =$request->academicLevel;
            $student->academic_year =$request->academicYear;
            $student->enrollment_date =$request->enrollmentDate;
            $student->graduation_date =null;
            $student->save();

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
                'slug' => 'required|exists:students,slug'
            ]);

            $data = Student::where('slug',$request->slug)->firstOrFail();
            $student = new StudentResource($data);
            return response()->json($student,200);

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
                'name' => 'required|string',
                'studentCode' => [
                    Rule::unique('students','student_code')->ignore($request->slug,'slug'),
                ],
                'address' => 'required|string',
                'email' => 'required|email',
                'phonenumber' => 'required',
                'pob' => 'required|string',
                'nationality' => 'required|string',
                'religion' => 'required|string',
                'bloodType' => 'required|in:A,B,AB,O',
                'status' => 'required|in:active,graduated,suspended,dropped',
                'academicLevel' => 'required',
                'academicYear' => 'required',
                'enrollmentDate' => 'required|date',
                'graduationDate' => 'required|date'
            ]);

            $student = Student::where('slug',$request->slug)->firstOrFail();
            $student->name = $request->name;
            $student->student_code = $request->studentCode;
            $student->address = $request->address;
            $student->email = $request->email;
            $student->phonenumber = $request->phonenumber;
            $student->pob = $request->pob;
            $student->nationality =$request->nationality;
            $student->religion =$request->religion;
            $student->blood_type =$request->bloodType;
            $student->status =$request->status;
            $student->academic_level =$request->academicLevel;
            $student->academic_year =$request->academicYear;
            $student->enrollment_date =$request->enrollmentDate;
            $student->graduation_date =null;
            $student->save();

            $student = new StudentResource($student);

            return response()->json($student,200);

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
            $student = Student::where('slug',$request->slug)->firstOrFail();
            $student->delete();

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

    public function bySection(Request $request)
    {
        try{
            $limit = (int) $request->limit;
            $section_id = $request->sectionId;

            $query = StudentSection::where('section_id',$section_id)->orderBy('id','desc');
            $data = $limit ? $query->paginate($limit) : $query->get();

            $total = StudentSection::count();

            $student_sections = StudentSectionResource::collection($data);

            return response()->json([
                "status" => "OK! The request was successful",
                "total" => $total,
                "data" => $student_sections
            ]);

        }catch (Exception $e) {
            return response()->json([
                'status' => 'An error occurred while showing.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
