<?php

/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Controller;

use App\Entity\JavMedia;
use App\Service\Crawler\R18Crawler;
use App\Utils\Filesystem;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;
use SplFileInfo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class MediaController
 * @package App\Controller
 */
class MediaController extends AbstractController
{
    /**
     * @Route("/media")
     * @param Request $request
     * @return Response
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function index(Request $request)
    {
        $limit       = $request->get('limit', 10);
        $currentPage = $request->get('page', 1);

        $pagination = $this->getDoctrine()
            ->getRepository(JavMedia::class)
            ->getItems($currentPage, $limit);

        $maxPages = ceil($pagination->count() / $limit);
        $items    = $pagination->getIterator();

        $crawler = new R18Crawler;

        foreach ($items as $index => $item) {
            $itemName    = pathinfo($item->getFilename(), PATHINFO_FILENAME);
            $searchLinks = $crawler->getSearchLinks($itemName);

            if (empty($searchLinks)) {
                continue;
            }

            foreach ($searchLinks as $searchLink) {
                if (!$searchLink) {
                    continue;
                }

                $items[$index]->detail = $crawler->getDetail($searchLink);
                break;
            }
        }

        return $this->render(
            'media/index.html.twig',
            [
                'totalPages' => $maxPages,
                'currentPage' => $currentPage,
                'limit' => $limit,
                'items' => $pagination->getIterator(),
                'pages' => $maxPages > 10 ? 10 : $maxPages,
            ]
        );
    }

    /**
     * @Route("/media/rename")
     * @param Request $request
     * @return RedirectResponse
     */
    public function rename(Request $request)
    {
        if ($_SERVER['REMOTE_ADDR'] !== '127.0.0.1') {
            exit;
        }

        $mediaEntity = $this->getDoctrine()->getRepository(JavMedia::class)->find($request->get('id'));

        if (!$mediaEntity) {
            $this->addFlash('notice', 'Media entity not found');

            return $this->redirect('/media?page=' . $request->get('page'));
        }

        // Rename file
        $originalFile = new SplFileInfo(
            $mediaEntity->getDirectory() . DIRECTORY_SEPARATOR . $mediaEntity->getFilename()
        );
        // Rename file name
        $newFileName = $request->get('use_suggest')
            ? $request->get('suggestFileName')
            : $request->get('filename');
        $newFileName .= '.' . $originalFile->getExtension();

        if (Filesystem::exists($mediaEntity->getDirectory() . DIRECTORY_SEPARATOR . $newFileName)) {
            $this->addFlash('notice', 'File already exist ' . $originalFile->getFilename() . ' to ' . $newFileName);

            return $this->redirect('/media?page=' . $request->get('page'));
        }

        var_dump($mediaEntity->getDirectory() . DIRECTORY_SEPARATOR . $mediaEntity->getFilename());
        var_dump($mediaEntity->getDirectory() . DIRECTORY_SEPARATOR . $newFileName);
        exit;
        Filesystem::rename(
            $mediaEntity->getDirectory() . DIRECTORY_SEPARATOR . $mediaEntity->getFilename(),
            $mediaEntity->getDirectory() . DIRECTORY_SEPARATOR . $newFileName
        );

        $mediaEntity->setFilename($newFileName);

        $this->getDoctrine()->getManager()->persist($mediaEntity);
        $this->getDoctrine()->getManager()->flush();

        $this->addFlash('success', 'Update success: ' . $originalFile->getFilename() . ' to ' . $newFileName);

        return $this->redirect('/media?page=' . $request->get('page'));
    }

    public function feature()
    {
    }
}
