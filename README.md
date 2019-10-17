# M.A.D. - My Analytics Dashboard

<p align="center">  
  <img width='45%' src='/.resources/logo.png'>
</p>


<p align="center">  
  <img src='https://scrutinizer-ci.com/g/xchopin/LAWA-UL/badges/build.png?b=master'>
  <a href='https://scrutinizer-ci.com/g/xchopin/LAWA-UL/?branch=master'>
     <img src='https://scrutinizer-ci.com/g/xchopin/LAWA-UL/badges/quality-score.png?b=master'>
  </a>
</p>



> Work in Progress <br>


# Introduction
**My Analytics Dashboard is an alternative project of the [Apereo OpenDashboard](https://github.com/Apereo-Learning-Analytics-Initiative/OpenDashboard) application. It is made for University of Lorraine and the ESUP-Portail consortium.** <br>
**MAD is an efficient CASified application that provide visualizations and data for professors and students about their Learning Analytics.**


## 1. Features
- CAS Authentication
- LDAP Access
- Roles (Admins and students)
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
    - [Symfony Framework (v4.3.2)](https://symfony.com/doc/4.3.2/index.html)
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

    DocumentRoot /Library/WebServer/www/LAWA/public
    <Directory /Library/WebServer/www/LAWA/public>
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
    <Directory /Library/WebServer/www/LAWA/public/bundles>
        <IfModule mod_rewrite.c>
            RewriteEngine Off
        </IfModule>
    </Directory>
    ErrorLog "/private/var/log/apache2/LAWA-error_log"
    CustomLog "/private/var/log/apache2/LAWA-access_log" combined
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
        fastcgi_pass unix:/var/run/php7.1-fpm.sock;
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
