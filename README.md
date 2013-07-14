Criterion (/krīˈti(ə)rēən/)
===
"A principle or standard by which something may be judged or decided"

----

Criterion is a simple Continuous Integration tool build in PHP using MongoDB.

You can add GIT repos from any provider, and run any commands you wish against it.


### Dependencies

- PHP >=5.3.3
- PHP Mongo Driver >= 1.3.0
- MongoDB >= 2.2.0
- Git >= 1.7.0

### Installation

```shell
bin/cli install
```

### Running Workers

```shell
php src/Criterion/Worker/test.php --debug
```

### Example criterion.yml
```yml
setup:
    - composer install --dev
script:
    - phpunit
fail:
    - sh log-fail.sh
pass:
    - sh deploy.sh
```

### Authors
- [Scott Robertson](http://github.com/scottymeuk)
- [Marc Qualie](http://github.com/marcqualie)