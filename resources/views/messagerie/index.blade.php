<!-- resources/views/messagerie/index.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="my-4">Messagerie entre Médecins</h2>

    <div class="alert alert-info" role="alert">
        Sélectionnez un médecin pour voir la conversation.
    </div>

    <div class="row">
        <!-- Liste des conversations -->
        <div class="col-md-4">
            <div class="list-group">
                <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Dr. Jean Dupont</strong>
                        <br>
                        <small>Dernier message: Bonjour, comment allez-vous ?</small>
                    </div>
                    <span class="badge badge-primary badge-pill">10 min ago</span>
                </a>

                <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Dr. Marie Durand</strong>
                        <br>
                        <small>Dernier message: Il faut discuter du cas urgent.</small>
                    </div>
                    <span class="badge badge-primary badge-pill">1h ago</span>
                </a>

                <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Dr. Paul Martin</strong>
                        <br>
                        <small>Dernier message: Le rapport est prêt, je vous l'enverrai demain.</small>
                    </div>
                    <span class="badge badge-primary badge-pill">5h ago</span>
                </a>
            </div>
        </div>

        <!-- Section de la discussion -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <strong>Dr. Jean Dupont</strong> - Discussion
                </div>
                <div class="card-body" style="height: 400px; overflow-y: scroll;">
                    <!-- Messages -->
                    <div class="message">
                        <strong>Dr. Jean Dupont:</strong> Bonjour, comment allez-vous ?
                        <br><small class="text-muted">Il y a 10 minutes</small>
                    </div>
                    <div class="message">
                        <strong>Vous:</strong> Je vais bien, merci ! Et vous ?
                        <br><small class="text-muted">Il y a 5 minutes</small>
                    </div>
                </div>
                <div class="card-footer">
                    <form action="#" method="POST">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Écrivez un message..." aria-label="Message" aria-describedby="button-send">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit" id="button-send">Envoyer</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
