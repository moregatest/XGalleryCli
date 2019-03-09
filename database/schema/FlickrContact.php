<?php

/**
 * @Entity
 * @Table(name="xgallery_flickr_contacts")
 **/
class FlickrContact
{

    /**
     * @Id
     * @Column(type="string", length=125, unique=true,
     *                        options={"comment":"NSID"})
     */
    protected $id;

    /**
     * @Column(type="string", length=255)
     */
    protected $username;

    /**
     * @Column(type="integer")
     */
    protected $iconserver;

    /**
     * @Column(type="string", length=255)
     */
    protected $realname;

    /**
     * @Column(type="integer")
     */
    protected $friend;

    /**
     * @Column(type="integer")
     */
    protected $family;

    /**
     * @Column(type="integer")
     */
    protected $ignored;

    /**
     * @Column(type="integer")
     */
    protected $ref_ignored;

    /**
     * @Column(type="integer")
     */
    protected $totalPhotos;
}