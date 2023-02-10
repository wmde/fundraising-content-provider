# fundraising-content-provider

A wrapper around [htmlpurifier](http://htmlpurifier.org) and [twig](https://github.com/twigphp/Twig) 
to purify [fundraising-frontend-content](https://github.com/wmde/fundraising-frontend-content) content 
before rendering it into [FundraisingFrontend](https://github.com/wmde/FundraisingFrontend/).

# Using fundraising-frontend-content inside FundraisingFrontend

    $contentProvider = TwigContentProviderFactory::createContentProvider(
		new TwigContentProviderConfig( '/my/fundraising-frontend-content/i18n/LOCALE/' )
	);

    // get contents of fundraising-frontend-content/web & fundraising-frontend-content/shared
    $contentProvider->getWeb('template_name');
    
	// get contents of fundraising-frontend-content/mail & fundraising-frontend-content/shared
    $contentProvider->getMail('template_name');

# Running lints

    # When installed as a dependency and PHP is available:
    vendor/bin/lint_content /contentpath/de_DE --web pages/imprint

    # running in a docker container
    docker run -it --rm -v
	~/projects/fundraising-frontend-content/i18n:/contentpath -v
	"$PWD":/usr/src/myapp -w /usr/src/myapp php:8.0-alpine ./bin/lint_content /contentpath/de_DE --web pages/imprint

# Development

## Run PHPUnit tests

    docker run -it --rm --user $(id -u):$(id -g) -v "$PWD":/app -w /app
	php:8.0-alpine ./vendor/bin/phpunit
    
(you have to have docker installed for this to work)

