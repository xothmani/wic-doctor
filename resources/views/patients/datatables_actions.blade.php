<div class='btn-group btn-group-sm'>
    @php
        $doctorId = auth()->user()->getDoctorId();
    @endphp

    {{-- Boutons indépendants --}}
    @if(auth()->user()->hasPermissionInContext('consultations.create', $doctorId))
        <a data-toggle="tooltip" title="{{ trans('lang.add_consultation') }}"
           href="{{ route('consultations.create', ['patient_id' => $id]) }}" class='btn btn-link'>
            <i class="fas fa-plus"></i>
        </a>
    @endif

    @if(auth()->user()->hasPermissionInContext('fiche.show', $doctorId))
        <a data-toggle="tooltip" title="{{ trans('lang.view_fiche') }}"
           href="{{ route('fiche.show', $id) }}" class='btn btn-link'>
            <i class="fas fa-file-alt"></i>
        </a>
    @endif

     @if(auth()->user()->hasPermissionInContext('patient_files.index', $doctorId))
        <a data-toggle="tooltip" title="{{ trans('lang.view_patient_files') }}"
           href="{{ route('patient_files.index', $id) }}" class='btn btn-link'>
            <i class="fas fa-cloud-upload-alt"></i>
        </a>
    @endif

    @if(auth()->user()->hasPermissionInContext('patients.edit', $doctorId))
        <a data-toggle="tooltip" title="{{ trans('lang.patient_edit') }}"
           href="{{ route('patients.edit', $id) }}" class='btn btn-link'>
            <i class="fas fa-edit"></i>
        </a>
    @endif

    {{-- Menu déroulant pour les messages --}}
    <div class="btn-group">
        <button type="button" class="btn btn-link dropdown-toggle" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false" title="Messages">
            <i class="fas fa-comment-dots"></i>
        </button>
        <div class="dropdown-menu dropdown-menu-right p-0">
            @if(auth()->user()->hasPermissionInContext('patients.email', $doctorId))
                <a class="dropdown-item small" href="{{ route('patients.email', $id) }}">
                    <i class="fas fa-envelope text-primary mr-1"></i> Envoyer un email
                </a>
            @endif

            @if(auth()->user()->hasPermissionInContext('patients.whatsapp', $doctorId))
                <a class="dropdown-item small" href="{{ route('patients.whatsapp', $id) }}">
                    <i class="fab fa-whatsapp text-success mr-1"></i> Envoyer via Whatsapp
                </a>
            @endif

            @can('sms.send')
                <a class="dropdown-item small" href="#" data-toggle="modal" data-target="#smsModal_{{ $id }}">
                    <i class="fas fa-sms text-info mr-1"></i> SMS personnalisé
                </a>
            @endcan

            @can('messages.view')
                <a href="{{ route('messages.history', $id) }}" data-patient-id="{{ $id }}" class="dropdown-item small view-history">
                    <i class="fas fa-history text-secondary mr-1"></i> Historique des messages
                </a>
            @endcan
           
        </div>
    </div>

    {{-- Suppression --}}
    @if(auth()->user()->hasPermissionInContext('patients.destroy', $doctorId))
        {!! Form::open(['route' => ['patients.destroy', $id], 'method' => 'delete', 'style' => 'display:inline']) !!}
        {!! Form::button('<i class="fas fa-trash"></i>', [
            'type' => 'submit',
            'class' => 'btn btn-link text-danger',
            'onclick' => "return confirm('Êtes-vous sûr de vouloir supprimer ce patient ?')"
        ]) !!}
        {!! Form::close() !!}
    @endif
</div>


<!-- Modal SMS -->
<div class="modal fade" id="smsModal_{{ $id }}" tabindex="-1" role="dialog" aria-labelledby="smsModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('sms.store') }}" method="POST">
            @csrf
            <input type="hidden" name="patient_id" value="{{ $id }}"> <!-- patient_id caché -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="smsModalLabel">Envoyer un SMS personnalisé</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong>Numéro :</strong> {{ $phone_number ?? 'Non disponible' }}</p>
                    @php
    // Tentative de décoder le JSON pour 'name'
    $decodedName = json_decode(auth()->user()->name);
    $name = is_object($decodedName) && isset($decodedName->fr) ? $decodedName->fr : auth()->user()->name;

    // Tentative de décoder le JSON pour 'lastname'
    $decodedLastname = json_decode(auth()->user()->lastname);
    $lastname = is_object($decodedLastname) && isset($decodedLastname->fr) ? $decodedLastname->fr : auth()->user()->lastname;

    // Message par défaut
    $defaultMessage = "Bienvenue cher patient chez Wic-Dr, avec Dr. " . $name . " " . $lastname . "\nINSÉRER VOTRE MESSAGE ICI ...\nL'équipe Wic-Dr";
@endphp



                    <div class="form-group">
                        <label for="message">Message :</label>
                        <textarea class="form-control message-textarea" name="message" rows="6" required
                            data-id="{{ $id }}" id="message_{{ $id }}">{{ $defaultMessage }}</textarea>
                        <small id="charCount_{{ $id }}">{{ strlen($defaultMessage) }} caractères</small><br>
                        <small id="smsWarning_{{ $id }}" class="text-danger" style="display: none;">
                            ⚠️ Un SMS contient 160 caractères. Si vous dépassez cette limite, le SMS sera considéré
                            comme deux messages ou plus.
                        </small>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn bg-{{setting('theme_color')}}">Envoyer</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function () {
        // Stocker le message par défaut pour chaque textarea
        const defaultMessages = {};

        // Initialiser quand le modal est sur le point d'être affiché
        $('#smsModal_{{ $id }}').on('show.bs.modal', function () {
            const textarea = $('#message_{{ $id }}');
            const id = textarea.data('id');

            // Sauvegarder le message par défaut si ce n'est pas déjà fait
            if (!defaultMessages[id]) {
                defaultMessages[id] = textarea.val();
            }

            // Réinitialiser le contenu
            textarea.val(defaultMessages[id]);

            // Mettre à jour l'affichage
            updateSMSDisplay(textarea[0]);
        });

        // Fonction de mise à jour
        function updateSMSDisplay(textarea) {
            const id = $(textarea).data('id');
            const length = $(textarea).val().length;
            const warningElement = $('#smsWarning_' + id);

            $('#charCount_' + id).text(length + ' caractères');
            warningElement.toggle(length >= 160);
        }

        // Initialisation au chargement de la page
        $('.message-textarea').each(function () {
            const id = $(this).data('id');
            defaultMessages[id] = $(this).val();
            $('#smsWarning_' + id).hide();
            updateSMSDisplay(this);
        });

        // Gérer les modifications en temps réel
        $('.message-textarea').on('input', function () {
            updateSMSDisplay(this);
        });
    });
</script>
<div class="modal fade" id="messagesModalDynamic" tabindex="-1" role="dialog" aria-labelledby="messagesModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Historique des messages personnalisés</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="messagesContent">
                <!-- Contenu AJAX ici -->
                <p>Chargement...</p>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
        $('.view-history').on('click', function (e) {
            e.preventDefault();

            const url = $(this).attr('href');
            $('#messagesContent').html('<p>Chargement...</p>');

            $.get(url, function (data) {
                $('#messagesContent').html(data);
                $('#messagesModalDynamic').modal('show');
            }).fail(function () {
                $('#messagesContent').html('<p class="text-danger">Erreur lors du chargement des messages.</p>');
            });
        });
    });
</script>

<style>
    .timeline {
        position: relative;
        margin-left: 20px;
        /* Espace pour la ligne verticale et les points */
        border-left: 2px solid #ccc;
        /* Le trait vertical à gauche */
        padding-left: 20px;
    }

    .timeline::before {
        border-radius: .25rem;
        background-color: #fff !important;
        bottom: 0;
        content: "";
        left: 31px;
        margin: 0;
        position: absolute;
        top: 0;
        width: 4px;
    }

    .timeline-entry {
        position: relative;
        margin-bottom: 20px;
    }

    .timeline-entry::before {
        content: "";
        position: absolute;
        left: -29px;
        /* Positionné sur la ligne verticale */
        top: 10px;
        /* Aligné avec la date */
        width: 15px;
        height: 15px;
        background-color: rgb(255, 255, 255);
        border-radius: 50%;
        border: 2px solid #5b6cc3;
        z-index: 1;
        /* Pour s'assurer que le point est au-dessus de la ligne */
    }

    .timeline-time {
        font-weight: bold;
        margin-bottom: 5px;
        position: relative;
        /* Pour positionner correctement par rapport au point */
    }

    .timeline-card {
        background: #f9f9f9;
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    /* Rendre la zone de contenu scrollable si elle dépasse une certaine hauteur */
    .modal-body {
        max-height: 70vh;
        /* ou une hauteur fixe comme 500px */
        overflow-y: auto;
    }

    /* Optionnel : améliore le style du scroll sur certains navigateurs */
    .modal-body::-webkit-scrollbar {
        width: 8px;
    }

    .modal-body::-webkit-scrollbar-thumb {
        background-color: #bbb;
        border-radius: 4px;
    }

    .modal-body::-webkit-scrollbar-thumb:hover {
        background-color: #999;
    }

    .modal-content {
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
    }

    .modal-header {
        background-color: #5b6cc3;
        color: white;
        border-bottom: none;
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
    }

    .modal-title {
        font-weight: bold;
    }

    .close {
        color: white;
        opacity: 1;
    }
    .btn-group-sm .btn-link {
    opacity: 1 !important;
    transition: none !important;
}
</style>


