<?php

namespace Lolart\EzPublishCoreExtraBundle\Twig;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\Core\MVC\Symfony\Templating\Twig\Extension\ContentExtension as KernelContentExtension;

use Twig_Extension;
use Twig_Function_Method;

class ContentExtension extends KernelContentExtension {


    public function getFunctions() {
        return array(
            'ez_content_by_contentinfo' => new \Twig_Function_Method( $this, 'contentByContentInfo' ),
            'ez_contenttype_by_content' => new \Twig_Function_Method( $this, 'contentTypeByContent' )
        );

    }

    /**
     * @param ContentInfo $contentInfo
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function contentByContentInfo( ContentInfo $contentInfo ) {
        $repository = $this->container->get( 'ezpublish.api.repository' );
        return $repository->getContentService()->loadContentByContentInfo( $contentInfo );
    }

    /**
     * @param Content $content
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    public function contentTypeByContent( Content $content ) {
        $repository = $this->container->get( 'ezpublish.api.repository' );
        return $repository->getContentTypeService()->loadContentType( $content->contentInfo->contentTypeId );
    }

    public function getName() {
        return 'ezpublishcoreextra_content_extension';
    }
}