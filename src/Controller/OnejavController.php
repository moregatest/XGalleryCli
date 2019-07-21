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
use App\Entity\JavMyFavorite;
use App\Service\Crawler\OnejavCrawler;
use App\Service\Crawler\R18Crawler;
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
    /**
     * @var OnejavCrawler
     */
    private $crawler;

    /**
     * OnejavController constructor.
     * @param OnejavCrawler $crawler
     */
    public function __construct(OnejavCrawler $crawler)
    {
        $this->crawler = $crawler;
    }

    /**
     * Index view for Onejav
     * @Route("/onejav")
     */
    public function index()
    {
        $featured = $this->crawler->getFeatured();

        // Today
        $date  = new DateTime;
        $today = $this->crawler->getAllDetailItems('https://onejav.com/' . $date->format('Y/m/d'));
        $tags  = [];

        foreach ($featured as $index => $item) {
            $date                       = DateTime::createFromFormat('F j, Y', $item->date);
            $featured[$index]->dateSlug = $date->format('Y_m_d');
            $tags                       = array_merge($tags, $item->tags);
        }

        foreach ($today as $index => $item) {
            $date                    = DateTime::createFromFormat('F j, Y', $item->date);
            $today[$index]->dateSlug = $date->format('Y_m_d');
            $tags                    = array_merge($tags, $item->tags);
        }

        $tags = array_unique($tags);

        return $this->render(
            'onejav/index.html.twig',
            [
                'date' => $date->format('Y_m_d'), 'featured' => $featured, 'today' => $today, 'tags' => $tags
            ]
        );
    }

    /**
     * Detail page
     * @Route("/onejav/detail/{slug}")
     */
    public function detail($slug)
    {
        $crawler     = new R18Crawler;
        $searchLinks = $crawler->getSearchLinks($slug);

        if (!empty($searchLinks)) {
            foreach ($searchLinks as $searchLink) {
                if (!$searchLink) {
                    continue;
                }

                $detail = $crawler->getDetail($searchLink);
                break;
            }
        }

        $items     = $this->crawler->getAllDetailItems('https://onejav.com/search/' . urlencode($slug));
        $downloads = [];

        foreach ($items as $item) {
            $downloads[$item->size] = $item->torrent;
        }

        return $this->render('onejav/detail.html.twig', ['detail' => $detail ?? null, 'downloads' => $downloads]);
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

        return $this->showResults($this->crawler->getAllDetailItems('https://onejav.com/' . $slug), $slug);
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
        return $this->showResults($this->crawler->getAllDetailItems('https://onejav.com/tag/' . $slug), $slug);
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
        $r18             = new R18Crawler;
        $items['r18']    = $r18->getSearchDetail($slug);
        $items['onejav'] = $this->crawler->getAllDetailItems('https://onejav.com/actress/' . $slug);

        return $this->showResults($items, $slug);
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
        $items = $this->crawler->getAllDetailItems('https://onejav.com/search/' . urlencode($slug));

        if (empty($items)) {
            return $this->render('onejav/empty.html.twig', ['keyword' => $slug]);
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
        $saveTo      = getenv('storage_torrent') . '/' . basename($downloadUrl);

        if ($this->crawler->download($downloadUrl, $saveTo)) {
            $this->addFlash('success', 'Download torrent success: ' . $saveTo);
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
     * @param $items
     * @param $keyword
     * @return Response
     * @throws Exception
     */
    private function showResults($items, $keyword)
    {
        $onejavItems = [];

        foreach ($items['onejav'] as $item) {
            $date                             = DateTime::createFromFormat('F j, Y', $item->date ?? null);
            $item->dateSlug                   = $date ? $date->format('Y_m_d') : (new DateTime())->format('Y_m_d');
            $onejavItems[$item->itemNumber][] = $item;
        }

        $medias = [];

        foreach ($onejavItems as $index => $result) {
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
                'onejav' => $onejavItems,
                'r18' => $items['r18'],
                'medias' => $medias,
            ]
        );
    }
}

