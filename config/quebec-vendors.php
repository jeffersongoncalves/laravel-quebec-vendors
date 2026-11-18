<?php

return [

    'table' => 'quebec_vendors',

    /*
     * What the written contract with a service provider must cover (s. 18.3). Keys are stored on each
     * vendor's dpa_checklist; a service provider with an unchecked item is flagged.
     */
    'dpa_checklist' => [
        'confidentiality_measures' => 'Measures the provider must take to protect the confidentiality of the information (s. 18.3)',
        'mandate_only' => 'The information is used only to carry out the mandate or the contract (s. 18.3)',
        'no_retention' => 'The information is not kept after the contract ends (s. 18.3)',
        'incident_notice' => 'The provider notifies your person in charge without delay of any breach, or attempted breach, of confidentiality (s. 18.3)',
        'verification' => 'You may conduct any verification relating to confidentiality (s. 18.3)',
        'outside_quebec_protection' => 'Outside Quebec: the information receives adequate protection, as the privacy impact assessment requires (s. 17)',
    ],

    /*
     * How often each risk tier must be reviewed, counted from the last review
     * (or from registration). CarbonInterval strings.
     */
    'review_every' => [
        'low' => '3 years',
        'medium' => '2 years',
        'high' => '1 year',
    ],

    /*
     * Where the information stays in Quebec: no assessment needed. Any other location (another
     * province or a country) counts as outside Quebec (s. 17).
     */
    'home' => ['QC'],

    /*
     * Default location for new vendors.
     */
    'default_location' => 'QC',

];
