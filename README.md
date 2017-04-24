# fundraising-html-filter

[![Build Status](https://travis-ci.org/wmde/fundraising-html-filter.svg?branch=master)](https://travis-ci.org/wmde/fundraising-html-filter)

A wrapper around [htmlpurifier](http://htmlpurifier.org) 
to purify [fundraising-frontend-content](https://github.com/wmde/fundraising-frontend-content) content 
before rendering it into [FundraisingFrontend](https://github.com/wmde/FundraisingFrontend/).

## Run phpunit tests

    docker run -it --rm --user $(id -u):$(id -g) -v "$PWD":/app -w /app php:7.1-cli ./vendor/bin/phpunit
    
(you have to have docker installed for this to work)