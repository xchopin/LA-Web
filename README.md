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
// ToDo