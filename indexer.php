<?php

// A function to crawl the website and collect data
function crawlWebsite($url) {
    $content = [];
    $html = file_get_contents($url);

    // Check if the URL content is retrieved
    if (!$html) {
        return [];
    }

    // Parse the page content (you can use DOMDocument or simple HTML parsing)
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML($html);
    libxml_clear_errors();

    // Get the title
    $title = $dom->getElementsByTagName('title')->item(0)->nodeValue;

    // Get all headings (h1, h2, h3, etc.)
    $headings = [];
    foreach (['h1', 'h2', 'h3', 'h4', 'h5', 'h6'] as $tag) {
        $elements = $dom->getElementsByTagName($tag);
        foreach ($elements as $element) {
            $headings[] = $element->nodeValue;
        }
    }

    // Get all paragraphs
    $paragraphs = [];
    $pTags = $dom->getElementsByTagName('p');
    foreach ($pTags as $p) {
        $paragraphs[] = $p->nodeValue;
    }

    // Get all links and images (with alt text)
    $links = [];
    $imgs = [];
    $aTags = $dom->getElementsByTagName('a');
    foreach ($aTags as $a) {
        if ($a->hasAttribute('href')) {
            $links[] = $a->getAttribute('href');
        }
    }

    $imgTags = $dom->getElementsByTagName('img');
    foreach ($imgTags as $img) {
        if ($img->hasAttribute('alt')) {
            $imgs[] = $img->getAttribute('alt');
        }
    }

    // Store the content in an array (for JSON or database storage)
    $content = [
        'url' => $url,
        'title' => $title,
        'headings' => $headings,
        'paragraphs' => $paragraphs,
        'links' => $links,
        'images' => $imgs
    ];

    return $content;
}

// A function to recursively crawl all pages (subfolders)
function crawlDirectory($baseUrl, $path = '') {
    $allContent = [];
    $directory = opendir($baseUrl . $path);

    // Read all files and directories in the current folder
    while (($file = readdir($directory)) !== false) {
        if ($file == '.' || $file == '..') {
            continue; // Skip the current and parent directory links
        }

        // Get the full file path
        $fullPath = $baseUrl . $path . '/' . $file;

        if (is_dir($fullPath)) {
            // If it's a folder, recursively crawl that folder
            $allContent = array_merge($allContent, crawlDirectory($baseUrl, $path . '/' . $file));
        } else {
            // If it's a file, crawl it
            if (strpos($file, '.php') !== false || strpos($file, '.html') !== false) {
                $allContent[] = crawlWebsite($fullPath);
            }
        }
    }

    closedir($directory);
    return $allContent;
}

// Example of how you can call the crawler (starting point)
$baseUrl = 'http://yourwebsite.com'; // The base URL of your website
$indexData = crawlDirectory($baseUrl);

// Save the index data to a JSON file (or use a database)
file_put_contents('website_index.json', json_encode($indexData));

echo "Website content indexed successfully!";
?>