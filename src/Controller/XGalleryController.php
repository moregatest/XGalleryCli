<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class XGalleryController
 * @package App\Controller
 */
class XGalleryController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function index()
    {
        return $this->render(
            'index.html.twig'
        );
    }
}
