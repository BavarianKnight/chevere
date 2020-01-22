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

use DateTimeInterface;
use Chevere\Components\ExceptionHandler\Interfaces\DocumentInterface;
use Chevere\Components\ExceptionHandler\Interfaces\ExceptionHandlerInterface;
use Chevere\Components\ExceptionHandler\Interfaces\FormatterInterface;
use Chevere\Components\ExceptionHandler\Trace;

abstract class AbstractDocument implements DocumentInterface
{
    protected ExceptionHandlerInterface $exceptionHandler;

    protected FormatterInterface $formatter;

    protected array $sections = self::SECTIONS;

    protected array $template;

    private array $tags;

    final public function __construct(ExceptionHandlerInterface $exceptionHandler)
    {
        $this->exceptionHandler = $exceptionHandler;
        $this->formatter = $this->getFormatter();
        $this->template = $this->getTemplate();
    }

    final public function sections(): array
    {
        return $this->sections;
    }

    protected function prepare(string $value): string
    {
        return $value;
    }

    abstract public function getTemplate(): array;

    abstract public function getFormatter(): FormatterInterface;

    final public function toString(): string
    {
        $exeption = $this->exceptionHandler->exception();
        $dateTimeUtc = $this->exceptionHandler->dateTimeUtc();
        $this->tags = [
            static::TAG_TITLE => $exeption->className() . ' thrown',
            static::TAG_MESSAGE => $exeption->message(),
            static::TAG_CODE_WRAP => $this->getExceptionCode(),
            static::TAG_FILE_LINE => $exeption->file() . ':' . $exeption->line(),
            static::TAG_ID => $this->exceptionHandler->id(),
            static::TAG_DATE_TIME_UTC_ATOM => $dateTimeUtc->format(DateTimeInterface::ATOM),
            static::TAG_TIMESTAMP => $dateTimeUtc->getTimestamp(),
            static::TAG_LOG_DESTINATION => $this->exceptionHandler->logDestination(),
            static::TAG_STACK => $this->getStack(),
            static::TAG_PHP_UNAME => php_uname(),
        ];
        $this->handleRequestTags();
        $templated = [];
        foreach ($this->sections as $sectionName) {
            $templated[] = $this->template[$sectionName] ?? null;
        }

        return $this->prepare(
            strtr(
                implode($this->formatter->getLineBreak(), array_filter($templated)),
                $this->tags
            )
        );
    }

    private function getExceptionCode(): string
    {
        return
            $this->exceptionHandler->exception()->code() > 0
            ? '[Code #' . $this->exceptionHandler->exception()->code() . ']'
            : '';
    }

    private function getStack(): string
    {
        return
            (new Trace($this->exceptionHandler->exception()->trace(), $this->formatter))
                ->toString();
    }

    private function handleRequestTags(): void
    {
        if ($this->exceptionHandler->hasRequest()) {
            $request = $this->exceptionHandler->request();
            $this->tags = array_merge($this->tags, [
                static::TAG_CLIENT_IP => '*MISSING CLIENT IP*',
                static::TAG_CLIENT_USER_AGENT => $request->getHeaderLine('User-Agent'),
                static::TAG_SERVER_PROTOCOL => $request->protocolString(),
                static::TAG_REQUEST_METHOD => $request->getMethod(),
                static::TAG_URI => $request->getUri()->getPath(),
                static::TAG_SERVER_HOST => $request->getHeaderLine('Host'),
            ]);
        } else {
            $keyRequest = array_search(static::SECTION_REQUEST, $this->sections);
            $keyClient = array_search(static::SECTION_CLIENT, $this->sections);
            unset($this->sections[$keyRequest], $this->sections[$keyClient]);
        }
    }
}

//     private function processContentGlobals()
//     {
//         // $globals = $this->exceptionHandler->request()->globals()->globals();
//         $globals = $GLOBALS;
//         foreach (['_GET', '_POST', '_FILES', '_COOKIE', '_SESSION', '_SERVER'] as $global) {
//             $val = $globals[$global] ?? null;
//             if (!empty($val)) {
//                 $dumperVarDump = (new VarDump(new Dumpeable($val), new DumperFormatter()))->withProcess();
//                 $plainVarDump = (new VarDump(new Dumpeable($val), new PlainFormatter()))->withProcess();
//                 $wrapped = $dumperVarDump->toString();
//                 if (!BootstrapInstance::get()->isCli()) {
//                     $wrapped = '<pre>' . $wrapped . '</pre>';
//                 }
//                 $this->setRichContentSection($global, ['$' . $global, $this->wrapStringHr($wrapped)]);
//                 $this->setPlainContentSection($global, ['$' . $global, strip_tags($this->wrapStringHr($plainVarDump->toString()))]);
//             }
//         }
//     }
