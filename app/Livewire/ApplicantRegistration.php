<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Models\PaymentDetail;

class ApplicantRegistration extends Component
{
    use WithFileUploads;

    public $step = 1;
    public $experiences = [];
    public $documents = [];

    // Step 1 fields
    public $has_applied = null;
    public $transaction_number;
    public $amount;
    public $bank_name;
    public $payment_date;
    public $receipt;

    public array $documentFields = [
        'photo' => 'Photo',
        'signature' => 'Signature',
        'dob_certificate' => 'Date of Birth Proof - Matriculation Certificate',
        'caste_certificate' => 'Caste Certificate',
        'id_proof' => 'Photo Identity Proof',
        'tenth' => '10th Marksheet',
        'twelfth' => '12th Marksheet',
        'graduation' => 'Graduation',
        'post_graduation' => 'Post-Graduation',
        'bed' => 'B.Ed.',
        'employment1' => 'Employment-1',
        'employment2' => 'Employment-2',
    ];

    public function render()
    {
        return view('livewire.applicant-registration');
    }

    public function updatedHasApplied($value)
    {
        $this->has_applied = (string) $value;
        if ($this->has_applied === '0') {
            $user = auth()->user();
            $this->amount = $user->category === 'General' ? 1000 : 500;
        }
    }

    public function validateStepOne()
    {
        $rules = [
            'has_applied' => 'required|in:0,1',
        ];

        if ((string) $this->has_applied === '0') {
            $rules = array_merge($rules, [
                'transaction_number' => 'required|unique:payment_details,transaction_number',
                'amount' => 'required|numeric|min:0',
                'bank_name' => 'required|string',
                'payment_date' => 'required|date',
                'receipt' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            ]);
        }

        $this->validate($rules);
    }

    public function nextStep()
    {
        if ($this->step === 1 && (string)$this->has_applied === '0') {
            $this->validate([
                'transaction_number' => 'required',
                'amount' => 'required|numeric',
                'bank_name' => 'required',
                'payment_date' => 'required|date',
                // 'receipt' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            ]);
        }
        if ($this->step === 2) {
            if (empty($this->experiences) || !array_filter($this->experiences, function($exp) {
                return !empty($exp['institution']) && !empty($exp['designation']) && !empty($exp['from_date']) && !empty($exp['to_date']);
            })) {
                $this->dispatch('swal');
                return;
            }
        }
        $this->step++;
    }
    public function previousStep()
    {
        $this->step--;
    }
    public function experienceValidationRules(): array
    {
        $rules = [
            'experiences' => 'required|array|min:1',
            'experiences.*.institution' => 'required|string',
            'experiences.*.designation' => 'required|string',
            'experiences.*.from_date' => 'required|date',
            'experiences.*.to_date' => 'required|date|after_or_equal:experiences.*.from_date',
        ];
    
        $messages = [
            'experiences.min' => 'You must add at least one work experience.',
        ];
    
        return [$rules, $messages];
    }
    
    public function addExperience()
    {
        $this->experiences[] = [
            'institution' => '',
            'designation' => '',
            'from_date' => '',
            'to_date' => '',
            'total_period' => '',
            'subjects_taught' => '',
            'status' => '',
        ];
    }

    public function removeExperience($index)
    {
        unset($this->experiences[$index]);
        $this->experiences = array_values($this->experiences);
    }

    public function calculateTotal($index)
    {
        $from = $this->experiences[$index]['from_date'];
        $to = $this->experiences[$index]['to_date'];

        if ($from && $to) {
            try {
                $start = Carbon::parse($from);
                $end = Carbon::parse($to);

                if ($start > $end) {
                    $this->experiences[$index]['total_period'] = 'Invalid';
                    return;
                }

                $diff = $start->diff($end);
                $this->experiences[$index]['total_period'] = "{$diff->y} yr {$diff->m} mo";
            } catch (\Exception $e) {
                $this->experiences[$index]['total_period'] = 'Error';
            }
        }
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, $this->documentValidationRules());
    }

    public function documentValidationRules(): array
    {
        $rules = [];
        foreach (array_keys($this->documentFields) as $key) {
            $rules["documents.$key"] = 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048';
        }
        return $rules;
    }
    public function personalInfoValidationRules(): array
    {
        return [];
    }

    public function submitFinal()
    {
        $this->validate(array_merge(
            $this->documentValidationRules(),
            $this->experienceValidationRules(),
            $this->personalInfoValidationRules()
        ));

        try {
            DB::transaction(function () {
                $user = auth()->user();
                $ack = $this->generateAckNumber();
                $user->update([
                    'ack_no' => $ack,
                    'enrollment_status' => 'enrolled'
                ]);

                if ((string) $this->has_applied === '0') {
                    PaymentDetail::create([
                        'user_id' => $user->id,
                        'transaction_number' => $this->transaction_number,
                        'amount' => $this->amount,
                        'bank_name' => $this->bank_name,
                        'payment_date' => $this->payment_date,
                        'receipt_path' => $this->receipt->store('receipt_path', 'public'),
                    ]);
                }

                foreach ($this->experiences as $index => $exp) {
                    $experience = $user->experiences()->create($exp);

                    foreach ($this->documents as $key => $file) {
                        if ($key === 'bed' && $user->subject !== 'Agriculture') continue;
                        if ($key === 'employment2' && count($this->experiences) < 2) continue;

                        $filename = $file->store("documents/{$user->id}", 'public');
                        $user->documentUploads()->create([
                            'experience_id' => in_array($key, ['employment1', 'employment2']) ? $experience->id : null,
                            'document_type' => $key,
                            'file_path' => $filename,
                        ]);
                    }
                }

                $pdf = Pdf::loadView('pdf.acknowledgement', ['user' => $user]);
                $pdfPath = 'acknowledgements/' . $ack . '.pdf';
                Storage::disk('public')->put($pdfPath, $pdf->output());

                Mail::to($user->email)->send(new \App\Mail\ApplicationSubmittedMail($user, $pdfPath));
            });

            $this->reset();
            session()->flash('success', 'Application submitted successfully.');
            return redirect()->to('/dashboard');
        } catch (\Exception $e) {
            dd($e);
            session()->flash('error', 'Submission failed. Please try again.');
            report($e);
        }
    }

    public function generateAckNumber()
    {
        return str_pad(mt_rand(1, 9999999999), 10, '0', STR_PAD_LEFT);
    }
}
