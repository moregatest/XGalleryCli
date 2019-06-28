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

use App\Service\Crawler\PornhubCrawler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PornhubController
 * @package App\Controller
 */
class PornhubController extends AbstractController
{
    /**
     * @Route("/pornhub")
     */
    public function index()
    {
        return $this->render('pornhub/index.html.twig');
    }

    /**
     * @Route("/pornhub/search", name="search", methods="POST")
     */
    public function search(Request $request)
    {
        $crawler = new PornhubCrawler;
        var_dump($crawler->getDetail($request->get('url')));
    }
}
