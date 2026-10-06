/*
 * AI Course Finder - front-end behaviour.
 * Kept small: the pages work without it, this only hides the level section that does not apply.
 */
(function () {
    'use strict';

    var form = document.querySelector('[data-level-form]');
    if (!form) {
        return;
    }

    var levelInputs = form.querySelectorAll('input[name="highest_level"]');
    var sections = form.querySelectorAll('[data-level-section]');

    function selectedLevel() {
        var checked = form.querySelector('input[name="highest_level"]:checked');
        return checked ? checked.value : '';
    }

    function showSectionFor(level) {
        sections.forEach(function (section) {
            section.hidden = section.getAttribute('data-level-section') !== level;
        });
    }

    levelInputs.forEach(function (input) {
        input.addEventListener('change', function () {
            showSectionFor(selectedLevel());
        });
    });

    // Until a level is chosen, show neither section.
    if (selectedLevel() === '') {
        showSectionFor('');
    }
})();
