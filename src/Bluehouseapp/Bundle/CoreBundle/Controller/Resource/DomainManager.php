<?php



namespace Bluehouseapp\Bundle\CoreBundle\Controller\Resource;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Domain manager.
 */
class DomainManager
{
    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var null|FlashHelper
     */
    private $flashHelper;

    /**
     * @var Configuration
     */
    private $config;

    public function __construct(
        ObjectManager $manager,
        EventDispatcherInterface $eventDispatcher,
        Configuration $config,
        FlashHelper $flashHelper = null
    ) {
        $this->manager = $manager;
        $this->eventDispatcher = $eventDispatcher;
        $this->config = $config;
        $this->flashHelper = $flashHelper;
    }

    /**
     * @param object $resource
     *
     * @return object|null
     */
    public function create($resource)
    {
        $event = $this->dispatchEvent('pre_create', new ResourceEvent($resource));

        if ($event->isStopped()) {
            if (null !== $this->flashHelper) {
                $this->flashHelper->setFlash(
                    $event->getMessageType(),
                    $event->getMessage(),
                    $event->getMessageParameters()
                );
            }

            return null;
        }

        $this->manager->persist($resource);
        $this->manager->flush();

        if (null !== $this->flashHelper) {
            $this->flashHelper->setFlash('success', 'create');
        }

        $this->dispatchEvent('post_create', new ResourceEvent($resource));

        return $resource;
    }

    /**
     * @param object $resource
     * @param string $flash
     *
     * @return object|null
     */
    public function update($resource, $flash = 'update')
    {
        $event = $this->dispatchEvent('pre_update', new ResourceEvent($resource));

        if ($event->isStopped()) {
            if (null !== $this->flashHelper) {
                $this->flashHelper->setFlash(
                    $event->getMessageType(),
                    $event->getMessage(),
                    $event->getMessageParameters()
                );
            }

            return null;
        }

        $this->manager->persist($resource);
        $this->manager->flush();

        if (null !== $this->flashHelper) {
            $this->flashHelper->setFlash('success', $flash);
        }

        $this->dispatchEvent('post_update', new ResourceEvent($resource));

        return $resource;
    }

    /**
     * @param object $resource
     * @param int    $movement
     *
     * @return null|object
     */
    public function move($resource, $movement)
    {
        $position = $this->config->getSortablePosition();

        $accessor = PropertyAccess::createPropertyAccessor();

        $accessor->setValue(
            $resource,
            $position,
            $accessor->getValue($resource, $position) + $movement
        );

        return $this->update($resource, 'move');
    }

    /**
     * @param object $resource
     *
     * @return object|null
     */
    public function delete($resource)
    {
        $event = $this->dispatchEvent('pre_delete', new ResourceEvent($resource));

        if ($event->isStopped()) {
            if (null !== $this->flashHelper) {
                $this->flashHelper->setFlash(
                    $event->getMessageType(),
                    $event->getMessage(),
                    $event->getMessageParameters()
                );
            }

            return null;
        }

        $accessor = PropertyAccess::createPropertyAccessor();
        $accessor->setValue($resource,'status',false);


      //  $this->manager->remove($resource);
        $this->manager->flush();

        if (null !== $this->flashHelper) {
            $this->flashHelper->setFlash('success', 'delete');
        }

        $this->dispatchEvent('post_delete', new ResourceEvent($resource));

        return $resource;
    }

    /**
     * @param string $name
     * @param Event  $event
     *
     * @return ResourceEvent
     */
    public function dispatchEvent($name, Event $event)
    {
        $name = $this->config->getEventName($name);

        return $this->eventDispatcher->dispatch($name, $event);
    }
}
