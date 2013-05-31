Symfony2ErlangBundle with ZombieLandDB
======================================

Usage
-----

Compile and run ZombieLandDB:

    make
    erl -pa ebin deps/*/ebin -boot zldb -sname node0 -setcookie abc

Start test:

    app/console zldb:test

