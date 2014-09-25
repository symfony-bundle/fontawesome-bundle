Font Awesome Bundle for Symfony
=========================

Add the Font Awesome inside the Symfony2.

Installation
============

Add bundle to your composer.json file
-------------------------------------

    // ...
    "require": {
        // ...
        "symfony-bundle/fontawesome-bundle": "4.2.*";
        // for Font Awesome 4.2.latest
        // ...
    },
    "scripts": {
        // ...
        "post-install-cmd": [
            // ...
            // insert it before Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::installAssets
            "Anezi\\Bundle\\FontAwesomeBundle\\Composer\\ScriptHandler::copyFilesToBundle",
            "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::installAssets",
            // ...
        ],
        "post-update-cmd": [
            // ...
            // insert it before Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::installAssets
            "Anezi\\Bundle\\FontAwesomeBundle\\Composer\\ScriptHandler::copyFilesToBundle",
            "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::installAssets",
            // ...
        ]
    },

Add bundle to your application kernel
-------------------------------------

    // app/AppKernel.php

    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Anezi\Bundle\FontAwesomeBundle\FontAwesomeBundle(),
            // ...
        );
    }

Download the bundle using Composer
---------------------------------

    $ composer update symfony-bundle/fontawesome-bundle

Install assets
--------------

Update assets using composer post command:

    $ composer run-script post-update-cmd

Usage
=====

Refer to the full font awesome file in your HTML template:

    <link rel="stylesheet" type="text/css" href="{{ asset('bundles/fontawesome/css/font-awesome.css') }}">

Refer to the minimal font awesome file:

    <link rel="stylesheet" type="text/css" href="{{ asset('bundles/fontawesome/css/font-awesome.min.css') }}">

License
=======

This bundle is available under the MIT license.