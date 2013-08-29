Criterion
===
"A principle or standard by which something may be judged or decided"

----

[![Build Status](https://travis-ci.org/romhut/criterion.png)](https://travis-ci.org/romhut/criterion)
[![Dependency Status](https://www.versioneye.com/user/projects/520f94d0632bac1d6e00148d/badge.png)](https://www.versioneye.com/user/projects/520f94d0632bac1d6e00148d)
[![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/romhut/criterion/trend.png)](https://bitdeli.com/free "Bitdeli Badge")


Criterion is a Continuous Integration app built in PHP using MongoDB. Criterion is very easy to setup, and you can add GIT repositories from any provider, and run any commands you wish against it.

![Criterion](http://f.cl.ly/items/2k3M0b1c1H353C2w3q06/Screen%20Shot%202013-07-14%20at%2014.54.28.png)

Criterion will auto detect test environments if no .criterion.yml file is provided. This means you can setup CI with no changes to your code at all. For example, above we are testing a few libraries that have never even heard of Criterion, yet they still run.

### Recommendations
Before using Criterion please read our recommendations [here](https://github.com/romhut/criterion/wiki/Recommendations)

### Dependencies

- PHP >=5.3.3
- PHP Mongo Driver >= 1.3.0
- MongoDB >= 2.2.0
- Git >= 1.7.0

### Installation
To install criterion you must use [Composer](http://getcomposer.org/)

```shell
composer install && bin/cli install
```

### Running Workers

```shell
bin/cli worker:start --debug --interval=20
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

### Github Service Hook
You can add a Github WebHook to automatically run the tests on your project when a commit is pushed. For example:

```
http://criterion.example.com/hook/github?token=example
```
You can even add this hook to your Github repositories, and Criterion will automatically create a project if you have not already done so.

These tokens can be generated by any user, but the user must be an admin to be able to run tests.


### Limitations (Some of these may come in the future)
 - Multiple PHP Versions
 - Virtualization (A VM per Test)

### Support
If you need a hand with anything then use one of the following methods:
 - [Github Issues](http://github.com/romhut/criterion/issues)
 - IRC (#criterion on Freenode)
 - Twitter: [@criterionapp](http://twitter.com/criterionapp)
