<?php

$path = $container->getParameter('kernel.root_dir') . '/../translations/*.json';
$languages = [];
$dictionaries = [];

foreach (glob($path) as $file) {
    $languageId = substr(basename($file), 0, 2);
    $content = json_decode(file_get_contents($file), GLOB_BRACE);

    $dictionaries += [$languageId => $content]; // ToDo : optimize by selecting only the language used
    array_push($languages, $languageId);
}

$container->setParameter('languages', $languages);

$container->setParameter('dictionaries', $dictionaries);

$ldapInstance = ldap_connect(env('LDAP_HOST'), env('LDAP_PORT'));
ldap_set_option($ldapInstance, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option($ldapInstance, LDAP_OPT_REFERRALS, 0);
$container->set('ldap', /** @scrutinizer ignore-type */ $ldapInstance);