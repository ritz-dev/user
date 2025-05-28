<?php

namespace App\Http\Controllers\APIs;

use Exception;
use Carbon\Carbon;
use App\Models\Student;
use App\Models\Guardian;
use App\Models\Personal;
use Illuminate\Http\Request;
use App\Models\PersonalUpdate;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\StudentResource;


class StudentController extends Controller
{
    public function index(Request $request)
    {
        $students = Student::with(['personal', 'guardians'])->get();

        // For each student, check for latest personal update
        $students->transform(function ($student) {
            $latestUpdate = PersonalUpdate::where('updatable_type', Student::class)
                ->where('updatable_id', $student->id)
                ->where('personal_id', $student->personal_id)
                ->latest()
                ->first();

            // Override the personal relationship with the update if it exists
            $student->setRelation('personal', $latestUpdate ?? $student->personal);

            return $student;
        });

        return response()->json($students);
        // try {
        //     $limit = (int) $request->limit;
        //     $search = $request->search;

        //     $query = Student::orderBy('id', 'desc');

        //     if ($search) {
        //         $query->where('name', 'LIKE', $search . '%');
        //     }

        //     $data = $limit ? $query->paginate($limit) : $query->get();

        //     $data = StudentResource::collection($data);

        //     $total = Student::count();

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
        $validated = $request->validate([
            //student
            'student_number' => 'required|string|unique:students,student_number',
            'registration_number' => 'nullable|string',
            'school_name' => 'required|string',
            'school_code' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'status' => 'required|in:enrolled,graduated,suspended,inactive',
            'graduation_date' => 'nullable|date',
            'admission_date' => 'nullable|date',

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
                    'nationality' => $request->nationality,
                    'religion' => $request->religion,
                    'blood_type' => $request->blood_type,
                ]);
            } else {
                // Check if personal is already linked to a student
                $existingStudent = Student::where('personal_id', $personal->id)->first();
                if ($existingStudent) {
                    return response()->json([
                        'error' => 'This personal information is already exist.'
                    ], 409);
                }
            }

            // Create the student record
            $student = Student::create([
                'personal_id' => $personal->id,
                'student_number' => $request->student_number,
                'registration_number' => $request->registration_number,
                'school_name' => $request->school_name,
                'school_code' => $request->school_code,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'status' => $request->status,
                'graduation_date' => $request->graduation_date,
                'admission_date' => $request->admission_date,
            ]);

            // Create guardians if provided
            foreach ($request->guardian as $guardianData) {

                // Check if personal record for guardian exists, if not, create it
                $personalGuardian = Personal::where('region_code', $guardianData['region_code'])
                    ->where('township_code', $guardianData['township_code'])
                    ->where('citizenship', $guardianData['citizenship'])
                    ->where('serial_number', $guardianData['serial_number'])
                    ->first();

                if (!$personalGuardian) {
                    // Create a new personal record for the guardian if NRC fields do not exist
                    $personalGuardian = Personal::create([
                        'full_name' => $guardianData['full_name'],
                        'birth_date' => $guardianData['birth_date'],
                        'gender' => $guardianData['gender'],
                        'region_code' => $guardianData['region_code'],
                        'township_code' => $guardianData['township_code'],
                        'citizenship' => $guardianData['citizenship'],
                        'serial_number' => $guardianData['serial_number'],
                        'nationality' => $guardianData['nationality'] ?? null,
                        'religion' => $guardianData['religion'] ?? null,
                        'blood_type' => $guardianData['blood_type'] ?? null,
                    ]);
                }

                // Create guardian record and associate it with student and personal
                Guardian::create([
                    'student_id' => $student->id,
                    'personal_id' => $personalGuardian->id,
                    'relation' => $guardianData['relation'],
                    'occupation' => $guardianData['occupation'] ?? null,
                    'phone' => $guardianData['phone'] ?? null,
                    'email' => $guardianData['email'] ?? null,
                ]);
            }

            DB::commit();

            return response()->json(['message' => 'Student created successfully'], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create student: ' . $e->getMessage()], 500);
        }
    }

    public function show(Request $request)
    {
         // Validate the incoming request to ensure the 'slug' is provided
        $validated = $request->validate([
            'slug' => 'required|string|exists:students,slug',
        ]);

        // Retrieve the student with personal and guardians
        $student = Student::with(['guardians', 'personal']) // Include related models if needed
            ->where('slug', $validated['slug'])
            ->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        // Check if a personal update exists for this student
        $latestUpdate = PersonalUpdate::where('updatable_type', Student::class)
            ->where('updatable_id', $student->id)
            ->where('personal_id', $student->personal_id)
            ->latest()
            ->first();

        // Use updated personal if it exists, otherwise original
        $personalData = $latestUpdate ?? $student->personal;

        // Replace the student->personal with latest data (either original or updated)
        $student->setRelation('personal', $personalData);

        // Return the student with either updated or original personal data
        return response()->json($student);
    }

    public function update(Request $request)
    {
        // Sanitize empty strings to null
        $request->merge(array_map(function ($value) {
            return $value === '' ? null : $value;
        }, $request->all()));

        $student = Student::where('slug', $request->slug)->with('personal')->firstOrFail();

        // Validate the incoming request data
        $validated = $request->validate([
            // student
            'slug' => 'required|string',
            'student_number' => ['required', 'string', Rule::unique('students', 'student_number')->ignore($student->id)],
            'registration_number' => 'nullable|string',
            'school_name' => 'required|string',
            'school_code' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'status' => 'required|in:enrolled,graduated,suspended,inactive',
            'graduation_date' => 'nullable|date',
            'admission_date' => 'nullable|date',

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
            // Update the student details
            $student->fill([
                'student_number' => $request->student_number,
                'registration_number' => $request->registration_number,
                'school_name' => $request->school_name,
                'school_code' => $request->school_code,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'status' => $request->status,
                'graduation_date' => $request->graduation_date,
                'admission_date' => $request->admission_date,
            ])->save();

            // Check if any personal field has changed
            $fields = [
                'full_name', 'birth_date', 'gender',
                'region_code', 'township_code', 'citizenship',
                'serial_number', 'nationality', 'religion', 'blood_type'
            ];

            $hasChanges = false;

            foreach ($fields as $field) {
                $original = $student->personal->$field;
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
                    'personal_id'    => $student->personal->id,
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
                    'updatable_id'   => $student->id,
                    'updatable_type' => Student::class,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Student updated successfully',
                'student' => $student->load('personal')
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Failed to update student', [
                'error' => $e->getMessage(),
                'student_id' => $student->id ?? null,
                'request' => $request->all(),
                'user_id' => auth()->id(),
            ]);

            return response()->json(['error' => 'Failed to update student'], 500);
        }
    }

    public function handleAction(Request $request)
    {
        $request->validate([
            'slug' => 'required|string|exists:students,slug',
            'action' => 'required|string|in:enrolled,graduated,suspended,inactive,restore,delete',
        ]);

        $slug = $request->input('slug');
        $action = $request->input('action');

        // Support soft-deleted models as well
        $student = Student::withTrashed()->where('slug', $slug)->firstOrFail();

        switch ($action) {
            case 'enrolled':
                $student->status = 'enrolled';
                $student->save();
                return response()->json(['message' => 'Student status set to enrolled']);

            case 'graduated':
                $student->status = 'graduated';
                $student->save();
                return response()->json(['message' => 'Student graduated']);

            case 'suspended':
                $student->status = 'suspended';
                $student->save();
                return response()->json(['message' => 'Student suspended']);

            case 'inactive':
                $student->status = 'inactive';
                $student->save();
                return response()->json(['message' => 'Student deactivated']);

            case 'delete':
                $student->status = 'inactive';
                $student->save();
                $student->delete();
                return response()->json(['message' => 'Student soft-deleted']);

            case 'restore':
                if ($student->trashed()) {
                    $student->restore();
                    $student->status = 'enrolled'; // or whatever is appropriate
                    $student->save();
                    return response()->json(['message' => 'Student restored']);
                }
                return response()->json(['message' => 'Student is not deleted'], 400);

            default:
                return response()->json(['message' => 'Invalid action'], 400);
        }
    }

    public function enrollment(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'string|exists:students,slug',
        ]);
    
        $students = Student::whereIn('slug', $request->student_ids)->get();
    
        return response()->json($students);
    }
   
}
