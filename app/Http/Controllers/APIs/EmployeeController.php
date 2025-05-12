<?php

namespace App\Http\Controllers\APIs;

use Exception;
use Carbon\Carbon;
use App\Models\Employee;
use App\Models\Personal;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeRequest;
use App\Http\Resources\EmployeeResource;
use Illuminate\Validation\ValidationException;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(Employee::with('personal')->get());
        // try {
        //     $limit = (int) $request->limit;
        //     $search = $request->search;

        //     $query = Employee::orderBy('id', 'desc');

        //     if ($search) {
        //         $query->where('email', 'LIKE', $search . '%');
        //     }

        //     $data = $limit ? $query->paginate($limit) : $query->get();

        //     $data = EmployeeResource::collection($data);

        //     $total = Employee::count();

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

    public function store(Request $request)
    {
        $request->validate([
            // Personal info
            'full_name' => 'required|string|max:255',
            'birth_date' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'region_code' => 'required|string|max:5',
            'township_code' => 'required|string|max:5',
            'citizenship' => 'required|string|max:1',
            'serial_number' => 'required|string|max:10',

            // Employee info
            'employee_code' => 'required|string|unique:employees,employee_code',
            'email' => 'nullable|email|unique:employees,email',
            'phone' => 'nullable|string|unique:employees,phone',
            'address' => 'nullable|string',
            'position' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'employment_type' => 'required|in:full-time,part-time,contract',
            'hire_date' => 'required|date',
            'resign_date' => 'nullable|date',
            'experience_years' => 'nullable|integer|min:0',
            'salary' => 'required|numeric|min:0',
            'status' => 'required|in:active,resigned,on_leave,terminated',
        ]);

        DB::beginTransaction();

        try {
            // Check for existing personal
            $personal = Personal::where('region_code', $request->region_code)
                ->where('township_code', $request->township_code)
                ->where('citizenship', $request->citizenship)
                ->where('serial_number', $request->serial_number)
                ->first();

            if (!$personal) {
                // Create new personal
                $personal = Personal::create([
                    'full_name' => $request->full_name,
                    'birth_date' => $request->birth_date,
                    'gender' => $request->gender,
                    'region_code' => $request->region_code,
                    'township_code' => $request->township_code,
                    'citizenship' => $request->citizenship,
                    'serial_number' => $request->serial_number,
                    'nationality' => $request->nationality ?? null,
                    'religion' => $request->religion ?? null,
                    'blood_type' => $request->blood_type ?? null,
                ]);
            }

            // Check if personal is already linked to an employee
            if (Employee::where('personal_id', $personal->id)->exists()) {
                return response()->json(['error' => 'This person is already registered as an employee.'], 422);
            }

            // Create employee
            Employee::create([
                'slug' => Str::uuid(),
                'personal_id' => $personal->id,
                'employee_code' => $request->employee_code,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'position' => $request->position,
                'department' => $request->department,
                'employment_type' => $request->employment_type,
                'hire_date' => $request->hire_date,
                'resign_date' => $request->resign_date,
                'experience_years' => $request->experience_years ?? 0,
                'salary' => $request->salary,
                'status' => $request->status,
            ]);

            DB::commit();

            return response()->json(['message' => 'Employee created successfully'], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create employee: ' . $e->getMessage()], 500);
        }
    }

    public function show(Request $request)
    {
        // Validate the incoming request to ensure the 'slug' is provided
        $validated = $request->validate([
            'slug' => 'required|string|exists:employees,slug',
        ]);

        // Retrieve the student using the slug
        $employee = Employee::with(['personal']) // Include related models if needed
            ->where('slug', $validated['slug'])
            ->first();

        // If the student is not found, return a 404 error
        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        // Return the student data
        return response()->json($employee);
    }

    public function update(EmployeeRequest $request)
    {
        // Validate the incoming request
        $request->validate([
            // Personal fields
            'full_name' => 'required|string|max:255',
            'birth_date' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'region_code' => 'required|string|max:10',
            'township_code' => 'required|string|max:10',
            'citizenship' => 'required|string|max:10',
            'serial_number' => 'required|string|max:20',
    
            // Employee fields
            'employee_code' => "required|string|unique:employees,employee_code,$id",
            'email' => "nullable|email|unique:employees,email,$id",
            'phone' => "nullable|string|unique:employees,phone,$id",
            'address' => 'nullable|string',
            'position' => 'nullable|string',
            'department' => 'nullable|string',
            'experience_years' => 'nullable|integer|min:0',
            'salary' => 'required|numeric|min:0',
            'hire_date' => 'required|date',
            'resign_date' => 'nullable|date',
            'status' => 'nullable|in:active,resigned,on_leave,terminated',
            'employment_type' => 'nullable|in:full-time,part-time,contract',
        ]);
    
        try {
            // Start a database transaction
            DB::beginTransaction();
    
            // Find the employee by ID, along with their related personal data
            $employee = Employee::findOrFail($id);
            $personal = $employee->personal; // Assuming there's a relation to a Personal model
    
            // Update the personal data
            $personal->update([
                'full_name' => $request->full_name,
                'birth_date' => $request->birth_date,
                'gender' => $request->gender,
                'region_code' => $request->region_code,
                'township_code' => $request->township_code,
                'citizenship' => $request->citizenship,
                'serial_number' => $request->serial_number,
            ]);
    
            // Update the employee data
            $employee->update([
                'employee_code' => $request->employee_code,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'position' => $request->position,
                'department' => $request->department,
                'experience_years' => $request->experience_years ?? 0,
                'salary' => $request->salary,
                'hire_date' => $request->hire_date,
                'resign_date' => $request->resign_date,
                'status' => $request->status ?? 'active',
                'employment_type' => $request->employment_type ?? 'full-time',
            ]);
    
            // Commit the transaction
            DB::commit();
    
            return response()->json([
                'message' => 'Employee updated successfully.',
                'employee' => $employee->fresh('personal'), // Reload employee with personal info
            ], 200);
    
        } catch (\Exception $e) {
            // Rollback the transaction if there's an error
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to update employee: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function handleAction(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'slug' => 'required|string|exists:employees,slug',
            'action' => 'required|string|in:active,resigned,on_leave,restore,delete',
        ]);
    
        $slug = $request->input('slug');
        $action = $request->input('action');
    
        // Fetch the employee, including soft-deleted ones
        $employee = Employee::withTrashed()->where('slug', $slug)->firstOrFail();
    
        switch ($action) {
            case 'active':
                // Set the status to active and save
                $employee->status = 'active';
                $employee->save();
                return response()->json(['message' => 'Employee status set to active']);
    
            case 'resigned':
                // Set the status to resigned and save
                $employee->status = 'resigned';
                $employee->save();
                return response()->json(['message' => 'Employee resigned']);
    
            case 'on_leave':
                // Set the status to on leave and save
                $employee->status = 'on_leave';
                $employee->save();
                return response()->json(['message' => 'Employee is on leave']);
    
            case 'delete':
                // Set status to resigned and then delete the record
                $employee->status = 'resigned'; // or 'inactive' if preferred
                $employee->save();
                $employee->delete();
                return response()->json(['message' => 'Employee soft-deleted']);
    
            case 'restore':
                // Restore the soft-deleted employee and set status to active
                if ($employee->trashed()) {
                    $employee->restore();
                    $employee->status = 'active'; // or previous status if needed
                    $employee->save();
                    return response()->json(['message' => 'Employee restored']);
                }
                return response()->json(['message' => 'Employee is not deleted'], 400);
    
            default:
                // Return error for invalid action
                return response()->json(['message' => 'Invalid action'], 400);
        }
    }
}
