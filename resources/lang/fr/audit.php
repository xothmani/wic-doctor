<?php


return [
    // General
    'na' => 'N/A',
    'system' => 'Système',

    // Page Header
    'audit_logs_plural' => 'Journaux d’audit',
    'audit_logs_desc' => 'Suivi des activités du système',

    // Filter Card
    'filter_logs' => 'Filtrer les journaux',
    'action_type' => 'Type d’action',
    'all_actions' => 'Toutes les actions',
    'start_date' => 'Date de début',
    'end_date' => 'Date de fin',
    'apply_filters' => 'Appliquer les filtres',
    'reset' => 'Réinitialiser',

    // Logs Table Card
    'audit_logs_table' => 'Tableau des journaux d’audit',

    // DataTable Buttons and Status
    'view_changes' => 'Voir les modifications',
    'mark_as_read' => 'Marquer comme lu',
    'read' => 'Lu',

    // Modal
    'changes_details' => 'Détails des modifications',
    'old_values' => 'Anciennes valeurs',
    'new_values' => 'Nouvelles valeurs',
    'field' => 'Champ',
    'value' => 'Valeur',
    'action' => 'Action',
    'entity_type' => 'Type d’entité',
    'description' => 'Description',
    'by' => 'Par',
    'date' => 'Date',
    'close' => 'Fermer',
    'no_old_values' => 'Aucune ancienne valeur',
    'no_new_values' => 'Aucune nouvelle valeur',
    'failed_to_load_changes' => 'Échec du chargement des modifications',
    'failed_to_mark_read' => 'Échec du marquage comme lu',

    // Fields (for renaming keys in audit logs)
    'fields' => [
        'creator_id' => 'Créateur',
        'doctor_id' => 'Docteur',
        'patient_id' => 'Patient',
        'start_at' => 'Début',
        'ends_at' => 'Fin',
        'appointment_type' => 'Type de rendez-vous',
        'notes' => 'Notes',
        'old_status' => 'Ancien statut',
        'new_status' => 'Nouveau statut',
        'cancel_reason' => 'Raison de l’annulation',
        'first_name' => 'Prénom',
        'last_name' => 'Nom',
        'phone' => 'Téléphone',
        'email' => 'Email',
    ],

    // Actions
    'actions' => [
        'create_appointment' => 'Création de rendez-vous',
        'update_appointment_status' => 'Mise à jour du statut du rendez-vous',
        'create_patient' => 'Création de patient',
    ],

    // Entities
    'entities' => [
        'appointment' => 'Rendez-vous',
        'patient' => 'Patient',
    ],

    // Descriptions
    'descriptions' => [
        'create_appointment' => 'Rendez-vous créé',
        'appointment_status_changed' => 'Statut du rendez-vous modifié',
        'create_patient' => 'Patient créé',
    ],

    // Statuses
    'statuses' => [
        '1' => 'Reçu',
        '5' => 'Prêt',
        'received' => 'Reçu',
        'ready' => 'Prêt',
    ],

    // Appointment Types
    'appointment_types' => [
        'home_visit' => 'Visite à domicile',
        'teleconsultation' => 'Téléconsultation',
        'cabinet' => 'Visite au cabinet',
        'online' => 'En ligne',
        'in_clinic' => 'En clinique',
    ],
];