<?php

/*
 * This file is part of the EzCoreExtraBundle package.
 *
 * (c) Jérôme Vieilledent <jerome@vieilledent.fr>
 * (c) eZ Systems AS
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lolautruche\EzCoreExtraBundle\Templating\Twig;

use Twig_Template;

/**
 * Meant to be used as a Twig base template class.
 *
 * Wraps the display method to:
 * - Inject debug info into template to be able to see in the markup which one is used
 *
 * Mainly copy/paste of eZ\Bundle\EzPublishDebugBundle\Twig\DebugTemplate, courtesy of eZ Systems AS.
 * Adds mapping between template name and path, to display actual used template source when using themes.
 */
class DebugTemplate extends Twig_Template
{
    /**
     * Map between template names and associated paths.
     *
     * @var array
     */
    protected static $templatePathMap = [];

    public static function addPathMapping($templateName, $path)
    {
        static::$templatePathMap[$templateName] = $path;
    }

    public static function getTemplatePath($templateName)
    {
        return isset(static::$templatePathMap[$templateName]) ? static::$templatePathMap[$templateName] : null;
    }

    public function display(array $context, array $blocks = array())
    {
        // Bufferize to be able to insert template name as HTML comments if applicable.
        // Layout template name will only appear at the end, to avoid potential quirks with old browsers
        // when comments appear before doctype declaration.
        ob_start();
        parent::display($context, $blocks);
        $templateResult = ob_get_clean();

        $templateName = $this->getTemplateName();
        if ($templatePath = static::getTemplatePath($templateName)) {
            $templateFullName = sprintf('%s (%s)', $templateName, $templatePath);
        } else {
            $templateFullName = $templateName;
        }
        // Check if template name ends with "html.twig", indicating this is an HTML template.
        $isHtmlTemplate = substr($templateName, -strlen('html.twig')) === 'html.twig';

        // Display start template comment, if applicable.
        if ($isHtmlTemplate) {
            if (stripos(trim($templateResult), '<!doctype') !== false) {
                $templateResult = preg_replace(
                    '#(<!doctype[^>]+>)#im',
                    "$1\n<!-- START " . $templateFullName . ' -->',
                    $templateResult
                );
            } else {
                echo "\n<!-- START $templateFullName -->\n";
            }
        }

        // Display stop template comment after result, if applicable.
        if ($isHtmlTemplate) {
            $bodyPos = stripos($templateResult, '</body>');
            if ($bodyPos !== false) {
                // Add layout template name before </body>, to avoid display quirks in some browsers.
                echo substr($templateResult, 0, $bodyPos)
                    . "\n<!-- STOP $templateFullName -->\n"
                    . substr($templateResult, $bodyPos);
            } else {
                echo $templateResult;
                echo "\n<!-- STOP $templateFullName -->\n";
            }
        } else {
            echo $templateResult;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplateName()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    protected function doDisplay(array $context, array $blocks = array())
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getDebugInfo()
    {
        return array();
    }
}
