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

use App\Service\Crawler\OnejavCrawler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class OnejavController
 * @package App\Controller
 */
class OnejavController extends AbstractController
{
    /**
     * @var OnejavCrawler
     */
    private $crawler;

    public function __construct()
    {
        $this->crawler = new OnejavCrawler;
    }

    /**
     * @Route("/onejav")
     */
    public function index()
    {
        $featuredLinks = $this->crawler->getFeatured();

        return $this->render('onejav/index.html.twig', ['items' => $featuredLinks]);
    }

    /**
     * @Route("/onejav/search", name="search", methods="POST")
     */
    public function search(Request $request)
    {
        $results = $this->crawler->search($request->get('keyword'));

        if (empty($results)) {
            return $this->render('onejav/empty.html.twig', ['keyword' => $request->get('keyword')]);
        }

        return $this->render(
            'onejav/search.html.twig',
            [
                'keyword' => $request->get('keyword'),
                'results' => $results,
                'activeMenu' => 'onejav',
            ]
        );
    }
}
