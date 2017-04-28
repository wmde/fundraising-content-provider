# fundraising-content-provider

[![Build Status](https://travis-ci.org/wmde/fundraising-content-provider.svg?branch=master)](https://travis-ci.org/wmde/fundraising-content-provider)

A wrapper around [htmlpurifier](http://htmlpurifier.org) and [twig](https://github.com/twigphp/Twig) 
to purify [fundraising-frontend-content](https://github.com/wmde/fundraising-frontend-content) content 
before rendering it into [FundraisingFrontend](https://github.com/wmde/FundraisingFrontend/).

# Using fundraising-frontend-content inside FundraisingFrontend

    $contentProvider = new ContentProvider( ['content_path' => '/my/fundraising-frontend-content'] );
    // contents of fundraising-frontend-content/web & fundraising-frontend-content/shared
    $contentProvider->getWeb('template_name');
    // contents of fundraising-frontend-content/mail & fundraising-frontend-content/shared
    $contentProvider->getMail('template_name');

# Running lints

    # When installed as a dependency and PHP is available:
    vendor/bin/lint_content /contentpath/de_DE --web pages/imprint

    # running in a docker container
    docker run -it --rm -v ~/projects/fundraising-frontend-content/i18n:/contentpath -v "$PWD":/usr/src/myapp -w /usr/src/myapp php:7.1-cli ./bin/lint_content /contentpath/de_DE --web pages/imprint

# Development

## Run phpunit tests

    docker run -it --rm --user $(id -u):$(id -g) -v "$PWD":/app -w /app php:7.1-cli ./vendor/bin/phpunit
    
(you have to have docker installed for this to work)

# Release notes

## Version 2.0 (2017-04-28)

* Content is no longer purified when loading it (purifying broke twig templates)
* renamed linter & added twig syntax checks
* Also allowing the following HTML tags: `hr`, `u`
* Also allowing `target="_blank"` attribute links (`a`)

## Version 1.0 (2017-04-26)

* Initial Release
* Providing a service that loads and purifies (some paths) templates, returns content
