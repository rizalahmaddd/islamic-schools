<?php

namespace App\Events;

use App\Events\Concerns\BroadcastsToDashboard;
use App\Models\Student;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Broadcasting\ShouldRescue;
use Illuminate\Foundation\Events\Dispatchable;

class StudentChanged implements ShouldBroadcastNow, ShouldRescue
{
    use BroadcastsToDashboard, Dispatchable;

    public function __construct(public Student $student) {}

    public function broadcastAs(): string
    {
        return 'student.changed';
    }

    /**
     * @return array{id: int, nis: string}
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->student->id,
            'nis' => $this->student->nis,
        ];
    }
}
