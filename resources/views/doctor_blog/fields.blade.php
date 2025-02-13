
<div class="d-flex flex-column col-sm-12 col-md-6">
    <!-- Image Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('image', trans("lang.patient_image"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            <div style="width: 100%" class="dropzone image" id="image" data-field="image">
            </div>
            <div class="form-text text-muted w-50">
                {{ trans("lang.patient_image_help") }}
            </div>
        </div>
    </div>
    @prepend('scripts')
        <script type="text/javascript">
            var blog_image = [];
            @if(isset($doctorBlog) && $doctorBlog->hasMedia('image'))
                @foreach($doctorBlog->getMedia('image') as $media)
                    blog_image.push({
                        name: "{!! $media->name !!}",
                        size: "{!! $media->size !!}",
                        type: "{!! $media->mime_type !!}",
                        uuid: "{!! $media->getCustomProperty('uuid'); !!}",
                        thumb: "{!! $media->getUrl('thumb'); !!}",
                        collection_name: "{!! $media->collection_name !!}"
                    });
                @endforeach
            @endif
            var dz_blog_image = $(".dropzone.image").dropzone({
    url: "{!! url('uploads/storeImage') !!}", // Nouvelle URL personnalisée
    addRemoveLinks: true,
    maxFiles: 5 - blog_image.length,
    init: function () {
        @if(isset($doctorBlog) && $doctorBlog->hasMedia('image'))
            blog_image.forEach(media => {
                dzInit(this, media, media.thumb);
            });
        @endif
    },
    accept: function (file, done) {
        dzAccept(file, done, this.element, "{!! config('media-library.icons_folder') !!}");
    },
    sending: function (file, xhr, formData) {
        dzSendingMultiple(this, file, formData, '{!! csrf_token() !!}');
    },
    complete: function (file) {
        dzCompleteMultiple(this, file);
        dz_blog_image[0].mockFile = file;
    },
    removedfile: function (file) {
        dzRemoveFileMultiple(
            file, blog_image, '{!! url("doctor_blogs/remove-media") !!}',
            'image', '{!! isset($doctorBlog) ? $doctorBlog->id : 0 !!}', '{!! url("uploads/clear") !!}', '{!! csrf_token() !!}'
        );
    }
});

            dz_blog_image[0].mockFile = blog_image;
            dropzoneFields['image'] = dz_blog_image;
        </script>
    @endprepend

    <!-- titre abrégé Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('titre_court', trans("lang.blog_short_title"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::text('titre_court', $doctorBlog->titre_court ?? '',  ['class' => 'form-control','placeholder'=>  trans("lang.blog_short_title_placeholder"), 'required' => 'required']) !!}
        </div>
    </div>

    <!-- titre Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('titre', trans("lang.blog_title"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::textarea('titre', $doctorBlog->titre ?? '',  ['class' => 'form-control','placeholder'=>  trans("lang.blog_title_placeholder"), 'required' => 'required']) !!}
                 <div class="form-text text-muted">
                {{ trans("lang.blog_title_help") }}
            </div> 
        </div>
    </div>






</div>





<div class="d-flex flex-column col-sm-12 col-md-6">





    <!-- Description Field -->
    <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
        {!! Form::label('contenu', trans("lang.my_blog_content"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
        <div class="col-md-9">
            {!! Form::textarea('contenu', $doctorBlog->contenu ?? '',  ['class' => 'form-control','placeholder'=>  trans("lang.blog_content_placeholder"),'style' => 'height: 250px;', 'required' => 'required']) !!}
            <div class="form-text text-muted">
                {{ trans("lang.blog_content_help") }}
            </div>
        </div>
    </div>

</div>


</style>
<!-- Submit Field -->
<div class="form-group col-12 d-flex flex-column flex-md-row justify-content-md-end justify-content-sm-center border-top pt-4">
    <button type="submit" class="btn bg-{{setting('theme_color')}} mx-md-3 my-lg-0 my-xl-0 my-md-0 my-2">
        <i class="fa fa-save"></i> {{trans('lang.save')}} {{trans('lang.my_blog')}}
    </button>
    <a href="{!! route('doctor_blog.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i> {{trans('lang.cancel')}}</a>
</div>
