@component('mail::message')
# Application Submitted

Dear {{ $user->name }},

Your application has been submitted successfully.

**Acknowledgement No:** {{ $user->acknowledgement_no }}
@component('mail::button', ['url' => $pdfUrl])
Download Acknowledgement PDF
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
