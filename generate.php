<?php

/**
 * Simple script to generate a sitemap file from html files located within the
 * current directory.
 *
 * Run as, for example:
 *
 * /var/www/mysite $ php ~/bin/generate.php . > sitemap.xml
 */

declare(strict_types=1);

require_once "find_files.php";

$files = find_files(".", "html");
$now = time();

$out = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
$out .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

foreach ($files as $file) {
    $doc = new DOMDocument();
    $res = @$doc->load($file);

    if ($res == false) {
        fwrite(STDERR, "Error in [$file]: could not load\n");
        continue;
    }

    $xpath = new DOMXPath($doc);
    $nodes = $xpath->query("/html/head/link[@rel='canonical']/@href");

    if ($nodes->length !== 1) {
        fwrite(STDERR, "Error in [$file]: could not fetch canonical URL\n");
        continue;
    }

    $loc = trim($nodes->item(0)->value);

    if ($loc === "") {
        fwrite(STDERR, "Error in [$file]: canonical URL is empty\n");
        continue;
    }

    $mtime = filemtime($file);
    $age = $now - $mtime;
    $freq = "";

    if ($age <= 60 * 60) {
        $freq = "hourly";
    } else if ($age <= 24 * 60 * 60) {
        $freq = "daily";
    } else if ($age <= 7 * 24 * 60 * 60) {
        $freq = "weekly";
    } else if ($age <= 31 * 24 * 60 * 60) {
        $freq = "monthly";
    } else if ($age <= 365 * 24 * 60 * 60) {
        $freq = "yearly";
    }

    $out .= '    <url>'."\n";
    $out .= '        <loc>'.$loc.'</loc>'."\n";
    $out .= '        <lastmod>'.date('Y-m-d', $mtime).'</lastmod>'."\n";

    if ($freq) {
        $out .= '        <changefreq>'.$freq.'</changefreq>'."\n";
    }

    $out .= '    </url>'."\n";
}

$out .= '</urlset>'."\n";
echo $out;
