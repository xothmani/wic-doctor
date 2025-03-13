<div class="d-flex flex-column col-sm-12 col-md-6">
    <!-- Name Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('name', trans("lang.user_name"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => trans("lang.user_name_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.user_name_help") }}
            </div>
        </div>
    </div>

    <!-- Email Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('email', trans("lang.user_email"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::email('email', null, ['class' => 'form-control', 'placeholder' => trans("lang.user_email_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.user_email_help") }}
            </div>
        </div>
    </div>

    <!-- Phone Number Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('phone_number', trans("lang.user_phone_number"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::text('phone_number', null, ['class' => 'form-control', 'placeholder' => trans("lang.user_phone_number_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.user_phone_number_help") }}
            </div>
        </div>
    </div>

    <!-- Password Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('password', trans("lang.user_password"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::password('password', ['class' => 'form-control', 'placeholder' => trans("lang.user_password_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.user_password_help") }}
            </div>
        </div>
    </div>
</div>

<div class="d-flex flex-column col-sm-12 col-md-6">
    <!-- Avatar Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('avatar', trans("lang.user_avatar"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            <div style="width: 100%" class="dropzone avatar" id="avatar" data-field="avatar">
                <input type="hidden" name="avatar">
            </div>
            @can('Media.create')
                <a href="#loadMediaModal" data-dropzone="avatar" data-toggle="modal" data-target="#mediaModal"
                    class="btn btn-outline-{{setting('theme_color', 'primary')}} btn-sm float-right mt-1">{{ trans('lang.media_select')}}</a>
            @endcan
            <div class="form-text text-muted w-50">
                {{ trans("lang.user_avatar_help") }}
            </div>
        </div>
    </div>

    <!-- Roles Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('role', trans("lang.user_role_id"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            <select name="role" class="form-control">
                @foreach($roles as $role)
                    <option value="{{ $role }}">{{ $role }}</option>
                @endforeach
            </select>
        </div>
    </div>

</div>

<!-- Submit Field -->
<div
    class="form-group col-12 d-flex flex-column flex-md-row justify-content-md-end justify-content-sm-center border-top pt-4">
    <button type="submit" class="btn bg-{{setting('theme_color')}} mx-md-3 my-lg-0 my-xl-0 my-md-0 my-2">
        <i class="fas fa-save"></i> {{ trans('lang.save') }} {{ trans('lang.user') }}
    </button>
    <a href="{!! route('Doctors_users.index') !!}" class="btn btn-default">
        <i class="fas fa-undo"></i> {{ trans('lang.cancel') }}
    </a>
</div>