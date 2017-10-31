# Learning Analytics Web Application (L.A.W.A.)

<p align="center">  
  <img src='https://scrutinizer-ci.com/g/xchopin/LAWA-UL/badges/build.png?b=master'>
  <a href='https://scrutinizer-ci.com/g/xchopin/LAWA-UL/?branch=master'>
     <img src='https://scrutinizer-ci.com/g/xchopin/LAWA-UL/badges/quality-score.png?b=master'>
  </a>
</p>

> Work in Progress <br>

##### Web application for universities using the Moodle LMS and OpenLRW.

## Features
- CAS Authentication
- LDAP Access
- Roles (Admins and users)
- Multilingual
- Responsive-Design
- Charts
- Active Record ORM for MongoDB
- API calls to OpenLRW

## Requirements
- PHP ⩾ 7.0
- PHP LDAP module
- MongoDB
- [OpenLRW](https://github.com/Apereo-Learning-Analytics-Initiative/OpenLRW)

## Installation
### 1. Clone the repository
``` bash
$ git clone https://gitlab.univ-lorraine.fr/chopin1/LA-Webapp
```

### 2. Install front-end dependencies
``` bash
$ bower install
```
It will create `lib/` in the `public/` directory for jQuery and Semantic UI dependencies.

### 3. Install PHP dependencies
``` bash
$ composer install
```

### 4. Setup permissions
You will have to give write permissions to the `var/cache/` and `var/logs/` folders
``` bash
$ chmod 777 var/cache var/logs
```

### 5. Fill the settings file
``` bash
$ cp app/config/parameters.yml.dist app/config/parameters.yml ; nano app/config/parameters.yml
```
## Key files
// ToDo
