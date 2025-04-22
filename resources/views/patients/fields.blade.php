
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    @if($customFields)
    <h5 class="col-12 pb-4">{!! trans('lang.main_fields') !!}</h5>
    @endif

    <div class="d-flex flex-column col-sm-12 col-md-6">
    <p class="text-left mb-2" style="font-size: 14px; color: red; font-weight: bold;">
    * {{trans('lang.required_fields')}}
    </p>

        <!-- Image Field -->
        <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
            {!! Form::label('image', trans("lang.patient_image"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
            <div class="col-md-9">
                <div style="width: 100%" class="dropzone image" id="image" data-field="image">
                </div>
                <a href="#loadMediaModal" data-dropzone="image" data-toggle="modal" data-target="#mediaModal" class="btn btn-outline-{{setting('theme_color','primary')}} btn-sm float-right mt-1">{{ trans('lang.media_select')}}</a>
                <div class="form-text text-muted w-50">
                    {{ trans("lang.patient_image_help") }}
                </div>
            </div>
        </div>

        @if(!isset($patient) || !$patient->id)
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('assign_users', trans("Assigner sous-profils"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
        <button type="button" class="btn btn-outline-{{ setting('theme_color','primary') }}" 
                id="assignUserBtn" 
                data-toggle="modal" 
                data-target="#assignUserModal">
                <i class="fas fa-user-plus"></i> {{ trans('Assigner sous-profils') }}
            </button>
            <small class="form-text text-muted text-gray-600">
            Ajouter un accompagnant pour soutenir ce patient !            </small>
        </div>
    </div>
    @endif


        <!-- First Name Field -->
        <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
            {!! Form::label('first_name', trans("lang.patient_first_name"), ['class' => 'col-md-3 control-label text-md-right']) !!}
            <span class="text-danger">*</span>
            <div class="col-md-9">
                {!! Form::text('first_name', null,  ['class' => 'form-control','placeholder'=>  trans("lang.patient_first_name_placeholder")]) !!}
                <div class="form-text text-muted">
                    {{ trans("lang.patient_first_name_help") }}
                </div>
            </div>
        </div>
        

    <!-- Last Name Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
    {!! Form::label('last_name', trans("lang.patient_last_name"), ['class' => 'col-md-3 control-label text-md-right']) !!}
    <span class="text-danger">*</span>
    <div class="col-md-9">
        {!! Form::text('last_name', null,  ['class' => 'form-control','placeholder'=>  trans("lang.patient_last_name_placeholder")]) !!}
        <div class="form-text text-muted">
        {{ trans("lang.patient_last_name_help") }}
        </div>
    </div>
    </div>

    <!-- Email Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
    {!! Form::label('email', trans("lang.email"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
        {!! Form::email('email', $patient->user->email ?? null, ['class' => 'form-control','placeholder'=>  trans("lang.user_email_placeholder")]) !!}
        <div class="form-text text-muted">
        {{ trans("lang.user_email_help") }}
        </div>
    </div>
    </div>



    {{--<!-- ID Card Field -->--}}
    {{--    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">--}}
    {{--        {!! Form::label('id_card', trans("lang.patient_id_card"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}--}}
    {{--        <div class="col-md-9">--}}
    {{--            <div style="width: 100%" class="dropzone id_card" id="id_card" data-field="id_card">--}}
    {{--            </div>--}}
    {{--            <a href="#loadMediaModal" data-dropzone="image" data-toggle="modal" data-target="#mediaModal" class="btn btn-outline-{{setting('theme_color','primary')}} btn-sm float-right mt-1">{{ trans('lang.media_select')}}</a>--}}
    {{--            <div class="form-text text-muted w-50">--}}
    {{--                {{ trans("lang.patient_id_card_help") }}--}}
    {{--            </div>--}}
    {{--        </div>--}}
    {{--    </div>--}}
    {{--    @prepend('scripts')--}}
    {{--        <script type="text/javascript">--}}
    {{--            var var1666017496669196819ble = [];--}}
    {{--            @if(isset($patient) && $patient->hasMedia('card_id'))--}}
    {{--            @forEach($patient->getMedia('card_id') as $card_id)--}}
    {{--            var1666017496669196819ble.push({--}}
    {{--                name: "{!! $card_id->name !!}",--}}
    {{--                size: "{!! $card_id->size !!}",--}}
    {{--                type: "{!! $card_id->mime_type !!}",--}}
    {{--                uuid: "{!! $card_id->getCustomProperty('uuid'); !!}",--}}
    {{--                thumb: "{!! $card_id->getUrl('thumb'); !!}",--}}
    {{--                collection_name: "{!! $card_id->collection_name !!}"--}}
    {{--            });--}}
    {{--            @endforeach--}}
    {{--            @endif--}}
    {{--            var dz_var1666017496669196819ble = $(".dropzone.image").dropzone({--}}
    {{--                url: "{!!url('uploads/store')!!}",--}}
    {{--                addRemoveLinks: true,--}}
    {{--                maxFiles: 5 - var1666017496669196819ble.length,--}}
    {{--                init: function () {--}}
    {{--                    @if(isset($patient) && $patient->hasMedia('card_id'))--}}
    {{--                    var1666017496669196819ble.forEach(card_id => {--}}
    {{--                        dzInit(this, card_id, card_id.thumb);--}}
    {{--                    });--}}
    {{--                    @endif--}}
    {{--                },--}}
    {{--                accept: function (file, done) {--}}
    {{--                    dzAccept(file, done, this.element, "{!!config('media-library.icons_folder')!!}");--}}
    {{--                },--}}
    {{--                sending: function (file, xhr, formData) {--}}
    {{--                    dzSendingMultiple(this, file, formData, '{!! csrf_token() !!}');--}}
    {{--                },--}}
    {{--                complete: function (file) {--}}
    {{--                    dzCompleteMultiple(this, file);--}}
    {{--                    dz_var1666017496669196819ble[0].mockFile = file;--}}
    {{--                },--}}
    {{--                removedfile: function (file) {--}}
    {{--                    dzRemoveFileMultiple(--}}
    {{--                        file, var1666017496669196819ble, '{!! url("patients/remove-media") !!}',--}}
    {{--                        'card_id', '{!! isset($patient) ? $patient->id : 0 !!}', '{!! url("uploads/clear") !!}', '{!! csrf_token() !!}'--}}
    {{--                    );--}}
    {{--                }--}}
    {{--            });--}}
    {{--            dz_var1666017496669196819ble[0].mockFile = var1666017496669196819ble;--}}
    {{--            dropzoneFields['card_id'] = dz_var1666017496669196819ble;--}}
    {{--        </script>--}}
    {{--    @endprepend--}}

    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.min.css">

    <!-- Phone Number Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('phone_number', trans("lang.patient_phone_number"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <span class="text-danger">*</span>

        <div class="col-md-9">
            <div id="phone-number-input">
            {!! Form::text('phone_number', null, ['class' => 'form-control', 'id' => 'phone-input', 'placeholder' => trans("lang.patient_phone_number_placeholder"), 'style' => 'width: 545px;',     'onkeypress' => 'return event.charCode >= 48 && event.charCode <= 57'
                ]) !!}
            </div>
            <div class="form-text text-muted">
                {{ trans("lang.patient_phone_number_help") }}
            </div>
        </div>
    </div>



    <!-- Mobile Number Field -->
        <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
            {!! Form::label('mobile_number', trans("lang.patient_mobile_number"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
            <div class="col-md-9">
                {!! Form::text('mobile_number', null,  ['class' => 'form-control','placeholder'=>  trans("lang.patient_mobile_number_placeholder"), 'onkeypress' => 'return event.charCode >= 48 && event.charCode <= 57']) !!}
                <div class="form-text text-muted">
                    {{ trans("lang.patient_mobile_number_help") }}
                </div>
            </div>
        </div>
        <!-- CNSS and Assurance Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('cnss_assurance', trans("lang.patient_cnss_assurance"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9 d-flex align-items-center">
            <div class="form-check mx-2 d-flex align-items-center">
                {!! Form::checkbox('cnss', 1, null, ['class' => 'form-check-input', 'id' => 'cnss', 'onchange' => 'toggleFields()']) !!}
                {!! Form::label('cnss', trans("lang.patient_cnss"), ['class' => 'form-check-label mx-1']) !!}
            </div>
            <div class="form-check mx-2 d-flex align-items-center">
                {!! Form::checkbox('assurance', 1, null, ['class' => 'form-check-input', 'id' => 'assurance', 'onchange' => 'toggleFields()']) !!}
                {!! Form::label('assurance', trans("lang.patient_assurance"), ['class' => 'form-check-label mx-1']) !!}
            </div>
        </div>
    </div>

    <!-- Matricule CNSS Field -->
    <div id="cnssFields" class="d-none">
        <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
            {!! Form::label('matriculeCNSS', trans("lang.patient_matricule_cnss"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
            <div class="col-md-9">
                {!! Form::text('matriculeCNSS', null, ['class' => 'form-control', 'placeholder' => trans("lang.patient_matricule_cnss_placeholder"), 'id' => 'matriculeCNSS']) !!}
                <div class="form-text text-muted">
                    {{ trans("lang.patient_matricule_cnss_help") }}
                </div>
            </div>
        </div>
        <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
            {!! Form::label('dateExpiration', trans("lang.patient_date_expiration"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
            <div class="col-md-9">
                {!! Form::date('dateExpiration', null, ['class' => 'form-control', 'id' => 'dateExpiration']) !!}
                <div class="form-text text-muted">
                    {{ trans("lang.patient_date_expiration_help") }}
                </div>
            </div>
        </div>
    </div>

    <!-- Assurance Field -->
    <div id="assuranceField" class="d-none">
        <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
            {!! Form::label('assurance', trans("lang.patient_assurance"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
            <div class="col-md-9">
                <select name="assurance" id="assuranceFieldInput" class="form-control">
                    <option value="" disabled {{ old('assurance', $patient->assurance ?? '') == '' ? 'selected' : '' }}>
                        {{ trans("lang.patient_assurance_placeholder") }}
                    </option>
                    @foreach($assurances as $id => $nom)
                        <option value="{{ $id }}" {{ old('assurance', $patient->assurance ?? '') == $id ? 'selected' : '' }}>
                            {{ $nom }}
                        </option>
                    @endforeach
                </select>
                <div class="form-text text-muted">
                    {{ trans("lang.patient_assurance_help") }}
                </div>
            </div>
        </div>
    </div>




    <!-- Notes Field -->
        <div class="form-group align-items-baseline d-flex flex-column flex-md-row ">
            {!! Form::label('notes', trans("lang.patient_notes"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
            <div class="col-md-9">
                {!! Form::textarea('notes', null, ['class' => 'form-control','placeholder'=>
                trans("lang.patient_notes_placeholder")  ]) !!}
                <div class="form-text text-muted">{{ trans("lang.patient_notes_help") }}</div>
            </div>
        </div>



    </div>
    <div class="d-flex flex-column col-sm-12 col-md-6">






    <!-- Gender Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('gender', trans("lang.patient_gender"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <span class="text-danger">*</span>

        <div class="col-md-9">
            {!! Form::select('gender', [
                'homme' => trans('lang.patient_male'), 
                'femme' => trans('lang.patient_female'), 
                'autre' => trans('lang.patient_other')
            ], null, ['class' => 'select2 form-control']) !!}
            <div class="form-text text-muted">{{ trans("lang.patient_gender_help") }}</div>
        </div>
    </div>
    <!-- Date Naissance Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('date_naissance', trans("lang.patient_date_naissance"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <span class="text-danger">*</span>

        <div class="col-md-9">
            {!! Form::date('date_naissance', old('date_naissance', $patient->date_naissance ?? null), ['class' => 'form-control']) !!}
            <div class="form-text text-muted">
                {{ trans("lang.patient_date_naissance_help") }}
            </div>
        </div>
    </div>



    <!-- Weight Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
    {!! Form::label('weight', trans("lang.patient_weight"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
        {!! Form::text('weight', null,  ['class' => 'form-control','placeholder'=>  trans("lang.patient_weight_placeholder")]) !!}
        <div class="form-text text-muted">
        {{ trans("lang.patient_weight_help") }}
        </div>
    </div>
    </div>
    <!-- Height Field -->
        <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('height', trans("lang.patient_height"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::text('height', null,  ['class' => 'form-control','placeholder'=>  trans("lang.patient_height_placeholder")]) !!}
            <div class="form-text text-muted">
            {{ trans("lang.patient_height_help") }}
            </div>
        </div>
        </div>
        <!-- Groupe Sanguin Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('groupe_sanguin', trans("lang.patient_groupe_sanguin"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::select('groupe_sanguin', [
                '' => trans('lang.select_groupe_sanguin'), // Option vide par défaut
                'A+' => 'A+',
                'A-' => 'A-',
                'B+' => 'B+',
                'B-' => 'B-',
                'AB+' => 'AB+',
                'AB-' => 'AB-',
                'O+' => 'O+',
                'O-' => 'O-'
            ], null, ['class' => 'select2 form-control']) !!}
            <div class="form-text text-muted">
                {{ trans("lang.patient_groupe_sanguin_help") }}
            </div>
        </div>
    </div>


    <!-- Allergie Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('allergie', trans("lang.patient_allergie"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::textarea('allergie', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => trans("lang.patient_allergie_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.patient_allergie_help") }}
            </div>
        </div>
    </div>
    <!-- Antecedent Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('antecedent', trans("lang.patient_antecedent"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::textarea('antecedent', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => trans("lang.patient_antecedent_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.patient_antecedent_help") }}
            </div>
        </div>
    </div>
        

    <!-- Medical Historic Field -->
        <div class="form-group align-items-baseline d-flex flex-column flex-md-row ">
            {!! Form::label('medical_history', trans("lang.patient_medical_history"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
            <div class="col-md-9">
                {!! Form::textarea('medical_history', null, ['class' => 'form-control','placeholder'=>
                trans("lang.patient_medical_history_placeholder")  ]) !!}
                <div class="form-text text-muted">{{ trans("lang.patient_medical_history_help") }}</div>
            </div>
        </div>



    </div>

    @if($customFields)
    <div class="clearfix"></div>
    <div class="col-12 custom-field-container">
    <h5 class="col-12 pb-4">{!! trans('lang.custom_field_plural') !!}</h5>
    {!! $customFields !!}
    </div>
    @endif
    <!-- Submit Field -->
    <div class="form-group col-12 d-flex flex-column flex-md-row justify-content-md-end justify-content-sm-center border-top pt-4">
    <button type="submit" class="btn bg-{{setting('theme_color')}} mx-md-3 my-lg-0 my-xl-0 my-md-0 my-2" ><i class="fas fa-save"></i> {{trans('lang.save')}} {{trans('lang.patient')}}</button>
    <a href="{!! route('patients.index') !!}" class="btn btn-default"><i class="fas fa-undo"></i> {{trans('lang.cancel')}}</a>
    </div>
    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmationModalLabel">Confirmation</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Voulez-vous créer un rendez-vous pour ce patient ?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Non</button>
                    <button type="button" class="btn bg-{{ setting('theme_color') }}" id="confirmSave">Oui</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="assignUserModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        <div class="modal-header" style="background-color:rgb(47, 117, 209);">
    <h5 class="modal-title text-white">                        <i class="fas fa-user-plus custom-icon-margin"></i>
    Assigner un sous Profil</h5>
    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

            <div class="modal-body">
                <div class="form-group">
                    <label>Relation</label>
                    <select name="assigned_user[relationship]" class="form-control">
                        <option value="">Sélectionnez...</option>
                        <option value="mere">Mère</option>
                        <option value="pere">Père</option>

    <option value="conjoint">Conjoint(e)</option>
    <option value="grand_pere">Grand-père</option>
    <option value="grand_mere">Grand-mère</option>
    <option value="enfant">Enfant</option>
    <option value="aide_soignante">Aide-soignante</option>
 

    <option value="autre">Autre</option>
                        <!-- autres options -->
                    </select>
                </div>
                
               <div class="form-group">
    <label>Prénom</label>
    <div class="input-group">
        <div class="input-group-prepend">
            <span class="input-group-text"><i class="fas fa-user"></i></span>
        </div>
        <input type="text" name="assigned_user[first_name]" class="form-control">
    </div>
</div>

<div class="form-group">
    <label>Nom</label>
    <div class="input-group">
        <div class="input-group-prepend">
            <span class="input-group-text"><i class="fas fa-user"></i></span>
        </div>
        <input type="text" name="assigned_user[last_name]" class="form-control">
    </div>
</div>

<div class="form-group">
    <label>Téléphone</label>
    <div class="input-group">
        <div class="input-group-prepend">
            <span class="input-group-text"><i class="fas fa-phone"></i></span>
        </div>
        <input type="text" name="assigned_user[phone]" class="form-control">
    </div>
</div>

<div class="form-group">
    <label>Email</label>
    <div class="input-group">
        <div class="input-group-prepend">
            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
        </div>
        <input type="email" name="assigned_user[email]" class="form-control">
    </div>
</div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
<button type="button" id="confirmAssignUser" style="background-color:rgb(47, 117, 209);"class="btn text-white">
    Assigner
</button>
            </div>
        </div>
    </div>
</div>
    <!-- Rest of your existing form... -->
    <script>
        $(document).ready(function () {
            $('select[name="assigned_users[0][relationship]"]').on('change', function () {
                if ($(this).val() === 'autre') {
                    $('#otherRelationshipGroup').removeClass('d-none');
                } else {
                    $('#otherRelationshipGroup').addClass('d-none');
                }
            });
        });
    </script>

    <script>
   
    $(document).ready(function() {
        let userCounter = 1;

        $('#addMoreUsers').click(function() {
            const newUserForm = `
                <div class="assigned-user-form mt-4 border-top pt-3">
                    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
                        <div class="col-md-9 offset-md-3">
                            <button type="button" class="btn btn-sm btn-danger float-right remove-user-btn">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <!-- Type de relation -->
                    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
                        <label class="col-md-3 control-label text-md-right mx-1">{{ trans('lang.relationship_type') }}</label>
                        <div class="col-md-9">
                            <select name="assigned_users[${userCounter}][relationship]" class="form-control">
                                <option value="pere">{{ trans('lang.father') }}</option>
                                <option value="mere">{{ trans('lang.mother') }}</option>
                                <option value="fils">{{ trans('lang.son') }}</option>
                                <option value="conjoint">{{ trans('Conjoint(e)') }}</option>
                                <option value="Grand-mère">{{ trans('Grand-mère') }}</option>
                                <option value="Grand-pere">{{ trans('Grand-père') }}</option>
                                <option value="Fille">{{ trans('lang.daughter') }}</option>
                                <option value="autre">{{ trans('lang.other') }}</option>
                            </select>
                        </div>
                    </div>
                    <!-- Nom -->
                    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
                        <label class="col-md-3 control-label text-md-right mx-1">Nom</label>
                        <div class="col-md-9">
                            <input type="text" name="assigned_users[${userCounter}][last_name]" class="form-control">
                        </div>
                    </div>
                    <!-- Prénom -->
                    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
                        <label class="col-md-3 control-label text-md-right mx-1">Prénom</label>
                        <div class="col-md-9">
                            <input type="text" name="assigned_users[${userCounter}][first_name]" class="form-control">
                        </div>
                    </div>
                    <!-- Date de naissance -->
                    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
                        <label class="col-md-3 control-label text-md-right mx-1">Date de naissance</label>
                        <div class="col-md-9">
                            <input type="date" name="assigned_users[${userCounter}][birth_date]" class="form-control">
                        </div>
                    </div>
                    <!-- Sexe -->
                    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
                        <label class="col-md-3 control-label text-md-right mx-1">Sexe</label>
                        <div class="col-md-9">
                            <select name="assigned_users[${userCounter}][gender]" class="form-control">
                                <option value="male">{{ trans('lang.patient_male') }}</option>
                                <option value="female">{{ trans('lang.patient_female') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            `;
            $('#assignedUsersContainer').append(newUserForm);
            userCounter++;
        });

        // Supprimer un utilisateur
        $(document).on('click', '.remove-user-btn', function() {
            $(this).closest('.assigned-user-form').remove();
        });

        // Fermer la modal
        $('#saveAssignedUsers').click(function() {
            $('#assignUserModal').modal('hide');
        });
    });
    </script>


    <script>
    $(document).ready(function() {
        let relationshipCounter = 1;
        
        // Ajouter une nouvelle relation
        $('#add-relationship').click(function() {
            const newForm = `
                <div class="patient-relationship-form mb-3 p-3 border rounded">
                    <div class="form-group">
                        <label class="control-label">Type de relation</label>
                        <select name="patient_relationships[${relationshipCounter}][relationship_type]" class="form-control">
                        <option value="pere">{{ trans('Père') }}</option>
    <option value="mere">{{ trans('Mère') }}</option>
    <option value="conjoint">{{ trans('Conjoint(e)') }}</option>
    <option value="enfant">{{ trans('Enfant') }}</option>
    <option value="grand_pere">{{ trans('Grand-père') }}</option>
    <option value="grand_mere">{{ trans('Grand-mère') }}</option>
    <option value="aide_soignante">{{ trans('Aide-soignante') }}</option>
    <option value="autre">{{ trans('Autre') }}</option>

                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="control-label">Prénom</label>
                        <input type="text" name="patient_relationships[${relationshipCounter}][first_name]" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label class="control-label">Nom</label>
                        <input type="text" name="patient_relationships[${relationshipCounter}][last_name]" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label class="control-label">Téléphone</label>
                        <input type="text" name="patient_relationships[${relationshipCounter}][phone_number]" class="form-control">
                    </div>
                    
                    <button type="button" class="btn btn-sm btn-danger remove-relationship">Supprimer</button>
                </div>
            `;
            
            $('#patient-relationships-container').append(newForm);
            relationshipCounter++;
        });
        
        // Supprimer une relation
        $(document).on('click', '.remove-relationship', function() {
            $(this).closest('.patient-relationship-form').remove();
        });
    });
    </script>
<script>
$(document).ready(function() {
    // Gestion de la soumission du formulaire principal
    $('#patientForm').on('submit', function(e) {
        // Vérifier si un utilisateur est en cours d'assignation
        const isAssigningUser = $('input[name="has_assigned_user"]').length > 0;
        
        if (isAssigningUser) {
            // Valider les champs de l'utilisateur assigné
            const firstName = $('input[name="assigned_user[first_name]"]').val();
            const lastName = $('input[name="assigned_user[last_name]"]').val();
            const phone = $('input[name="assigned_user[phone]"]').val();
            const relationship = $('select[name="assigned_user[relationship]"]').val();
            
            if (!firstName || !lastName || !phone || !relationship) {
                e.preventDefault();
                alert('Veuillez remplir tous les champs obligatoires pour l\'utilisateur assigné');
                $('#assignUserModal').modal('show');
                return false;
            }
        }
        
        // Si pas d'utilisateur assigné ou validation OK, laisser le formulaire se soumettre
        return true;
    });

    // Gestion de l'assignation d'utilisateur
    $('#confirmAssignUser').click(function() {
        const formData = {
            first_name: $('input[name="assigned_user[first_name]"]').val(),
            last_name: $('input[name="assigned_user[last_name]"]').val(),
            phone: $('input[name="assigned_user[phone]"]').val(),
            email: $('input[name="assigned_user[email]"]').val(),
            relationship: $('select[name="assigned_user[relationship]"]').val()
        };

        // Valider les champs requis
        if (!formData.first_name || !formData.last_name || !formData.phone || !formData.relationship) {
            alert("Veuillez remplir tous les champs obligatoires");
            return;
        }

        // Ajouter un indicateur qu'un utilisateur est assigné
        $('<input>').attr({
            type: 'hidden',
            name: 'has_assigned_user',
            value: '1'
        }).appendTo('#patientForm');

        // Ajouter chaque champ au formulaire principal
        Object.keys(formData).forEach(key => {
            $('<input>').attr({
                type: 'hidden',
                name: `assigned_user[${key}]`,
                value: formData[key]
            }).appendTo('#patientForm');
        });

        $('#assignUserModal').modal('hide');
    });
});
</script>
    <script>
    function toggleFields() {
        const cnssCheckbox = document.getElementById('cnss');
        const assuranceCheckbox = document.getElementById('assurance');
        const cnssFields = document.getElementById('cnssFields');
        const assuranceField = document.getElementById('assuranceField');
        
        const matriculeCNSS = document.getElementById('matriculeCNSS');
        const dateExpiration = document.getElementById('dateExpiration');
        const assuranceFieldInput = document.getElementById('assuranceFieldInput');

        // Afficher/masquer et gérer les champs CNSS
        if (cnssCheckbox.checked) {
            cnssFields.classList.remove('d-none');
            matriculeCNSS.setAttribute('required', 'required');
            dateExpiration.setAttribute('required', 'required');
        } else {
            cnssFields.classList.add('d-none');
            matriculeCNSS.removeAttribute('required');
            dateExpiration.removeAttribute('required');
        }

        // Afficher/masquer et gérer le champ Assurance
        if (assuranceCheckbox.checked) {
            assuranceField.classList.remove('d-none');
            assuranceFieldInput.setAttribute('required', 'required');
        } else {
            assuranceField.classList.add('d-none');
            assuranceFieldInput.removeAttribute('required');
        }
    }

    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var input = document.querySelector("#phone-input");
        var phoneInput = window.intlTelInput(input, {
            initialCountry: "TN", // Pays par défaut (Tunisie)
            geoIpLookup: function(callback) {
                fetch("https://ipinfo.io")
                    .then(function(response) { return response.json(); })
                    .then(function(location) {
                        var countryCode = location && location.country ? location.country : "TN"; // Défaut: TN
                        callback(countryCode);
                    });
            },
            preferredCountries: ["us", "ca", "fr", "tn"], // Pays préférés
            separateDialCode: true, // Code séparé
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"
        });

        // Associer le code téléphonique au numéro de téléphone
        input.addEventListener('blur', function() {
            var countryData = phoneInput.getSelectedCountryData(); // Obtenir les infos pays
            var dialCode = countryData.dialCode; // Récupérer le code téléphonique, ex: "216"
            var phoneNumber = input.value; // Numéro de téléphone saisi

            // Associer le code au numéro si ce n'est pas déjà fait
            if (phoneNumber && !phoneNumber.startsWith("+" + dialCode)) {
                input.value = "+" + dialCode + phoneNumber; // Ajouter le code au début
            }
        });
    });

    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if(session()->has('showModal') && session('showModal') == true)
                $('#confirmationModal').modal('show');
            @endif

            // Gestion du clic sur le bouton "Oui" dans le modal
            $('#confirmSave').on('click', function () {
                // Rediriger vers l'agenda pour la création de rendez-vous
                window.location.href = "{{ route('appointment-events.index') }}";
            });

            // Gestion du clic sur le bouton "Non" dans le modal
            $('#confirmationModal .btn-secondary').on('click', function () {
                // Rediriger vers la liste des patients
                window.location.href = "{{ route('patients.index') }}";
            });
        });
    </script>

