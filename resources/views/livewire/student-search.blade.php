<div class="text-center mt-4">
    <input type="text" wire:model.debounce.300ms="studentName" class="form-control w-50 mx-auto" placeholder="Cari nama murid...">

    @if(strlen($studentName) > 1)
        <div class="mt-4">
            <h5>Hasil carian:</h5>
            <ul class="list-group">
                @forelse ($students as $student)
                    <li class="list-group-item">
                        {{ $student->student_name }} – {{ $student->class_name }}
                    </li>
                @empty
                    <li class="list-group-item text-muted">Tiada rekod dijumpai.</li>
                @endforelse
            </ul>
        </div>
    @endif
</div>