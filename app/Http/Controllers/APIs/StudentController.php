<?php

namespace App\Http\Controllers\APIs;

use Exception;
use Carbon\Carbon;
use App\Models\Student;
use App\Models\Guardian;
use App\Models\Personal;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\PersonalUpdate;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class StudentController extends Controller
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
                'status' => 'sometimes|in:active,resigned,on_leave,terminated',
            ]);

            $limit = $validated['limit'] ?? null;
            $search = $validated['search'] ?? null;
            $status = $validated['status'] ?? null;

            // Build query with eager loading
            $query = Student::with(['personal', 'guardians'])->orderBy('student_name', 'asc');

            if(!empty($validated['slugs'])) {
                // If slugs are provided, filter by slugs
                $query->whereIn('slug', $validated['slugs']);
            }

            // Apply status filter if provided
            if ($status) {
                $query->where('status', $status);
            }

            // Apply search filter if provided
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('student_name', 'like', "%{$search}%")
                    ->orWhere('student_number', 'like', "%{$search}%");
                });
            }

            $total = (clone $query)->count();

            if (!empty($validated['skip'])) {
                $query->skip($validated['skip']);
            }
            if (!empty($validated['limit'])) {
                $query->take($validated['limit']);
            }

            $students = $query->get();

            // Replace 'personal' relation with latest update if available
            $students->transform(function ($student) {
                $latestUpdate = PersonalUpdate::where('updatable_type', Student::class)
                    ->where('updatable_slug', $student->slug)
                    ->where('personal_slug', $student->personal_slug)
                    ->latest()
                    ->first();

                $student->setRelation('personal', $latestUpdate ?? $student->personal);
                return $student;
            });

            // Respond with paginated or simple data
            return response()->json([
                'status' => 'OK! The request was successful',
                'total' => $total,
                'data' => $students,
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'status' => 'false',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {

        // ✅ Validate the input
        $validator = Validator::make($request->all(), [
            'student_number' => 'required|string|unique:students,student_number',
            'registration_number' => 'nullable|string|unique:students,registration_number',
            'school_name' => 'required|string',
            'school_code' => 'nullable|string',
            'email' => 'nullable|email|unique:students,email',
            'phone' => 'nullable|string|unique:students,phone',
            'address' => 'nullable|string',
            'status' => 'required|in:enrolled,graduated,suspended,inactive',
            'graduation_date' => 'nullable|date',
            'admission_date' => 'nullable|date',

            'personal.full_name' => 'required|string',
            'personal.gender' => 'required|in:male,female,other',
            'personal.birth_date' => 'nullable|date',
            'personal.region_code' => 'required|string',
            'personal.township_code' => 'required|string',
            'personal.serial_number' => 'required|string',
            'personal.nationality' => 'required|string',
            'personal.citizenship' => 'required|string',
            'personal.religion' => 'nullable|string',
            'personal.blood_type' => 'nullable|string',

            'guardians' => 'nullable|array',
            'guardians.*.full_name' => 'required|string',
            'guardians.*.birth_date' => 'nullable|date',
            'guardians.*.region_code' => 'required|string',
            'guardians.*.township_code' => 'required|string',
            'guardians.*.serial_number' => 'required|string',
            'guardians.*.citizenship' => 'required|string',
            'guardians.*.relation' => 'required|string',
            'guardians.*.occupation' => 'nullable|string',
            'guardians.*.phone' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

            // ✅ Create Personal
            $personalData = $request->input('personal');

            $personalData = Personal::create([
                'full_name' => $personalData['full_name'],
                'gender' => $personalData['gender'],
                'birth_date' => $personalData['birth_date'] ?? null,
                'region_code' => $personalData['region_code'],
                'township_code' => $personalData['township_code'],
                'serial_number' => $personalData['serial_number'],
                'nationality' => $personalData['nationality'],
                'citizenship' => $personalData['citizenship'],
                'religion' => $personalData['religion'] ?? null,
                'blood_type' => $personalData['blood_type'] ?? null,
            ]);

            // ✅ Create Student
            $studentSlug = Str::uuid()->toString();

            $student = Student::create([
                'personal_slug' => $personalData->slug,
                'student_name' => $personalData->full_name,
                'student_number' => $request->input('student_number'),
                'registration_number' => $request->input('registration_number'),
                'school_name' => $request->input('school_name'),
                'school_code' => $request->input('school_code'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'address' => $request->input('address'),
                'status' => $request->input('status', 'enrolled'),
                'graduation_date' => $request->input('graduation_date'),
                'admission_date' => $request->input('admission_date'),
            ]);

            // ✅ Create Guardians (if any)
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
                            'gender' => $guardianData['relation'] === 'Mother' ? 'female' : 'male',
                            'region_code' => $guardianData['region_code'],
                            'township_code' => $guardianData['township_code'],
                            'citizenship' => $guardianData['citizenship'],
                            'serial_number' => $guardianData['serial_number'],
                        ]);
                    }

                    // Create guardian record and associate it with student and personal
                    Guardian::create([
                        'student_slug' => $student->slug,
                        'personal_slug' => $personalGuardian->slug,
                        'relation' => $guardianData['relation'],
                        'occupation' => $guardianData['occupation'] ?? null,
                        'name' => $personalGuardian->full_name,
                        'phone' => $guardianData['phone'] ?? null,
                        'email' => $guardianData['email'] ?? null,
                    ]);
                }

            DB::commit();

            return response()->json([
                'message' => 'Student created successfully.',
                'data' => $student->load('personal', 'guardians')
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create student.',
                'error' => $e->getMessage(),
            ], 500);
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
            return response()->json([
                'status' => 'Not Found',
                'message' => 'Stutdent not found'
            ], 404);
        }

        // Check if a personal update exists for this student
        $latestUpdate = PersonalUpdate::where('updatable_type', Student::class)
            ->where('updatable_slug', $student->slug)
            ->where('personal_slug', $student->personal_slug)
            ->latest()
            ->first();

        // Use updated personal if it exists, otherwise original
        $personalData = $latestUpdate ?? $student->personal;

        // Replace the student->personal with latest data (either original or updated)
        $student->setRelation('personal', $personalData);

        // Return the student with either updated or original personal data
        return response()->json([
            'status' => 'OK! The request was successful',
            'data' => $student,
        ]);
    }

    public function update(Request $request)
    {
        try{
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
                    'student_name' => $request->full_name,
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
                        'personal_slug'    => $student->personal->slug,
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
                        'updatable_slug'   => $student->slug,
                        'updatable_type' => Student::class,
                    ]);
                }

                DB::commit();

                return response()->json([
                    'message' => 'Student updated successfully',
                    'data' => $student->load('personal')
                ], 200);

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
                return response()->json(['message' => 'Student status set to enrolled'],200);

            case 'graduated':
                $student->status = 'graduated';
                $student->save();
                return response()->json(['message' => 'Student graduated'],200);

            case 'suspended':
                $student->status = 'suspended';
                $student->save();
                return response()->json(['message' => 'Student suspended'],200);

            case 'inactive':
                $student->status = 'inactive';
                $student->save();
                return response()->json(['message' => 'Student deactivated'],200);

            case 'delete':
                $student->status = 'inactive';
                $student->save();
                $student->delete();
                return response()->json(['message' => 'Student soft-deleted'],200);

            case 'restore':
                if ($student->trashed()) {
                    $student->restore();
                    $student->status = 'enrolled'; // or whatever is appropriate
                    $student->save();
                    return response()->json(['message' => 'Student restored'],200);
                }
                return response()->json(['message' => 'Student is not deleted'], 400);

            default:
                return response()->json(['message' => 'Invalid action'], 400);
        }
    }

    public function enrollment(Request $request)
    {
        $request->validate([
            'student_slugs' => 'required|array',
            'student_slugs.*' => 'string|exists:students,slug',
        ]);

        $students = Student::whereIn('slug', $request->student_slugs)->get();
        return response()->json($students);
    }
   
}
