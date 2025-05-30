<!-- resources/views/pdf/prescription_template.blade.php -->
<div style="font-family: 'Arial', sans-serif; padding: 20px;">
    <h2 align="center">Medical Prescription</h2>
    <hr>

    <p><strong>Doctor Name:</strong> {{ $doctor->first_name. ' '. $doctor->last_name }} ({{ $doctor->specialty ?? 'No Specialty' }})</p>
    <p><strong>Patient Name:</strong> {{ $patient->first_name. ' '. $patient->last_name }}</p>
    <p><strong>Date:</strong> {{ date('Y-m-d') }}</p>

    @if($prescription->note)
        <p><strong>Doctor's Notes:</strong> {{ $prescription->note }}</p>
    @endif

    <hr>
    <h4>Prescribed Medicines:</h4>
    <table width="100%" border="1" cellspacing="0" cellpadding="5">
        <thead>
            <tr>
                <th>Medicine</th>
                <th>Dosage</th>
                <th>Frequency</th>
                <th>Strength</th>
                <th>Until</th>
                <th>When to Take</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @foreach($medicines as $med)
            <tr>
                <td>{{ $med->name }}</td>
                <td>{{ $med->dose }}</td>
                <td>{{ $med->frequency }}</td>
                <td>{{ $med->strength }}</td>
                <td>{{ $med->until }}</td>
                <td>{{ $med->whenToTake }}</td>
                <td>{{ $med->note }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <br><br>
    <hr>
    {{-- <p><strong>Doctor's Signature:</strong></p>
    @if($signatureExists)
       <img src="{{asset($signatureRelativePath)}}" alt="Doctor Signature" style="width: 150px; height: auto; margin-top: 10px;">
    @else
        <p><em>No signature available.</em></p>
    @endif --}}

    <br><br>
    <p>Generated by system at {{ date('Y-m-d H:i') }}</p>
</div>
