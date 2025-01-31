<div class="d-flex flex-column col-sm-12 col-md-6">
    <!-- Name Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('name', trans("lang.doctor_name"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::text('name', null,  ['class' => 'form-control','placeholder'=>  trans("lang.doctor_name_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.doctor_name_help") }}
            </div>
        </div>
    </div>

 

    <!-- Diplome Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('diplome', trans("lang.diplome"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::text('diplome', null,  ['class' => 'form-control','placeholder'=>  trans("lang.doctor_diplome_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.doctor_diplome_help") }}
            </div>
        </div>
    </div>

    <!-- Order Number Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('numOrdre', trans("lang.numOrdre"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::text('numOrdre', null,  ['class' => 'form-control','placeholder'=>  trans("lang.doctor_numOrdre_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.doctor_numOrdre_help") }}
            </div>
        </div>
    </div>

    <!-- Matricule Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('matricule_CNAM', trans("lang.matricule_CNAM"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::text('matricule_CNAM', null,  ['class' => 'form-control','placeholder'=>  trans("lang.matricule_CNAM_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.matricule_CNAM_help") }}
            </div>
        </div>
    </div>
</div>

<div class="d-flex flex-column col-sm-12 col-md-6">

    <!-- Specialities Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row ">
        {!! Form::label('specialities[]', trans("lang.doctor_specialities"),['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::select('specialities[]', $speciality, $specialitiesSelected, ['class' => 'select2 form-control not-required' , 'data-empty'=>trans('lang.doctor_specialities_placeholder'),'multiple'=>'multiple']) !!}
            <div class="form-text text-muted">{{ trans("lang.doctor_specialities_help") }}</div>
        </div>
    </div>


        
     <!-- Description Field -->
     <div class="form-group align-items-baseline d-flex flex-column flex-md-row ">
        {!! Form::label('description', trans("lang.doctor_description"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::textarea('description', null, ['class' => 'form-control','placeholder'=>
             trans("lang.doctor_description_placeholder")  ]) !!}
            <div class="form-text text-muted">{{ trans("lang.doctor_description_help") }}</div>
        </div>
    </div>


</div>

<!-- Submit Field -->
<div class="form-group col-12 d-flex flex-column flex-md-row justify-content-md-end justify-content-sm-center border-top pt-4">
    <button type="submit" class="btn bg-{{setting('theme_color')}} mx-md-3 my-lg-0 my-xl-0 my-md-0 my-2">
        <i class="fas fa-save"></i> {{trans('lang.save')}} {{trans('lang.doctor')}}</button>
    
</div>
