<div class="p-6 space-y-6 max-w-5xl mx-auto bg-white shadow rounded-lg" style="margin-left: 15%">
    <!-- Stepper UI -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        @foreach ([
        1 => ['icon' => '📝', 'label' => 'Application Check'],
        2 => ['icon' => '💼', 'label' => 'Work Experience'],
        3 => ['icon' => '📎', 'label' => 'Document Upload'],
        4 => ['icon' => '✅', 'label' => 'Final Preview & Submit'],
    ] as $s => $data)
            <div class="flex items-center space-x-2">
                <div
                    class="w-8 h-8 flex items-center justify-center rounded-full text-white transition-all duration-300 {{ $step === $s ? 'bg-blue-600 scale-110' : 'bg-gray-400' }}">
                    {{ $data['icon'] }}
                </div>
                <span
                    class="{{ $step === $s ? 'text-blue-600 font-semibold' : 'text-gray-600' }}">{{ $data['label'] }}</span>
            </div>
        @endforeach
    </div>

    @if ($step === 1)
        <div class="mb-4">
            <label class="block font-semibold">Have you already applied?</label>
            <label>
                <input type="radio" wire:model="has_applied" name="has_applied" id="has_applied" wire:click="$refresh"
                    value="1"> Yes
            </label>
            <label class="ml-4">
                <input type="radio" wire:model="has_applied" name="has_applied" id="has_applied" wire:click="$refresh"
                    value="0"> No
            </label>
            @error('has_applied')
                <div class="text-red-600">{{ $message }}</div>
            @enderror
        </div>

        @if ((string) $has_applied === '0')
            <div class="mt-4 p-4 border rounded bg-gray-50">
                <!-- Payment Fields Go Here -->
                <x-input-field label="Transaction ID" model="transaction_number" class="form-input" />
                <label class="block text-sm font-medium">Payment Amount</label>
                <input type="number" wire:model="amount" readonly class="w-full border p-2 rounded mt-1">
                <x-input-field label="Bank Name" model="bank_name" class="form-input" />
                <x-input-field label="Payment Date" model="payment_date" type="date" class="form-input" />
                <div class="mt-2">
                    <label>Upload Receipt</label>
                    <input type="file" wire:model="receipt" class="w-full border p-2 rounded">
                    @error('receipt')
                        <span class="text-red-600">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        @endif
    @endif

    @if ($step === 2)
        <div class="space-y-6">
            <h2 class="text-lg font-semibold text-gray-800">Work Experience</h2>
            <div class="mb-4 border p-4 rounded bg-gray-100">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                    <!-- Left Side -->
                    <div class="grid grid-cols-2 gap-x-4 gap-y-2">
                        <span><strong>Name:</strong> {{ auth()->user()->full_name }}</span>
                        <span><strong>DOB:</strong> {{ auth()->user()->dob->format('d-m-Y') }}</span>
                        <span>
                            <strong>Age:</strong>
                            {{ now()->diff(auth()->user()->dob)->y }} years
                            {{ now()->diff(auth()->user()->dob)->m }} months
                            {{ now()->diff(auth()->user()->dob)->d }} days
                        </span>
                        <span><strong>Gender:</strong> {{ auth()->user()->gender }}</span>
                    </div>

                    <!-- Right Side -->
                    <div class="grid grid-cols-2 gap-x-4 gap-y-2">
                        <span><strong>Category:</strong> {{ auth()->user()->category }}</span>
                        <span><strong>Mobile:</strong> {{ auth()->user()->mobile }}</span>
                        <span><strong>Email:</strong> {{ auth()->user()->email }}</span>
                    </div>
                </div>
            </div>
            @foreach ($experiences as $index => $experience)
                <div class="grid grid-cols-1 md:grid-cols-6 gap-4 border p-4 rounded shadow-sm bg-gray-50">
                    <x-input-array label="Institution" model="experiences.{{ $index }}.institution" />
                    <x-input-array label="Designation" model="experiences.{{ $index }}.designation" />
                    <x-input-array label="From" model="experiences.{{ $index }}.from_date" type="date"
                        onchange="calculateTotal({{ $index }})" />
                    <x-input-array label="To" model="experiences.{{ $index }}.to_date" type="date"
                        onchange="calculateTotal({{ $index }})" />
                    <input type="text" readonly wire:model="experiences.{{ $index }}.total_period"
                        placeholder="Total Period" class="border p-2 bg-gray-100 rounded">
                    <x-input-array label="Subjects Taught" model="experiences.{{ $index }}.subjects_taught" />

                    <div class="md:col-span-6">
                        <label>Status</label>
                        <select wire:model="experiences.{{ $index }}.status" class="border p-2 w-full rounded">
                            <option value="">Select Status</option>
                            <option value="current">Current</option>
                            <option value="previous">Previous</option>
                        </select>
                    </div>
                    <div class="md:col-span-6">
                        <button type="button" wire:click="removeExperience({{ $index }})"
                            class="text-red-600 text-sm mt-2">🗑 Remove</button>
                    </div>
                </div>
            @endforeach

            <button type="button" wire:click="addExperience"
                class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                ➕ Add Experience
            </button>
        </div>
    @endif

    @if ($step === 3)
        <div class="space-y-4">
            <h2 class="text-lg font-semibold text-gray-800">Document Upload</h2>

            @foreach ($documentFields as $key => $label)
                @if ($key === 'bed' && auth()->user()->subject !== 'Agriculture')
                    @continue
                @endif

                @if ($key === 'employment2' && count($experiences) < 2)
                    @continue
                @endif

                <div>
                    <label class="block text-sm font-medium">{{ $label }}</label>
                    <input type="file" wire:model="documents.{{ $key }}"
                        class="mt-1 block w-full text-sm border p-2 rounded">
                    @error("documents.$key")
                        <span class="text-red-600 text-sm">{{ $message }}</span>
                    @enderror
                </div>
            @endforeach

            <div wire:loading wire:target="documents" class="text-blue-600 text-sm">Uploading documents, please wait...
            </div>
        </div>

    @endif

    @if ($step === 4)
        <div class="space-y-4">
            <h2 class="text-lg font-semibold text-gray-800">Final Preview</h2>

            <div class="bg-gray-100 p-4 rounded text-sm space-y-2">
                <p><strong>Applied Subject:</strong> {{ auth()->user()->subject ?? '-' }}</p>
                <p><strong>Experience:</strong></p>
                <ul class="ml-4 list-disc">
                    @foreach ($experiences as $exp)
                        <li>{{ $exp['institution'] }} - {{ $exp['designation'] }} ({{ $exp['from_date'] }} to
                            {{ $exp['to_date'] }})</li>
                    @endforeach
                </ul>
            </div>

            <button wire:click="submitFinal"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-all duration-200">
                🚀 Submit Application
            </button>
        </div>
    @endif

    <div class="flex justify-between mt-6">
        @if ($step > 1)
            <button wire:click="previousStep"
                class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition">⬅ Back</button>
        @endif

        <button wire:click="nextStep" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition"
            {{ $step === 4 ? 'disabled' : '' }}>
            {{ $step === 4 ? '📤 Submit' : '➡ Next' }}
        </button>
    </div>
    <script>
        window.addEventListener('swal', event => {
            Swal.fire({
                icon: 'warning',
                text: 'Please fill in at least one experience detail.',
                title: 'No Experience Found',
                showConfirmButton: true,
                timer: 5000,
                timerProgressBar: true,
                willClose: () => {}
            });
        });
    </script>
</div>
