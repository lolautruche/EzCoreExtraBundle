<?php

namespace Lolart\EzPublishCoreExtraBundle\Controller;

use eZ\Bundle\EzPublishCoreBundle\Controller;
use Symfony\Component\HttpFoundation\Request;

class PictureController extends Controller {

    /**
     * Controller to handler image alias of an content id
     * @param $contentId
     * @param $fieldIdentifier
     * @param $alias
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function aliasAction( $contentId, $fieldIdentifier, $alias ) {
        $repository = $this->getRepository();
        $content = $repository->getContentService()->loadContent( $contentId );
        return $this->render(
            "EzPublishCoreExtraBundle:Picture:alias.html.twig",
            array( "picture" => $content, "fieldIdentifier" => $fieldIdentifier, "alias" => $alias )
        );
    }

}