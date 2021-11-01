<?php

declare (strict_types=1);
namespace Staatic\Vendor\GuzzleHttp\Psr7;

final class Rfc7230
{
    const HEADER_REGEX = "(^([^()<>@,;:\\\"/[\\]?={}\1- ]++):[ \t]*+((?:[ \t]*+[!-~�-�]++)*+)[ \t]*+\r?\n)m";
    const HEADER_FOLD_REGEX = "(\r?\n[ \t]++)";
}
