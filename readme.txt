--------------------
REQUIREMENTS:
 - docker
 - docker compose
 - PHPStorm
--------------------
ACTIONS PLANS:
 - get remote PHP server
 - drag all project dependencies
 - configure our IDE (PHPStorm) to use remote PHP server and run tests
 - run tests and be sure all tests are pathing
--------------------
getting remote PHP server:
 - go into project root
 - edit .env, if it needs
 - up containers, running:
   $ docker-compose up -d
 - check them, running:
   $ docker container ls
--------------------
project composer installation with docker:
 - view current docker containers list
   $ docker container ls
 - connect to current compose PHP container
   $ docker exec -it ${CONTAINER_ID} sh
 - use composer
   $ composer install/update
--------------------
PHPStorm configure (register remote PHP server SSH connection):
 - go to File | Settings | Tools | SSH Configurations -> add new
 - docker is on host machine case:
       Host         : 0.0.0.0
       Port         : ${parameter from .env file}
       User name    : root
       Password     : root
 - docker is on virtual machine with NAT network case:
       add ports rule on VM
           Host         : PHP server virtual host       (for example 127.0.0.10)
           Host port    : PHP server virtual SSH port   (for example 33)
           Guest port   : ${parameter from .env file}

       Host         : PHP server virtual host, added into VM ports rules        (127.0.0.10 from our example)
       Port         : PHP server virtual SSH port, added into VM ports rules    (33 from our example)
       User name    : root
       Password     : root
--------------------
PHPStorm configure (remote interpreter usage):
 - go to File | Settings | Languages & Frameworks | PHP
 - add PHP remote interpreter: CLI Interpreters -> add new, using SSH connection, added before
 - add PHP remote interpreter path mapping: Path mapping -> add new
       Local path   : PHPStorm project root
       Remote path  : PHP server root (/var/www/html)
--------------------
PHPStorm configure (tests framework registration):
 - go to File | Settings | Languages & Frameworks | PHP | Test Frameworks
 - add new configuration type (PHPUnit Local)
       Use composer autoloader
       Pth to script : local path to composer autoloader file (autoload.php)
--------------------
PHPStorm configure (PHPUnit run configuration):
 - go to Run | Edit Configurations
 - add new, using PHPUnit template
 - parameters:
       Test scope   : Directory
       Directory    : local path to project "tests" directory
       Interpreter  : PHP remote interpreter, added before
--------------------
RUNNING TESTS:
 - fire Run | Run
 - read