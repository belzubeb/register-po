<?php
/**
 * Pembersih HTML untuk isi WYSIWYG (barang proposal).
 * Daftar putih: hanya tag, atribut, dan properti CSS yang aman yang lolos.
 * Gambar hanya boleh berupa data URI (hasil tempel/unggah di editor), jadi PDF tidak perlu akses internet.
 */
class Sanitizer
{
    const MAX_BYTES = 8000000;

    private static $tags = [
        'p', 'br', 'div', 'span', 'strong', 'b', 'em', 'i', 'u', 's', 'strike', 'sub', 'sup',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'pre', 'code', 'hr',
        'ul', 'ol', 'li',
        'table', 'caption', 'colgroup', 'col', 'thead', 'tbody', 'tfoot', 'tr', 'td', 'th',
        'img', 'a', 'figure', 'figcaption',
    ];
    // Dibuang beserta isinya (bukan cuma tag-nya).
    private static $drop = [
        'script', 'style', 'iframe', 'frame', 'frameset', 'object', 'embed', 'applet', 'form', 'input', 'button',
        'textarea', 'select', 'option', 'svg', 'math', 'link', 'meta', 'base', 'noscript', 'template', 'head', 'title', 'audio', 'video',
    ];
    private static $attrs = [
        '*'     => ['style'],
        'a'     => ['href', 'title', 'target'],
        'img'   => ['src', 'alt', 'width', 'height'],
        'table' => ['border', 'cellpadding', 'cellspacing', 'width', 'align'],
        'td'    => ['colspan', 'rowspan', 'width', 'height', 'align', 'valign'],
        'th'    => ['colspan', 'rowspan', 'width', 'height', 'align', 'valign', 'scope'],
        'col'   => ['width', 'span'],
        'ol'    => ['type', 'start'],
        'p'     => ['align'],
        'div'   => ['align'],
        'h1' => ['align'], 'h2' => ['align'], 'h3' => ['align'], 'h4' => ['align'], 'h5' => ['align'], 'h6' => ['align'],
    ];
    private static $css = [
        'color', 'background-color', 'font-size', 'font-weight', 'font-style', 'font-family', 'text-align', 'text-decoration',
        'line-height', 'vertical-align', 'width', 'height', 'min-width', 'max-width', 'float', 'text-indent', 'list-style-type',
        'border', 'border-top', 'border-right', 'border-bottom', 'border-left', 'border-color', 'border-style', 'border-width', 'border-collapse',
        'padding', 'padding-top', 'padding-right', 'padding-bottom', 'padding-left',
        'margin', 'margin-top', 'margin-right', 'margin-bottom', 'margin-left',
    ];

    public static function clean($html)
    {
        $html = (string) $html;
        if (trim($html) === '') { return ''; }
        if (strlen($html) > self::MAX_BYTES) { return ''; }

        $prev = libxml_use_internal_errors(true);
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->loadHTML('<?xml encoding="UTF-8"><div id="__root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        $root = $doc->getElementById('__root');
        if (!$root) { return ''; }
        self::walk($root);

        $out = '';
        foreach (iterator_to_array($root->childNodes) as $child) { $out .= $doc->saveHTML($child); }
        return trim($out);
    }

    /** Ada isi nyata (teks atau gambar)? */
    public static function hasContent($html)
    {
        $html = (string) $html;
        if (stripos($html, '<img') !== false) { return true; }
        $t = html_entity_decode(strip_tags($html), ENT_QUOTES, 'UTF-8');
        $t = str_replace("\xC2\xA0", ' ', $t);
        return trim($t) !== '';
    }

    private static function walk(DOMNode $node)
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child->nodeType === XML_TEXT_NODE) { continue; }
            if ($child->nodeType !== XML_ELEMENT_NODE) { $node->removeChild($child); continue; } // komentar, PI, dll.

            $tag = strtolower($child->nodeName);
            if (in_array($tag, self::$drop, true)) { $node->removeChild($child); continue; }

            self::walk($child); // bersihkan anak dulu

            if (!in_array($tag, self::$tags, true)) {
                // Tag tak dikenal: buang pembungkusnya, isinya dipertahankan.
                while ($child->firstChild) { $node->insertBefore($child->firstChild, $child); }
                $node->removeChild($child);
                continue;
            }
            if (!self::cleanAttrs($child, $tag)) { $node->removeChild($child); }
        }
    }

    /** @return bool false kalau elemen harus dibuang seluruhnya */
    private static function cleanAttrs(DOMElement $el, $tag)
    {
        $allowed = array_merge(self::$attrs['*'], isset(self::$attrs[$tag]) ? self::$attrs[$tag] : []);
        foreach (iterator_to_array($el->attributes) as $a) {
            $name = strtolower($a->name);
            if (!in_array($name, $allowed, true)) { $el->removeAttribute($a->name); continue; }
            $val = $a->value;

            if ($name === 'style') {
                $s = self::cleanStyle($val);
                if ($s === '') { $el->removeAttribute($a->name); } else { $el->setAttribute('style', $s); }
            } elseif ($name === 'src') {
                // Cek awalan pakai regex, isi base64 pakai strspn (regex pada string multi-MB bisa gagal karena batas PCRE).
                if (!preg_match('#^data:image/(png|jpe?g|gif|webp);base64,#i', $val, $m)) { return false; }
                $payload = substr($val, strlen($m[0]));
                if ($payload === '' || strspn($payload, "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=\r\n\t ") !== strlen($payload)) { return false; }
            } elseif ($name === 'href') {
                if (!preg_match('#^(https?://|mailto:|\#)#i', trim($val))) { $el->removeAttribute($a->name); }
            } elseif ($name === 'target') {
                $el->setAttribute('target', '_blank');
            } elseif (in_array($name, ['width', 'height', 'colspan', 'rowspan', 'border', 'cellpadding', 'cellspacing', 'span', 'start'], true)) {
                if (!preg_match('/^\d{1,4}(\.\d+)?%?$/', trim($val))) { $el->removeAttribute($a->name); }
            } elseif (in_array($name, ['align', 'valign', 'scope', 'type'], true)) {
                if (!preg_match('/^[a-zA-Z0-9]{1,10}$/', trim($val))) { $el->removeAttribute($a->name); }
            }
        }
        if ($tag === 'a' && $el->hasAttribute('target')) { $el->setAttribute('rel', 'noopener noreferrer'); }
        if ($tag === 'img' && !$el->hasAttribute('src')) { return false; }
        return true;
    }

    private static function cleanStyle($style)
    {
        $keep = [];
        foreach (explode(';', $style) as $decl) {
            if (strpos($decl, ':') === false) { continue; }
            list($p, $v) = array_map('trim', explode(':', $decl, 2));
            $p = strtolower($p);
            if (!in_array($p, self::$css, true) || $v === '') { continue; }
            if (preg_match('/url\s*\(|expression|javascript|@import|[<>\\\\]/i', $v)) { continue; }
            if (!preg_match('/^[\w\s#%.,()\-+\/\'"!]+$/u', $v)) { continue; }
            $keep[] = $p . ': ' . $v;
        }
        return implode('; ', $keep);
    }
}
