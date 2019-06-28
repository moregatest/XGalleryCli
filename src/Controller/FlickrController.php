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

use App\Entity\FlickrContact;
use App\Service\OAuth\Flickr\FlickrClient;
use DateTime;
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

    public function __construct(FlickrClient $client)
    {
        $this->client = $client;
    }

    /**
     * @Route("/flickr")
     */
    public function index()
    {
        return $this->render('flickr/index.html.twig');
    }

    /**
     * @Route("/flickr/contact", methods="POST")
     * @param Request $request
     * @return RedirectResponse
     * @throws Exception
     */
    public function contact(Request $request)
    {
        $contact = $this->getContact($request->get('contact'));

        if (!$contact) {
            $this->addFlash('error', 'Can not get contact NSID ' . $request->get('contact'));

            return $this->redirect('/flickr');
        }

        $contactEntity = $this->getDoctrine()->getRepository(FlickrContact::class)
            ->find($contact->person->nsid);

        if ($contactEntity) {
            $this->addFlash('info', 'NSID ' . $contact->person->nsid . ' already exists');

            return $this->redirect('/flickr');
        }

        $now = new DateTime();

        // Contact not found
        if ($contactEntity === null) {
            $contactEntity = new FlickrContact;
            $contactEntity->setCreated($now);
            $contactEntity->setNsid($contact->person->nsid);
        }

        $contactEntity->setIconserver($contact->person->iconserver);
        $contactEntity->setIconfarm($contact->person->iconfarm);
        $contactEntity->setPathAlias($contact->person->path_alias);
        $contactEntity->setIgnored($contact->person->ignored);
        $contactEntity->setFriend($contact->person->friend);
        $contactEntity->setFamily($contact->person->family);
        $contactEntity->setUsername($contact->person->username->_content);
        $contactEntity->setRealname($contact->person->realname->_content ?? null);
        $contactEntity->setLocation($contact->person->location->_content ?? null);
        $contactEntity->setDescription($contact->person->description->_content ?? null);
        $contactEntity->setPhotos($contact->person->photos->count->_content);
        $contactEntity->setUpdated(new DateTime);

        $this->getDoctrine()->getManager()->persist($contactEntity);
        $this->getDoctrine()->getManager()->flush();

        $this->addFlash('success', 'Added NSID ' . $contact->person->nsid . ' success');

        return $this->redirect('/flickr');
    }

    protected function getContact($contact)
    {
        if (!$contact || empty($contact)) {
            return false;
        }

        if (filter_var($contact, FILTER_VALIDATE_URL)) {
            $user = $this->client->flickrUrlsLookupUser($contact);

            $contact = $user->user->id;
        }

        return $this->client->flickrPeopleGetInfo($contact);
    }

    /**
     * @Route("/flickr/photos", methods="POST")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function photos(Request $request)
    {
        $contact = $this->getContact($request->get('contact'));

        if (!$contact) {
            $this->addFlash('error', 'Can not get contact NSID ' . $request->get('contact'));

            return $this->redirect('/flickr');
        }

        $process = new Process(
            [
                'php',
                $this->getParameter('kernel.project_dir') . '/bin/console',
                'flickr:photos',
                '--nsid=' . $contact->person->nsid,
            ]
        );


        $process->run();

        return $this->render('flickr/index.html.twig', ['output' => $process->getOutput()]);
    }
}
