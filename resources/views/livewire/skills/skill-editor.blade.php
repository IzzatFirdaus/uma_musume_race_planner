<div>
    <h2 class="text-lg font-bold mb-4">Skill Editor</h2>

    <div class="space-y-4">
        @foreach ($skills as $index => $skill)
            <div class="flex items-center space-x-4">
                <input type="text" wire:model="skills.{{ $index }}.name" placeholder="Skill Name" class="border rounded px-2 py-1">
                <input type="number" wire:model="skills.{{ $index }}.level" placeholder="Level" min="1" max="5" class="border rounded px-2 py-1">
                <button type="button" wire:click="removeSkill({{ $index }})" class="text-red-500">Remove</button>
            </div>
        @endforeach
    </div>

    <button type="button" wire:click="addSkill" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded">Add Skill</button>

    <button type="button" wire:click="save" class="mt-4 px-4 py-2 bg-green-500 text-white rounded">Save</button>

    @if (session()->has('message'))
        <div class="mt-4 text-green-500">
            {{ session('message') }}
        </div>
    @endif
</div>
