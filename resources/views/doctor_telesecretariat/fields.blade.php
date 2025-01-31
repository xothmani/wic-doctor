<!-- Introduction Telesecretariat -->
<div class="form-group">
    <div class="card-body">
    <h3 class="text-dark font-weight-bold">Télésécrétariat</h3>

    <p>
        Le télésécrétariat vous permet de déléguer la gestion de votre agenda à un professionnel de manière sécurisée et personnalisée.
    </p>
    <p >
        Lorsque vous ajoutez une adresse e-mail de télésécrétaire, cette dernière obtient un accès direct à votre agenda, avec des permissions personnalisées pour gérer vos rendez-vous. Elle pourra ainsi planifier, modifier ou annuler des créneaux, tout en respectant vos préférences et votre emploi du temps. Cette fonctionnalité permet de déléguer certaines tâches administratives tout en gardant un contrôle total sur les informations sensibles.
    </p>
    </div>
</div>
<!-- Email Field -->
<div class="form-group d-flex align-items-center">
    <label for="email" class="col-md-6 control-label text-md-right">Adresse email :</label>
    <div class="col-md-9">
        <input type="email" id="email" name="email" class="form-control" placeholder="Entrez votre email" required>
    </div>
</div>

<!-- Submit Field -->
<div class="form-group col-12 d-flex flex-column flex-md-row justify-content-md-end justify-content-sm-center border-top pt-4">
    <button type="submit" class="btn bg-{{setting('theme_color')}} mx-md-3 my-lg-0 my-xl-0 my-md-0 my-2">
        <i class="fa fa-save"></i> {{trans('lang.save')}} {{trans('lang.telesecretariat')}}
    </button>
    <a href="{!! route('doctor_requests.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i> {{trans('lang.cancel')}}</a>
</div>
