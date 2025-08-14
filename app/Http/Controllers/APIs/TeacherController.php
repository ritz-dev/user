<?php

namespace App\Http\Controllers\APIs;

use Exception;
use Carbon\Carbon;
use App\Models\Teacher;
use App\Models\Personal;
use Illuminate\Http\Request;
use App\Models\PersonalUpdate;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        try {
            $validated = $request->validate([
                'slugs'    => ['nullable', 'array'],
                'slugs.*'  => ['string'],
                'search'    => ['nullable', 'array'],
                'status'   => ['nullable', 'in:active,resigned,on_leave,terminated'],
                'orderBy'  => ['nullable', 'in:student_name,student_number,status'],
                'sortedBy' => ['nullable', 'in:asc,desc'],
                'limit'    => ['nullable', 'integer', 'min:1', 'max:100'],
                'skip'     => ['nullable', 'integer', 'min:0', 'max:1000'],
            ]);

            // Build query with eager loading
            $query = Teacher::with('personal')->orderBy('teacher_name', 'asc');

            if(!empty($validated['slugs'])) {
                $query->whereIn('slug', $validated['slugs']);
            }

            // Apply status filter if provided
            if (!empty($validated['status'])) {
                $query->where('status', $validated['status']);
            }

            // Apply search filter if provided
            if (!empty($validated['search'])) {
                $filter = $validated['search'];
                $allowedFields = ['teacher_name', 'teacher_code', 'status'];

                foreach ($filter as $column => $value) {
                    if (!in_array($column, $allowedFields)) {
                        continue; // Skip unsupported fields
                    }

                    // Apply case-insensitive partial match
                    $query->whereRaw("LOWER($column) LIKE ?", [strtolower($value) . '%']);
                }
            }

            // Ordering
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

            $teacher = $query->get();

            $teacher->transform(function ($teacher) {
                $latestUpdate = PersonalUpdate::where('updatable_type', Teacher::class)
                    ->where('updatable_slug', $teacher->slug)
                    ->where('personal_slug', $teacher->personal_slug)
                    ->latest()
                    ->first();

                $teacher->setRelation('personal', $latestUpdate ?? $teacher->personal);
                return $teacher;
            });

            return response()->json([
                'status' => 'OK! The request was successful',
                'total' => $total,
                'data' => $teacher,
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->validator->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Teacher index error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = $request->validate([
            'teacher_code' => 'required|string|unique:teachers,teacher_code',
            'email' => 'nullable|email|unique:teachers,email',
            'phone' => 'nullable|string|unique:teachers,phone',
            'address' => 'nullable|string',
            'qualification' => 'nullable|string',
            'subject' => 'nullable|string',
            'experience_years' => 'nullable|integer|min:0',
            'salary' => 'required|numeric|min:0',
            'hire_date' => 'required|date',
            'status' => 'required|in:active,resigned,on_leave',
            'employment_type' => 'required|in:fulltime,parttime,contract',

            'personal.full_name' => 'required|string',
            'personal.birth_date' => 'nullable|date',
            'personal.gender' => 'required|in:male,female',
            'personal.region_code' => 'required|string',
            'personal.township_code' => 'required|string',
            'personal.citizenship' => 'required|string',
            'personal.serial_number' => 'required|string',
            'personal.nationality' => 'nullable|string',
            'personal.religion' => 'nullable|string',
            'personal.blood_type' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
        ]);

        DB::beginTransaction();

        try {
            $personalData = $request->input('personal');

            // ✅ Check for existing personal by NRC (region, township, serial, citizenship)
            $personal = Personal::where('region_code', $personalData['region_code'])
                ->where('township_code', $personalData['township_code'])
                ->where('serial_number', $personalData['serial_number'])
                ->where('citizenship', $personalData['citizenship'])
                ->first();

            // ✅ Create personal if not exists
            if (!$personal) {
                $personal = Personal::create([
                    'full_name' => $personalData['full_name'],
                    'birth_date' => $personalData['birth_date'] ?? now()->toDateString(),
                    'gender' => $personalData['gender'],
                    'region_code' => $personalData['region_code'],
                    'township_code' => $personalData['township_code'],
                    'serial_number' => $personalData['serial_number'],
                    'citizenship' => $personalData['citizenship'],
                    'nationality' => $personalData['nationality'] ?? null,
                    'religion' => $personalData['religion'] ?? null,
                    'blood_type' => $personalData['blood_type'] ?? null,
                ]);
            }

            // ✅ Create teacher
            $teacher = Teacher::create([
                'personal_slug' => $personal->slug,
                'teacher_name' => $personal->full_name,
                'teacher_code' => $request->input('teacher_code'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'address' => $request->input('address'),
                'qualification' => $request->input('qualification'),
                'subject' => $request->input('subject'),
                'experience_years' => $request->input('experience_years', 0),
                'salary' => $request->input('salary'),
                'hire_date' => $request->input('hire_date'),
                'status' => $request->input('status', 'active'),
                'employment_type' => strtolower($request->input('employment_type')),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Teacher created successfully.',
                'data' => $teacher->load('personal')
            ], 201);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->validator->errors()
            ], 422);
        } catch (\Throwable $e) {
            \Log::error('Student index error', ['error' => $e->getMessage()]);
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create teacher.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request)
    {
        try {
            $validated = $request->validate([
                'slug' => 'required|string|exists:teachers,slug',
            ]);

        $teacher = Teacher::with('personal')->where('slug', $validated['slug'])->first();

        if (!$teacher) {
            return response()->json([
                'status' => 'Not Found',
                'message' => 'Teacher not found'
            ], 404);
        }

        // Get latest personal update if exists
        $latestUpdate = PersonalUpdate::where('updatable_type', Teacher::class)
            ->where('updatable_slug', $teacher->slug)
            ->where('personal_slug', $teacher->personal_slug)
            ->latest()
            ->first();

        $personal = $latestUpdate ?? $teacher->personal;

        // Format response to match TeacherInput + Teacher interface
        $response = [
            'slug' => $teacher->slug,
            'teacher_name' => $teacher->teacher_name,
            'teacher_code' => $teacher->teacher_code,
            'email' => $teacher->email,
            'phone' => $teacher->phone,
            'address' => $teacher->address,
            'qualification' => $teacher->qualification,
            'subject' => $teacher->subject,
            'experience_years' => $teacher->experience_years,
            'salary' => number_format((float) $teacher->salary, 2, '.', ''),
            'hire_date' => Carbon::parse($teacher->hire_date)->format('Y-m-d'),
            'status' => $teacher->status,
            'employment_type' => $teacher->employment_type,
            'personal' => [
                'full_name' => $personal->full_name,
                'birth_date' =>  $personal->birth_date ? Carbon::parse($personal->birth_date)->format('Y-m-d') : null,
                'gender' => $personal->gender,
                'region_code' => $personal->region_code,
                'township_code' => $personal->township_code,
                'citizenship' => $personal->citizenship,
                'serial_number' => $personal->serial_number,
                'nationality' => $personal->nationality,
                'religion' => $personal->religion,
                'blood_type' => $personal->blood_type,
            ],
        ];

        return response()->json($response);
    }
        catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->validator->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Teacher index error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try{

            $validated = $request->validate([
                // teacher
                'slug' => 'required|string|exists:teachers,slug',
                'teacher_code' => [
                    'required',
                    'string',
                    Rule::unique('teachers', 'teacher_code')->ignore($request->slug, 'slug')
                ],
                'email' => [
                    'nullable',
                    'email',
                    Rule::unique('teachers', 'email')->ignore($request->slug, 'slug')
                ],
                'phone' => [
                    'nullable',
                    'string',
                    Rule::unique('teachers', 'phone')->ignore($request->slug, 'slug')
                ],
                'address' => 'nullable|string',
                'qualification' => 'nullable|string',
                'subject' => 'nullable|string',
                'experience_years' => 'nullable|integer|min:0',
                'salary' => 'required|numeric|min:0',
                'hire_date' => 'required|date',
                'status' => 'required|in:active,resigned,on_leave',
                'employment_type' => 'required|in:fulltime,parttime,contract',

                // personal
                'full_name' => 'required|string',
                'birth_date' => 'required|date',
                'gender' => 'required|in:male,female',
                'region_code' => 'required|string',
                'township_code' => 'required|string',
                'citizenship' => 'required|string',
                'serial_number' => 'required|string',
                'nationality' => 'nullable|string',
                'religion' => 'nullable|string',
                'blood_type' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            ]);

            DB::beginTransaction();

            $teacher = Teacher::where('slug', $request->slug)->with('personal')->firstOrFail();

            $teacher->update([
                'teacher_name'     => $request->full_name,
                'teacher_code'     => $request->teacher_code,
                'email'            => $request->email,
                'phone'            => $request->phone,
                'address'          => $request->address,
                'qualification'    => $request->qualification,
                'subject'          => $request->subject,
                'experience_years' => $request->experience_years ?? 0,
                'salary'           => $request->salary,
                'hire_date'        => $request->hire_date,
                'status'           => $request->status,
                'employment_type'  => $request->employment_type,
            ]);

            $personalData = $request->input('personal');

            $teacher->personal->update([
                    'personal_slug'    => $teacher->personal->slug,
                    'full_name'      => $request->full_name,
                    'birth_date'     => $request->birth_date,
                    'gender'         => $request->gender,
                    'region_code'    => $request->region_code,
                    'township_code'  => $request->township_code,
                    'citizenship'    => $request->citizenship,
                    'serial_number'  => $request->serial_number,
                    'nationality'    => $request->nationality,
                    'religion'       => $request->religion,
                    'blood_type'     => $request->blood_type,
                    'updatable_slug'   => $teacher->slug,
                    'updatable_type' => Teacher::class,
                ]);
        
            DB::commit();

            return response()->json([
                'message' => 'Teacher updated successfully.',
                'data' => $teacher->load('personal'),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->validator->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Student index error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function handleAction(Request $request)
    {
        try {
        $request->validate([
            'slug' => 'required|string|exists:teachers,slug',
            'action' => 'required|string|in:active,resigned,on_leave,restore,delete',
        ]);

        $slug = $request->input('slug');
        $action = $request->input('action');

        // Fetch soft-deleted teachers as well
        $teacher = Teacher::withTrashed()->where('slug', $slug)->firstOrFail();

        switch ($action) {
            case 'active':
                $teacher->status = 'active';
                $teacher->save();
                return response()->json(['message' => 'Teacher status set to active'],200);

            case 'resigned':
                $teacher->status = 'resigned';
                $teacher->save();
                return response()->json(['message' => 'Teacher resigned'],200);

            case 'on_leave':
                $teacher->status = 'on_leave';
                $teacher->save();
                return response()->json(['message' => 'Teacher is on leave'],200);

            case 'delete':
                $teacher->status = 'resigned'; // or 'inactive' if you prefer
                $teacher->save();
                $teacher->delete();
                return response()->json(['message' => 'Teacher soft-deleted'],200);

            case 'restore':
                if ($teacher->trashed()) {
                    $teacher->restore();
                    $teacher->status = 'active'; // or previous state
                    $teacher->save();
                    return response()->json(['message' => 'Teacher restored'],200);
                }
                return response()->json(['message' => 'Teacher is not deleted'], 400);

            default:
                return response()->json(['message' => 'Invalid action'], 400);
        }
    }   
        catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->validator->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Teacher index error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        } 
    }
}
