## Rationale

The advantages of this framework:

* faster
* runs every test case in a clean WordPress install
* uses the default PHPUnit runner, instead of custom one
* doesn't encourage or support the usage of shared/prebuilt fixtures

It uses SQL transactions to clean up automatically after each test.

## Current Status

This framework [has been ported](http://unit-test.trac.wordpress.org/ticket/42) to the official testing suite.

To get involved with WordPress testing, see http://unit-test.trac.wordpress.org/