@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="my-4">Envoyer le lien de la réunion</h2>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h4>Détails de la réunion</h4>
        </div>
        <div class="card-body">
            <form id="sendMeetingForm" action="{{ route('send.meeting.info') }}" method="POST"
                onsubmit="return validatePrices();">
                @csrf
                <!-- Lien de la réunion -->
                <div class="form-group">
                    <label for="meetLink">Lien de la réunion</label>
                    <input type="text" id="meetLink" class="form-control" name="meet_link"
                        value="{{ $data['meet_link'] }}" required>
                </div>

                <!-- Prénom du patient -->
                <div class="form-group">
                    <label for="patient_first_name">Prénom du patient</label>
                    <input type="text" id="patient_first_name" class="form-control" name="patient_first_name"
                        value="{{ $data['patient_first_name'] }}" required>
                </div>
                <!-- Nom de famille du patient -->
                <div class="form-group">
                    <label for="patient_last_name">Nom de famille du patient</label>
                    <input type="text" id="patient_last_name" class="form-control" name="patient_last_name"
                        value="{{ $data['patient_last_name'] }}" required>
                </div>
                <!-- Téléphone du patient -->
                <div class="form-group">
                    <label for="patientPhone">Téléphone du patient</label>
                    <input type="text" id="patientPhone" class="form-control" name="phone" value="{{ $data['phone'] }}"
                        required>
                </div>
                <!-- Email du patient -->
                <div class="form-group">
                    <label for="patientEmail">Email du patient</label>
                    <input type="email" id="patientEmail" class="form-control" name="email"
                        value="{{ $data['patient_Email'] }}" required>
                </div>
                <!-- Heure de début -->
                <div class="form-group">
                    <label for="startAt">Heure de début</label>
                    <input type="datetime-local" id="startAt" class="form-control" name="start_at"
                        value="{{ $data['start_at'] }}" required>
                </div>

                <!-- patient_id -->
                <div class="form-group">
                    <input type="hidden" id="patient_id" class="form-control" name="patient_id"
                        value="{{ $data['patient_id'] }}">
                </div>
                <!-- appointment_id -->
                <div class="form-group">
                    <input type="hidden" id="appointment_id" class="form-control" name="appointment_id"
                        value="{{ $data['appointment_id'] }}">
                </div>
                <!-- Prix en TND -->
                <div class="form-group">
                    <label for="tele_price_tnd">Prix (TND)</label>
                    <input type="number" id="tele_price_tnd" class="form-control" name="tele_price_tnd"
                        value="{{ $data['tele_price_tnd'] }}" step="0.01" min="0" required>
                    <small class="text-danger">Le prix doit être supérieur ou égal à 0 et en dinars tunisiens (exemple :
                        100) et non en
                        millimes.</small>
                </div>

                <!-- Prix en EUR -->
                <div class="form-group">
                    <label for="tele_price_eur">Prix (EUR)</label>
                    <input type="number" id="tele_price_eur" class="form-control" name="tele_price_eur"
                        value="{{ $data['tele_price_eur'] }}" step="0.01" min="0" required>
                    <small class="text-danger">Le prix doit être supérieur ou égal à 0 et en euros (exemple :
                        100).</small>
                </div>

                <!-- Boutons -->
                <div class="form-group mt-4">
                    <a href="{{ $data['meet_link'] }}" class="btn btn-primary" target="_blank">Rejoindre la réunion</a>
                    <button type="button" class="btn btn-secondary" onclick="copyToClipboard()">Copier le lien</button>
                    <button type="submit" class="btn btn-success">Envoyer les informations de la réunion</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function copyToClipboard() {
        const meetLink = document.getElementById('meetLink');
        navigator.clipboard.writeText(meetLink.value).then(() => {
            alert('Le lien de la réunion a été copié dans le presse-papiers !');
        }).catch(err => {
            alert('Impossible de copier le lien de la réunion.');
        });
    }

    function validatePrices() {
        const telePriceTnd = parseFloat(document.getElementById('tele_price_tnd').value) || 0;
        const telePriceEur = parseFloat(document.getElementById('tele_price_eur').value) || 0;

        if (telePriceTnd < 0 || telePriceEur < 0) {
            alert('Les prix doivent être supérieurs ou égaux à 0.');
            return false; // Prevent form submission
        }

        if (telePriceTnd === 0 && telePriceEur === 0) {
            alert('Veuillez renseigner au moins un prix (en TND ou en EUR) avant d\'envoyer.');
            return false; // Prevent form submission
        }
        return true; // Allow form submission
    }
</script>
@endsection
