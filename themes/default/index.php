<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo $documentConfig['title']; ?></title>

    <style type="text/css">
        <?php echo file_get_contents(__DIR__.'/build/index.css'); ?>

        body {
            font-family: "PT Serif","Georgia","Helvetica Neue",Arial,"Microsoft JhengHei",sans-serif;
        }

        #document-summary {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: 300px;
            color: #364149;
            background: #fafafa;
            border-right: 1px solid rgba(0,0,0,.07);
            overflow-y: auto;
            padding: 10px 0 0 0;
        }
        .document-summary-header {
            padding: 0 15px;
        }
        .document-summary-header small {
            font-size: 0.8em;
        }
        .document-summary-title {
            font-size: 1.5em;
        }
        .document-summary-title a {
            color: #364149;
            text-decoration: none;
        }
        .document-summary-indexes a, .document-summary-indexes a:visited {
            color: #364149;
        }
        .document-summary-indexes a:hover, .document-summary-indexes a.active {
            background: #000;
            color: #FFF;
        }

        #document-body {
            margin-left: 300px;
        }

        .document-contents {
            max-width: 1024px;
            margin: 0 auto;
            padding: 20px;
        }

        .document-content {
            margin: 4em 0;
        }
        .document-content:first-child {
            margin-top: 0;
        }

        h1.document-content-header {
            font-size: 3rem;
            margin-bottom: 2rem;
        }

        .header-indent-1 {
            padding-left: 2rem;
        }
        .header-indent-2 {
            padding-left: 3rem;
        }
        .header-indent-3 {
            padding-left: 4rem;
        }
        .header-indent-4 {
            padding-left: 5rem;
        }
        .header-indent-5 {
            padding-left: 6rem;
        }
        .header-indent-6 {
            padding-left: 7rem;
        }

        .document-content-body .h1, .document-content-body h1,
        .document-content-body .h2, .document-content-body h2,
        .document-content-body .h3, .document-content-body h3,
        .document-content-body .h4, .document-content-body h4,
        .document-content-body .h5, .document-content-body h5,
        .document-content-body .h6, .document-content-body h6 {
            margin-bottom: 1.25rem;
            margin-top: 2.25rem;
            border-left: solid black 5px;
            padding-left: 0.8rem;
            /*padding-bottom: 5px;*/
            line-height: 1.25em;
        }

        .code-block {
            margin-bottom: 1rem;
        }
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
    var navLinks = document.querySelectorAll('.document-summary-indexes a.nav-link');
    var documentSummary = document.getElementById('document-summary');
    var documentBody = document.getElementById('document-body');
    var links = document.querySelectorAll('.document-content a');
    var documentHeaders = documentBody.querySelectorAll('h1, h2, h3, h4, h5, h6');
    var onScrollSmoothly = false;
    var halfHeightOfDocumentSummary = parseInt(documentSummary.clientHeight / 2);
    var oneThirdHeightOfDocumentSummary = parseInt(documentSummary.clientHeight / 3);
    var twoThirdHeightOfDocumentSummary = parseInt(documentSummary.clientHeight * 2 / 3);

    /**
     * @param {Element} elm
     *
     * @returns {number}
     */
    function distanceToTop(elm) {
        return Math.floor(elm.getBoundingClientRect().top) - 20;
    }

    /**
     * @param {Element} element
     */
    function scrollSmoothly(element) {
        var target = document.querySelector(element.getAttribute('href'));

        if (!target) {
            return;
        }

        var targetTop = distanceToTop(target);

        onScrollSmoothly = true;
        element.setAttribute('data-scroll-smoothly-target', '1');

        window.scrollBy({top: targetTop, left: 0, behavior: 'smooth'});
    }

    navLinks.forEach(function(navLink) {
        navLink.addEventListener('click', function(event) {
            event.preventDefault();
            scrollSmoothly(this);

            navLinks.forEach(function(navLink) {
                navLink.classList.remove('active');
            });

            this.classList.add('active');
        });
    });

    links.forEach(function(link) {
        link.addEventListener('click', function(event) {
            var href = this.getAttribute('href');

            if (0 === href.indexOf('#')) {
                event.preventDefault();

                var id = href.substr(1);
                var navLink = document.getElementById('nav-link-'+id);

                if (navLink) {
                    navLink.click();
                }
            }
        });
    });

    window.addEventListener('scroll', function() {
        var header = null;
        var scrollTop = window.scrollY;

        for (var i in documentHeaders) {
            if (documentHeaders.hasOwnProperty(i)) {
                if (0 === scrollTop) {
                    header = documentHeaders[i];
                    break;
                }
                if (documentHeaders[i].offsetTop - scrollTop > 20) {
                    break;
                }

                header = documentHeaders[i];
            }
        }

        if (header) {
            var navLink = document.getElementById('nav-link-'+header.id);

            if (navLink) {
                if (navLink.hasAttribute('data-scroll-smoothly-target')) {
                    navLink.removeAttribute('data-scroll-smoothly-target');
                    onScrollSmoothly = false;
                }

                if (onScrollSmoothly) {
                    return;
                }

                if (!navLink.classList.contains('active')) {
                    var activeNavLink = document.querySelector('.document-summary-indexes a.active');

                    if (activeNavLink) {
                        activeNavLink.classList.remove('active');
                    }

                    navLink.classList.add('active');
                }

                var newScrollTopOfDocumentSummary = distanceToTop(navLink);

                if (newScrollTopOfDocumentSummary >= twoThirdHeightOfDocumentSummary
                    || newScrollTopOfDocumentSummary <= oneThirdHeightOfDocumentSummary ) {
                    documentSummary.scrollBy({
                        top: newScrollTopOfDocumentSummary - halfHeightOfDocumentSummary - 30,
                        left: 0,
                        behavior: 'smooth'
                    });
                }
            }
        }
    });
</script>

</body>
</html>
