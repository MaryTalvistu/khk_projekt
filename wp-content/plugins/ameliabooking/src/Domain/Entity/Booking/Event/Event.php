<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Domain\Entity\Booking\Event;

use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Entity\Bookable\AbstractBookable;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Entity\Location\Location;
use AmeliaBooking\Domain\ValueObjects\BooleanValueObject;
use AmeliaBooking\Domain\ValueObjects\DateTime\DateTimeValue;
use AmeliaBooking\Domain\ValueObjects\Number\Float\Price;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\Id;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\IntegerValue;
use AmeliaBooking\Domain\ValueObjects\Recurring;
use AmeliaBooking\Domain\ValueObjects\String\BookingStatus;
use AmeliaBooking\Domain\ValueObjects\String\BookingType;
use AmeliaBooking\Domain\ValueObjects\String\Name;

/**
 * Class Event
 *
 * @package AmeliaBooking\Domain\Entity\Booking\Event
 */
class Event extends AbstractBookable
{
    /** @var  Id */
    protected $parentId;

    /** @var  BookingStatus */
    protected $status;

    /** @var  Collection */
    protected $bookings;

    /** @var DateTimeValue */
    protected $bookingOpens;

    /** @var DateTimeValue */
    protected $bookingCloses;

    /** @var Recurring */
    private $recurring;

    /** @var IntegerValue */
    private $maxCapacity;

    /** @var BooleanValueObject */
    private $show;

    /** @var  Collection */
    protected $periods;

    /** @var Collection */
    private $tags;

    /** @var Collection */
    private $gallery;

    /** @var Collection */
    private $providers;

    /** @var bool */
    protected $notifyParticipants;

    /** @var Id */
    protected $locationId;

    /** @var Location */
    private $location;

    /** @var Name */
    protected $customLocation;

    /** @var DateTimeValue */
    protected $created;

    /** @var Name */
    private $zoomUserId;

    /** @var BooleanValueObject */
    private $bringingAnyone;

    /** @var BooleanValueObject */
    private $bookMultipleTimes;

    /**
     * Event constructor.
     *
     * @param Name             $name
     * @param Price            $price
     */
    public function __construct(
        Name $name,
        Price $price
    ) {
        parent::__construct($name, $price);
    }

    /**
     * @return Id
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * @param Id $parentId
     */
    public function setParentId(Id $parentId)
    {
        $this->parentId = $parentId;
    }

    /**
     * @return Recurring
     */
    public function getRecurring()
    {
        return $this->recurring;
    }

    /**
     * @param Recurring $recurring
     */
    public function setRecurring(Recurring $recurring)
    {
        $this->recurring = $recurring;
    }

    /**
     * @return void
     */
    public function unsetRecurring()
    {
        $this->recurring = null;
    }

    /**
     * @return IntegerValue
     */
    public function getMaxCapacity()
    {
        return $this->maxCapacity;
    }

    /**
     * @param IntegerValue $maxCapacity
     */
    public function setMaxCapacity(IntegerValue $maxCapacity)
    {
        $this->maxCapacity = $maxCapacity;
    }

    /**
     * @return BooleanValueObject
     */
    public function getShow()
    {
        return $this->show;
    }

    /**
     * @param BooleanValueObject $show
     */
    public function setShow(BooleanValueObject $show)
    {
        $this->show = $show;
    }

    /**
     * @return Collection
     */
    public function getBookings()
    {
        return $this->bookings;
    }

    /**
     * @param Collection $bookings
     */
    public function setBookings(Collection $bookings)
    {
        $this->bookings = $bookings;
    }

    /**
     * @return Collection
     */
    public function getPeriods()
    {
        return $this->periods;
    }

    /**
     * @param Collection $periods
     */
    public function setPeriods(Collection $periods)
    {
        $this->periods = $periods;
    }

    /**
     * @return DateTimeValue
     */
    public function getBookingOpens()
    {
        return $this->bookingOpens;
    }

    /**
     * @param DateTimeValue|null $bookingOpens
     */
    public function setBookingOpens(DateTimeValue $bookingOpens = null)
    {
        $this->bookingOpens = $bookingOpens;
    }

    /**
     * @return DateTimeValue
     */
    public function getBookingCloses()
    {
        return $this->bookingCloses;
    }

    /**
     * @param DateTimeValue|null $bookingCloses
     */
    public function setBookingCloses(DateTimeValue $bookingCloses = null)
    {
        $this->bookingCloses = $bookingCloses;
    }

    /**
     * @return BookingStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param BookingStatus $status
     */
    public function setStatus(BookingStatus $status)
    {
        $this->status = $status;
    }

    /**
     * @return Collection
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param Collection $tags
     */
    public function setTags(Collection $tags)
    {
        $this->tags = $tags;
    }

    /**
     * @return Collection
     */
    public function getGallery()
    {
        return $this->gallery;
    }

    /**
     * @param Collection $gallery
     */
    public function setGallery(Collection $gallery)
    {
        $this->gallery = $gallery;
    }

    /**
     * @return Collection
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * @param Collection $providers
     */
    public function setProviders(Collection $providers)
    {
        $this->providers = $providers;
    }

    /**
     * @return bool
     */
    public function isNotifyParticipants()
    {
        return $this->notifyParticipants;
    }

    /**
     * @param bool $notifyParticipants
     */
    public function setNotifyParticipants($notifyParticipants)
    {
        $this->notifyParticipants = $notifyParticipants;
    }

    /**
     * @return Id
     */
    public function getLocationId()
    {
        return $this->locationId;
    }

    /**
     * @param Id $locationId
     */
    public function setLocationId(Id $locationId)
    {
        $this->locationId = $locationId;
    }

    /**
     * @return Location
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param Location $location
     */
    public function setLocation(Location $location)
    {
        $this->location = $location;
    }

    /**
     * @return Name
     */
    public function getCustomLocation()
    {
        return $this->customLocation;
    }

    /**
     * @param Name $customLocation
     */
    public function setCustomLocation(Name $customLocation)
    {
        $this->customLocation = $customLocation;
    }

    /**
     * @return DateTimeValue
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param DateTimeValue $created
     */
    public function setCreated(DateTimeValue $created)
    {
        $this->created = $created;
    }

    /**
     * @return Name
     */
    public function getZoomUserId()
    {
        return $this->zoomUserId;
    }

    /**
     * @param Name $zoomUserId
     */
    public function setZoomUserId(Name $zoomUserId)
    {
        $this->zoomUserId = $zoomUserId;
    }

    /**
     * @return BookingType
     */
    public function getType()
    {
        return new Bookingtype(Entities::EVENT);
    }

    /**
     * @return BooleanValueObject
     */
    public function getBringingAnyone()
    {
        return $this->bringingAnyone;
    }

    /**
     * @param BooleanValueObject $bringingAnyone
     */
    public function setBringingAnyone(BooleanValueObject $bringingAnyone)
    {
        $this->bringingAnyone = $bringingAnyone;
    }

    /**
     * @return BooleanValueObject
     */
    public function getBookMultipleTimes()
    {
        return $this->bookMultipleTimes;
    }

    /**
     * @param BooleanValueObject $bookMultipleTimes
     */
    public function setBookMultipleTimes(BooleanValueObject $bookMultipleTimes)
    {
        $this->bookMultipleTimes = $bookMultipleTimes;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array_merge(
            parent::toArray(),
            [
                'bookings'           => $this->getBookings() ? $this->getBookings()->toArray() : [],
                'periods'            => $this->getPeriods()->toArray(),
                'bookingOpens'       => $this->getBookingOpens() ?
                    $this->getBookingOpens()->getValue()->format('Y-m-d H:i:s') : null,
                'bookingCloses'      => $this->getBookingCloses() ?
                    $this->getBookingCloses()->getValue()->format('Y-m-d H:i:s') : null,
                'status'             => $this->getStatus() ? $this->getStatus()->getValue() : null,
                'recurring'          => $this->getRecurring() ? $this->getRecurring()->toArray() : null,
                'maxCapacity'        => $this->getMaxCapacity() ? $this->getMaxCapacity()->getValue() : null,
                'show'               => $this->getShow() ? $this->getShow()->getValue() : null,
                'tags'               => $this->getTags() ? $this->getTags()->toArray() : null,
                'gallery'            => $this->getGallery() ? $this->getGallery()->toArray() : [],
                'providers'          => $this->getProviders() ? $this->getProviders()->toArray() : [],
                'notifyParticipants' => $this->isNotifyParticipants(),
                'locationId'         => $this->getLocationId() ? $this->getLocationId()->getValue() : null,
                'location'           => $this->getLocation() ? $this->getLocation()->toArray() : null,
                'customLocation'     => $this->getCustomLocation() ? $this->getCustomLocation()->getValue() : null,
                'parentId'           => $this->getParentId() ? $this->getParentId()->getValue() : null,
                'created'            => $this->getCreated() ? $this->getCreated()->getValue()->format('Y-m-d H:i:s') : null,
                'zoomUserId'         => $this->getZoomUserId() ? $this->getZoomUserId()->getValue() : null,
                'type'               => $this->getType()->getValue(),
                'bringingAnyone'     => $this->getBringingAnyone() ? $this->getBringingAnyone()->getValue() : null,
                'bookMultipleTimes'  => $this->getBookMultipleTimes() ? $this->getBookMultipleTimes()->getValue() : null,
            ]
        );
    }
}
