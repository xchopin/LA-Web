# Université de Lorraine - Learning Analytics Application

## Installation
### 1. Create project using Composer
``` bash
$ git clone https://gitlab.univ-lorraine.fr/chopin1/LA-Webapp
```

### 2. Download bower and npm dependencies
``` bash
$ bower install
$ npm install
```
This will create a `lib/` folder in `public/` for jQuery and Semantic UI

##### Install Gulp globally
``` bash
$ npm install -g gulp-cli
```

##### Run watcher to compile SASS and Javascript
``` bash
$ gulp
```

This will compile and watch all SASS and JS files and put the result in the `public/` folder

### 3. Setup permissions
You will have to give write permissions to the `var/cache/` and `var/logs/` folders
``` bash
$ chmod 777 var/cache var/logs
```


## Key files
- `public/index.php`: Application entry point
- `var/cache/twig/`: Twig cache
- `app/`: Configuration files
    - `controllers.php`: Registers every controller in the app container
    - `database.php`: Script for creating database tables
    - `parameters.yml.dist`: Database configuration file model (do not put your database configuration here)
    - `dependencies.php`: Services for Pimple
    - `handlers.php`: Slim error handlers
    - `middleware.php`: Application middleware
    - `settings.php`: Application configuration
- `src/`
    - `App/`
        - `Controller/`: Application controllers
            - `Controller.php`: Base controller. All controllers should extend this class
        - `Middleware/`: Application middleware
        - `Model/`: Eloquent model classes
        - `Resources/`
            - `routes/`: Application routes
                - `app.php`: Main routing file
                - `auth.php`: Routing file for authentication
            - `views/`: Twig templates
