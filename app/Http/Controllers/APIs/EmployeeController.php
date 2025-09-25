<?php

namespace App\Http\Controllers\APIs;

use Carbon\Carbon;
use App\Models\Employee;
use App\Models\Personal;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\PersonalUpdate;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        try {
            $validated = $request->validate([
                'slugs'    => ['nullable', 'array'],
                'slugs.*'  => ['string'],
                'search'    => ['nullable', 'array'],
                'status'   => ['nullable', 'in:active,resigned,on_leave,terminated'],
                'orderBy'  => ['nullable', 'in:employee_name,employee_number,status'],
                'sortedBy' => ['nullable', 'in:asc,desc'],
                'skip'   => 'nullable|integer|min:0|max:1000',
                'limit'  => 'nullable|integer|min:1|max:100',
            ]);

            $query = Employee::with('personal')->orderBy('employee_name', 'asc');

            if(!empty($validated['slugs'])) {
                $query->whereIn('slug', $validated['slugs']);
            }

            if (!empty($validated['status'])) {
                $query->where('status', $validated['status']);
            }

            if (!empty($validated['search'])) {
                $filter = $validated['search'];
                $allowedFields = ['employee_name', 'employee_code', 'status'];

                foreach ($filter as $column => $value) {
                    if (!in_array($column, $allowedFields)) {
                        continue; // Skip unsupported fields
                    }

                    // Apply case-insensitive partial match
                    $query->whereRaw("LOWER($column) LIKE ?", [strtolower($value) . '%']);
                }
            }

            if (!empty($validated['orderBy'])) {
                $query->orderBy($validated['orderBy'], $validated['sortedBy'] ?? 'asc');
            } else {
                $query->orderByDesc('id');
            }

            $total = (clone $query)->count();

            if (!empty($validated['skip'])) {
                $query->skip($validated['skip']);
            }

            if (!empty($validated['limit'])) {
                $query->take($validated['limit']);
            }

            $employees = $query->get();

            $employees->transform(function ($employee) {
                $latestUpdate = PersonalUpdate::where('updatable_type', Employee::class)
                    ->where('updatable_slug', $employee->slug)
                    ->where('personal_slug', $employee->personal_slug)
                    ->latest()
                    ->first();

                $employee->setRelation('personal', $latestUpdate ?? $employee->personal);
                return $employee;
            });

            return response()->json([
                'status' => 'OK! The request was successful',
                'total' => $total,
                'data' => $employees,
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->validator->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Employee index error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // Personal
            'full_name'       => 'required|string|max:255',
            'birth_date'      => 'required|date',
            'gender'          => 'required|in:male,female',
            'region_code'     => 'required|string|max:10',
            'township_code'   => 'required|string|max:10',
            'citizenship'     => 'required|string|max:10',
            'serial_number'   => 'required|string|max:20',
            'nationality'     => 'nullable|string',
            'religion'        => 'nullable|string',
            'blood_type'      => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',

            // Employee
            'employee_code'   => 'required|string|unique:employees,employee_code',
            'email'           => 'nullable|email|unique:employees,email',
            'phone'           => 'nullable|string|unique:employees,phone',
            'address'         => 'nullable|string',
            'position'        => 'nullable|string|max:100',
            'department'      => 'nullable|string|max:100',
            'employment_type' => 'required|in:full-time,part-time,contract',
            'hire_date'       => 'required|date',
            'resign_date'     => 'nullable|date',
            'experience_years'=> 'nullable|integer|min:0',
            'salary'          => 'required|numeric|min:0',
            'status'          => 'required|in:active,resigned,on_leave,terminated',
        ]);

        DB::beginTransaction();

        try {
            $personal = Personal::firstOrCreate([
                'region_code'    => $validated['region_code'],
                'township_code'  => $validated['township_code'],
                'citizenship'    => $validated['citizenship'],
                'serial_number'  => $validated['serial_number'],
            ], [
                'full_name'      => $validated['full_name'],
                'birth_date'     => $validated['birth_date'],
                'gender'         => $validated['gender'],
                'nationality'    => $validated['nationality'] ?? null,
                'religion'       => $validated['religion'] ?? null,
                'blood_type'     => $validated['blood_type'] ?? null,
            ]);

            if (Employee::where('personal_slug', $personal->slug)->exists()) {
                return response()->json([
                    'error' => 'This personal is already assigned to another employee.'
                ], 409);
            }

            $employee = Employee::create([
                'slug'            => Str::uuid(),
                'personal_slug'   => $personal->slug,
                'employee_name'   => $personal->full_name,
                'employee_code'   => $validated['employee_code'],
                'email'           => $validated['email'],
                'phone'           => $validated['phone'],
                'address'         => $validated['address'],
                'position'        => $validated['position'],
                'department'      => $validated['department'],
                'employment_type' => $validated['employment_type'],
                'hire_date'       => $validated['hire_date'],
                'resign_date'     => $validated['resign_date'],
                'experience_years'=> $validated['experience_years'] ?? 0,
                'salary'          => $validated['salary'],
                'status'          => $validated['status'],
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Employee created successfully',
                'data' => $employee->load('personal')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Employee store error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create employee: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request)
    {
        try {
            $validated = $request->validate([
                'slug' => 'required|string|exists:employees,slug',
            ]);

            $employee = Employee::with('personal')
                ->where('slug', $validated['slug'])
                ->first();

            if (!$employee) {
                return response()->json([
                    'status' => 'Not Found',
                    'message' => 'Employee not found'
                ], 404);
            }

            $latestUpdate = PersonalUpdate::where('updatable_type', Employee::class)
                ->where('updatable_slug', $employee->slug)
                ->where('personal_slug', $employee->personal_slug)
                ->latest()
                ->first();

            $employee->setRelation('personal', $latestUpdate ?? $employee->personal);

            return response()->json([
                'status' => 'OK! The request was successful',
                'data' => $employee,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->validator->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Employee show error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request)
    {
        try {

            $validated = $request->validate([
                'slug' => 'required|string|exists:employees,slug',
                'employee_code' => [
                    'required',
                    'string',
                    Rule::unique('employees', 'employee_code')->ignore($request->slug, 'slug')
                ],
                'email' => [
                    'nullable',
                    'email',
                    Rule::unique('employees', 'email')->ignore($request->slug, 'slug')
                ],
                'phone' => [
                    'nullable',
                    'string',
                    Rule::unique('employees', 'phone')->ignore($request->slug, 'slug')
                ],
                'address' => 'nullable|string',
                'position' => 'nullable|string',
                'department' => 'nullable|string',
                'experience_years' => 'nullable|integer|min:0',
                'salary' => 'required|numeric|min:0',
                'hire_date' => 'required|date',
                'resign_date' => 'nullable|date',
                'status' => 'nullable|in:active,resigned,on_leave,terminated',
                'employment_type' => 'nullable|in:full-time,part-time,contract',
                'full_name' => 'required|string|max:255',
                'birth_date' => 'required|date',
                'gender' => 'required|in:male,female',
                'region_code' => 'required|string|max:10',
                'township_code' => 'required|string|max:10',
                'citizenship' => 'required|string|max:10',
                'serial_number' => 'required|string|max:20',
                'nationality' => 'nullable|string',
                'religion' => 'nullable|string',
                'blood_type' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            ]);

            DB::beginTransaction();

            $employee = Employee::with('personal')->where('slug', $request->slug)->firstOrFail();

            $employee->update([
                'employee_code'     => $validated['employee_code'],
                'employee_name'   => $validated['full_name'],
                'email'             => $validated['email'],
                'phone'             => $validated['phone'],
                'address'           => $validated['address'],
                'position'          => $validated['position'],
                'department'        => $validated['department'],
                'experience_years'  => $validated['experience_years'] ?? 0,
                'salary'            => $validated['salary'],
                'hire_date'         => $validated['hire_date'],
                'resign_date'       => $validated['resign_date'],
                'status'            => $validated['status'] ?? 'active',
                'employment_type'   => $validated['employment_type'] ?? 'full-time',
            ]);

            $fields = ['full_name', 'birth_date', 'gender', 'region_code', 'township_code', 'citizenship', 'serial_number', 'nationality', 'religion', 'blood_type'];
            $hasChanges = false;

            foreach ($fields as $field) {
                $original = $employee->personal->$field;
                $new = $validated[$field];
                if ($field === 'birth_date') {
                    $original = Carbon::parse($original)->toDateString();
                    $new = Carbon::parse($new)->toDateString();
                }
                if ($original !== $new) {
                    $hasChanges = true;
                    break;
                }
            }

            if ($hasChanges) {
                PersonalUpdate::create([
                    'personal_slug' => $employee->personal->slug,
                    'full_name' => $validated['full_name'],
                    'birth_date' => $validated['birth_date'],
                    'gender' => $validated['gender'],
                    'region_code' => $validated['region_code'],
                    'township_code' => $validated['township_code'],
                    'citizenship' => $validated['citizenship'],
                    'serial_number' => $validated['serial_number'],
                    'nationality' => $validated['nationality'] ?? null,
                    'religion' => $validated['religion'] ?? null,
                    'blood_type' => $validated['blood_type'] ?? null,
                    'updatable_slug' => $employee->slug,
                    'updatable_type' => Employee::class,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Employee updated successfully.',
                'data' => $employee->fresh('personal'),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->validator->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Employee update error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function handleAction(Request $request)
    {
        try {
            $validated = $request->validate([
                'slug' => 'required|string|exists:employees,slug',
                'action' => 'required|string|in:active,resigned,on_leave,restore,delete',
            ]);

            $employee = Employee::withTrashed()->where('slug', $validated['slug'])->firstOrFail();

            switch ($validated['action']) {
                case 'active':
                case 'resigned':
                case 'on_leave':
                    $employee->status = $validated['action'];
                    $employee->save();
                    return response()->json(['message' => "Employee status set to {$validated['action']}"], 200);

                case 'delete':
                    $employee->status = 'resigned';
                    $employee->save();
                    $employee->delete();
                    return response()->json(['message' => 'Employee soft-deleted'], 200);

                case 'restore':
                    if ($employee->trashed()) {
                        $employee->restore();
                        $employee->status = 'active';
                        $employee->save();
                        return response()->json(['message' => 'Employee restored'], 200);
                    }
                    return response()->json(['message' => 'Employee is not deleted'], 400);

                default:
                    return response()->json(['message' => 'Invalid action'], 400);
            }
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->validator->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Employee action error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
