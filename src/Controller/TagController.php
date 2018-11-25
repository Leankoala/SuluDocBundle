<?php

namespace Leankoala\SuluDocBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TagController extends Controller
{
    public function doIndexAction()
    {
        return $this->render('LeankoalaSuluDocBundle:Tag:index.html.twig');
    }
}