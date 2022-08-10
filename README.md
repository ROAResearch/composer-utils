Composer Install Utils
======================

Utils to automatically configure and bootstrap the installation of a project or library using composer.

Installation
------------

The preferred way to install this extension is through
[composer](http://getcomposer.org/download/).

add

```json
"roaresearch/composer-utils": "~1.0.0"
```

to the require section of your composer.json for projects or the require-dev
section for libraries.

    That means its important that a library doesnt inherit
    this dependency but projects that are meant to be used on production and are
    not going to be inherited can use this library as part of their installation
    bootstrap process.

Usage
-----

To utilize this library first the concept of "builder" and "template" needs to be
explained.

A "builder" is a composer script that gets information from the developer or
environment to build a series of templates.

The "templates" are files that will be generated when the project is created or
the builder is manually called. These files will contain information to
bootstrap the project or library.

As such the composer.json file needs to include a call for the builder and a
configuration for the templates.

```json
    "scripts": {
        "bootstrap-install": "roaresearch\\composer\\utils\\DBListener::config",
        "blank-install": "roaresearch\\composer\\utils\\DBListener::blankConfig",
        "post-create-project-cmd": "@bootstrap-install"
    },
    "extra": {
        "utilConfig": {
            "dbname": "cool_project",
            "dbtestsuffix": "_test",
            "dbtpls": {
                "db.local.php": "roaresearch\\composer\\utils\\VarDBTPL"
            }
            "dbtesttpls": {
                "db.test.php": "roaresearch\\composer\\utils\\VarDBTPL"
            }
        }
    },
```

This creates scripts bootstraps-install and blank-install then calls
bootstrap-install after creating a new project with this library.

If bootstrap-install is called then it will ask for the db credentials, check
those credentials works, that the database exists and will create a config file
db.local.php using the template VarDBTPL.

If blank-install is called then it will ask for the db credentials but wont make
any check or validation about it and will simply create the config file with the
provided credentials.

In the above case dbtestsuffix is configured aswell which means a database meant
purely for testing will be created by adding the suffix to the databse name. In
this case the testing databse will be `cool_project_test` and the respective
config file db.test.php

If dbtestsuffix is not set or if an argument "environment" is set to "prod" the
test database and config file wont be created. For more info on parameters check
the following section.

Notice that the tpl file routes are relative to the root folder of the project
composer installation, subfolders are allowed but no parents folders or absolute
routes.

### Arguments

Arguments are the information that the builders need to fill the templates.

Arguments can be obtained by 3 main ways which are detailed in the order they
are processed.

#### Command Arguments

This arguments are only used when the builders are called manually and not by
getting called after creating a project.

The arguments are written on the console as `arg=value`.

Using the composer.json from the example above as reference

```bash
composer create-project coolcompany/coolproject web/ --no-scripts
cd web/
composer bootstrap-install -- dbname=prod_project dbuser=root dbpass=s3cr3t
```

#### Package Arguments

This arguments are configured on the composer.json file on the extra.utilConfig
section. On the example above only dbname is configured this way since its
expected that dbuser, dbpass and dbdsn will change every time the project is
cloned on a new environment but all arguments can be set on the composer.json
in case your company or development team have standarized users and passwords.

To use them just call the create-project command or the script manually

#### Environment Arguments

PHP supports environmental variables in server but also on console which doesnt
affect the server handling the actual requests.

As such its safe to use environment variables during the bootstrap install.

```bash
export dbuser="root"
export dbpass="s3cr3t"
composer create-project coolcompany/coolproject web/
composer create-project coolcompany/anotherproject web2/
```

The create-project command will create config files for both projects using the
environment variables this way. It also helps when determining if a server is
meant for production or development by using the "environment" argument. Yes its
redundant on purpose.

```bash
export environment="prod"
```
or
```bash
export environment="dev"
````

#### Prompt Arguments

Finally if an argument is not set but the builder needs it then the console
will prompt the user to write it on a prompt. If the variable has a default
value to use when receiving an empty response then that default value will
appear on the prompt between parenthesis.

Supported Arguments
-------------------

roaresearch\composer\utils\DBListener::config and
roaresearch\composer\utils\DBListener::blankConfig support the same arguments
with the difference that comfig will corroborate the given credentials while
blankConfig wont.

- dbuser
- dbpass
- dbname
- dbdsn
- dbtestsuffix
- environment
