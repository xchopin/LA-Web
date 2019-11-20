# M.A.D. - My Analytics Dashboard

<p align="center">  
  <img width='45%' src='/.resources/logo.png'>
</p>


<p align="center">  
  <img src='https://scrutinizer-ci.com/g/xchopin/LAWA-UL/badges/build.png?b=master'>
  <a href='https://scrutinizer-ci.com/g/xchopin/LAWA-UL/?branch=master'>
     <img src='https://scrutinizer-ci.com/g/xchopin/LAWA-UL/badges/quality-score.png?b=master'>
  </a>
  <img src='https://img.shields.io/badge/GDPR-friendly-blue.svg'>

</p>


# Introduction
**My Analytics Dashboard is an alternative project of the [Apereo OpenDashboard](https://github.com/Apereo-Learning-Analytics-Initiative/OpenDashboard) application. It is made for University of Lorraine and the ESUP-Portail consortium.** <br>
**MAD is an efficient CASified application that provide visualizations and data for professors and students about their Learning Analytics.**


<p align="center">  
  <img src='/.resources/preview_mad.JPG'>
</p>


## 1. Features
- CAS Authentication
- GDPR compliant
- LDAP Access
- Roles (admin, professor and student)
- Admin tools
- Multilingual
- Modern Design
- Responsive-Design
- Dynamic Charts

## 2. Requirements
- Apache or Nginx
- [OpenLRW](https://github.com/Apereo-Learning-Analytics-Initiative/OpenLRW)
- PHP ⩾ 7.1
- PHP modules: 
    - LDAP module

## 3. Technology Used
- **Back-End:**
    - [Symfony Framework (v4.3.4)](https://symfony.com/doc/4.3.4/index.html)
    - [OpenLRW PHP Client](https://github.com/Apereo-Learning-Analytics-Initiative/OpenLRW-php-api-client)
- **Front-End:**
    - [Chart.js](http://www.chartjs.org/docs/latest/)
    - [Semantic UI](https://semantic-ui.com/introduction/getting-started.html)
    - [jQuery](https://api.jquery.com/)


# Installation
## 1. Clone the repository
``` bash
$ git clone https://github.com/xchopin/my-analytics-dashboard
```

## 2. Install the PHP dependencies
``` bash
$ composer install
```

## 3. Setup permissions
You will have to give write permissions to the `var/cache/` and `var/logs/` folders

Example given
``` bash
$ chown -R someuser:somegroup  var/cache var/logs
```

## 4. Fill the settings file
``` bash
$ cp .env.dist .env ; nano .env
```

## 5.1 Apache settings
Example of a Virtual Host: 
```apacheconf
<VirtualHost *:80>
    ServerAdmin foo.bar.com
    ServerName foo.bar.com
    ServerAlias www.foo.bar.com

    DocumentRoot /Library/WebServer/www/mad/public
    <Directory /Library/WebServer/www/mad/public>
        AllowOverride None
        Order Allow,Deny
        Allow from All

        <IfModule mod_rewrite.c>
            Options -MultiViews
            RewriteEngine On
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteRule ^(.*)$ index.php [QSA,L]
        </IfModule>
    </Directory>

    # optionally disable the RewriteEngine for the asset directories
    # which will allow apache to simply reply with a 404 when files are
    # not found instead of passing the request into the full symfony stack
    <Directory /Library/WebServer/www/mad/public/bundles>
        <IfModule mod_rewrite.c>
            RewriteEngine Off
        </IfModule>
    </Directory>
    ErrorLog "/private/var/log/apache2/mad-error_log"
    CustomLog "/private/var/log/apache2/mad-access_log" combined
</VirtualHost>
``` 

## 5.2 Nginx settings
```nginx 
server {
    server_name domain.tld www.domain.tld;
    root /var/www/project/public;

    location / {
        # try to serve file directly, fallback to index.php
        try_files $uri /index.php$is_args$args;
    }

    location ~ ^/index\.php(/|$) {
        fastcgi_pass unix:/var/run/php7.2-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        # When you are using symlinks to link the document root to the
        # current version of your application, you should pass the real
        # application path instead of the path to the symlink to PHP
        # FPM.
        # Otherwise, PHP's OPcache may not properly detect changes to
        # your PHP files (see https://github.com/zendtech/ZendOptimizerPlus/issues/126
        # for more information).
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        # Prevents URIs that include the front controller. This will 404:
        # http://domain.tld/index.php/some-path
        # Remove the internal directive to allow URIs like this
        internal;
    }

    # return 404 for all other php files not matching the front controller
    # this prevents access to other php files you don't want to be accessible.
    location ~ \.php$ {
        return 404;
    }

    error_log /var/log/nginx/project_error.log;
    access_log /var/log/nginx/project_access.log;
}
``` 
## 6. Optimization

For a faster loading you have to add the MongoDB indices written in the OpenLRW documentation.
Enable OPCache and APCu for better cache performances.


# Customisation

## 1. Add a new language

Go to the `translations/` directory then  copy the `en.json` file in order to use it as a template. Once you translated into the desired language, you have to save the file by only using the alpha-2 of the language (eg: `de.json`for a German translation).

That's it! the application is smart enough for adding it by itself to your navbar menu.


## 2. Dashboard

## 2.1 Use the included dashboards

In order to use the included dashboards you need to have the following structure for the Risk data collection
```json

{ 
    "_id" : "533332b21c83439383278409cc46d27c", 
    "orgId" : "62313a4d-f633-4be1-94b0-863067302671", 
    "tenantId" : "5888c16a53f86dab4261523d", 
    "userSourcedId" : "foobar", 
    "classSourcedId" : "28138", 
    "dateTime" : ISODate("2019-10-14T00:00:00.000+0000"), 
    "timeZoneOffset" : NumberLong(3600), 
    "active" : true, 
    "metadata" : {
        "consultedResources" : "33/35", 
        "activeDays" : "28/52", 
        "clicksCount" : "220/356", 
        "submittedWorkWeight" : "0.31", 
        "activitiesResultsWeight" : "0.56", 
        "forumViews" : "1/2", 
        "activitiesResults" : "9.81/10.0", 
        "global18" : "14/100", 
        "consultedResourcesWeight" : "0.28", 
        "forumViewsWeight" : "0.37", 
        "activeDaysWeight" : "0.32", 
        "global12" : "52/100", 
        "clicksCountWeight" : "0.13", 
        "global10" : "14.3/100", 
        "global16" : "33/100", 
        "submittedWork" : "8/9", 
        "global14" : "49/100"
    }, 
    "_class" : "org.apereo.openlrw.risk.MongoRisk"
}

```

`globalX` attributes represent the score of a student for the value X. Other attributes are indicators for the pie chart. All the attributes with `Weight`as a suffix are the visual 'weight' (width) of an indicator (reminder: a full chart is 2pi, the application will adjust automatically if it's less than this value).


## 2.2 Create your own dashboard

You will have to edit the full `ClassController.php` in order to create the own logic of your dashboards. For the front-end part, it is managed in `templates/User/Student/Class/*.twig`and `templates/User/Professor/class.twig`.
