<?php

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Chevere\Components\VarDump\Interfaces;

interface PalleteInterface
{
    /**
     * Color palette used in HTML.
     */
    const HTML = [
        VarDumpInterface::TYPE_STRING => '#e67e22', // orange
        VarDumpInterface::TYPE_FLOAT => '#f1c40f', // yellow
        VarDumpInterface::TYPE_INTEGER => '#f1c40f', // yellow
        VarDumpInterface::TYPE_BOOLEAN => '#9b59b6', // purple
        VarDumpInterface::TYPE_NULL => '#7f8c8d', // grey
        VarDumpInterface::TYPE_OBJECT => '#e74c3c', // red
        VarDumpInterface::TYPE_ARRAY => '#2ecc71', // green
        VarDumpInterface::_FILE => 'inherith',
        VarDumpInterface::_CLASS => '#3498db', // blue
        VarDumpInterface::_OPERATOR => '#7f8c8d', // grey
        VarDumpInterface::_FUNCTION => '#9b59b6', // purple
        VarDumpInterface::_PRIVACY => '#9b59b6', // purple
        VarDumpInterface::_VARIABLE => '#e67e22', // orange
        VarDumpInterface::_EMPHASIS => '#7f8c8d',
    ];

    /**
     * Color palette used in CLI.
     */
    const CONSOLE = [
        VarDumpInterface::TYPE_STRING => 'color_11',
        VarDumpInterface::TYPE_FLOAT => 'color_11',
        VarDumpInterface::TYPE_INTEGER => 'color_11',
        VarDumpInterface::TYPE_BOOLEAN => 'color_163', // purple
        VarDumpInterface::TYPE_NULL => 'color_245', // grey
        VarDumpInterface::TYPE_OBJECT => 'color_39',
        VarDumpInterface::TYPE_ARRAY => 'color_41', // green
        VarDumpInterface::_FILE => 'default',
        VarDumpInterface::_CLASS => 'color_147', // blue
        VarDumpInterface::_OPERATOR => 'color_245', // grey
        VarDumpInterface::_FUNCTION => 'color_39',
        VarDumpInterface::_PRIVACY => 'color_133',
        VarDumpInterface::_VARIABLE => 'color_208',
        VarDumpInterface::_EMPHASIS => ['color_245', 'italic']
    ];
}