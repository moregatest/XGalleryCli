<?php

/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Controller;

use App\Service\Crawler\PornhubCrawler;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
     * @param Request $request
     * @return RedirectResponse
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function search(Request $request)
    {
        $crawler = new PornhubCrawler;
        $detail  = $crawler->getDetail($request->get('url'));

        foreach ($detail->mediaDefinitions as $media) {
            if (empty($media->videoUrl)) {
                continue;
            }

            $this->addFlash('info', $media->videoUrl);
            break;
        }

        return $this->redirect('/pornhub');
    }
}
