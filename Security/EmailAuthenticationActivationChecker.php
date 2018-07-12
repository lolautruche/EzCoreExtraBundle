<?php

/*
 * This file is part of the EzCoreExtraBundle package.
 *
 * @copyright Jérôme Vieilledent <jerome@vieilledent.fr>
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Lolautruche\EzCoreExtraBundle\Security;

use eZ\Publish\Core\MVC\ConfigResolverInterface;

trait EmailAuthenticationActivationChecker
{
    /**
     * @var ConfigResolverInterface
     */
    private $configResolver;

    public function setConfigResolver(ConfigResolverInterface $configResolver)
    {
        $this->configResolver = $configResolver;
    }

    /**
     * @return bool
     */
    protected function isEmailAuthenticationEnabled()
    {
        return (bool)$this->configResolver->getParameter('security.authentication_email.enabled', 'ez_core_extra');
    }
}
