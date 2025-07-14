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
use Illuminate\Support\Facades\Validator;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        try {
            $validated = $request->validate([
                'slugs' => 'sometimes|array',
                'slugs.*' => 'string',
                'skip' => 'sometimes|integer|min:0|max:100',
                'limit' => 'sometimes|integer|min:1|max:100',
                'search' => 'sometimes|string|max:255',
                'status' => 'sometimes|in:active,resigned,on_leave',
            ]);

            $limit = $validated['limit'] ?? null;
            $search = $validated['search'] ?? null;
            $status = $validated['status'] ?? null;

            // Build query with eager loading
            $query = Teacher::with('personal')->orderBy('teacher_name', 'asc');

            if(!empty($validated['slugs'])) {
                $query->whereIn('slug', $validated['slugs']);
            }

            // Apply status filter if provided
            if ($status) {
                $query->where('status', $status);
            }

            // Apply search filter if provided
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('teacher_name', 'like', "%{$search}%")
                    ->orWhere('teacher_code', 'like', "%{$search}%");
                });
            }

            $total = (clone $query)->count();

            if (!empty($validated['skip'])) {
                $query->skip($validated['skip']);
            }
            if (!empty($validated['limit'])) {
                $query->take($validated['limit']);
            }

            $results = $query->get();

            $results->transform(function ($teacher) {
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
                'data' => $results,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
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

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

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

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create teacher.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function show(Request $request)
    {
        $validated = $request->validate([
            'slug' => 'required|string|exists:teachers,slug',
        ]);

        // Retrieve the student with personal and guardians
        $teacher = Teacher::with('personal') // Include related models if needed
            ->where('slug', $validated['slug'])
            ->first();

        if (!$teacher) {
            return response()->json([
                'status' => 'Not Found',
                'message' => 'Teacher not found'
            ], 404);
        }
        // Check if a personal update exists for this teacher
        $latestUpdate = PersonalUpdate::where('updatable_type', Teacher::class)
            ->where('updatable_slug', $teacher->slug)
            ->where('personal_slug', $teacher->personal_slug)
            ->latest()
            ->first();

        // Use updated personal if it exists, otherwise original
        $personalData = $latestUpdate ?? $teacher->personal;

        // Replace the teacher->personal with latest data (either original or updated)
        $teacher->setRelation('personal', $personalData);

        return response()->json([
            'status' => 'OK! The request was successful',
            'data' => $teacher,
        ]);
    }

    public function update(Request $request)
    {
        try{
            $request->merge(array_map(function ($value) {
                return $value === '' ? null : $value;
            }, $request->all()));

            $teacher = Teacher::where('slug', $request->slug)->with('personal')->firstOrFail();

            

            $validated = $request->validate([
                // teacher
                'slug' => 'required|string',
                'teacher_code' => ['required', 'string', Rule::unique('teachers', 'teacher_code')->ignore($teacher->id)],
                'email' => ['nullable', 'email', Rule::unique('teachers', 'email')->ignore($teacher->id)],
                'phone' => ['nullable', 'string', Rule::unique('teachers', 'phone')->ignore($teacher->id)],
                'address' => 'nullable|string',
                'qualification' => 'nullable|string',
                'subject' => 'nullable|string',
                'experience_years' => 'nullable|integer|min:0',
                'salary' => 'required|numeric|min:0',
                'hire_date' => 'required|date',
                'status' => 'required|in:active,resigned,on_leave',
                'employment_type' => 'required|in:full-time,part-time,contract',

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

            try {

                $teacher->fill([
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
                ])->save();

                $fields = [
                    'full_name', 'birth_date', 'gender',
                    'region_code', 'township_code', 'citizenship',
                    'serial_number', 'nationality', 'religion', 'blood_type'
                ];

                $hasChanges = false;

                foreach ($fields as $field) {
                    $original = $teacher->personal->$field;
                    $new = $request->input($field);

                    if ($field === 'birth_date') {
                        $original = \Carbon\Carbon::parse($original)->toDateString();
                        $new = \Carbon\Carbon::parse($new)->toDateString();
                    }

                    if ($original !== $new) {
                        $hasChanges = true;
                        break;
                    }
                }

                if ($hasChanges) {
                    PersonalUpdate::create([
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
                }

                DB::commit();

                return response()->json([
                    'message' => 'Teacher updated successfully.',
                    'data' => $teacher->load('personal'),
                ]);

            } catch (\Exception $e) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function handleAction(Request $request)
    {
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
                return response()->json(['message' => 'Teacher is resigned'],200);

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

}
