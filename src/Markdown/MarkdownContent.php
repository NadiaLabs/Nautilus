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
 * Customized Markdown Parser for Nautilus
 */
class MarkdownContent
{
    /**
     * @var string
     */
    protected $body;
    /**
     * @var array
     */
    protected $outlines = array();
    /**
     * @var array
     */
    protected $documentConfig = array();

    /**
     * Markdown constructor.
     *
     * @param string $body           Transformed Markdown HTML
     * @param array  $outlines       Document outlines
     * @param array  $documentConfig Document configuration
     */
    public function __construct($body, array $outlines, array $documentConfig)
    {
        $this->body = $body;
        $this->outlines = $outlines;
        $this->documentConfig = $documentConfig;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return array Format: <code><pre>[
     *   ['level' => 1, 'html' => 'This is title #1-1'],
     *   ['level' => 2, 'html' => 'This is title #1-2'],
     *   ['level' => 3, 'html' => 'This is title #1-3'],
     *   ['level' => 1, 'html' => 'This is title #2-1'],
     *   ['level' => 2, 'html' => 'This is title #2-2'],
     * ]</pre></code>
     */
    public function getOutlines()
    {
        return $this->outlines;
    }

    /**
     * @return array
     */
    public function getDocumentConfig()
    {
        return $this->documentConfig;
    }
}
