<?php

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevere.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Chevere\Components\ExceptionHandler\Documents;

use Chevere\Components\ExceptionHandler\Formatters\HtmlFormatter;
use Chevere\Components\ExceptionHandler\Interfaces\FormatterInterface;

final class HtmlDocument extends AbstractDocument
{
    /** @var string Title used when debug=0 */
    const NO_DEBUG_TITLE_PLAIN = 'Something went wrong';

    /** @var string HTML content used when debug=0 */
    const NO_DEBUG_CONTENT_HTML = '<p>The system has failed and the server wasn\'t able to fulfil your request. This incident has been logged.</p><p>Please try again later and if the problem persist don\'t hesitate to contact your system administrator.</p>';

    /** @var string HTML document template */
    const HTML_TEMPLATE = '<html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><style>%css%</style></head><body class="%bodyClass%">%body%</body></html>';

    /** @var string HTML body used when debug=0 */
    const BODY_DEBUG_0_HTML = '<main><div>%content%</div></main>';

    /** @var string HTML body used when debug=1 */
    const BODY_DEBUG_1_HTML = '<main class="main--stack"><div>%content%</div></main>';

    /**
     * {@inheritdoc}
     */
    public function getFormatter(): FormatterInterface
    {
        return new HtmlFormatter;
    }

    public function getTemplate(): array
    {
        if ($this->exceptionHandler->isDebug()) {
            return [
                static::SECTION_TITLE => $this->wrapTitle(static::TAG_TITLE . ' <span>in&nbsp;' . static::TAG_FILE_LINE . '</span>'),
                static::SECTION_MESSAGE => $this->wrapSectionTitle('# Message ' . static::TAG_CODE_WRAP) . "\n" . $this->wrapContent(static::TAG_MESSAGE),
                static::SECTION_TIME => $this->wrapSectionTitle('# Time') . "\n" . $this->wrapContent(static::TAG_DATE_TIME_UTC_ATOM . ' [' . static::TAG_TIMESTAMP . ']'),
                static::SECTION_ID => $this->wrapSectionTitle('# Incident ID:' . static::TAG_ID) . "\n" . $this->wrapContent('Logged at ' . static::TAG_LOG_FILENAME),
                static::SECTION_STACK => $this->wrapSectionTitle('# Stack trace') . "\n" . $this->wrapContent(static::TAG_STACK),
                static::SECTION_CLIENT => $this->wrapSectionTitle('# Client') . "\n" . $this->wrapContent(static::TAG_CLIENT_IP . ' ' . static::TAG_CLIENT_USER_AGENT),
                static::SECTION_REQUEST => $this->wrapSectionTitle('# Request') . "\n" . $this->wrapContent(static::TAG_SERVER_PROTOCOL . ' ' . static::TAG_REQUEST_METHOD . ' ' . static::TAG_URI),
                static::SECTION_SERVER => $this->wrapSectionTitle('# Server') . "\n" . $this->wrapContent(static::TAG_PHP_UNAME . ' ' . static::TAG_SERVER_SOFTWARE),
            ];
        } else {
            return [
                static::SECTION_TITLE => $this->wrapTitle(static::NO_DEBUG_TITLE_PLAIN) . static::NO_DEBUG_CONTENT_HTML . '<p class="fine-print">%dateTimeUtcAtom% • %id%</p>',
            ];
        }
    }

    protected function getLineBreak(): string
    {
        return "\n<br>\n";
    }

    protected function prepare(string $value): string
    {
        $preDocument = strtr(static::HTML_TEMPLATE, [
            '%bodyClass%' => !headers_sent() ? 'body--flex' : 'body--block',
            '%css%' => file_get_contents(dirname(__DIR__) . '/src/template.css'),
            '%body%' => $this->exceptionHandler->isDebug() ? static::BODY_DEBUG_1_HTML : static::BODY_DEBUG_0_HTML,
        ]);

        return str_replace('%content%', $value, $preDocument);
    }

    private function wrapTitle(string $value): string
    {
        return '<div class="title title--scream">' . $value . '</div>';
    }

    private function wrapSectionTitle(string $value): string
    {
        $value = str_replace('# ', $this->wrapHidden('#&nbsp;'), $value);

        return '<div class="title">' . $value . '</div>';
    }

    private function wrapHidden(string $value): string
    {
        return '<span class="hide">' . $value . '</span>';
    }

    private function wrapContent(string $value): string
    {
        return '<div class="content">' . $value . '</div>';
    }
}