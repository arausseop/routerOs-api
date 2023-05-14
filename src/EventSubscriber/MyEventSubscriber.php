<?php

namespace App\EventSubscriber;

use App\Document\UnitDetail\UnitDetail;
use App\Entity\Main\Unit;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\Event\LifecycleEventArgs;

class MyEventSubscriber
{
    public function __construct(private DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function postLoad(LifecycleEventArgs $eventArgs): void
    {
        $unit = $eventArgs->getObject();

        if (!$unit instanceof Unit) {
            return;
        }


        $em = $eventArgs->getObjectManager();
        $productReflProp = $em->getClassMetadata(Unit::class)
            ->reflClass->getProperty('unitDetail');
        $productReflProp->setAccessible(true);
        $productReflProp->setValue(
            $unit,
            $this->dm->getReference(UnitDetail::class, $unit->getUnitDetailId())
        );
    }
}
