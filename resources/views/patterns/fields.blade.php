<div class="d-flex flex-column col-sm-12 col-md-6">
    <!-- Nom Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('nom', trans("lang.pattern_nom"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::text('nom', null, ['class' => 'form-control', 'placeholder' => trans("lang.pattern_nom_placeholder")]) !!}
            <div class="form-text text-muted">{{ trans("lang.pattern_nom_help") }}</div>
        </div>
    </div>

    <!-- Speciality Id Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row" style="display: none;">
        <div class="col-md-9">
            {!! Form::hidden('specialite_id', $doctorSpecialityId, ['class' => 'form-control']) !!}
        </div>
    </div>

    <!-- Price Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('price', trans("lang.pattern_price"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::number('price', null, ['class' => 'form-control', 'placeholder' => trans("lang.pattern_price_placeholder")]) !!}
            <div class="form-text text-muted">{{ trans("lang.pattern_price_help") }}</div>
        </div>
    </div>
</div>

<div class="d-flex flex-column col-sm-12 col-md-6">
    <!-- Type Radio Buttons -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('type', trans('lang.pattern_type'), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
        <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="type" id="type_all" value="all"
        {{ (isset($pattern) && is_null($pattern->type)) || !isset($pattern) ? 'checked' : '' }}>
            <label class="form-check-label" for="type_all">{{ trans('lang.all_types') }}</label>
</div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="type" id="type_cabinet" value="cabinet" {{ !$isClinicSet && !$isAdomicileSet ? 'checked' : '' }}>
                <label class="form-check-label" for="type_cabinet">{{ trans("lang.cabinet") }}</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="type" id="type_teleconsultation" value="teleconsultation" {{ $isTeleconsultationSet ? 'checked' : '' }}>
                <label class="form-check-label" for="type_teleconsultation">{{ trans("lang.teleconsultation") }}</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="type" id="type_adomicile" value="adomicile" {{ $isAdomicileSet ? 'checked' : '' }}>
                <label class="form-check-label" for="type_adomicile">{{ trans("lang.adomicile") }}</label>
            </div>
            <div class="form-check form-check-inline d-none">
    <input class="form-check-input" type="radio" name="type" id="type_clinique" value="clinique" {{ $isClinicSet ? 'checked' : '' }}>
    <label class="form-check-label" for="type_clinique">{{ trans("lang.clinique") }}</label>
</div>

        </div>
    </div>
    <!-- Speciality Id Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row" style="display: none;">
        
    </div>
    
        <!-- Clinic Fields -->
        <div id="clinic_fields" class="d-none ">
            <!-- Clinic Id Field -->
            <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
                {!! Form::label('clinic_id', trans("lang.pattern_clinic_id"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
                <div class="col-md-9">
                    <select name="clinic_id" class="select2 form-control">
                        <option value="" disabled selected>{{ trans("lang.select_clinic") }}</option>
                        @foreach($clinics as $id => $name)
                            <option value="{{ $id }}" {{ isset($selectedClinicId) && $selectedClinicId == $id ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text text-muted">{{ trans("lang.pattern_clinic_id_help") }}</div>
                </div>
            </div>
        </div>
        
    
</div>

<div class="d-flex flex-column col-sm-12 col-md-6">



    <!-- Color Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('color', trans('lang.pattern_color'), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::color('color', $pattern->color ?? '#000000', ['class' => 'form-control']) !!}
            <div class="form-text text-muted">{{ trans('lang.pattern_color_help') }}</div>
        </div>
    </div>
</div>

@if($customFields)
    <div class="clearfix"></div>
    <div class="col-12 custom-field-container">
        <h5 class="col-12 pb-4">{{ trans('lang.custom_field_plural') }}</h5>
        {!! $customFields !!}
    </div>
@endif

<!-- Submit Field -->
<div
    class="form-group col-12 d-flex flex-column flex-md-row justify-content-md-end justify-content-sm-center border-top pt-4">
    <button type="submit" class="btn mx-md-3 my-lg-0 my-xl-0 my-md-0 my-2" style="color: #001f3f;">
        <i class="fa fa-save"></i> {{ trans('lang.save') }} {{ trans('lang.pattern') }}
    </button>
    <a href="{!! route('patterns.index') !!}" class="btn btn-default">
        <i class="fa fa-undo"></i> {{ trans('lang.cancel') }}
    </a>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const clinicFields = document.getElementById('clinic_fields');
        const radioCabinet = document.getElementById('type_cabinet');
        const radioClinique = document.getElementById('type_clinique');
        const radioAdomicile = document.getElementById('type_adomicile');
        const isClinicSet = @json($isClinicSet);
        const isAdomicileSet = @json($isAdomicileSet);
        const radioTeleconsultation = document.getElementById('type_teleconsultation');


        function toggleFields() {
            if (radioClinique.checked) {
                clinicFields.classList.remove('d-none');
            } else {
                clinicFields.classList.add('d-none');
            }
        }

        // Initial state
        toggleFields();

        // Event listeners for radio buttons
        radioCabinet.addEventListener('change', toggleFields);
        radioClinique.addEventListener('change', toggleFields);
        radioAdomicile.addEventListener('change', toggleFields);
        radioTeleconsultation.addEventListener('change', toggleFields);

    });
</script>
