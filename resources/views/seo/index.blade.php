@extends('layouts.app')

@section('content')

<!-- Content Header (Page header) -->
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-md-6">
        <h1 class="m-0 text-dark">Visibilité SEO </h1>
      </div><!-- /.col -->
      <div class="col-md-6">
        <ol class="breadcrumb bg-white float-sm-right rounded-pill px-4 py-2 d-none d-md-flex">
          <li class="breadcrumb-item"><a href="{{ url('/') }}"><i class="fa fa-dashboard"></i> {{ trans('lang.dashboard') }}</a></li>
          <li class="breadcrumb-item active">Visibilité SEO</li>
        </ol>
      </div><!-- /.col -->
    </div><!-- /.row -->
  </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main Content -->
<div class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-body">
        <h3>Participez à notre Journal Médical : Partagez vos connaissances avec la communauté médicale !</h3>
        <p>
          Nous sommes ravis de vous annoncer le lancement du Journal Médical, une plateforme dédiée aux professionnels de santé pour partager leur expertise, leurs recherches et leurs points de vue avec un large public. Grâce à cette fonctionnalité, les médecins inscrits sur notre plateforme de télémédecine peuvent publier des articles qui enrichissent la communauté médicale et participent à la diffusion des savoirs.
        </p>
        <h4>Pourquoi publier sur notre Journal Médical ?</h4>
        <ul>
          <li><strong>Valorisez votre expertise :</strong> Faites reconnaître vos compétences et votre spécialité en contribuant à des contenus de qualité.</li>
          <li><strong>Renforcez votre visibilité professionnelle :</strong> Bénéficiez d’une plateforme en ligne qui met en avant vos travaux auprès de patients et de confrères.</li>
          <li><strong>Contribuez à l’éducation médicale :</strong> Partagez des informations utiles qui aident vos collègues et améliorent la compréhension des patients.</li>
        </ul>

        <h4>Les types de contenus acceptés</h4>
        <p>Nous encourageons la publication d’articles dans les domaines suivants :</p>
        <ul>
          <li>Articles scientifiques ou cliniques</li>
          <li>Études de cas ou rapports d’expérience</li>
          <li>Conseils et guides pratiques destinés aux patients</li>
          <li>Revues de littérature ou synthèses sur des thèmes spécifiques</li>
          <li>Tribunes libres sur des enjeux de santé publique</li>
        </ul>

        <h4>Conditions de publication</h4>
        <p>Afin de garantir un contenu de qualité et pertinent pour notre audience, les articles soumis doivent respecter les conditions suivantes :</p>
        <ul>
          <li><strong>Originalité :</strong> Les articles doivent être originaux et ne pas avoir été publiés ailleurs.</li>
          <li><strong>Crédibilité scientifique :</strong> Les informations doivent être basées sur des données fiables et vérifiées. Les sources doivent être citées.</li>
          <li><strong>Respect de la déontologie médicale :</strong> Aucun contenu ne doit contenir de propos discriminatoires, promotionnels ou contraires à l’éthique médicale.</li>
        </ul>

        <h4>Format requis :</h4>
        <ul>
          <li><strong>Longueur :</strong> Entre 800 et 2 000 mots.</li>
          <li><strong>Structure :</strong> Un titre clair et accrocheur, une introduction, des sous-titres organisés, et une conclusion.</li>
          <li><strong>Références :</strong> Une liste des sources et références à la fin de l’article.</li>
          <li><strong>Validation éditoriale :</strong> Tous les articles seront relus et validés par notre comité éditorial avant publication.</li>
          <li><strong>Droits d'auteur :</strong> En publiant un article sur notre plateforme, vous accordez à WIC DOCTOR un droit exclusif de publication et de diffusion de votre contenu.</li>
        </ul>

        <h4>Comment soumettre votre article ?</h4>
        <p>
          Rédigez votre article en respectant les consignes ci-dessus. Envoyez-le à l’adresse : 
          <strong>departement.sem@way-interactive-convergence.com</strong><br>
          Notre comité éditorial vous donnera un retour sous 7 jours ouvrés.
        </p>
      </div>
    </div>
  </div>
</div>

@endsection
