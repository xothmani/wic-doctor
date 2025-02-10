
<div class="d-flex flex-column col-sm-12 col-md-6">
    <!-- Nom du Centre d'Appel Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('nom_centre', trans("lang.telesecretariat_nom_centre"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::text('nom_centre', $telesecretariat->nomCentre ?? '',  ['class' => 'form-control','placeholder'=>  trans("lang.telesecretariat_nom_centre_placeholder"), 'required' => 'required']) !!}
          <!--   <div class="form-text text-muted">
                {{ trans("lang.telesecretariat_nom_centre_help") }}
            </div> -->
        </div>
    </div>



    <!-- Nom Responsable Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('nom_responsable', trans("lang.telesecretariat_nom_responsable"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::text('nom_responsable', $telesecretariat->user->lastname ?? '',  ['class' => 'form-control','placeholder'=>  trans("lang.telesecretariat_nom_responsable_placeholder"), 'required' => 'required']) !!}
        <!--     <div class="form-text text-muted">
                {{ trans("lang.telesecretariat_nom_responsable_help") }}
            </div> -->
        </div>
    </div>

    <!-- Prénom Responsable Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('prenom_responsable', trans("lang.telesecretariat_prenom_responsable"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::text('prenom_responsable', $telesecretariat->user->name ?? '',  ['class' => 'form-control','placeholder'=>  trans("lang.telesecretariat_prenom_responsable_placeholder"), 'required' => 'required']) !!}
        <!--     <div class="form-text text-muted">
                {{ trans("lang.telesecretariat_prenom_responsable_help") }}
            </div> -->
        </div>
    </div>

        <!-- Numéro de Téléphone Field -->
        <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('phone_number', trans("lang.telesecretariat_phone_number"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
        {!! Form::text('phone_number', $telesecretariat->user->phone_number ?? '', ['class' => 'form-control', 'placeholder'=> trans("lang.telesecretariat_phone_number_placeholder"), 'id' => 'phone_number', 'required' => 'required']) !!}
                    <!--  <div class="form-text text-muted">
                {{ trans("lang.telesecretariat_phone_number_help") }}
            </div> -->
        </div>

    </div>

        <!-- host Field -->
        <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('host', trans("lang.telesecretariat_host"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::text('host', $telesecretariat->host ?? '',  ['class' => 'form-control','placeholder'=>  trans("lang.telesecretariat_host_placeholder"), 'required' => 'required']) !!}
        <!--     <div class="form-text text-muted">
                {{ trans("lang.telesecretariat_prenom_responsable_help") }}
            </div> -->
        </div>
    </div>
    <!-- username Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('username', trans("lang.telesecretariat_username"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::text('username', $telesecretariat->username ?? '',  ['class' => 'form-control','placeholder'=>  trans("lang.telesecretariat_username_placeholder"), 'required' => 'required']) !!}
        <!--     <div class="form-text text-muted">
                {{ trans("lang.telesecretariat_prenom_responsable_help") }}
            </div> -->
        </div>
    </div>
    <!-- password Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('password', trans("lang.telesecretariat_password"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
        {!! Form::password('password', ['class' => 'form-control', 'placeholder' => trans("lang.telesecretariat_password_placeholder"), 'required' => 'required']) !!}
        <!--     <div class="form-text text-muted">
                {{ trans("lang.telesecretariat_prenom_responsable_help") }}
            </div> -->
        </div>
    </div>

</div>


<script>
    // Empêcher la saisie de lettres
    document.getElementById('phone_number').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, ''); // Remplacer tout sauf les chiffres par une chaîne vide
    });
</script>


<div class="d-flex flex-column col-sm-12 col-md-6">
    <!-- Email Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('email', trans("lang.telesecretariat_email"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::email('email', $telesecretariat->user->email ?? '',  ['class' => 'form-control','placeholder'=>  trans("lang.telesecretariat_email_placeholder"), 'required' => 'required']) !!}
          <!--   <div class="form-text text-muted">
                {{ trans("lang.telesecretariat_email_help") }}
            </div> -->
        </div>
    </div>

    <!-- Adresse Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('adresse', trans("lang.telesecretariat_adresse"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::text('adresse', $telesecretariat->adresse ?? '',  ['class' => 'form-control','placeholder'=>  trans("lang.telesecretariat_adresse_placeholder"), 'required' => 'required']) !!}
           <!--  <div class="form-text text-muted">
                {{ trans("lang.telesecretariat_adresse_help") }}
            </div> -->
        </div>
    </div>

    <!-- Etat Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('etat', trans("lang.telesecretariat_etat"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::select('etat', [1 => trans('lang.active'), 0 => trans('lang.inactive')], $telesecretariat->etat ?? null, ['class' => 'form-control']) !!}
        <!--     <div class="form-text text-muted">
                {{ trans("lang.telesecretariat_etat_help") }}
            </div> -->
        </div>
    </div>

    <!-- Description Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('description', trans("lang.telesecretariat_description"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::textarea('description', $telesecretariat->description ?? '',  ['class' => 'form-control','placeholder'=>  trans("lang.telesecretariat_description_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.telesecretariat_description_help") }}
            </div>
        </div>
    </div>
</div>

<!-- Submit Field -->
<div class="form-group col-12 d-flex flex-column flex-md-row justify-content-md-end justify-content-sm-center border-top pt-4">
    <button type="submit" class="btn bg-{{setting('theme_color')}} mx-md-3 my-lg-0 my-xl-0 my-md-0 my-2">
        <i class="fa fa-save"></i> {{trans('lang.save')}} {{trans('lang.telesecretariat')}}
    </button>
    <a href="{!! route('telesecretariats.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i> {{trans('lang.cancel')}}</a>
</div>
