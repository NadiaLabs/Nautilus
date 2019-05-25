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

use Highlight\Highlighter;
use Michelf\MarkdownExtra;

/**
 * Customized Markdown Parser for Nautilus
 */
class Markdown extends MarkdownExtra
{
    const ENABLE_PHP_EVAL = 'enablePhpEval';
    const WORKING_DIR = 'workingDir';
    const TABLE_CLASSES = 'tableClasses';
    const THEAD_CLASSES = 'theadClasses';

    public $table_align_class_tmpl = 'text-%%';

    protected $id;
    /**
     * @var array
     */
    protected $parameters = array();
    /**
     * @var array
     */
    protected $outlines = array();
    /**
     * @var array
     */
    protected $idList = array();
    /**
     * @var PhpRenderer
     */
    protected $phpRenderer;

    /**
     * @var array
     */
    protected $options = array(
        self::ENABLE_PHP_EVAL => false,
        self::WORKING_DIR => '',
        self::TABLE_CLASSES => 'table table-bordered',
        self::THEAD_CLASSES => 'table-active',
    );

    /**
     * Markdown constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        parent::__construct();

        foreach ($this->options as $key => $value) {
            if (array_key_exists($key, $options)) {
                $this->options[$key] = $options[$key];
            }
        }

        $this->code_block_content_func = array($this, 'parseCodeBlockContent');
        $this->phpRenderer = new PhpRenderer();
    }

    /**
     * Transform with parameters
     *
     * @param string $id            The Document ID
     * @param string $text          Markdown content
     * @param array  $chapterConfig Chapter configuration
     * @param array  $parameters    Document parameters
     *
     * @return string
     */
    public function transform2($id, $text, array $chapterConfig, array $parameters = array())
    {
        $this->id = $id;
        $this->parameters = $parameters;

        $body = $this->transform($text);

        foreach ($this->outlines as &$outline) {
            $outline['html'] = $this->transform($outline['html']);
        }

        $return = new MarkdownContent($body, $this->outlines, $chapterConfig);

        $this->id = null;
        $this->parameters = array();
        $this->outlines = array();
        $this->idList = array();

        return $return;
    }

    /**
     * @param string $code
     * @param string $language
     *
     * @return string
     *
     * @throws \Exception
     */
    public function parseCodeBlockContent($code, $language)
    {
        switch ($language) {
            case 'eval-php':
                if ($this->options[self::ENABLE_PHP_EVAL]) {
                    $code = $this->evalPhpCode($code);
                }
                break;
            default:
                if (!empty($language)) {
                    $hl = new Highlighter();

                    $highlighted = $hl->highlight($language, $code)->value;
                } else {
                    $highlighted = $code;
                }

                $code = '<pre><code class="hljs '.$language.'">';
                $code .= $highlighted;
                $code .= '</code></pre>';
        }

        return $this->hashBlock($code);
    }

    /**
     * {@inheritdoc}
     */
    protected function _doFencedCodeBlocks_callback($matches)
    {
        $className =& $matches[2];
        $attrs     =& $matches[3];
        $codeBlock = $matches[4];
        $classes = array('code-block');

        if ($this->code_block_content_func) {
            $codeBlock = call_user_func($this->code_block_content_func, $codeBlock, $className, $attrs);
        } else {
            $codeBlock = htmlspecialchars($codeBlock, ENT_NOQUOTES);
        }

        $codeBlock = preg_replace_callback('/^\n+/', array($this, '_doFencedCodeBlocks_newlines'), $codeBlock);

        if (!in_array($className, array('eval-php'))) {
            if ($className != "") {
                if ($className{0} == '.') {
                    $className = substr($className, 1);
                }

                $classes[] = $this->code_class_prefix . $className;
            }

            $attributes = $this->doExtraAttributes('div', $attrs, null, $classes);
            $codeBlock  = "<div$attributes>$codeBlock</div>";
        }

        return "\n\n".$this->hashBlock($codeBlock)."\n\n";
    }

    /**
     * {@inheritDoc}
     */
    protected function parseSpan($str)
    {
        $output = '';

        $span_re = '{
				(
					\\\\'.$this->escape_chars_re.'
				|
					(?<![`\\\\])
					`+						# code span marker
				|
					(?<!@\\\\])
					@+						# eval php script
				|
				    \{c:[^\}]+\}            # colorize span text
			'.( $this->no_markup ? '' : '
				|
					<!--    .*?     -->		# comment
				|
					<\?.*?\?> | <%.*?%>		# processing instruction
				|
					<[!$]?[-a-zA-Z0-9:_]+	# regular tags
					(?>
						\s
						(?>[^"\'>]+|"[^"]*"|\'[^\']*\')*
					)?
					>
				|
					<[-a-zA-Z0-9:_]+\s*/> # xml-style empty tag
				|
					</[-a-zA-Z0-9:_]+\s*> # closing tag
			').'
				)
				}xs';

        while (1) {
            // Each loop iteration seach for either the next tag, the next
            // openning code span marker, or the next escaped character.
            // Each token is then passed to handleSpanToken.
            $parts = preg_split($span_re, $str, 2, PREG_SPLIT_DELIM_CAPTURE);

            // Create token from text preceding tag.
            if ($parts[0] != "") {
                $output .= $parts[0];
            }

            // Check if we reach the end.
            if (isset($parts[1])) {
                $output .= $this->handleSpanToken($parts[1], $parts[2]);
                $str = $parts[2];
            } else {
                break;
            }
        }

        return $output;
    }

    /**
     * {@inheritDoc}
     */
    protected function handleSpanToken($token, &$str)
    {
        switch ($token{0}) {
            case '@':
                if (preg_match('/^(.*?[^@])'.preg_quote($token).'(?!@)(.*)$/sm', $str, $matches)) {
                    $str = $matches[2];

                    if ($this->options[self::ENABLE_PHP_EVAL]) {
                        $code = trim($matches[1]);
                        $content = $this->evalPhpCode($code);

                        return $this->hashPart($content);
                    }
                }
        }

        if (preg_match('/\{c:([^\}]+)\}/', $token, $colorMatches)) {
            $color = $colorMatches[1];

            if (preg_match('/(.*?)\{\/c\}(.*)/', $str, $colorTextMatches)) {
                $colorText = $colorTextMatches[1];
                $str = $colorTextMatches[2];

                return $this->hashPart('<span style="color:'.$color.';">'.$colorText.'</span>');
            }
        }

        return parent::handleSpanToken($token, $str);
    }

    /**
     * {@inheritDoc}
     */
    protected function _doHeaders_callback_atx($matches)
    {
        $level = strlen($matches[1]);
        $text = $matches[2];
        $idDuplicatedCount = 0;

        // Avoid duplicated id
        do {
            $text = $this->id.$text.str_repeat('-', $idDuplicatedCount++);
            $defaultId = 'header-'.md5($text);
        } while (isset($this->idList[$defaultId]));

        $this->idList[$defaultId] = 1;

        $attr = $this->doExtraAttributes("h$level", $dummy =& $matches[3], $defaultId);
        $id = (string) $defaultId;

        if (preg_match('/id="(.*?)"/', $attr, $attrMatches)) {
            if (!empty($attrMatches[1])) {
                $id = $attrMatches[1];
            }
        }

        $headerHtml = $this->runSpanGamut($matches[2]);
        $block = "<h$level$attr>".$headerHtml."</h$level>";


        // Add outline
        $this->outlines[] = array('id' => $id, 'level' => $level, 'html' => $matches[2]);

        return "\n" . $this->hashBlock($block) . "\n\n";
    }

    /**
     * {@inheritDoc}
     */
    protected function _doImages_inline_callback($matches)
    {
        $whole_match	= $matches[1];
        $alt_text		= $matches[2];
        $url			= $matches[3] == '' ? $matches[4] : $matches[3];
        $title			=& $matches[7];
        $attr  = $this->doExtraAttributes("img", $dummy =& $matches[8]);

        $alt_text = $this->encodeAttribute($alt_text);
        // Encode image to base64 encoded string
        $url = $this->encodeImageToBase64($url);
        $url = $this->encodeURLAttribute($url);
        $result = "<img src=\"$url\" alt=\"$alt_text\"";
        if (isset($title)) {
            $title = $this->encodeAttribute($title);
            $result .=  " title=\"$title\""; // $title already quoted
        }
        $result .= $attr;
        $result .= $this->empty_element_suffix;

        return $this->hashPart($result);
    }

    /**
     * {@inheritdoc}
     */
    protected function _doTable_callback($matches)
    {
        $key = parent::_doTable_callback($matches);
        $key = substr($key, 0, -1);
        $text = $this->html_hashes[$key];

        unset($this->html_hashes[$key]);

        $text = str_replace('<table>', '<table class="'.$this->options[self::TABLE_CLASSES].'">', $text);
        $text = str_replace('<thead>', '<thead class="'.$this->options[self::THEAD_CLASSES].'">', $text);

        return $this->hashBlock($text) . "\n";
    }

    /**
     * @param string $url
     *
     * @return string
     */
    protected function encodeImageToBase64($url)
    {
        if (preg_match('/^http/', $url)) {
            return $url;
        }

        $filePath = $this->options[self::WORKING_DIR].'/'.trim($url, './ ');

        if (!file_exists($filePath)) {
            return $url;
        }

        $content = file_get_contents($filePath);
        $type = mime_content_type($filePath);
        $base64Url = 'data:image/' . $type . ';base64,' . base64_encode($content);

        return $base64Url;
    }

    /**
     * @param string $code
     *
     * @return string
     */
    protected function evalPhpCode($code)
    {
        return $this->phpRenderer->renderWithEval($code, $this->parameters);
    }
}
