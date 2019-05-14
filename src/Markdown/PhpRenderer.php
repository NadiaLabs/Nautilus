<?php
/*
 * This file is part of the Nautilus package.
 *
 * (c) Leo <leo.on.the.earth@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nautilus\Markdown;

/**
 * Render PHP template
 */
class PhpRenderer
{
    /**
     * Render PHP template
     *
     * @param string $template   Template file path
     * @param array  $parameters Template parameters
     *
     * @return string
     */
    public function render($template, array $parameters = array())
    {
        extract($parameters);

        ob_start();

        include $template;

        $content = ob_get_contents();

        ob_end_clean();

        return $content;
    }

    /**
     * Render PHP template with 'eval' PHP code
     *
     * @param string $code       The PHP script to run with 'eval'
     * @param array  $parameters Parameters for the PHP script
     *
     * @return false|string
     */
    public function renderWithEval($code, array $parameters = array())
    {
        extract($parameters);

        ob_start();

        eval($code);

        $content = ob_get_contents();

        ob_end_clean();

        return !empty($content) ? $content : '';
    }
}
