<?php

/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Controller;

use App\Entity\JavDownload;
use App\Entity\JavMyFavorite;
use App\Service\Crawler\OnejavCrawler;
use App\Service\Crawler\R18Crawler;
use App\Traits\HasCache;
use App\Traits\HasStorage;
use DateTime;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class OnejavController
 * @package App\Controller
 */
class OnejavController extends AbstractController
{
    use HasCache;
    use HasStorage;

    /**
     * @var OnejavCrawler
     */
    private $onejavCrawler;

    /**
     * @var R18Crawler
     */
    private $r18Crawler;

    /**
     * OnejavController constructor.
     * @param OnejavCrawler $crawler
     * @param R18Crawler $r18Crawler
     */
    public function __construct(OnejavCrawler $crawler, R18Crawler $r18Crawler)
    {
        $this->onejavCrawler = $crawler;
        $this->r18Crawler    = $r18Crawler;
    }

    /**
     * Index view for Onejav
     * @Route("/onejav")
     * @param Request $request
     * @return Response
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function index(Request $request)
    {
        $formatDateTime = 'Y/m/d';
        $date           = \App\Utils\DateTime::getDateTime($request->get('date', null), $formatDateTime);
        $today          = $date->format($formatDateTime);

        $daily[$today] = $this->prepareItems($this->onejavCrawler->getAllDetailItems($today));

        $response = $this->render(
            'onejav/index.html.twig',
            [
                'featured'  => $this->prepareItems($this->onejavCrawler->getFeatured()),
                'daily'     => $daily,
                'yesterday' => $date->add(\DateInterval::createFromDateString('yesterday'))->format('Y/m/d')
            ]
        );

        return $response;
    }

    /**
     * @Route("/onejav/ajax")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function indexAjax(Request $request)
    {
        $formatDateTime = 'Y/m/d';
        $date           = DateTime::createFromFormat($formatDateTime, $request->get('date'));
        $requestDate    = $date->add(\DateInterval::createFromDateString('yesterday'))->format('Y/m/d');

        $daily[$requestDate] = $this->prepareItems(
            $this->onejavCrawler->getAllDetailItems($requestDate)
        );

        return $this->json($this->renderView('onejav/cards.html.twig', ['daily' => $daily]));
    }

    /**
     * Detail page
     * @Route("/onejav/detail/{slug}")
     * @param $slug
     * @return Response
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function detail($slug)
    {
        $item = $this->onejavCrawler->getDetailFromUrl(
            'https://onejav.com/torrent/' . strtolower(str_replace('-', '', $slug))
        );

        $item->r18 = $this->onejavCrawler->getR18($item->itemNumber);

        /**
         * @TODO Related items
         */

        return $this->showResults($this->prepareItems([$item]), $slug);
    }

    /**
     * @Route("/onejav/daily/{slug}", methods="GET")
     * @param $slug
     * @return Response
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function daily($slug)
    {
        $slug = str_replace('_', '/', $slug);

        return $this->showResults($this->prepareItems($this->onejavCrawler->getAllDetailItems($slug)), $slug);
    }

    /**
     * @Route("/onejav/tag/{slug}", methods="GET")
     * @param $slug
     * @return Response
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function tag($slug)
    {
        return $this->showResults($this->prepareItems($this->onejavCrawler->getAllDetailItems('tag/' . $slug)), $slug);
    }

    /**
     * @Route("/onejav/actress/{slug}", methods="GET")
     * @param $slug
     * @return Response
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function actress($slug)
    {
        $r18      = new R18Crawler;
        $r18Items = $r18->getSearchDetail($slug);
        $items    = [];

        foreach ($r18Items as $item) {
            if (!$item->dvd_id) {
                continue;
            }

            if (!$item = $this->onejavCrawler->getDetailFromUrl('https://onejav.com/torrent/' . strtolower(str_replace('-', '', $item->dvd_id)))) {
                continue;
            }

            $items [] = $item;
        }

        $items = array_merge($items, $this->onejavCrawler->getAllDetailItems('actress/' . $slug));

        return $this->showResults($this->prepareItems($items), $slug);
    }

    /**
     * @Route("/onejav/search", methods="POST")
     * @param Request $request
     * @return RedirectResponse
     */
    public function search(Request $request)
    {
        if (!$this->isCsrfTokenValid('onejav-search', $request->get('token'))) {
            return $this->redirect('/onejav');
        }

        return $this->redirect('/onejav/search/' . $request->get('keyword'));
    }

    /**
     * @Route("/onejav/search/{slug}", methods="GET")
     * @param string $slug
     * @return Response
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function result($slug)
    {
        $items = $this->onejavCrawler->getAllDetailItems('search/' . urlencode($slug));

        if (empty($items)) {
            return $this->render('onejav/empty.html.twig', ['keyword' => $slug]);
        }

        return $this->showResults($this->prepareItems($items), $slug);
    }

    /**
     * @Route("/onejav/download/", methods="GET")
     * @param Request $request
     * @return RedirectResponse
     */
    public function downloadTorrent(Request $request)
    {
        $itemNumber     = $request->get('item');
        $downloadEntity = $this->getDoctrine()->getRepository(JavDownload::class)
            ->findOneBy(['item_number' => $itemNumber]);
        $downloadUrl    = 'https://onejav.com/' . ($request->get('url'));

        if ($downloadEntity) {
            $this->addFlash('warning', 'Already downloaded: ' . $downloadUrl);
            return $this->redirect('/onejav');
        }

        $saveTo = $this->getStorage('torrent') . '/' . basename($downloadUrl);

        if ($this->onejavCrawler->download($downloadUrl, $saveTo)) {
            $this->addFlash('success', 'Download torrent success: ' . $saveTo);
            $entity = new JavDownload;
            $entity->setItemNumber($itemNumber);
            $this->getDoctrine()->getManager()->persist($entity);
            $this->getDoctrine()->getManager()->flush();
        } else {
            $this->addFlash('warning', 'Can not download torrent file: ' . $downloadUrl);
        }

        $entity = $this->getDoctrine()->getManager()->getRepository(JavMyFavorite::class)
            ->findOneBy(['item_number' => $itemNumber]);

        if (!$entity) {
            $entity = new JavMyFavorite;
            $entity->setItemNumber($itemNumber);
            $this->getDoctrine()->getManager()->persist($entity);
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'Item added success: ' . $itemNumber);
        }

        return $this->redirect('/onejav');
    }

    /**
     * @Route("/onejav/addFavorite", methods="GET")
     * @param Request $request
     * @return RedirectResponse
     */
    public function addFavorite(Request $request)
    {
        $itemNumber = $request->get('itemNumber');
        $entity     = $this->getDoctrine()->getManager()->getRepository(JavMyFavorite::class)->findOneBy(['item_number' => $itemNumber]);

        if ($entity) {
            $this->addFlash('info', 'Item already exists');
            return $this->redirect('/onejav');
        }

        $entity = new JavMyFavorite;
        $entity->setItemNumber($itemNumber);
        $this->getDoctrine()->getManager()->persist($entity);
        $this->getDoctrine()->getManager()->flush();

        $this->addFlash('success', 'Item added success: ' . $itemNumber);
        return $this->redirect('/onejav');
    }

    /**
     * Prepare items
     * @param array $items
     * @return array|boolean
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    private function prepareItems($items)
    {
        if (!$items) {
            return false;
        }

        foreach ($items as $index => $item) {
            unset($items[$index]);

            $downloads                      = [];
            $downloads[(string)$item->size] = $item->torrent;

            if ($sameItems = $this->onejavCrawler->getAllDetailItems('search/' . urlencode($item->itemNumber))) {
                if (empty($sameItems)) {
                    continue;
                }
                foreach ($sameItems as $sameItem) {
                    $downloads[(string)$sameItem->size] = $sameItem->torrent;
                }
            }

            $item->downloads          = $downloads;
            $date                     = DateTime::createFromFormat('F j, Y', $item->date);
            $item->dateSlug           = $date ? $date->format('Y_m_d') : (new DateTime())->format('Y_m_d');
            $items[$item->itemNumber] = $item;
        }

        return $items;
    }

    /**
     * @param $items
     * @param $keyword
     * @return Response
     * @throws Exception
     */
    private function showResults($items, $keyword)
    {
        if (count($items) == 1) {
            return $this->render(
                'onejav/detail.html.twig',
                ['keyword' => $keyword, 'item' => reset($items)]
            );
        }

        return $this->render(
            'onejav/results.html.twig',
            ['keyword' => $keyword, 'items' => $items]
        );
    }
}
