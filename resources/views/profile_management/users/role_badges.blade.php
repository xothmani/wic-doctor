@php
    $roleTranslations = [
        'Secretary' => 'Secrétaire',
        'Telesecretary' => 'Télésecrétaire',
        'Doctor' => 'Médecin',
    ];
@endphp

@foreach ($roles as $role)
    <span class="badge badge-info">
        {{ $roleTranslations[$role] ?? $role }}
    </span>
@endforeach