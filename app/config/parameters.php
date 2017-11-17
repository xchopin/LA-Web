<?php

// Adds the languages to different scopes.
$path = $container->getParameter('kernel.root_dir') . '/resources/Translations/*.json';
$languages = [];
$dictionaries = [];

foreach (glob($path) as $file) {
    $languageId = substr(basename($file), 0, 2);
    $content = json_decode(file_get_contents($file), GLOB_BRACE);

    $dictionaries += [$languageId => $content];
    array_push($languages, $languageId);
}

$container->setParameter('languages', $languages);
$container->setParameter('dictionaries', $dictionaries);