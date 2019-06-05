import './index.scss';

let navLinks = document.querySelectorAll('.document-summary-indexes a.nav-link');
let documentSummary = document.getElementById('document-summary');
let documentBody = document.getElementById('document-body');
let links = document.querySelectorAll('.document-content a');
let documentHeaders = documentBody.querySelectorAll('h1, h2, h3, h4, h5, h6');
let onScrollSmoothly = false;
let halfHeightOfDocumentSummary = parseInt(documentSummary.clientHeight / 2);
let oneThirdHeightOfDocumentSummary = parseInt(documentSummary.clientHeight / 3);
let twoThirdHeightOfDocumentSummary = parseInt(documentSummary.clientHeight * 2 / 3);

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
    let target = document.querySelector(element.getAttribute('href'));

    if (!target) {
        return;
    }

    let targetTop = distanceToTop(target);

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
        let href = this.getAttribute('href');

        if (0 === href.indexOf('#')) {
            event.preventDefault();

            let id = href.substr(1);
            let navLink = document.getElementById('nav-link-'+id);

            if (navLink) {
                navLink.click();
            }
        }
    });
});

window.addEventListener('scroll', function() {
    let header = null;
    let scrollTop = window.scrollY;

    for (let i in documentHeaders) {
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
        let navLink = document.getElementById('nav-link-'+header.id);

        if (navLink) {
            if (navLink.hasAttribute('data-scroll-smoothly-target')) {
                navLink.removeAttribute('data-scroll-smoothly-target');
                onScrollSmoothly = false;
            }

            if (onScrollSmoothly) {
                return;
            }

            if (!navLink.classList.contains('active')) {
                let activeNavLink = document.querySelector('.document-summary-indexes a.active');

                if (activeNavLink) {
                    activeNavLink.classList.remove('active');
                }

                navLink.classList.add('active');
            }

            let newScrollTopOfDocumentSummary = distanceToTop(navLink);

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
