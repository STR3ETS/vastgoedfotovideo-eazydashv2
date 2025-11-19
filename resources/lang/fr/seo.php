<?php

return [

    'page_title' => 'Audits SEO',

    'filters' => [
        'company'        => 'Client / entreprise',
        'all_companies'  => 'Toutes les entreprises',
        'status'         => 'Statut',
        'all_statuses'   => 'Tous les statuts',
        'apply'          => 'Appliquer les filtres',
        'reset'          => 'Réinitialiser',
    ],

    'new_audit' => [
        'title'              => 'Lancer un nouvel audit SEO',
        'subtitle'           => 'Sélectionnez un client et un type d’audit. L’analyse s’exécute en arrière-plan.',
        'company'            => 'Client / entreprise',
        'company_placeholder'=> 'Sélectionnez une entreprise',
        'domain'             => 'Domaine (optionnel)',
        'domain_placeholder' => 'Exemple : exemple.fr',
        'domain_help'        => 'Laissez vide pour utiliser automatiquement le domaine du client (si disponible).',
        'type'               => 'Type d’audit',
        'country'            => 'Pays',
        'locale'             => 'Langue / locale',
        'start'              => 'Démarrer l’audit SEO',
    ],

    'list' => [
        'title'        => 'Historique des audits',
        'count_suffix' => 'audits',
        'empty'        => 'Aucun audit SEO n’a encore été effectué.',
    ],

    'detail' => [
        'title'        => 'Dernier audit',
        'subtitle'     => 'Résumé de l’audit exécuté le plus récemment.',
        'overall_score'=> 'Score global',
        'sections'     => 'Catégories principales',
        'issues_found' => 'points à améliorer trouvés',
        'no_sections'  => 'Une fois la connexion SE Ranking activée, les résultats apparaîtront ici.',
        'empty_title'  => 'Aucun audit',
        'empty_text'   => 'Lancez un premier audit à gauche. Le résumé apparaîtra ici ensuite.',
    ],

];
