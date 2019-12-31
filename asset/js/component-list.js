'use strict';

{
    const urls = {
        'components': 'https://docs.laminas.dev/data/component-list.components.json',
        'mezzio': 'https://docs.mezzio.dev/data/component-list.mezzio.json',
        'mvc': 'https://docs.laminas.dev/data/component-list.mvc.json',
    };

    function getContainerElements() {
        return document.querySelectorAll('.component-selector');
    }

    function hideContainerElements() {
        getContainerElements().forEach(element => {
            element.classList.add('d-none');
        });
    }

    function prepareComponentList(components) {
        let componentList = [];

        // eslint-disable-next-line no-use-before-define
        const matchActive = new RegExp('\/' + siteName + '(\/|$)');

        components.forEach(function (component) {
            const selected = matchActive.test(component.url);
            const packageName = component.package.split('/');
            const label = component.name + '<br/><span class="choices__packagename">' + packageName[1] + '</span>';
            const choice = {
                value: component.url,
                label: label,
                selected: selected,
                customProperties: {
                    description: component.description
                }
            };

            componentList.push(choice);
        });

        // Initialize the Choices selector using the component selector as its element
        document.querySelectorAll('.component-selector__control').forEach(element => {
            const choices = new Choices(element, {
                itemSelectText: '',
                renderChoiceLimit: -1,
                searchChoices: true,
                searchEnabled: true,
                searchFields: ['label', 'customProperties.description'],
                searchPlaceholderValue: 'Jump to package…',
                searchResultLimit: 10,
                shouldSort: false
            });

            choices.setChoices(
                componentList,
                'value',
                'label',
                true
            );

            // On selection of a choice, redirect to its URL
            element.addEventListener('choice', function (event) {
                window.location.href = event.detail.choice.value;
            }, false);

            // Ensure the parent container expands to fit the list...
            element.addEventListener('showDropdown', function () {
                getContainerElements().forEach(function (container) {
                    const choicesList = container.querySelector('.choices__list--dropdown');
                    container.parentElement.style.minHeight = container.clientHeight + choicesList.clientHeight + "px";
                });
            }, false);

            // ... and then shrinks back to size again.
            element.addEventListener('hideDropdown', function () {
                getContainerElements().forEach(function (container) {
                    container.parentElement.style.minHeight = 'unset';
                });
            }, false);

            // Load all starting and ending points for the sticky scene.
            // After all of them have loaded, toggle the sticky styles accordingly.
            element.addEventListener('showDropdown', function () {
                const groups = choices.choiceList.element.querySelectorAll('.choices__group');

                groups.forEach(function (group, index) {
                    const stickyStart = group.offsetTop;
                    let stickyEnd = null;

                    if (groups.hasOwnProperty(index + 1)) {
                        const nextGroup = groups[index + 1];
                        stickyEnd = nextGroup.offsetTop;
                    }

                    group.setAttribute('data-sticky-start', stickyStart);
                    group.setAttribute('data-sticky-end', stickyEnd ? stickyEnd : '');
                });

                groups.forEach(function (group) {
                    toggleStickyGroupStyles(group, choices.choiceList.element.scrollTop);
                });
            }, false);

            // Remove all sticky styles when the drop-down hides
            element.addEventListener('hideDropdown', function () {
                const groups = choices.choiceList.element.querySelectorAll('.choices__group');

                groups.forEach(function (group) {
                    unsetStickyGroupStyles(group);
                });
            }, false);

            // Track the scroll position and apply sticky styles to the opt-group elements.
            // The "touchmove" event is used for touch devices, because on some browsers
            // the scroll event is triggered only once after the scroll animation has completed.
            ['scroll', 'touchmove'].forEach(function (eventName) {
                choices.choiceList.element.addEventListener(eventName, function () {
                    const groups = this.querySelectorAll('.choices__group');
                    const scrollTop = this.scrollTop;

                    groups.forEach(function (group) {
                        toggleStickyGroupStyles(group, scrollTop);
                    });
                });
            });
        });
    }

    function toggleStickyGroupStyles(group, scrollTop) {
        const stickyStart = parseInt(group.getAttribute('data-sticky-start'));
        const stickyEnd = group.getAttribute('data-sticky-end')
            ? parseInt(group.getAttribute('data-sticky-end'))
            : null;

        if (scrollTop >= stickyStart && (scrollTop <= stickyEnd || !stickyEnd)) {
            setStickyGroupStyles(group);
            return;
        }

        unsetStickyGroupStyles(group);
    }

    function setStickyGroupStyles(group) {
        group.classList.add('is-sticky');

        if (group.nextElementSibling) {
            // Group element's position becomes fixed,
            // causing the height of the scrollable element to decrease.
            // The "removed" height is added as a margin top
            // on the next element in order to compensate for the losses.
            // This gives a smooth, non-jumping feeling to the end-user.
            group.nextElementSibling.style.marginTop = group.clientHeight + 'px';
        }
    }

    function unsetStickyGroupStyles(group) {
        group.classList.remove('is-sticky');

        if (group.nextElementSibling) {
            group.nextElementSibling.style.marginTop = null;
        }
    }

    // When the window has finished loading the DOM, fetch the components and
    // populate the dropdown.
    window.addEventListener('load', function () {
        if (!project || !urls.hasOwnProperty(project)) {
            hideContainerElements();

            return;
        }

        fetch(urls[project])
            .then(response => response.json())
            .then(data => prepareComponentList(data))
            .catch(() => {
                hideContainerElements();
            });
    });
}
