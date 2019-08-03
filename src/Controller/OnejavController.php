<?php

/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Controller;

use App\Entity\JavMyFavorite;
use App\Service\Crawler\OnejavCrawler;
use App\Service\Crawler\R18Crawler;
use App\Traits\HasCache;
use App\Traits\HasStorage;
use DateInterval;
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
     */
    public function index()
    {
        // Today
        $date      = new DateTime;
        $today     = $date->format('Y/m/d');
        $yesterday = $date->add(DateInterval::createFromDateString('yesterday'))->format('Y/m/d');

        $id = md5($today);

        if ($this->isHit($id, $response)) {
            return $response;
        }

        // Merging tags
        $tags      = [];
        $actresses = [];

        $featuredItems = $this->getItems($this->onejavCrawler->getFeatured());

        foreach ($featuredItems as $index => $item) {
            $tags      = array_merge($tags, $item->tags);
            $actresses = array_merge($actresses, $item->actresses);
        }

        $daily[$today]     = $this->onejavCrawler->getAllDetailItems('https://onejav.com/' . $today);
        $daily[$yesterday] = $this->onejavCrawler->getAllDetailItems('https://onejav.com/' . $yesterday);

        foreach ($daily as $day => $items) {
            $items = $this->getItems($items);

            foreach ($items as $index => $item) {
                $tags      = array_merge($tags, $item->tags);
                $actresses = array_merge($actresses, $item->actresses);
            }
        }

        $response = $this->render(
            'onejav/index.html.twig',
            [
                'featured' => $featuredItems, 'daily' => $daily,
                'tags' => array_unique($tags), 'actresses' => array_unique($actresses)
            ]
        );

        $this->saveCache($id, $response);

        return $response;
    }

    /**
     * @param array $items
     * @return mixed
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    private function getItems($items)
    {
        foreach ($items as $index => $item) {
            unset($items[$index]);

            $downloads                      = [];
            $downloads[(string)$item->size] = $item->torrent;

            if ($sameItems = $this->onejavCrawler->getAllDetailItems('https://onejav.com/search/' . urlencode($item->itemNumber))) {
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
     * Detail page
     * @Route("/onejav/detail/{slug}")
     * @param $slug
     * @return Response
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function detail($slug)
    {
        $item = $this->onejavCrawler->getDetailFromUrl('https://onejav.com/torrent/' . strtolower(str_replace('-', '', $slug)));

        /**
         * @TODO Related items
         */

        return $this->showResults($this->getItems([$item]), $slug);
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

    /**
     * @Route("/onejav/daily/{slug}", methods="GET")
     * @param $slug
     * @return Response
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function daily($slug)
    {
        $slug  = str_replace('_', '/', $slug);
        $items = $this->onejavCrawler->getAllDetailItems('https://onejav.com/' . $slug);

        return $this->showResults($this->getItems($items), $slug);
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
        return $this->showResults($this->getItems($this->onejavCrawler->getAllDetailItems('https://onejav.com/tag/' . $slug)), $slug);
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

        $items = array_merge($items, $this->onejavCrawler->getAllDetailItems('https://onejav.com/actress/' . $slug));

        return $this->showResults($this->getItems($items), $slug);
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
        $items = $this->onejavCrawler->getAllDetailItems('https://onejav.com/search/' . urlencode($slug));

        if (empty($items)) {
            return $this->render('onejav/empty.html.twig', ['keyword' => $slug]);
        }

        foreach ($items as $index => $item) {
            unset($items[$index]);

            $date            = DateTime::createFromFormat('F j, Y', $item->date);
            $item->dateSlug  = $date->format('Y_m_d');
            $item->downloads = $this->getDownloads($item->itemNumber);

            if (empty($item->downloads)) {
                $item->downloads[(string)$item->size] = $item->torrent;
            }

            $items[$item->itemNumber] = $item;
        }

        return $this->showResults($items, $slug);
    }

    /**
     * @Route("/onejav/download/", methods="GET")
     * @param Request $request
     * @return RedirectResponse
     */
    public function downloadTorrent(Request $request)
    {
        $downloadUrl = 'https://onejav.com/' . ($request->get('url'));
        $saveTo      = $this->getStorage('torrent') . '/' . basename($downloadUrl);

        if ($this->onejavCrawler->download($downloadUrl, $saveTo)) {
            $this->addFlash('success', 'Download torrent success: ' . $saveTo);
        } else {
            $this->addFlash('warning', 'Can not download torrent file: ' . $downloadUrl);
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
}
