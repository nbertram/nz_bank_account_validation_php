NZ Bank Account Validation
==========================

This is a simple library for validating input of New Zealand bank account numbers.
It is based on the documentation provided by the Inland Revenue Department:
  http://www.ird.govt.nz/resources/5/0/502c0d02-4a12-493a-8d6d-cf0560071c7d/payroll-spec-2016-v1+2.pdf

This library is not however affiliated with or endorsed by the IRD.


Quick start
-----------

```php
<?php

$accountNumber = '12-3140-0171323-50';
list($bank, $branch, $account, $suffix) = explode('-', $accountNumber);

var_dump(NeilNZ\NZBankAccountValidation\Validator::validate($bank, $branch, $account, $suffix));
```


Installing
----------

Install using Composer with:

  ```bash
  composer.phar require neilnz/nzbankaccountvalidation
  ```


Support
-------
Raise a Github ticket if you find something wrong with this:
  https://github.com/nbertram/nz_bank_account_validation_php/issues

Pull requests also gratefully considered.

