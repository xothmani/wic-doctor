<div>
        <i class="fas fa-shield-alt" style="color: #ff9800; margin-right: 8px;"></i>
        <strong style="font-size: 18px; color: #333;">Chaque blog soumis sera relu et validé par notre comité éditorial. Vous recevrez une réponse dans un délai de 7 jours ouvrés.</strong>
    </div><br>
<div class="row">
    
    <!-- Colonne gauche -->
    <div class="col-md-6">
    <p style="font-weight: bold; color: red;">* Tous les champs sont obligatoires</p>

        <!-- Image Field -->
        <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
    {!! Form::label('image', trans("lang.patient_image"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
    <div class="col-md-9">
        <div style="width: 100%" class="dropzone image" id="image" data-field="image"></div>
        <div class="form-text text-muted w-50">
            {{ trans("lang.patient_image_help") }}
        </div>
<input type="hidden" name="media_id" id="media_id" value="{{ $doctorBlog->media_id ?? '' }}">    </div>
</div>
        <!-- titre abrégé Field -->
        <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
            {!! Form::label('titre_court', trans("lang.blog_short_title"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
            <div class="col-md-9">
                {!! Form::text('titre_court', $doctorBlog->titre_court ?? '', ['class' => 'form-control', 'placeholder'=> trans("lang.blog_short_title_placeholder"), 'required' => 'required']) !!}
            </div>
        </div>

        <!-- titre Field -->
        <div class="form-group align-items-baseline d-flex flex-column flex-md-row">
            {!! Form::label('titre', trans("lang.blog_title"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
            <div class="col-md-9" style="max-width: 570px;">
    {!! Form::textarea('titre', $doctorBlog->titre ?? '', ['class' => 'form-control', 'placeholder'=> trans("lang.blog_title_placeholder"), 'required' => 'required']) !!}
            <div class="form-text text-muted">{{ trans("lang.blog_title_help") }}</div> 
            </div>
        </div>
    </div>

    <!-- Colonne droite -->
    <div class="col-md-6">
        <!-- Description Field -->
        <div class="form-group align-items-baseline d-flex flex-column flex-md-row mt-4">
            {!! Form::label('contenu', trans("lang.my_blog_content"), ['class' => 'col-md-3 control-label text-md-right mx-1']) !!}
            <div class="col-md-9"  style="max-width: 570px;">
                {!! Form::textarea('contenu', $doctorBlog->contenu ?? '', ['class' => 'form-control', 'placeholder'=> trans("lang.blog_content_placeholder"), 'style' => 'height: 250px;', 'required' => 'required']) !!}
                <div class="form-text text-muted">{{ trans("lang.blog_content_help") }}</div>
            </div>
        </div>
    </div>
</div>


<div class="form-group col-12 d-flex flex-column flex-md-row justify-content-md-end justify-content-sm-center border-top pt-4">
    <button type="submit" class="btn bg-{{setting('theme_color')}} mx-md-3 my-lg-0 my-xl-0 my-md-0 my-2">
        <i class="fa fa-save"></i> {{trans('lang.save')}} {{trans('lang.my_blog')}}
    </button>
    <a href="{!! route('doctor_blog.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i> {{trans('lang.cancel')}}</a>
</div>
@prepend('scripts')
<script type="text/javascript">
    var blog_image = [];

    @if(isset($media) && $media)
        var mockFile = { name: "{{ $media->file_name }}", size: {{ $media->size }}, url: "{{ asset('storage/' . $media->id . '/' . $media->file_name) }}" };
        blog_image.push(mockFile);
    @endif
    var dz_blog_image = $(".dropzone.image").dropzone({
    url: "{!! url('uploads/storeImage') !!}",
    addRemoveLinks: true,
    maxFiles: 1, // Permet une seule image
    init: function () {
        var dzInstance = this;

        // Pré-remplir l'image si elle existe
        @if(isset($media) && $media)
            var mockFile = { name: "{{ $media->file_name }}", size: {{ $media->size }}, url: "{{ asset('storage/' . $media->id . '/' . $media->file_name) }}" };
            dzInstance.emit("addedfile", mockFile);
            dzInstance.emit("thumbnail", mockFile, mockFile.url);
            dzInstance.emit("complete", mockFile);
        @endif

        // Supprimer l'ancienne image lorsqu'une nouvelle est ajoutée
        dzInstance.on("addedfile", function (file) {
            if (dzInstance.files.length > 1) {
                var oldFile = dzInstance.files[0]; // Récupérer l'ancien fichier
                dzInstance.removeFile(oldFile); // Supprime le fichier précédent

                // Envoyer une requête pour supprimer l'ancienne image du serveur
                if (oldFile && oldFile.name) {
                    $.ajax({
                        url: "{!! url('uploads/deleteImage') !!}",
                        type: "POST",
                        data: {
                            _token: '{!! csrf_token() !!}',
                            media_id: $('#media_id').val() // Envoyer l'ID de l'ancienne image
                        },
                        success: function(response) {
                            console.log('Ancienne image supprimée:', response);
                        },
                        error: function(response) {
                            console.error('Erreur lors de la suppression de l\'ancienne image:', response);
                        }
                    });
                }
            }
        });
    },
    accept: function (file, done) {
        dzAccept(file, done, this.element, "{!! config('media-library.icons_folder') !!}");
    },
    sending: function (file, xhr, formData) {
        dzSendingMultiple(this, file, formData, '{!! csrf_token() !!}');
    },
    success: function (file, response) {
        if (response.success && response.media_id) {
            $('#media_id').val(response.media_id); // Mettre à jour l'ID du média
            console.log('Media ID:', response.media_id);
        } else {
            console.error('Erreur: Media ID non reçu dans la réponse');
        }
    },
    complete: function (file) {
        dzCompleteMultiple(this, file);
        dz_blog_image[0].mockFile = file;
    },
    removedfile: function (file) {
        $('#media_id').val(""); // Réinitialiser le champ caché si l'image est supprimée
        var _ref;
        return (_ref = file.previewElement) != null ? _ref.parentNode.removeChild(file.previewElement) : void 0;
    }
});

dz_blog_image[0].mockFile = blog_image;
dropzoneFields['image'] = dz_blog_image;
</script>
@endprepend