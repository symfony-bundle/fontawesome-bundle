<?php

namespace Anezi\Bundle\FontAwesomeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DocsController extends Controller
{
    public function indexAction()
    {
        $headers = array(
            array(
                'id' => 'installation',
                'children' => array(
                    array(
                        'id' => 'systemRequirements'
                    ),
                    array(
                        'id' => 'composerRequirements'
                    ),
                    array(
                        'id' => 'enableAssetsInstaller'
                    ),
                    array(
                        'id' => 'bundleActivation'
                    ),
                    array(
                        'id' => 'projectUpdate'
                    ),
                )
            ),
            array(
                'id' => 'usage',
                'children' => array(
                    array(
                        'id' => 'twigAssets'
                    ),
                    array(
                        'id' => 'docs'
                    )
                )
            ),
            array(
                'id' => 'license'
            )
        );
        return $this->render(
            'FontAwesomeBundle:docs:index.html.twig',
            array(
                'headers' => $headers,
                'title' => 'FontAwesome',
                'composer_version' => '~5.0',
                'current_version' => '5.0.0'
            )
        );
    }
}
