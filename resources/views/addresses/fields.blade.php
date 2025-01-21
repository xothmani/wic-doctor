@if($customFields)
    <h5 class="col-12 pb-4">{!! trans('lang.main_fields') !!}</h5>
@endif
<div class="d-flex flex-column col-sm-12 col-md-6">
    <!-- Description Field -->
    <!-- Description Field -->
<div class="form-group align-items-baseline d-flex flex-column flex-md-row">
    {!! Form::label('description', trans("lang.address_description"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
        {!! Form::text('description', isset($address) ? json_decode($address->description, true)['fr'] ?? '' : null, ['class' => 'form-control','placeholder'=> trans("lang.address_description_placeholder")]) !!}
        <div class="form-text text-muted">
            {{ trans("lang.address_description_help") }}
        </div>
    </div>
</div>

    <!-- Address Field -->
  <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
    {!! Form::label('address', trans("lang.address_address"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
        {!! Form::text('address', isset($address) ? json_decode($address->address, true)['fr'] ?? '' : null, ['class' => 'form-control','placeholder'=> trans("lang.address_address_placeholder")]) !!}
        <div class="form-text text-muted">
            {{ trans("lang.address_address_help") }}
        </div>
    </div>
</div>

	<div class="form-group align-items-baseline d-flex flex-column flex-md-row">
    {!! Form::label('pays', trans("lang.address_pays"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
        {!! Form::text('pays', isset($address) ? json_decode($address->pays, true)['fr'] ?? '' : null, ['class' => 'form-control','placeholder'=> trans("lang.address_pays_placeholder")]) !!}
        <div class="form-text text-muted">
            {{ trans("lang.address_pays_help") }}
        </div>
    </div>
</div>
</div>
<div class="d-flex flex-column col-sm-12 col-md-6">

    <!-- Latitude Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('latitude', trans("lang.address_latitude"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::number('latitude', null,  ['class' => 'form-control','step'=>'any', 'placeholder'=>  trans("lang.address_latitude_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.address_latitude_help") }}
            </div>
        </div>
    </div>

    <!-- Longitude Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('longitude', trans("lang.address_longitude"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::number('longitude', null,  ['class' => 'form-control','step'=>'any', 'placeholder'=>  trans("lang.address_longitude_placeholder")]) !!}
            <div class="form-text text-muted">
                {{ trans("lang.address_longitude_help") }}
            </div>
        </div>
    </div>
	<!-- ville Field -->
<div class="form-group align-items-baseline d-flex flex-column flex-md-row">
    {!! Form::label('ville', trans("lang.address_ville"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
        <select id="ville-dropdown" name="ville" class="form-control">
            <option value="" disabled selected>{{ trans("lang.address_ville_placeholder") }}</option>
        </select>
        <div class="form-text text-muted">
            {{ trans("lang.address_ville_help") }}
        </div>
    </div>
</div>

<!-- Include your JSON data as a script variable -->
<script>
    const villes = [
        {"ville":"{\"fr\":\"Ariana\"}"},
        {"ville":"{\"fr\":\"Béja\"}"},
        {"ville":"{\"fr\":\"Ben Arous\"}"},
        {"ville":"{\"fr\":\"Bizerte\"}"},
        {"ville":"{\"fr\":\"Gabès\"}"},
        {"ville":"{\"fr\":\"Gafsa\"}"},
        {"ville":"{\"fr\":\"Jendouba\"}"},
        {"ville":"{\"fr\":\"Kairouan\"}"},
        {"ville":"{\"fr\":\"Kasserine\"}"},
        {"ville":"{\"fr\":\"Kébili\"}"},
        {"ville":"{\"fr\":\"Le Kef\"}"},
        {"ville":"{\"fr\":\"Mahdia\"}"},
        {"ville":"{\"fr\":\"La Manouba\"}"},
        {"ville":"{\"fr\":\"Médenine\"}"},
        {"ville":"{\"fr\":\"Monastir\"}"},
        {"ville":"{\"fr\":\"Nabeul\"}"},
        {"ville":"{\"fr\":\"Sfax\"}"},
        {"ville":"{\"fr\":\"Sidi Bouzid\"}"},
        {"ville":"{\"fr\":\"Siliana\"}"},
        {"ville":"{\"fr\":\"Sousse\"}"},
        {"ville":"{\"fr\":\"Tataouine\"}"},
        {"ville":"{\"fr\":\"Tozeur\"}"},
        {"ville":"{\"fr\":\"Tunis\"}"},
        {"ville":"{\"fr\":\"Zaghouan\"}"}
    ];

    // Populate the dropdown
    document.addEventListener('DOMContentLoaded', () => {
        const dropdown = document.getElementById('ville-dropdown');
        villes.forEach(item => {
            const villeName = JSON.parse(item.ville).fr; // Extract the 'fr' property
            const option = document.createElement('option');
            option.value = villeName;
            option.textContent = villeName;
            dropdown.appendChild(option);
        });
    });
</script>

</div>
<div class="col-12" style="height:400px; ">
    <div style="width: 100%; height: 100%" id="map"></div>
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
    <!-- 'Boolean Default Field' -->
    <div class="d-flex flex-row justify-content-between align-items-center">
        {!! Form::label('default', trans("lang.address_default"),['class' => 'control-label my-0 mx-3']) !!} {!! Form::hidden('default', 0, ['id'=>"hidden_default"]) !!}
        <span class="icheck-{{setting('theme_color')}}">
            {!! Form::checkbox('default', 1, null) !!} <label for="default"></label> </span>
    </div>
    <button type="submit" class="btn bg-{{setting('theme_color')}} mx-md-3 my-lg-0 my-xl-0 my-md-0 my-2">
        <i class="fa fa-save"></i> {{trans('lang.save')}} {{trans('lang.address')}}</button>
    <a href="{!! route('addresses.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i> {{trans('lang.cancel')}}</a>
</div>
@push('scripts')
    <script src="https://maps.googleapis.com/maps/api/js?key={{ setting('google_maps_key') }}&libraries=places&callback=initializeGoogleMaps"></script>
@endpush
