<?php

namespace App\Support;

/**
 * Tiny self-contained QR code -> inline SVG encoder.
 *
 * No Composer package, no external image request: it implements just enough of
 * the QR spec (byte mode, error-correction level M, automatic version sizing up
 * to version 10) to encode the short capture URLs we print on window stickers.
 * The output is an inline <svg> string that can be dropped straight into a Blade
 * view, so it renders identically everywhere and works offline / in print.
 *
 * This is intentionally compact rather than a general-purpose library; if a URL
 * is ever too long to fit version 10 it throws, which never happens for our
 * /j/{slug} links.
 */
class QrSvg
{
    /** Galois field log/antilog tables for Reed-Solomon. */
    private static array $expTable = [];

    private static array $logTable = [];

    /**
     * Return an inline SVG QR for the given text.
     *
     * @param  int  $size  rendered pixel size of the square SVG
     */
    public static function make(string $text, int $size = 220, string $dark = '#0a0a0a', string $light = '#ffffff'): string
    {
        self::initTables();

        [$version, $ecCodewords] = self::pickVersion($text);
        $modulesCount = 17 + $version * 4;

        $bits = self::buildBitStream($text, $version, $ecCodewords);
        $matrix = self::buildMatrix($version, $modulesCount, $bits);

        return self::renderSvg($matrix, $modulesCount, $size, $dark, $light);
    }

    // ── Galois field setup ───────────────────────────────────────────────────

    private static function initTables(): void
    {
        if (self::$expTable !== []) {
            return;
        }

        $exp = array_fill(0, 256, 0);
        $log = array_fill(0, 256, 0);
        $x = 1;
        for ($i = 0; $i < 255; $i++) {
            $exp[$i] = $x;
            $log[$x] = $i;
            $x <<= 1;
            if ($x & 0x100) {
                $x ^= 0x11D;
            }
        }
        for ($i = 255; $i < 256; $i++) {
            $exp[$i] = $exp[$i - 255];
        }
        self::$expTable = $exp;
        self::$logTable = $log;
    }

    private static function gfMul(int $a, int $b): int
    {
        if ($a === 0 || $b === 0) {
            return 0;
        }

        return self::$expTable[(self::$logTable[$a] + self::$logTable[$b]) % 255];
    }

    // ── Version / capacity tables (EC level M, byte mode) ─────────────────────

    /**
     * [version => [total data codewords, ec codewords per block, block layout]]
     * Block layout: [[count, dataCodewords], ...] for level M.
     * Covers versions 1-10, which is plenty for our short URLs.
     */
    private const SPEC_M = [
        1 => [16, 10, [[1, 16]]],
        2 => [28, 16, [[1, 28]]],
        3 => [44, 26, [[1, 44]]],
        4 => [64, 18, [[2, 32]]],
        5 => [86, 24, [[2, 43]]],
        6 => [108, 16, [[4, 27]]],
        7 => [124, 18, [[4, 31]]],
        8 => [154, 22, [[2, 38], [2, 39]]],
        9 => [182, 22, [[3, 36], [2, 37]]],
        10 => [216, 26, [[4, 43], [1, 44]]],
    ];

    /** Alignment-pattern centre coordinates per version. */
    private const ALIGN = [
        1 => [], 2 => [6, 18], 3 => [6, 22], 4 => [6, 26], 5 => [6, 30],
        6 => [6, 34], 7 => [6, 22, 38], 8 => [6, 24, 42], 9 => [6, 26, 46],
        10 => [6, 28, 50],
    ];

    private static function pickVersion(string $text): array
    {
        $len = strlen($text);
        foreach (self::SPEC_M as $version => [$totalData, $ec, $blocks]) {
            // data capacity in bits, minus mode (4) + length (8 or 16) + terminator
            $charCountBits = $version < 10 ? 8 : 16;
            $headerBits = 4 + $charCountBits;
            $capacityBits = $totalData * 8 - $headerBits;
            if ($len * 8 <= $capacityBits) {
                return [$version, $ec];
            }
        }

        throw new \RuntimeException('QrSvg: payload too long for supported versions');
    }

    // ── Bit stream (data + error correction, interleaved) ─────────────────────

    private static function buildBitStream(string $text, int $version, int $ecCodewords): array
    {
        [$totalData, , $blocks] = self::SPEC_M[$version];
        $charCountBits = $version < 10 ? 8 : 16;

        $bits = '';
        $bits .= '0100'; // byte mode
        $bits .= str_pad(decbin(strlen($text)), $charCountBits, '0', STR_PAD_LEFT);
        foreach (str_split($text) as $ch) {
            $bits .= str_pad(decbin(ord($ch)), 8, '0', STR_PAD_LEFT);
        }

        $capacityBits = $totalData * 8;
        // terminator
        $bits .= str_repeat('0', min(4, $capacityBits - strlen($bits)));
        // pad to byte boundary
        if (strlen($bits) % 8 !== 0) {
            $bits .= str_repeat('0', 8 - strlen($bits) % 8);
        }
        // pad bytes
        $pad = ['11101100', '00010001'];
        $i = 0;
        while (strlen($bits) < $capacityBits) {
            $bits .= $pad[$i % 2];
            $i++;
        }

        // split into codewords
        $dataCodewords = [];
        for ($j = 0; $j < strlen($bits); $j += 8) {
            $dataCodewords[] = bindec(substr($bits, $j, 8));
        }

        // split into blocks, compute EC per block
        $dataBlocks = [];
        $ecBlocks = [];
        $pos = 0;
        foreach ($blocks as [$count, $dataLen]) {
            for ($b = 0; $b < $count; $b++) {
                $blockData = array_slice($dataCodewords, $pos, $dataLen);
                $pos += $dataLen;
                $dataBlocks[] = $blockData;
                $ecBlocks[] = self::reedSolomon($blockData, $ecCodewords);
            }
        }

        // interleave data, then EC
        $result = [];
        $maxData = max(array_map('count', $dataBlocks));
        for ($c = 0; $c < $maxData; $c++) {
            foreach ($dataBlocks as $blk) {
                if (isset($blk[$c])) {
                    $result[] = $blk[$c];
                }
            }
        }
        $maxEc = max(array_map('count', $ecBlocks));
        for ($c = 0; $c < $maxEc; $c++) {
            foreach ($ecBlocks as $blk) {
                if (isset($blk[$c])) {
                    $result[] = $blk[$c];
                }
            }
        }

        // back to a bit array
        $out = [];
        foreach ($result as $cw) {
            for ($b = 7; $b >= 0; $b--) {
                $out[] = ($cw >> $b) & 1;
            }
        }

        return $out;
    }

    private static function reedSolomon(array $data, int $ecLen): array
    {
        // generator polynomial
        $gen = [1];
        for ($i = 0; $i < $ecLen; $i++) {
            $next = array_fill(0, count($gen) + 1, 0);
            foreach ($gen as $k => $coef) {
                $next[$k] ^= self::gfMul($coef, 1);
                $next[$k + 1] ^= self::gfMul($coef, self::$expTable[$i]);
            }
            $gen = $next;
        }

        $res = array_merge($data, array_fill(0, $ecLen, 0));
        for ($i = 0; $i < count($data); $i++) {
            $factor = $res[$i];
            if ($factor === 0) {
                continue;
            }
            for ($j = 0; $j < count($gen); $j++) {
                $res[$i + $j] ^= self::gfMul($gen[$j], $factor);
            }
        }

        return array_slice($res, count($data), $ecLen);
    }

    // ── Matrix construction ───────────────────────────────────────────────────

    private static function buildMatrix(int $version, int $n, array $bits): array
    {
        $m = array_fill(0, $n, array_fill(0, $n, null));      // module value
        $reserved = array_fill(0, $n, array_fill(0, $n, false));

        $place = function (int $r, int $c, int $v) use (&$m, &$reserved) {
            $m[$r][$c] = $v;
            $reserved[$r][$c] = true;
        };

        // finder patterns + separators
        foreach ([[0, 0], [0, $n - 7], [$n - 7, 0]] as [$fr, $fc]) {
            for ($r = -1; $r <= 7; $r++) {
                for ($c = -1; $c <= 7; $c++) {
                    $rr = $fr + $r;
                    $cc = $fc + $c;
                    if ($rr < 0 || $rr >= $n || $cc < 0 || $cc >= $n) {
                        continue;
                    }
                    $isBorder = ($r >= 0 && $r <= 6 && ($c === 0 || $c === 6))
                        || ($c >= 0 && $c <= 6 && ($r === 0 || $r === 6));
                    $isCore = $r >= 2 && $r <= 4 && $c >= 2 && $c <= 4;
                    $place($rr, $cc, ($isBorder || $isCore) ? 1 : 0);
                }
            }
        }

        // timing patterns
        for ($i = 8; $i < $n - 8; $i++) {
            if ($m[6][$i] === null) {
                $place(6, $i, ($i % 2 === 0) ? 1 : 0);
            }
            if ($m[$i][6] === null) {
                $place($i, 6, ($i % 2 === 0) ? 1 : 0);
            }
        }

        // dark module
        $place($n - 8, 8, 1);

        // alignment patterns
        $centres = self::ALIGN[$version];
        foreach ($centres as $ar) {
            foreach ($centres as $ac) {
                if ($m[$ar][$ac] !== null) {
                    continue; // overlaps finder
                }
                for ($r = -2; $r <= 2; $r++) {
                    for ($c = -2; $c <= 2; $c++) {
                        $ring = max(abs($r), abs($c));
                        $place($ar + $r, $ac + $c, ($ring === 1) ? 0 : 1);
                    }
                }
            }
        }

        // reserve format-info areas (filled after masking)
        for ($i = 0; $i <= 8; $i++) {
            if ($m[8][$i] === null) {
                $reserved[8][$i] = true;
            }
            if ($m[$i][8] === null) {
                $reserved[$i][8] = true;
            }
        }
        for ($i = 0; $i < 8; $i++) {
            $reserved[8][$n - 1 - $i] = true;
            $reserved[$n - 1 - $i][8] = true;
        }

        // place data bits in zig-zag
        $idx = 0;
        $up = true;
        for ($col = $n - 1; $col > 0; $col -= 2) {
            if ($col === 6) {
                $col--; // skip timing column
            }
            for ($i = 0; $i < $n; $i++) {
                $row = $up ? ($n - 1 - $i) : $i;
                foreach ([0, 1] as $cOff) {
                    $c = $col - $cOff;
                    if ($reserved[$row][$c]) {
                        continue;
                    }
                    $bit = $bits[$idx] ?? 0;
                    $idx++;
                    // mask 0: (row + col) % 2 == 0
                    if (($row + $c) % 2 === 0) {
                        $bit ^= 1;
                    }
                    $m[$row][$c] = $bit;
                }
            }
            $up = ! $up;
        }

        self::placeFormatInfo($m, $n);

        return $m;
    }

    /** Format info for EC level M + mask pattern 0 (15 bits, pre-computed). */
    private static function placeFormatInfo(array &$m, int $n): void
    {
        // EC=M(00) mask=000 -> data 00000; with BCH + mask = 0x5412 reversed bits.
        $formatBits = '101010000010010';
        $bits = array_map('intval', str_split($formatBits));

        // around top-left finder
        $coords1 = [
            [8, 0], [8, 1], [8, 2], [8, 3], [8, 4], [8, 5], [8, 7], [8, 8],
            [7, 8], [5, 8], [4, 8], [3, 8], [2, 8], [1, 8], [0, 8],
        ];
        foreach ($coords1 as $i => [$r, $c]) {
            $m[$r][$c] = $bits[$i];
        }

        // second copy: bits 0-6 up col 8 from the bottom, bits 7-14 along row 8
        // from the right (this orientation is what conformant decoders expect).
        for ($i = 0; $i < 7; $i++) {
            $m[$n - 1 - $i][8] = $bits[$i];
        }
        for ($i = 0; $i < 8; $i++) {
            $m[8][$n - 1 - $i] = $bits[14 - $i];
        }
    }

    // ── SVG output ─────────────────────────────────────────────────────────────

    private static function renderSvg(array $m, int $n, int $size, string $dark, string $light): string
    {
        $quiet = 4;
        $total = $n + $quiet * 2;
        $rects = '';
        for ($r = 0; $r < $n; $r++) {
            for ($c = 0; $c < $n; $c++) {
                if (($m[$r][$c] ?? 0) === 1) {
                    $x = $c + $quiet;
                    $y = $r + $quiet;
                    $rects .= "<rect x=\"{$x}\" y=\"{$y}\" width=\"1\" height=\"1\"/>";
                }
            }
        }

        return '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'" '
            .'viewBox="0 0 '.$total.' '.$total.'" shape-rendering="crispEdges" role="img" aria-label="QR code">'
            .'<rect width="'.$total.'" height="'.$total.'" fill="'.$light.'"/>'
            .'<g fill="'.$dark.'">'.$rects.'</g>'
            .'</svg>';
    }
}
