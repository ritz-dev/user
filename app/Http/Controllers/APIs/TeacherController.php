<?php

namespace App\Http\Controllers\APIs;

use Exception;
use App\Models\Teacher;
use App\Models\Personal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\TeacherResource;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(Teacher::with('personal')->get());
        // try {
        //     $limit = (int) $request->limit;
        //     $search = $request->search;

        //     $query = Teacher::orderBy('id', 'desc');

        //     if ($search) {
        //         $query->where('name', 'LIKE', $search . '%');
        //     }

        //     $data = $limit ? $query->paginate($limit) : $query->get();

        //     $data = TeacherResource::collection($data);

        //     $total = Teacher::count();

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
            // Personal fields
            'full_name' => 'required|string|max:255',
            'birth_date' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'region_code' => 'required|string|max:10',
            'township_code' => 'required|string|max:10',
            'citizenship' => 'required|string|max:10',
            'serial_number' => 'required|string|max:20',
            // Teacher fields
            'teacher_code' => 'required|string|unique:teachers,teacher_code',
            'email' => 'nullable|email|unique:teachers,email',
            'phone' => 'nullable|string|unique:teachers,phone',
            'address' => 'nullable|string',
            'qualification' => 'nullable|string',
            'subject' => 'nullable|string',
            'experience_years' => 'nullable|integer|min:0',
            'salary' => 'required|numeric|min:0',
            'hire_date' => 'required|date',
            'status' => 'nullable|in:active,resigned,on_leave',
            'employment_type' => 'nullable|in:full-time,part-time,contract',
        ]);

        try {
            DB::beginTransaction();

            $personal = Personal::where('region_code', $request->region_code)
                ->where('township_code', $request->township_code)
                ->where('citizenship', $request->citizenship)
                ->where('serial_number', $request->serial_number)
                ->first();
       
            if (!$personal) {
                $personal = Personal::create([
                    'full_name' => $request->full_name,
                    'birth_date' => $request->birth_date,
                    'gender' => $request->gender,
                    'region_code' => $request->region_code,
                    'township_code' => $request->township_code,
                    'citizenship' => $request->citizenship,
                    'serial_number' => $request->serial_number,
                ]);
            } else {
                // Check if personal already used by a teacher
                $existingTeacher = Teacher::where('personal_id', $personal->id)->first();
                if ($existingTeacher) {
                    return response()->json(['error' => 'This personal is already assigned to another teacher.'], 409);
                }
            }

            $teacher = Teacher::create([
                'personal_id' => $personal->id,
                'teacher_code' => $request->teacher_code,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'qualification' => $request->qualification,
                'subject' => $request->subject,
                'experience_years' => $request->experience_years ?? 0,
                'salary' => $request->salary,
                'hire_date' => $request->hire_date,
                'status' => $request->status ?? 'active',
                'employment_type' => $request->employment_type ?? 'full-time',
            ]);
    
            DB::commit();
    
            return response()->json(['message' => 'Teacher created successfully.', 'teacher' => $teacher], 201);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create teacher: ' . $e->getMessage()], 500);
        }
    }

    public function show(Request $request)
    {
        // Validate the incoming request to ensure the 'slug' is provided
        $validated = $request->validate([
            'slug' => 'required|string|exists:teachers,slug',
        ]);

        // Retrieve the student using the slug
        $teacher = Teacher::with(['personal']) // Include related models if needed
            ->where('slug', $validated['slug'])
            ->first();

        // If the student is not found, return a 404 error
        if (!$teacher) {
            return response()->json(['error' => 'Teacher not found'], 404);
        }

        // Return the student data
        return response()->json($teacher);
    }

    public function update(Request $request)
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

            // Teacher fields
            'teacher_code' => "required|string|unique:teachers,teacher_code,$id",
            'email' => "nullable|email|unique:teachers,email,$id",
            'phone' => "nullable|string|unique:teachers,phone,$id",
            'address' => 'nullable|string',
            'qualification' => 'nullable|string',
            'subject' => 'nullable|string',
            'experience_years' => 'nullable|integer|min:0',
            'salary' => 'required|numeric|min:0',
            'hire_date' => 'required|date',
            'status' => 'nullable|in:active,resigned,on_leave',
            'employment_type' => 'nullable|in:full-time,part-time,contract',
        ]);

        try {
            DB::beginTransaction();

            $teacher = Teacher::findOrFail($id);
            $personal = $teacher->personal;

            // Update personal data
            $personal->update([
                'full_name' => $request->full_name,
                'birth_date' => $request->birth_date,
                'gender' => $request->gender,
                'region_code' => $request->region_code,
                'township_code' => $request->township_code,
                'citizenship' => $request->citizenship,
                'serial_number' => $request->serial_number,
            ]);

            // Update teacher data
            $teacher->update([
                'teacher_code' => $request->teacher_code,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'qualification' => $request->qualification,
                'subject' => $request->subject,
                'experience_years' => $request->experience_years ?? 0,
                'salary' => $request->salary,
                'hire_date' => $request->hire_date,
                'status' => $request->status ?? 'active',
                'employment_type' => $request->employment_type ?? 'full-time',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Teacher updated successfully.',
                'teacher' => $teacher->fresh('personal'),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to update teacher: ' . $e->getMessage(),
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
                return response()->json(['message' => 'Teacher status set to active']);

            case 'resigned':
                $teacher->status = 'resigned';
                $teacher->save();
                return response()->json(['message' => 'Teacher resigned']);

            case 'on_leave':
                $teacher->status = 'on_leave';
                $teacher->save();
                return response()->json(['message' => 'Teacher is on leave']);

            case 'delete':
                $teacher->status = 'resigned'; // or 'inactive' if you prefer
                $teacher->save();
                $teacher->delete();
                return response()->json(['message' => 'Teacher soft-deleted']);

            case 'restore':
                if ($teacher->trashed()) {
                    $teacher->restore();
                    $teacher->status = 'active'; // or previous state
                    $teacher->save();
                    return response()->json(['message' => 'Teacher restored']);
                }
                return response()->json(['message' => 'Teacher is not deleted'], 400);

            default:
                return response()->json(['message' => 'Invalid action'], 400);
        }
    }

}
