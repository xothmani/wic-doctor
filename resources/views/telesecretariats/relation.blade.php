@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h4>Télésécrétariat</h4>
        </div>
        <div class="card-body">
            <p>
                Le télésécrétariat vous permet de déléguer la gestion de votre agenda à un professionnel de manière sécurisée et personnalisée.
            </p>
            <p>
                Lorsque vous ajoutez une adresse e-mail de télésécrétaire, cette dernière obtient un accès direct à votre agenda, avec des permissions personnalisées pour gérer vos rendez-vous. Elle pourra ainsi planifier, modifier ou annuler des créneaux, tout en respectant vos préférences et votre emploi du temps. Cette fonctionnalité permet de déléguer certaines tâches administratives tout en gardant un contrôle total sur les informations sensibles.
            </p>

            <form action="{{ route('telesecretariats.relation') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="email">Adresse e-mail du télésécrétaire</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Entrez l'adresse e-mail" required>
                </div>
                <div class="form-group mt-2">
                    <button type="submit" class="btn btn-primary">Envoyer</button>
                    <a href="{{ route('telesecretariats.index') }}" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
