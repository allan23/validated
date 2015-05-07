# Validated - WordPress W3C Validation
[![Code Climate](https://codeclimate.com/github/allan23/validated/badges/gpa.svg)](https://codeclimate.com/github/allan23/validated)
[![Build Status](https://travis-ci.org/allan23/validated.svg?branch=master)](https://travis-ci.org/allan23/validated)

This plugin will allow you to check your pages/posts HTML against the W3C Validator.

## Local Development Friendly
Sometimes you need to test your local HTML against the Validator. To accomplish this, you need to add the following to your wp-config.php:
```define( 'VALIDATED_LOCAL', true);```