<?php 

return [
    1 => [
        "id" => 1,
        "name" => "app_search",
        "pattern" => "/^\\/([a-z]{2})\\/suche/",
        "reverse" => "/%_locale/suche",
        "module" => "AppBundle",
        "controller" => "@AppBundle\\Controller\\SearchController",
        "action" => "search",
        "variables" => "_locale",
        "defaults" => NULL,
        "siteId" => [

        ],
        "priority" => 1,
        "legacy" => FALSE,
        "creationDate" => 1553768287,
        "modificationDate" => 1553768290
    ],
    2 => [
        "id" => 2,
        "name" => "mt_journey",
        "pattern" => "/^\\/([a-z]{2})\\/mt([0-9])/",
        "reverse" => "/%_locale/mt%id",
        "module" => "AppBundle",
        "controller" => "@AppBundle\\Controller\\MyTripController",
        "action" => "map",
        "variables" => "_locale,id",
        "defaults" => "",
        "siteId" => [

        ],
        "priority" => 2,
        "creationDate" => 1565955049,
        "modificationDate" => 1565955466
    ]
];
