<?php

namespace App\Http\Controllers\APIs;

use Exception;
use App\Models\Student;
use App\Models\Guardian;
use App\Models\Personal;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\PersonalUpdate;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;

class StudentController extends Controller
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

            $query = Student::with(['personal', 'guardians'])->orderBy('student_name', 'asc');

            // Filter by slugs if provided
            if (!empty($validated['slugs'])) {
                $query->whereIn('slug', $validated['slugs']);
            }

            // Filter by status if provided
            if (!empty($validated['status'])) {
                $query->where('status', $validated['status']);
            }

            if (!empty($validated['search'])) {
                $filter = $validated['search'];
                $allowedFields = ['student_name', 'student_number', 'status'];

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

            // Total count before pagination
            $total = (clone $query)->count();

            // Pagination
            if (!empty($validated['skip'])) {
                $query->skip($validated['skip']);
            }
            if (!empty($validated['limit'])) {
                $query->take($validated['limit']);
            }

            $students = $query->get();

            // Replace personal relation with latest update if exists
            $students->transform(function ($student) {
                $latestUpdate = PersonalUpdate::where('updatable_type', Student::class)
                    ->where('updatable_slug', $student->slug)
                    ->where('personal_slug', $student->personal_slug)
                    ->latest()
                    ->first();

                $student->setRelation('personal', $latestUpdate ?? $student->personal);
                return $student;
            });

            return response()->json([
                'status' => 'success',
                'total'  => $total,
                'data'   => $students,
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'validation_error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Student index error', ['error' => $e->getMessage()]);

            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {

        // ✅ Validate the input
        $validated = $request->validate([
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

            'guardians' => 'required|array',
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
            foreach ($request->guardians as $guardianData) {

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

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            \Log::error('Student store error', ['error' => $e->getMessage()]);

            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create student.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Request $request)
    {
        try {
        $validated = $request->validate([
            'slug' => 'required|string|exists:students,slug',
        ]);

        $student = Student::with(['personal', 'guardians.personal'])
            ->where('slug', $validated['slug'])
            ->firstOrFail();

        // Load latest personal update for student
        $latestStudentPersonal = PersonalUpdate::where('updatable_type', Student::class)
            ->where('updatable_slug', $student->slug)
            ->where('personal_slug', $student->personal_slug)
            ->latest()
            ->first();

        $studentPersonal = $latestStudentPersonal ?? $student->personal;

        // Transform guardians with latest personal update
        $guardians = $student->guardians->map(function ($guardian) {
            $latest = PersonalUpdate::where('updatable_type', get_class($guardian))
                ->where('updatable_slug', $guardian->slug)
                ->where('personal_slug', $guardian->personal_slug)
                ->latest()
                ->first();

            $personal = $latest ?? $guardian->personal;

            return [
                'slug'           => $guardian->slug,
                'full_name'      => $personal->full_name,
                'birth_date'     => $personal->birth_date,
                'gender'         => $personal->gender,
                'region_code'    => $personal->region_code,
                'township_code'  => $personal->township_code,
                'citizenship'    => $personal->citizenship,
                'serial_number'  => $personal->serial_number,
                'relation'       => $guardian->relation,
                'occupation'     => $guardian->occupation,
                'phone'          => $guardian->phone,
                'email'          => $guardian->email,
            ];
        });

        // Format student response
        $response = [
            'slug'                => $student->slug,
            'student_name'        => $student->student_name,
            'student_number'      => $student->student_number,
            'registration_number' => $student->registration_number,
            'school_name'         => $student->school_name,
            'school_code'         => $student->school_code,
            'email'               => $student->email,
            'phone'               => $student->phone,
            'address'             => $student->address,
            'status'              => $student->status,
            'graduation_date'     => $student->graduation_date,
            'admission_date'      => $student->admission_date,
            'personal' => [
                'slug'           => $studentPersonal->slug,
                'full_name'      => $studentPersonal->full_name,
                'birth_date'     => $studentPersonal->birth_date,
                'gender'         => $studentPersonal->gender,
                'region_code'    => $studentPersonal->region_code,
                'township_code'  => $studentPersonal->township_code,
                'citizenship'    => $studentPersonal->citizenship,
                'serial_number'  => $studentPersonal->serial_number,
                'nationality'    => $studentPersonal->nationality,
                'religion'       => $studentPersonal->religion,
                'blood_type'     => $studentPersonal->blood_type,
            ],
            'guardians' => $guardians,
        ];

        return response()->json($response);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            Log::error('Student show error', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Failed to retrieve student.',
                'error' => $e->getMessage(),
            ], 500);
        } catch (\Throwable $e) {
            Log::error('Student show error', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Failed to retrieve student.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function update(Request $request)
    {
        try {
            
            // ✅ Validate input
            $validated = $request->validate([
                'slug' => 'required|string|exists:students,slug',
                'student_number' => [
                    'required',
                    'string',
                    Rule::unique('students', 'student_number')->ignore($request->slug, 'slug')
                ],
                'registration_number' => [
                    'nullable',
                    'string',
                    Rule::unique('students', 'registration_number')->ignore($request->slug, 'slug')
                ],
                'email' => [
                    'nullable',
                    'email',
                    Rule::unique('students', 'email')->ignore($request->slug, 'slug')
                ],
                'phone' => [
                    'nullable',
                    'string',
                    Rule::unique('students', 'phone')->ignore($request->slug, 'slug')
                ],
                'school_name' => 'required|string',
                'school_code' => 'nullable|string',
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

            DB::beginTransaction();

            // ✅ Fetch existing student
            $student = Student::where('slug', $request->slug)->firstOrFail();

            // ✅ Update student fields
            $student->update([
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

            // ✅ Update related personal
            $personalData = $request->input('personal');
            $student->personal->update([
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

            // ✅ Update Guardians
            if ($request->filled('guardians')) {
                // Optional: Delete existing guardians and recreate (simpler logic)
                Guardian::where('student_slug', $student->slug)->delete();

                foreach ($request->guardians as $guardianData) {
                    // Lookup or create personal record for guardian
                    $personalGuardian = Personal::where('region_code', $guardianData['region_code'])
                        ->where('township_code', $guardianData['township_code'])
                        ->where('citizenship', $guardianData['citizenship'])
                        ->where('serial_number', $guardianData['serial_number'])
                        ->first();

                    if (!$personalGuardian) {
                        $personalGuardian = Personal::create([
                            'full_name' => $guardianData['full_name'],
                            'birth_date' => $guardianData['birth_date'] ?? null,
                            'gender' => $guardianData['relation'] === 'Mother' ? 'female' : 'male',
                            'region_code' => $guardianData['region_code'],
                            'township_code' => $guardianData['township_code'],
                            'citizenship' => $guardianData['citizenship'],
                            'serial_number' => $guardianData['serial_number'],
                        ]);
                    }

                    // Create guardian
                    Guardian::create([
                        'student_slug' => $student->slug,
                        'personal_slug' => $personalGuardian->slug,
                        'relation' => $guardianData['relation'],
                        'occupation' => $guardianData['occupation'] ?? null,
                        'name' => $personalGuardian->full_name,
                        'phone' => $guardianData['phone'],
                        'email' => $guardianData['email'] ?? null,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Student updated successfully.',
                'data' => $student->load('personal', 'guardians'),
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            Log::error('Student update error', ['error' => $e->getMessage()]);

            DB::rollBack();

            return response()->json([
                'message' => 'Failed to update student.',
                'error' => $e->getMessage(),
            ], 500);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update student.',
                'error' => $e->getMessage(),
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
