<?php

namespace App\Http\Controllers\APIs;

use Exception;
use Carbon\Carbon;
use App\Models\Teacher;
use App\Models\Employee;
use App\Models\Personal;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\EmployeeRequest;
use App\Http\Resources\EmployeeResource;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EmployeeController extends Controller
{
    public function list(Request $request)
    {
        try {
            $limit = (int) $request->limit;
            $search = $request->search;

            $query = Employee::orderBy('id', 'desc');

            if ($search) {
                $query->where('email', 'LIKE', $search . '%');
            }

            $data = $limit ? $query->paginate($limit) : $query->get();

            $data = EmployeeResource::collection($data);

            $total = Employee::count();

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
                'email' => 'required|email|unique:employees,email',
                'phonenumber' => 'required|unique:employees,phonenumber',
                'department' => 'required',
                'salary' => 'required|numeric',
                'hireDate' => 'required|date',
                'status' => 'required|in:active,inactive,suspended,disabled',
                'employmentType' => 'required|in:full-time, part-time, contract'
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

            $employee = new Employee;
            $employee->slug = Str::uuid();
            $employee->personal_id = $personal->id;
            $employee->email = $request->email;
            $employee->phonenumber = $request->phonenumber;
            $employee->department = $request->department;
            $employee->salary = $request->salary;
            $employee->hire_date = $request->hireDate;
            $employee->status = $request->status;
            $employee->employment_type = $request->employmentType;
            $employee->save();
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
                'slug' => 'required|exists:employees,slug'
            ]);

            $data = Employee::where('slug',$request->slug)->firstOrFail();
            $employee = new EmployeeResource($data);
            return response()->json($employee,200);

        }catch (Exception $e) {
            return response()->json([
                'status' => 'An error occurred while showing.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(EmployeeRequest $request)
    {
        try {

            DB::beginTransaction();

            $dob = Carbon::parse($request->dob)->format('Y-m-d');
            $hireDate = Carbon::parse($request->hireDate)->format('Y-m-d');

            $employee = Employee::where('slug',$request->slug)->firstOrFail();

            // Check if the NRC (state, district, register code) already exists in Personal Data
            $existingPersonal = Personal::where('id', '!=', $employee->personal_id)
                ->where('state', $request->state)
                ->where('district', $request->district)
                ->where('register_code', $request->registerCode)
                ->first();

            if ($existingPersonal) {
                throw new Exception('A record with the same state, district, and register code already exists.');
            }

            $personal = Personal::findOrFail($employee->personal_id);

            $personal->update([
                'name' => $request->name,
                'gender' => $request->gender,
                'dob' => $dob,
                'address' => $request->address,
                'state' => $request->state,
                'district' => $request->district,
                'register_code' => (string)$request->registerCode,
            ]);

            $employee->update([
                'email' => $request->email,
                'phonenumber' => $request->phonenumber,
                'department' => $request->department,
                'salary' => $request->salary,
                'hire_date' => $hireDate,
                'status' => $request->status,
                'employment_type' => $request->employmentType,
            ]);

            $employee = new EmployeeResource($employee);

            DB::commit();

            return response()->json($employee,200);

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
            $employee = Employee::where('slug',$request->slug)->firstOrFail();
            $employee->delete();

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
