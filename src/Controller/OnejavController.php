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

use App\Entity\JavMedia;
use App\Service\Crawler\OnejavCrawler;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

    /**
     * OnejavController constructor.
     */
    public function __construct()
    {
        $this->crawler = new OnejavCrawler;
    }

    /**
     * @Route("/onejav")
     */
    public function index()
    {
        return $this->render('onejav/index.html.twig', ['featured' => $this->crawler->getFeatured()]);
    }

    /**
     * @Route("/onejav/search", methods="POST")
     * @param Request $request
     * @return Response
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function search(Request $request)
    {
        if (!$this->isCsrfTokenValid('onejav-search', $request->get('token'))) {
            return;
        }

        return $this->searchByKeyword($request->get('keyword'));
    }

    /**
     * @param $keyword
     * @return Response
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    private function searchByKeyword($keyword)
    {
        $results = $this->crawler->search($keyword);

        if (empty($results)) {
            return $this->render('onejav/empty.html.twig', ['keyword' => $keyword]);
        }

        $medias = [];

        foreach ($results as $index => $result) {
            $result  = reset($result);
            $keyword = $result->itemNumber;

            $medias [$index] = $this->getDoctrine()->getRepository(JavMedia::class)
                ->createQueryBuilder('media')
                ->where('LOWER(media.filename) = :keyword1')
                ->orWhere('LOWER(media.filename) = :keyword2')
                ->setParameter('keyword1', '%' . $keyword . '%')
                ->setParameter('keyword2', '%' . str_replace('-', '', $keyword) . '%')
                ->getQuery()
                ->getFirstResult();
        }

        return $this->render(
            'onejav/results.html.twig',
            [
                'keyword' => $keyword,
                'results' => $results,
                'medias' => $medias,
                'activeMenu' => 'onejav',
            ]
        );
    }

    /**
     * @Route("/onejav/search/{slug}", methods="GET")
     * @param $slug
     * @return Response
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function searchShow($slug)
    {
        return $this->searchByKeyword($slug);
    }
}
