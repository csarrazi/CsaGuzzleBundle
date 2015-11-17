var Guzzle = Guzzle || {};

Guzzle.addClass = function(el, cssClass) { el.classList.add(cssClass); };
Guzzle.hasClass = function (el, cssClass) { return el.classList.contains(cssClass); };
Guzzle.removeClass = function(el, cssClass) { el.classList.remove(cssClass); };

Guzzle.createTabs = function() {
    var tabGroups = document.querySelectorAll('.sf-tabs');

    /* create the tab navigation for each group of tabs */
    for (var i = 0; i < tabGroups.length; i++) {
        var tabs = tabGroups[i].querySelectorAll('.tab');
        var tabNavigation = document.createElement('ul');
        tabNavigation.className = 'tab-navigation';

        for (var j = 0; j < tabs.length; j++) {
            var tabId = 'tab-' + i + '-' + j;
            var tabTitle = tabs[j].querySelector('.tab-title').innerHTML;

            var tabNavigationItem = document.createElement('li');
            tabNavigationItem.setAttribute('data-tab-id', tabId);
            if (j == 0) {
                Guzzle.addClass(tabNavigationItem, 'active');
            }
            if (Guzzle.hasClass(tabs[j], 'disabled')) {
                Guzzle.addClass(tabNavigationItem, 'disabled');
            }
            tabNavigationItem.innerHTML = tabTitle;
            tabNavigation.appendChild(tabNavigationItem);

            var tabContent = tabs[j].querySelector('.tab-content');
            tabContent.parentElement.setAttribute('id', tabId);
        }

        tabGroups[i].insertBefore(tabNavigation, tabGroups[i].firstChild);
    }

    /* display the active tab and add the 'click' event listeners */
    for (i = 0; i < tabGroups.length; i++) {
        tabNavigation = tabGroups[i].querySelectorAll('.tab-navigation li');

        for (j = 0; j < tabNavigation.length; j++) {
            tabId = tabNavigation[j].getAttribute('data-tab-id');
            document.getElementById(tabId).querySelector('.tab-title').className = 'hidden';

            if (Guzzle.hasClass(tabNavigation[j], 'active')) {
                document.getElementById(tabId).className = 'block';
            } else {
                document.getElementById(tabId).className = 'hidden';
            }

            tabNavigation[j].addEventListener('click', function (e) {
                var activeTab = e.target || e.srcElement;

                /* needed because when the tab contains HTML contents, user can click */
                /* on any of those elements instead of their parent '<li>' element */
                while (activeTab.tagName.toLowerCase() !== 'li') {
                    activeTab = activeTab.parentNode;
                }

                /* get the full list of tabs through the parent of the active tab element */
                var tabNavigation = activeTab.parentNode.children;

                for (var k = 0; k < tabNavigation.length; k++) {
                    var tabId = tabNavigation[k].getAttribute('data-tab-id');
                    document.getElementById(tabId).className = 'hidden';
                    Guzzle.removeClass(tabNavigation[k], 'active');
                }

                Guzzle.addClass(activeTab, 'active');
                var activeTabId = activeTab.getAttribute('data-tab-id');
                document.getElementById(activeTabId).className = 'block';
            });
        }
    }
};
