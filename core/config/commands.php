<?php

return [

    // ==========================
    // Communication
    // ==========================

    [
        'logicalId' => 'connectivity',
        'name' => 'Communication',
        'type' => 'info',
        'subType' => 'binary',
    ],

    // ==========================
    // État de charge
    // ==========================

    [
        'logicalId' => 'charge_state_label',
        'name' => 'État recharge',
        'type' => 'info',
        'subType' => 'string',
    ],

    [
        'logicalId' => 'charge_state',
        'name' => 'État recharge (code)',
        'type' => 'info',
        'subType' => 'numeric',
    ],

    [
        'logicalId' => 'ready_state',
        'name' => 'État prêt (code)',
        'type' => 'info',
        'subType' => 'numeric',
    ],

    [
        'logicalId' => 'paused',
        'name' => 'Charge en pause',
        'type' => 'info',
        'subType' => 'binary',
    ],

    [
        'logicalId' => 'locked',
        'name' => 'Chargeur verrouillé',
        'type' => 'info',
        'subType' => 'binary',
    ],

    // ==========================
    // Mesures électriques
    // ==========================

    [
        'logicalId' => 'charge_power',
        'name' => 'Puissance de charge',
        'type' => 'info',
        'subType' => 'numeric',
        'unit' => 'W',
    ],

    [
        'logicalId' => 'charge_energy',
        'name' => 'Énergie de la session',
        'type' => 'info',
        'subType' => 'numeric',
        'unit' => 'kWh',
    ],

    [
        'logicalId' => 'charge_time',
        'name' => 'Durée de charge',
        'type' => 'info',
        'subType' => 'string',
    ],

    [
        'logicalId' => 'charge_limited_power',
        'name' => 'Charge limitée',
        'type' => 'info',
        'subType' => 'numeric',
        'unit' => '%',
        'configuration' => ['minValue' => 80, 'maxValue' => 100],
    ],

    [
        'logicalId' => 'min_intensity',
        'name' => 'Intensité minimale',
        'type' => 'info',
        'subType' => 'numeric',
        'unit' => 'A',
        'configuration' => ['minValue' => 6, 'maxValue' => 16],
    ],
    
    [
        'logicalId' => 'max_intensity',
        'name' => 'Intensité maximale',
        'type' => 'info',
        'subType' => 'numeric',
        'unit' => 'A',
        'configuration' => ['minValue' => 6, 'maxValue' => 32],
    ],

    [
        'logicalId' => 'voltage_installation',
        'name' => 'Tension installation',
        'type' => 'info',
        'subType' => 'numeric',
        'unit' => 'V',
    ],

    // ==========================
    // Mesures par phase (firmware >= 2.5.0)
    // ==========================

    [
        'logicalId' => 'intensity_l1',
        'name' => 'Intensité phase L1',
        'type' => 'info',
        'subType' => 'numeric',
        'unit' => 'A',
    ],

    [
        'logicalId' => 'intensity_l2',
        'name' => 'Intensité phase L2',
        'type' => 'info',
        'subType' => 'numeric',
        'unit' => 'A',
    ],

    [
        'logicalId' => 'intensity_l3',
        'name' => 'Intensité phase L3',
        'type' => 'info',
        'subType' => 'numeric',
        'unit' => 'A',
    ],

    [
        'logicalId' => 'voltage_l1',
        'name' => 'Tension phase L1',
        'type' => 'info',
        'subType' => 'numeric',
        'unit' => 'V',
    ],

    [
        'logicalId' => 'voltage_l2',
        'name' => 'Tension phase L2',
        'type' => 'info',
        'subType' => 'numeric',
        'unit' => 'V',
    ],

    [
        'logicalId' => 'voltage_l3',
        'name' => 'Tension phase L3',
        'type' => 'info',
        'subType' => 'numeric',
        'unit' => 'V',
    ],

    [
        'logicalId' => 'contracted_power',
        'name' => 'Puissance contractée',
        'type' => 'info',
        'subType' => 'numeric',
        'unit' => 'W',
    ],

    // ==========================
    // Énergie domestique / solaire
    // ==========================

    [
        'logicalId' => 'house_power',
        'name' => 'Puissance maison',
        'type' => 'info',
        'subType' => 'numeric',
        'unit' => 'W',
    ],

    [
        'logicalId' => 'fv_power',
        'name' => 'Puissance photovoltaïque',
        'type' => 'info',
        'subType' => 'numeric',
        'unit' => 'W',
    ],

    [
        'logicalId' => 'battery_power',
        'name' => 'Puissance batterie domestique',
        'type' => 'info',
        'subType' => 'numeric',
        'unit' => 'W',
    ],

    // ==========================
    // Mode dynamique / solaire
    // ==========================

    [
        'logicalId' => 'charge_mode',
        'name' => 'Mode de charge (code)',
        'type' => 'info',
        'subType' => 'numeric',
    ],

    [
        'logicalId' => 'charge_mode_label',
        'name' => 'Mode de charge',
        'type' => 'info',
        'subType' => 'string',
    ],

    [
        'logicalId' => 'dynamic',
        'name' => 'Mode dynamique actif',
        'type' => 'info',
        'subType' => 'binary',
    ],

    [
        'logicalId' => 'dynamic_power_mode',
        'name' => 'Mode de puissance dynamique',
        'type' => 'info',
        'subType' => 'string',
    ],

    [
        'logicalId' => 'pause_dynamic',
        'name' => 'Pause dynamique',
        'type' => 'info',
        'subType' => 'binary',
    ],

    [
        'logicalId' => 'timer',
        'name' => 'Minuterie',
        'type' => 'info',
        'subType' => 'string',
    ],

    // ==========================
    // Diagnostic / Divers
    // ==========================

    [
        'logicalId' => 'slave_error',
        'name' => 'Erreur esclave',
        'type' => 'info',
        'subType' => 'string',
    ],

    [
        'logicalId' => 'firmware_version',
        'name' => 'Version firmware',
        'type' => 'info',
        'subType' => 'string',
    ],

    [
        'logicalId' => 'signal_status',
        'name' => 'Qualité signal Wi-Fi',
        'type' => 'info',
        'subType' => 'string',
    ],

    [
        'logicalId' => 'ssid',
        'name' => 'SSID Wi-Fi',
        'type' => 'info',
        'subType' => 'string',
    ],

    [
        'logicalId' => 'device_ip',
        'name' => 'Adresse IP du chargeur',
        'type' => 'info',
        'subType' => 'string',
    ],

    [
        'logicalId' => 'device_id',
        'name' => 'Identifiant du chargeur',
        'type' => 'info',
        'subType' => 'string',
    ],

    // ==========================
    // Actions - Divers
    // ==========================

    [
        'logicalId' => 'refresh',
        'name' => 'Rafraîchir maintenant',
        'type' => 'action',
        'subType' => 'other',
    ],

    // ==========================
    // Actions - Charge
    // ==========================

    [
        'logicalId' => 'pause_charge',
        'name' => 'Mettre en pause la charge',
        'type' => 'action',
        'subType' => 'other',
    ],

    [
        'logicalId' => 'resume_charge',
        'name' => 'Reprendre la charge',
        'type' => 'action',
        'subType' => 'other',
    ],

    [
        'logicalId' => 'lock_charger',
        'name' => 'Verrouiller le chargeur',
        'type' => 'action',
        'subType' => 'other',
    ],

    [
        'logicalId' => 'unlock_charger',
        'name' => 'Déverrouiller le chargeur',
        'type' => 'action',
        'subType' => 'other',
    ],

    // ==========================
    // Actions - Mode dynamique / solaire
    // ==========================

    [
        'logicalId' => 'enable_dynamic',
        'name' => 'Activer le mode dynamique',
        'type' => 'action',
        'subType' => 'other',
    ],

    [
        'logicalId' => 'disable_dynamic',
        'name' => 'Désactiver le mode dynamique',
        'type' => 'action',
        'subType' => 'other',
    ],

    [
        'logicalId' => 'set_dynamic_power_mode',
        'name' => 'Régler le mode de puissance dynamique',
        'type' => 'action',
        'subType' => 'select',
        'configuration' => [
            'listValue' => '0|Boost (minuterie activée);1|Minuterie désactivée;2|Charge exclusive;3|Puissance minimale;4|Réseau + Solaire (surplus PV);5|Arrêt',
        ],
    ],

    [
        'logicalId' => 'set_charge_mode',
        'name' => 'Régler le mode de charge',
        'type' => 'action',
        'subType' => 'select',
        'configuration' => [
            'listValue' => '0|Monophasé;1|Triphasé;2|Mixte (triphasé + monophasé)',
        ],
    ],

    // ==========================
    // Actions - Intensité
    // ==========================
    [
        'logicalId' => 'set_charge_limited_power',
        'name' => 'Régler la limite de charge',
        'type' => 'action',
        'subType' => 'slider',
        'unit' => '%',
        'configuration' => ['minValue' => 80, 'maxValue' => 100],
    ],

    [
        'logicalId' => 'set_min_intensity',
        'name' => 'Régler l\'intensité minimale',
        'type' => 'action',
        'subType' => 'slider',
        'unit' => 'A',
        'configuration' => ['minValue' => 6, 'maxValue' => 16],
    ],

    [
        'logicalId' => 'set_max_intensity',
        'name' => 'Régler l\'intensité maximale',
        'type' => 'action',
        'subType' => 'slider',
        'unit' => 'A',
        'configuration' => ['minValue' => 6, 'maxValue' => 32],
    ],

];