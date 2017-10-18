<?php

/**
function rechercheUserLDAP($ldapconn,$dn,$nom,$prenom){
    if((!empty($nom))&&(!empty($prenom))){
        $filtre="(&(!(businesscategory=E*))(sn=".$nom.")(givenname=".$prenom."))";
    }
    elseif(!empty($nom)){
        $filtre="(&(!(businesscategory=E*))(sn=".$nom."))";
    }
    elseif(!empty($prenom)){
        $filtre="(&(!(businesscategory=E*))(givenname=".$prenom."))";
    }
    $attrs = array("cn", "uid");
    $sr = ldap_search ($ldapconn, $dn, $filtre,$attrs);
    return ldap_get_entries($ldapconn,$sr);
}
*/