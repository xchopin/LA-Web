# Learning Analytics Web Application (L.A.W.A.)

<p align="center">  
  <img src='https://scrutinizer-ci.com/g/xchopin/LAWA-UL/badges/build.png?b=master'>
  <a href='https://scrutinizer-ci.com/g/xchopin/LAWA-UL/?branch=master'>
     <img src='https://scrutinizer-ci.com/g/xchopin/LAWA-UL/badges/quality-score.png?b=master'>
  </a>
</p>

> Work in Progress <br>

# Introduction
#### Learning Analytics Web Application for universities using the Moodle LMS and OpenLRW.

## 1. Features
- CAS Authentication
- LDAP Access
- Roles (Admins and users)
- Multilingual
- Responsive-Design
- Dynamic Charts
- ODM for MongoDB
- API calls to OpenLRW

## 2. Requirements
- PHP ⩾ 7.0
- PHP LDAP module
- MongoDB PHP Driver
- MongoDB
- [OpenLRW](https://github.com/Apereo-Learning-Analytics-Initiative/OpenLRW)
- [Moodle LMS](https://moodle.org)

## 3. Technology Used
- [Symfony Framework (v3.3)](https://symfony.com/doc/3.3/index.html)
- [Eloquent ORM for MongoDB](https://github.com/jenssegers/laravel-mongodb) ([since Doctrine does not support PHP⩾7.0 yet](http://www.doctrine-project.org/2016/02/16/doctrine-mongodb-odm-release-1.0.5.html))
- [Guzzle](http://docs.guzzlephp.org)
- [Semantic UI](https://semantic-ui.com/introduction/getting-started.html)
- [jQuery](https://api.jquery.com/)


# Installation
## 1. Clone the repository
``` bash
$ git clone https://gitlab.univ-lorraine.fr/chopin1/LA-Webapp
```

## 2. Install front-end dependencies
``` bash
$ bower install
```
It will create `lib/` in the `web/` directory for jQuery and Semantic UI dependencies.

## 3. Install PHP dependencies
``` bash
$ composer install
```

## 4. Setup permissions
You will have to give write permissions to the `var/cache/` and `var/logs/` folders
``` bash
$ chmod 777 var/cache var/logs
```

## 5. Fill the settings file
``` bash
$ cp app/config/parameters.yml.dist app/config/parameters.yml ; nano app/config/parameters.yml
```

# Documentation
## Key files
// ToDo
