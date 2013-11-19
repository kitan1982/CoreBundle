<?php

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Claroline\CoreBundle\Entity\Resource\MenuAction;

class Updater020115
{
    private $container;
    private $logger;

    public function __construct($container)
    {
        $this->container = $container;
    }


    public function postUpdate()
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $resourceTypes = $em->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findAll();

        foreach ($resourceTypes as $resourceType) {
            $decoder = $em->getRepository('ClarolineCoreBundle:Resource\MenuAction')->findOneBy(array('resourceType' => $resourceType, 'name' => 'open-tracking'));
            if (!$decoder) {
                $trackingMenuAction = new MenuAction();
                $trackingMenuAction->setValue(pow(2, 3));
                $trackingMenuAction->setName('open-tracking');
                $trackingMenuAction->setResourceType($resourceType);
                $trackingMenuAction->setAsync(true);
                $trackingMenuAction->setIsForm(false);
                $trackingMenuAction->setIsCustom(false);

                $em->persist($trackingMenuAction);
                $this->log("Adding 'open-tracking' menu for resource type '".$resourceType->getName()."'");
            } else {
                $this->log("The 'open-tracking' permissions for resource type '".$resourceType->getName()."' already exists");
            }
        }
        $em->flush();
        
    }

    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    private function log($message)
    {
        if ($log = $this->logger) {
            $log('    ' . $message);
        }
    }
}