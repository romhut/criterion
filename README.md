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


### Example criterion.yml
```yml
setup:
    - composer install --dev
test:
    - phpunit
fail:
    - sh log-fail.sh
pass:
    - sh deploy.sh
```


# Running Workers

```shell
php src/Criterion/Worker/test.php
```
