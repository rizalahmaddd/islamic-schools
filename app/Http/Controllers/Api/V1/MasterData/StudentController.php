<?php

namespace App\Http\Controllers\Api\V1\MasterData;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\Api\V1\MasterData\StudentRequest;
use App\Http\Resources\V1\MasterData\StudentResource;
use App\Models\Student;
use App\Support\OpenApi\Attributes\ApiQuery;
use App\Support\OpenApi\Attributes\ApiResponse;
use App\Support\OpenApi\Attributes\ApiTag;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

#[ApiTag('Siswa & Santri', 'Master Data')]
class StudentController extends Controller
{
    /**
     * Daftar siswa dan santri.
     */
    #[ApiQuery('search', description: 'Cari NIS atau nama.')]
    #[ApiQuery('status', 'string', 'Filter berdasarkan status (active, inactive, dll).')]
    #[ApiResponse(StudentResource::class, paginated: true)]
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('view-master-data');

        $records = Student::query()
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('search'), fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$request->search}%")
                ->orWhere('nis', 'like', "%{$request->search}%")))
            ->orderBy('name')
            ->paginate($this->perPage($request));

        return StudentResource::collection($records);
    }

    /**
     * Detail siswa atau santri.
     */
    public function show(Student $student): StudentResource
    {
        Gate::authorize('view-master-data');

        return new StudentResource($student);
    }

    /**
     * Tambah data santri baru.
     */
    #[ApiResponse(StudentResource::class, status: 201)]
    public function store(StudentRequest $request): StudentResource
    {
        return new StudentResource(Student::create($request->validated()));
    }

    /**
     * Perbarui data santri.
     */
    public function update(StudentRequest $request, Student $student): StudentResource
    {
        $student->update($request->validated());

        return new StudentResource($student);
    }

    /**
     * Hapus data santri.
     */
    #[ApiResponse(status: 204)]
    public function destroy(Student $student): Response
    {
        Gate::authorize('manage-master-data');

        $student->delete();

        return response()->noContent();
    }
}
