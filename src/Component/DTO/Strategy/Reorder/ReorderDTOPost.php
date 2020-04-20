<?php

declare(strict_types=1);


namespace App\Component\DTO\Strategy\Reorder;


use App\Component\DTO\Strategy\DTOStrategyConfig;
use App\Component\DTO\Strategy\DTOStrategyInterface;
use App\Component\DTO\Tree\PositionConvertor;
use App\Entity\Narrative;
use App\Entity\Position;

class ReorderDTOPost implements DTOStrategyInterface
{
    /**
     * @param DTOStrategyConfig $config
     * @throws \Exception
     */
    public function proceed(DTOStrategyConfig $config)
    {
        $em = $config->getEm();

        // we extract the position to update
        $narrative = $config->getData()['narrative'];
        $position = $em->getRepository(Position::class)->findOneByNarrative($narrative);
        $createdAt = $position->getCreatedAt();

        // we remove from the tree only the selected narrative (not its children), the narrative will be deleted but all of the tree info will be updated correctly
        $em->getRepository(Position::class)->removeFromTree($position); // removeFromTree is unsafe according to Gedmo doc
        $em->clear();

        // create the new position
        $newPosition = new Position();
        $newPosition->setCreatedAt($createdAt);
        // change the parent of the narrative, if it's null, it means there is no parent
        $parentPosition = PositionConvertor::getParentPositionFromNarrativeUuid($config->getDto()->getParentUuid(), $em);
        $newPosition->setParent($parentPosition);
        $narrativeUuid = $config->getDto()->getNarrativeUuid();
        $positionNarrative = $em->getRepository(Narrative::class)->findOneByUuid($narrativeUuid);
        $newPosition->setNarrative($positionNarrative);

        $em->persist($newPosition);
        $em->flush();

        // place the narrative
        $childrenCount = count($parentPosition->getChildren());
        $em->getRepository(Position::class)->moveUp($newPosition, $childrenCount-$config->getDto()->getPosition());
    }

}