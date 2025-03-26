@if(in_array('Telesecretary', $rolesSelected))

    {{-- ****************
    TELESECRETARY
    Only show: start_date, end_date, is_active
    BUT also send hidden inputs for name/email/phone
    to satisfy validation and keep the same data
    **************** --}}

    <!-- Hidden inputs for name, email, phone_number -->
    <input type="hidden" name="name" value="{{ old('name', $user->name) }}">
    <input type="hidden" name="email" value="{{ old('email', $user->email) }}">
    <input type="hidden" name="phone_number" value="{{ old('phone_number', $user->phone_number) }}">

    <div class="row">
        <div class="col-sm-12 col-md-6">
            <!-- Start Date Field -->
            <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
                {!! Form::label('start_date', trans("lang.start_date"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
                <div class="col-md-9">
                    {!! Form::date('start_date', $profileManagement->start_date ?? null, ['class' => 'form-control']) !!}
                    <span class="text-danger">*</span>
                    <div class="form-text text-muted">
                        {{ trans("lang.start_date_help") ?? '' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-md-6">
            <!-- End Date Field -->
            <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
                {!! Form::label('end_date', trans("lang.end_date"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
                <span class="text-danger">*</span>
                <div class="col-md-9">
                    {!! Form::date('end_date', $profileManagement->end_date ?? null, ['class' => 'form-control']) !!}
                    <div class="form-text text-muted">
                        {{ trans("lang.end_date_help") ?? '' }}
                    </div>
                </div>
            </div>

            <!-- Is Active Field -->
            <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
                {!! Form::label('is_active', trans("lang.is_active"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
                <span class="text-danger">*</span>
                <div class="col-md-9 d-flex align-items-center">
                    <div class="custom-control custom-switch">
                        {!! Form::checkbox('is_active', 1, isset($profileManagement) ? $profileManagement->is_active : false, [
            'class' => 'custom-control-input',
            'id' => 'is_active'
        ]) !!}
                        <label class="custom-control-label" for="is_active"></label>
                    </div>
                </div>
            </div>
        </div>
    </div>

@else

    {{-- ****************
    NON-TELESECRETARY
    Show ALL fields (name/email/phone etc.)
    **************** --}}
    <div class="d-flex flex-column col-sm-12 col-md-6">
        <!-- Name Field -->
        <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
            {!! Form::label('name', trans("lang.user_name"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
            <span class="text-danger">*</span>
            <div class="col-md-9">
                {!! Form::text('name', null, [
            'class' => 'form-control',
            'placeholder' => trans("lang.user_name_placeholder")
        ]) !!}
                <div class="form-text text-muted">
                    {{ trans("lang.user_name_help") }}
                </div>
            </div>
        </div>

        <!-- Email Field -->
        <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
            {!! Form::label('email', trans("lang.user_email"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
            <span class="text-danger">*</span>
            <div class="col-md-9">
                {!! Form::email('email', null, [
            'class' => 'form-control',
            'placeholder' => trans("lang.user_email_placeholder")
        ]) !!}
                <div class="form-text text-muted">
                    {{ trans("lang.user_email_help") }}
                </div>
            </div>
        </div>

        <!-- Phone Number Field -->
        <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
            {!! Form::label('phone_number', trans("lang.patient_phone_number"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
            <span class="text-danger">*</span>

            <div class="col-md-9">
                <div id="phone-number-input">
                    {!! Form::text('phone_number', null, [
            'class' => 'form-control',
            'id' => 'phone-input',
            'placeholder' => trans("lang.patient_phone_number_placeholder"),
            'style' => 'width: 545px;',
            'onkeypress' => 'return event.charCode >= 48 && event.charCode <= 57'
        ]) !!}
                </div>
                <div class="form-text text-muted">
                    {{ trans("lang.patient_phone_number_help") }}
                </div>
            </div>
        </div>

        <!-- Password Field -->
        <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
            {!! Form::label('password', trans("lang.user_password"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
            <span class="text-danger">*</span>
            <div class="col-md-9">
                {!! Form::password('password', [
            'class' => 'form-control',
            'placeholder' => trans("lang.user_password_placeholder")
        ]) !!}
                <div class="form-text text-muted">
                    {{ trans("lang.user_password_help") }}
                </div>
            </div>
        </div>

        <!-- Start Date Field -->
        <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
            {!! Form::label('start_date', trans("lang.start_date"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
            <span class="text-danger">*</span>
            <div class="col-md-9">
                {!! Form::date('start_date', $profileManagement->start_date ?? null, ['class' => 'form-control']) !!}
                <div class="form-text text-muted">
                    {{ trans("lang.start_date_help") ?? '' }}
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-column col-sm-12 col-md-6">
        <!-- Avatar Field -->
        <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
            {!! Form::label('avatar', trans("lang.user_avatar"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
            <div class="col-md-9">
                <div style="width: 100%; height: 12.2rem;" class="dropzone avatar" id="avatar" data-field="avatar">
                    <input type="hidden" name="avatar">
                </div>
                @can('Media.create')
                    <a href="#loadMediaModal" data-dropzone="avatar" data-toggle="modal" data-target="#mediaModal"
                        class="btn btn-outline-{{ setting('theme_color', 'primary') }} btn-sm float-right mt-1">
                        {{ trans('lang.media_select') }}
                    </a>
                @endcan
                <div class="form-text text-muted w-50">
                    {{ trans("lang.user_avatar_help") }}
                </div>
            </div>
        </div>

        <!-- Role Field -->
        <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
            {!! Form::label('role', trans("lang.user_role_id"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
            <span class="text-danger">*</span>
            <div class="col-md-9">
                <select name="role" class="form-control">
                    @foreach($roles as $value => $label)
                        <option value="{{ $value }}" {{ in_array($value, $rolesSelected) ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- End Date Field -->
        <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
            {!! Form::label('end_date', trans("lang.end_date"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
            <div class="col-md-9">
                {!! Form::date('end_date', $profileManagement->end_date ?? null, ['class' => 'form-control']) !!}
                <div class="form-text text-muted">
                    {{ trans("lang.end_date_help") ?? '' }}
                </div>
            </div>
        </div>

        <!-- Is Active Field -->
        <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
            {!! Form::label('is_active', trans("lang.is_active"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
            <div class="col-md-9 d-flex align-items-center">
                <div class="custom-control custom-switch">
                    {!! Form::checkbox('is_active', 1, isset($profileManagement) ? $profileManagement->is_active : false, [
            'class' => 'custom-control-input',
            'id' => 'is_active'
        ]) !!}
                    <label class="custom-control-label" for="is_active"></label>
                </div>
            </div>
        </div>
    </div>

@endif


{{-- Submit & Cancel always shown --}}
<div
    class="form-group col-12 d-flex flex-column flex-md-row justify-content-md-end justify-content-sm-center border-top pt-4">
    <button type="submit" class="btn bg-{{ setting('theme_color') }} mx-md-3 my-lg-0 my-xl-0 my-md-0 my-2">
        <i class="fas fa-save"></i> {{ trans('lang.save') }} {{ trans('lang.user') }}
    </button>
    <a href="{!! route('Doctors_users.index') !!}" class="btn btn-default">
        <i class="fas fa-undo"></i> {{ trans('lang.cancel') }}
    </a>
</div>

{{-- Only needed if phone field is present. If Telesecretary never sees it, it won’t matter. --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // If the phone input exists (non-Telesecretary), attach the intlTelInput
        var input = document.querySelector("#phone-input");
        if (input) {
            var phoneInput = window.intlTelInput(input, {
                initialCountry: "tn",
                nationalMode: false,
                formatOnDisplay: false,
                separateDialCode: false,
                utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"
            });

            // Clean up phone number upon submit
            document.querySelector('form').addEventListener('submit', function () {
                let fullNumber = phoneInput.getNumber(); // e.g. +21612345678
                input.value = fullNumber.replace(/[^\d+]/g, '');
            });
        }
    });
</script>