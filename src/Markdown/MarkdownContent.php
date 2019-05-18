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
    protected $chapterConfig = array();

    /**
     * Markdown constructor.
     *
     * @param string $body           Transformed Markdown HTML
     * @param array  $outlines       Chapter outlines
     * @param array  $chapterConfig  Chapter configuration
     */
    public function __construct($body, array $outlines, array $chapterConfig)
    {
        $this->body = $body;
        $this->outlines = $outlines;
        $this->chapterConfig = $chapterConfig;
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
     * @return array Format: <code><pre>[
     *   'title' => 'Chapter 1: Introduction',
     *   'filePath' => 'chapters/chapter01.md',
     *   'parameters' => []
     * ]</pre></code>
     */
    public function getChapterConfig()
    {
        return $this->chapterConfig;
    }

    /**
     * @return string
     */
    public function getChapterHeaderId()
    {
        return 'header-'.md5($this->chapterConfig['title']);
    }

    /**
     * @return string
     */
    public function getChapterTitle()
    {
        return $this->chapterConfig['title'];
    }
}
