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

    # looks more complicated than it is because of mounting local content dir into docker container
    docker run -it --rm -v ~/projects/fundraising-frontend-content/i18n:/contentpath -v "$PWD":/usr/src/myapp -w /usr/src/myapp php:7.1-cli ./bin/purifier_lint lint-content /contentpath/de_DE --web pages/imprint

# Development

## Run phpunit tests

    docker run -it --rm --user $(id -u):$(id -g) -v "$PWD":/app -w /app php:7.1-cli ./vendor/bin/phpunit
    
(you have to have docker installed for this to work)