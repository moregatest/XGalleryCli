<?php

/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Controller;

use App\Service\OAuth\Flickr\FlickrClient;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class FlickrController
 * @package App\Controller
 */
class FlickrController extends AbstractController
{
    /**
     * @var FlickrClient
     */
    private $client;

    /**
     * FlickrController constructor.
     * @param FlickrClient $client
     */
    public function __construct(FlickrClient $client)
    {
        $this->client = $client;
    }

    /**
     * @Route("/flickr", methods="GET")
     * @return Response
     */
    public function index()
    {
        return $this->render('flickr/index.html.twig');
    }

    /**
     * @Route("/flickr/contact", methods="POST")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function contact(Request $request)
    {
        $submit  = $request->get('submit');
        $command = ['flickr:' . $submit];

        if ($submit === 'contact') {
            $command[] = '--nsid=' . $request->get('nsid');
        }

        $process = new Process(
            array_merge(['php', realpath(__DIR__ . '/../../bin/console')], $command),
            null,
            null,
            null,
            (float)600
        );
        $process->disableOutput();
        $process->run();

        $this->addFlash('info', 'Request succeed: ' . implode(' ', $command));

        return $this->redirect('/');
    }

    /**
     * @Route("/flickr/photos", methods="POST")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function photos(Request $request)
    {
        $submit  = $request->get('submit');
        $data    = $request->get('data');
        $command = [];

        switch ($submit) {
            case 'nsid':
                $command = ['flickr:photos', '--nsid=' . $data];
                break;
            case 'album':
                $command = ['flickr:photos', '--album=' . $data];
                break;
            case 'gallery':
                $command = ['flickr:photos', '--gallery=' . $data];
                break;
            case 'photo_ids':
                $command = ['flickr:photos', '--photo_ids=' . $data];
                break;
        }

        $process = new Process(
            array_merge(['php', realpath(__DIR__ . '/../../bin/console')], $command),
            null,
            null,
            null,
            (float)600
        );
        $process->disableOutput();
        $process->run();

        $this->addFlash('info', 'Request succeed: ' . implode(' ', $command));

        return $this->redirect('/');
    }

    /**
     * @Route("/flickr/photossize", methods="POST")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function photosSize(Request $request)
    {
        $submit  = $request->get('submit');
        $data    = $request->get('data');
        $command = [];

        switch ($submit) {
            case 'nsid':
                $command = ['flickr:photossize', '--nsid=' . $data];
                break;
            case 'album':
                $command = ['flickr:photossize', '--album=' . $data];
                break;
            case 'gallery':
                $command = ['flickr:photossize', '--gallery=' . $data];
                break;
            case 'photo_ids':
                $command = ['flickr:photossize', '--photo_ids=' . $data];
                break;
        }

        $process = new Process(
            array_merge(['php', realpath(__DIR__ . '/../../bin/console')], $command),
            null,
            null,
            null,
            (float)600
        );
        $process->disableOutput();
        $process->run();

        $this->addFlash('info', 'Request succeed: ' . implode(' ', $command));

        return $this->redirect('/');
    }

    /**
     * @Route("/flickr/download", methods="POST")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function download(Request $request)
    {
        $submit  = $request->get('submit');
        $data    = $request->get('data');
        $command = [];

        switch ($submit) {
            case 'nsid':
                $command = ['flickr:photosdownload', '--nsid=' . $data];
                break;
            case 'album':
                $command = ['flickr:photosdownload', '--album=' . $data];
                break;
            case 'gallery':
                $command = ['flickr:photosdownload', '--gallery=' . $data];
                break;
            case 'photo_ids':
                $command = ['flickr:photosdownload', '--photo_ids=' . $data];
                break;
        }

        $process = new Process(
            array_merge(['php', realpath(__DIR__ . '/../../bin/console')], $command),
            null,
            null,
            null,
            (float)600
        );
        $process->disableOutput();
        $process->run();

        $this->addFlash('info', 'Request succeed: ' . implode(' ', $command));

        return $this->redirect('/');
    }
}
