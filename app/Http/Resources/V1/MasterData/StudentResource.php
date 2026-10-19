<?php

namespace App\Http\Resources\V1\MasterData;

use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Student
 */
class StudentResource extends JsonResource
{
    /**
     * @return array{id: int, nis: string, nisn: string|null, name: string, gender: string, pob: string|null, dob: string|null, address: string|null, status: string}
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nis' => $this->nis,
            'nisn' => $this->nisn,
            'name' => $this->name,
            'gender' => $this->gender,
            'pob' => $this->pob,
            'dob' => $this->dob ? Carbon::parse($this->dob)->toDateString() : null,
            'address' => $this->address,
            'status' => (string) $this->status,
        ];
    }
}
