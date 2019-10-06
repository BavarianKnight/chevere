<?php

declare(strict_types=1);

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevere\VarDump\Formatters;

use Chevere\Contracts\VarDump\FormatterContract;
use Chevere\VarDump\src\Template;
use Chevere\VarDump\src\Wrapper;

/**
 * Provide HTML VarDump representation.
 */
final class HtmlFormatter implements FormatterContract
{
  public function getIndent(int $indent): string
  {
    return str_repeat(Template::HTML_INLINE_PREFIX, $indent);
  }

  public function getEmphasis(string $string): string
  {
    return sprintf(Template::HTML_EMPHASIS, $string);
  }

  public function getEncodedChars(string $string): string
  {
    return htmlspecialchars($string);
  }

  public function wrap(string $key, string $dump): string
  {
    $wrapper = new Wrapper($key, $dump);

    return $wrapper->toString();
  }
}