<?php

namespace Leankoala\SuluDocBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('LeankoalaSuluDocBundle:Default:index.html.twig');
    }
}
