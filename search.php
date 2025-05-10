<?php
$query = isset($_GET['q']) ? strtolower(trim($_GET['q'])) : '';
$results = [];

// Load the indexed content
$indexData = json_decode(file_get_contents('website_index.json'), true);

// If there's a query, search through the index
if ($query !== '') {
    foreach ($indexData as $item) {
        $matches = false;

        // Search through title, headings, paragraphs, and links
        if (
            strpos(strtolower($item['title']), $query) !== false ||
            array_filter($item['headings'], fn($h) => strpos(strtolower($h), $query) !== false) ||
            array_filter($item['paragraphs'], fn($p) => strpos(strtolower($p), $query) !== false) ||
            array_filter($item['links'], fn($l) => strpos(strtolower($l), $query) !== false) ||
            array_filter($item['images'], fn($i) => strpos(strtolower($i), $query) !== false)
        ) {
            $matches = true;
        }

        if ($matches) {
            $results[] = $item;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
</head>
<body>
    <h1>Search Results for: <?= htmlspecialchars($query) ?></h1>
    <?php if (empty($results)): ?>
        <p>No results found.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($results as $result): ?>
                <li>
                    <a href="<?= $result['url'] ?>"><?= htmlspecialchars($result['title']) ?></a>
                    <p><?= implode(' ', array_slice($result['paragraphs'], 0, 2)) ?></p> <!-- Show a snippet of the content -->
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</body>
</html>