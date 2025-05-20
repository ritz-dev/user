<?php

namespace App\Http\Controllers\APIs;

use Exception;
use App\Models\Student;
use App\Models\Guardian;
use App\Models\Personal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\StudentResource;


class StudentController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(Student::with('personal','guardians')->get());
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

        // Retrieve the student using the slug
        $student = Student::with(['guardians', 'personal']) // Include related models if needed
            ->where('slug', $validated['slug'])
            ->first();

        // If the student is not found, return a 404 error
        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        // Return the student data
        return response()->json($student);
    }

    public function update(Request $request)
    {
        // Validate the incoming request data
        $validated = $request->validate([
            'slug' => 'required|string|exists:students,slug',
            'student_number' => 'nullable|string|unique:students,student_number',
            'school_name' => 'nullable|string',
            'school_code' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'status' => 'nullable|in:enrolled,graduated,suspended,inactive',
            'graduation_date' => 'nullable|date',
            'admission_date' => 'nullable|date',

            // Student personal
            'personal.full_name' => 'nullable|string',
            'personal.birth_date' => 'nullable|date',
            'personal.gender' => 'nullable|in:male,female',
            'personal.region_code' => 'nullable|string',
            'personal.township_code' => 'nullable|string',
            'personal.citizenship' => 'nullable|string',
            'personal.serial_number' => 'nullable|string',
            'personal.nationality' => 'nullable|string',
            'personal.religion' => 'nullable|string',
            'personal.blood_type' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',

        ]);

        // Retrieve the student using the slug
        $student = Student::where('slug', $validated['slug'])->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        DB::beginTransaction();

        try {
            // Update the student details
            $student->update([
                'student_number' => $request->student_number ?? $student->student_number,
                'school_name' => $request->school_name ?? $student->school_name,
                'school_code' => $request->school_code ?? $student->school_code,
                'email' => $request->email ?? $student->email,
                'phone' => $request->phone ?? $student->phone,
                'address' => $request->address ?? $student->address,
                'status' => $request->status ?? $student->status,
                'graduation_date' => $request->graduation_date ?? $student->graduation_date,
                'admission_date' => $request->admission_date ?? $student->admission_date,
            ]);

            // Update the student's personal details if provided
            if ($request->has('personal')) {
                $personal = $student->personal; // Assuming there's a one-to-one relation
                $personal->update([
                    'full_name' => $request->personal['full_name'] ?? $personal->full_name,
                    'birth_date' => $request->personal['birth_date'] ?? $personal->birth_date,
                    'gender' => $request->personal['gender'] ?? $personal->gender,
                    'region_code' => $request->personal['region_code'] ?? $personal->region_code,
                    'township_code' => $request->personal['township_code'] ?? $personal->township_code,
                    'citizenship' => $request->personal['citizenship'] ?? $personal->citizenship,
                    'serial_number' => $request->personal['serial_number'] ?? $personal->serial_number,
                    'nationality' => $request->personal['nationality'] ?? $personal->nationality,
                    'religion' => $request->personal['religion'] ?? $personal->religion,
                    'blood_type' => $request->personal['blood_type'] ?? $personal->blood_type,
                ]);
            }

            // Update the guardian details if provided
            if ($request->has('guardian')) {
                foreach ($request->guardian as $guardianData) {
                    // If a guardian already exists, update, else create
                    $guardian = $student->guardians()->where('personal_id', $guardianData['personal_id'])->first();

                    if ($guardian) {
                        $guardian->update([
                            'relation' => $guardianData['relation'] ?? $guardian->relation,
                            'occupation' => $guardianData['occupation'] ?? $guardian->occupation,
                            'phone' => $guardianData['phone'] ?? $guardian->phone,
                            'email' => $guardianData['email'] ?? $guardian->email,
                        ]);
                    } else {
                        // If no guardian exists, create a new one
                        $personalGuardian = Personal::create([
                            'slug' => uniqid(),
                            'full_name' => $guardianData['personal']['full_name'],
                            'birth_date' => $guardianData['personal']['birth_date'],
                            'gender' => $guardianData['personal']['gender'],
                            'region_code' => $guardianData['personal']['region_code'],
                            'township_code' => $guardianData['personal']['township_code'],
                            'citizenship' => $guardianData['personal']['citizenship'],
                            'serial_number' => $guardianData['personal']['serial_number'],
                            'nationality' => $guardianData['personal']['nationality'] ?? null,
                            'religion' => $guardianData['personal']['religion'] ?? null,
                            'blood_type' => $guardianData['personal']['blood_type'] ?? null,
                        ]);

                        Guardian::create([
                            'student_id' => $student->id,
                            'personal_id' => $personalGuardian->id,
                            'relation' => $guardianData['relation'],
                            'occupation' => $guardianData['occupation'] ?? null,
                            'phone' => $guardianData['phone'] ?? null,
                            'email' => $guardianData['email'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json(['message' => 'Student updated successfully'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to update student: ' . $e->getMessage()], 500);
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
            'student_ids.*' => 'integer|exists:students,id',
        ]);

        logger($request->student_ids);
    
        $students = Student::whereIn('slug', $request->student_ids)->get();
    
        return response()->json($students);
    }
   
}
