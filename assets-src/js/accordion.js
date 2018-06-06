var Guzzle = Guzzle || {};

Guzzle.accordion = function () {
    var elements = document.querySelectorAll('.accordion .accordion-header');

    for (var i = 0, l = elements.length, element; i < l, element = elements[i]; i++) {
        element.addEventListener('click', function () {
            this
                .parentNode
                .getElementsByClassName('accordion-content')[0]
                .classList
                .toggle('expanded')
            ;
        });

        var links = element.getElementsByTagName('a');

        for (var j = 0, k = links.length, link; j < k, link = links[j]; j++) {
            link.addEventListener('click', function (e) {
                e.stopPropagation();
            });
        }
    }
};

document.addEventListener('DOMContentLoaded', Guzzle.accordion, false);
