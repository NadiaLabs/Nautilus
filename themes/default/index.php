<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo $documentConfig['title']; ?></title>

    <style type="text/css">
        <?php echo file_get_contents(__DIR__.'/build/index.css'); ?>
    </style>
</head>
<body>

<div class="container-fluid">
    <div id="document-summary">
        <div class="document-summary-header">
            <div class="document-summary-title">
                <a href=""><?php echo $documentConfig['title']; ?></a>
            </div>
            <?php if (!empty($parameters['version'])) : ?>
            <div>
                <small>Version: <?php echo $parameters['version']; ?></small>
            </div>
            <?php endif; ?>
            <?php if (!empty($parameters['updateAt'])) : ?>
            <div>
                <small>Updated At: <?php echo $parameters['updateAt']; ?></small>
            </div>
            <?php endif; ?>
        </div>

        <hr>

        <ul class="nav flex-column document-summary-indexes">
        <?php
            foreach ($posts as $postIndex => $post): /** @var \Nautilus\Markdown\MarkdownContent $post */
                $outlineLevels = array();
                foreach ($post->getOutlines() as $outline) {
                    $outlineLevels[] = $outline['level'];
                }
                $outlineLevels = array_unique($outlineLevels);
                sort($outlineLevels);

                // Prepare outline indent classes
                $headerIndentClasses = array();
                foreach ($outlineLevels as $levelIndex => $level) {
                    $headerIndentClasses[$level] = 'header-indent-'.($levelIndex+1);
                }

                $activeClass = $postIndex === 0 ? 'active' : '';
        ?>
            <li class="nav-item">
                <a href="#<?php echo $post->getChapterHeaderId(); ?>"
                   class="nav-link <?php echo $activeClass; ?>"
                   id="nav-link-<?php echo $post->getChapterHeaderId(); ?>">
                    <?php echo $post->getChapterTitle(); ?>
                </a>
            </li>
            <?php foreach ($post->getOutlines() as $outline): ?>
            <li class="nav-item">
                <a href="#<?php echo $outline['id']; ?>"
                   class="nav-link <?php echo $headerIndentClasses[$outline['level']]; ?>"
                   id="nav-link-<?php echo $outline['id']; ?>">
                    <?php echo $outline['html']; ?>
                </a>
            </li>
            <?php endforeach; ?>
        <?php endforeach; ?>
        </ul>
    </div>
    <div id="document-body">
        <div class="document-contents">
        <?php foreach ($posts as $post): /** @var \Nautilus\Markdown\MarkdownContent $post */ ?>
            <div class="document-content">
                <h1 class="document-content-header" id="<?php echo $post->getChapterHeaderId(); ?>">
                    <?php echo $post->getChapterTitle(); ?>
                </h1>

                <div class="document-content-body">
                    <?php echo $post->getBody(); ?>
                </div>
            </div>
            <hr>
        <?php endforeach; ?>
        </div>
    </div>
</div>

<script type="text/javascript">
    <?php echo file_get_contents(__DIR__.'/build/runtime.js'); ?>
    <?php echo file_get_contents(__DIR__.'/build/index.js'); ?>
</script>

</body>
</html>
