
window.projectVersion = 'master';

(function(root) {

    var bhIndex = null;
    var rootPath = '';
    var treeHtml = '        <ul>                <li data-name="namespace:JsonBrowser" class="opened">                    <div style="padding-left:0px" class="hd">                        <span class="glyphicon glyphicon-play"></span><a href="JsonBrowser.html">JsonBrowser</a>                    </div>                    <div class="bd">                                <ul>                <li data-name="class:JsonBrowser_Exception" class="opened">                    <div style="padding-left:26px" class="hd leaf">                        <a href="JsonBrowser/Exception.html">Exception</a>                    </div>                </li>                            <li data-name="class:JsonBrowser_Iterator" class="opened">                    <div style="padding-left:26px" class="hd leaf">                        <a href="JsonBrowser/Iterator.html">Iterator</a>                    </div>                </li>                            <li data-name="class:JsonBrowser_JsonBrowser" class="opened">                    <div style="padding-left:26px" class="hd leaf">                        <a href="JsonBrowser/JsonBrowser.html">JsonBrowser</a>                    </div>                </li>                </ul></div>                </li>                </ul>';

    var searchTypeClasses = {
        'Namespace': 'label-default',
        'Class': 'label-info',
        'Interface': 'label-primary',
        'Trait': 'label-success',
        'Method': 'label-danger',
        '_': 'label-warning'
    };

    var searchIndex = [
                    
            {"type": "Namespace", "link": "JsonBrowser.html", "name": "JsonBrowser", "doc": "Namespace JsonBrowser"},
            
            {"type": "Class", "fromName": "JsonBrowser", "fromLink": "JsonBrowser.html", "link": "JsonBrowser/Exception.html", "name": "JsonBrowser\\Exception", "doc": "&quot;Custom exception class&quot;"},
                                                        {"type": "Method", "fromName": "JsonBrowser\\Exception", "fromLink": "JsonBrowser/Exception.html", "link": "JsonBrowser/Exception.html#method___construct", "name": "JsonBrowser\\Exception::__construct", "doc": "&quot;Create a new instance&quot;"},
                    {"type": "Method", "fromName": "JsonBrowser\\Exception", "fromLink": "JsonBrowser/Exception.html", "link": "JsonBrowser/Exception.html#method_wrap", "name": "JsonBrowser\\Exception::wrap", "doc": "&quot;Wrap some code and catch errors with a custom exception&quot;"},
            
            {"type": "Class", "fromName": "JsonBrowser", "fromLink": "JsonBrowser.html", "link": "JsonBrowser/Iterator.html", "name": "JsonBrowser\\Iterator", "doc": "&quot;Iterate through child nodes&quot;"},
                                                        {"type": "Method", "fromName": "JsonBrowser\\Iterator", "fromLink": "JsonBrowser/Iterator.html", "link": "JsonBrowser/Iterator.html#method___construct", "name": "JsonBrowser\\Iterator::__construct", "doc": "&quot;Create a new instance&quot;"},
                    {"type": "Method", "fromName": "JsonBrowser\\Iterator", "fromLink": "JsonBrowser/Iterator.html", "link": "JsonBrowser/Iterator.html#method_current", "name": "JsonBrowser\\Iterator::current", "doc": "&quot;Get a browser object for the current child&quot;"},
                    {"type": "Method", "fromName": "JsonBrowser\\Iterator", "fromLink": "JsonBrowser/Iterator.html", "link": "JsonBrowser/Iterator.html#method_key", "name": "JsonBrowser\\Iterator::key", "doc": "&quot;Get the current child index&quot;"},
                    {"type": "Method", "fromName": "JsonBrowser\\Iterator", "fromLink": "JsonBrowser/Iterator.html", "link": "JsonBrowser/Iterator.html#method_next", "name": "JsonBrowser\\Iterator::next", "doc": "&quot;Advance the internal pointer to the next child&quot;"},
                    {"type": "Method", "fromName": "JsonBrowser\\Iterator", "fromLink": "JsonBrowser/Iterator.html", "link": "JsonBrowser/Iterator.html#method_rewind", "name": "JsonBrowser\\Iterator::rewind", "doc": "&quot;Reset the internal pointer to the first child&quot;"},
                    {"type": "Method", "fromName": "JsonBrowser\\Iterator", "fromLink": "JsonBrowser/Iterator.html", "link": "JsonBrowser/Iterator.html#method_valid", "name": "JsonBrowser\\Iterator::valid", "doc": "&quot;Test whether there are more children to iterate over&quot;"},
            
            {"type": "Class", "fromName": "JsonBrowser", "fromLink": "JsonBrowser.html", "link": "JsonBrowser/JsonBrowser.html", "name": "JsonBrowser\\JsonBrowser", "doc": "&quot;Helper class for working with JSON-encoded data&quot;"},
                                                        {"type": "Method", "fromName": "JsonBrowser\\JsonBrowser", "fromLink": "JsonBrowser/JsonBrowser.html", "link": "JsonBrowser/JsonBrowser.html#method___construct", "name": "JsonBrowser\\JsonBrowser::__construct", "doc": "&quot;Create a new instance&quot;"},
                    {"type": "Method", "fromName": "JsonBrowser\\JsonBrowser", "fromLink": "JsonBrowser/JsonBrowser.html", "link": "JsonBrowser/JsonBrowser.html#method_childExists", "name": "JsonBrowser\\JsonBrowser::childExists", "doc": "&quot;Check whether a child element exists&quot;"},
                    {"type": "Method", "fromName": "JsonBrowser\\JsonBrowser", "fromLink": "JsonBrowser/JsonBrowser.html", "link": "JsonBrowser/JsonBrowser.html#method_getChild", "name": "JsonBrowser\\JsonBrowser::getChild", "doc": "&quot;Get a child node&quot;"},
                    {"type": "Method", "fromName": "JsonBrowser\\JsonBrowser", "fromLink": "JsonBrowser/JsonBrowser.html", "link": "JsonBrowser/JsonBrowser.html#method_getIterator", "name": "JsonBrowser\\JsonBrowser::getIterator", "doc": "&quot;Get an iterator handle&quot;"},
                    {"type": "Method", "fromName": "JsonBrowser\\JsonBrowser", "fromLink": "JsonBrowser/JsonBrowser.html", "link": "JsonBrowser/JsonBrowser.html#method_getJSON", "name": "JsonBrowser\\JsonBrowser::getJSON", "doc": "&quot;Get the JSON source for the current node&quot;"},
                    {"type": "Method", "fromName": "JsonBrowser\\JsonBrowser", "fromLink": "JsonBrowser/JsonBrowser.html", "link": "JsonBrowser/JsonBrowser.html#method_getKey", "name": "JsonBrowser\\JsonBrowser::getKey", "doc": "&quot;Get the node index key (i.e. the child name within the parent node)&quot;"},
                    {"type": "Method", "fromName": "JsonBrowser\\JsonBrowser", "fromLink": "JsonBrowser/JsonBrowser.html", "link": "JsonBrowser/JsonBrowser.html#method_getNodeAt", "name": "JsonBrowser\\JsonBrowser::getNodeAt", "doc": "&quot;Get the node at a given path&quot;"},
                    {"type": "Method", "fromName": "JsonBrowser\\JsonBrowser", "fromLink": "JsonBrowser/JsonBrowser.html", "link": "JsonBrowser/JsonBrowser.html#method_getParent", "name": "JsonBrowser\\JsonBrowser::getParent", "doc": "&quot;Get parent node&quot;"},
                    {"type": "Method", "fromName": "JsonBrowser\\JsonBrowser", "fromLink": "JsonBrowser/JsonBrowser.html", "link": "JsonBrowser/JsonBrowser.html#method_getPath", "name": "JsonBrowser\\JsonBrowser::getPath", "doc": "&quot;Get the node path&quot;"},
                    {"type": "Method", "fromName": "JsonBrowser\\JsonBrowser", "fromLink": "JsonBrowser/JsonBrowser.html", "link": "JsonBrowser/JsonBrowser.html#method_getRoot", "name": "JsonBrowser\\JsonBrowser::getRoot", "doc": "&quot;Get root node&quot;"},
                    {"type": "Method", "fromName": "JsonBrowser\\JsonBrowser", "fromLink": "JsonBrowser/JsonBrowser.html", "link": "JsonBrowser/JsonBrowser.html#method_getSibling", "name": "JsonBrowser\\JsonBrowser::getSibling", "doc": "&quot;Get a sibling node&quot;"},
                    {"type": "Method", "fromName": "JsonBrowser\\JsonBrowser", "fromLink": "JsonBrowser/JsonBrowser.html", "link": "JsonBrowser/JsonBrowser.html#method_getType", "name": "JsonBrowser\\JsonBrowser::getType", "doc": "&quot;Get the document value type&quot;"},
                    {"type": "Method", "fromName": "JsonBrowser\\JsonBrowser", "fromLink": "JsonBrowser/JsonBrowser.html", "link": "JsonBrowser/JsonBrowser.html#method_getValue", "name": "JsonBrowser\\JsonBrowser::getValue", "doc": "&quot;Get the document value&quot;"},
                    {"type": "Method", "fromName": "JsonBrowser\\JsonBrowser", "fromLink": "JsonBrowser/JsonBrowser.html", "link": "JsonBrowser/JsonBrowser.html#method_getValueAt", "name": "JsonBrowser\\JsonBrowser::getValueAt", "doc": "&quot;Get the value at a given path&quot;"},
                    {"type": "Method", "fromName": "JsonBrowser\\JsonBrowser", "fromLink": "JsonBrowser/JsonBrowser.html", "link": "JsonBrowser/JsonBrowser.html#method_isEqualTo", "name": "JsonBrowser\\JsonBrowser::isEqualTo", "doc": "&quot;Test whether the document value is equal to a given value&quot;"},
                    {"type": "Method", "fromName": "JsonBrowser\\JsonBrowser", "fromLink": "JsonBrowser/JsonBrowser.html", "link": "JsonBrowser/JsonBrowser.html#method_isNotType", "name": "JsonBrowser\\JsonBrowser::isNotType", "doc": "&quot;Test whether the document value is &lt;em&gt;not&lt;\/em&gt; of a given type&quot;"},
                    {"type": "Method", "fromName": "JsonBrowser\\JsonBrowser", "fromLink": "JsonBrowser/JsonBrowser.html", "link": "JsonBrowser/JsonBrowser.html#method_isType", "name": "JsonBrowser\\JsonBrowser::isType", "doc": "&quot;Test whether the document value is of a given type&quot;"},
                    {"type": "Method", "fromName": "JsonBrowser\\JsonBrowser", "fromLink": "JsonBrowser/JsonBrowser.html", "link": "JsonBrowser/JsonBrowser.html#method_siblingExists", "name": "JsonBrowser\\JsonBrowser::siblingExists", "doc": "&quot;Check whether a sibling exists&quot;"},
            
            
                                        // Fix trailing commas in the index
        {}
    ];

    /** Tokenizes strings by namespaces and functions */
    function tokenizer(term) {
        if (!term) {
            return [];
        }

        var tokens = [term];
        var meth = term.indexOf('::');

        // Split tokens into methods if "::" is found.
        if (meth > -1) {
            tokens.push(term.substr(meth + 2));
            term = term.substr(0, meth - 2);
        }

        // Split by namespace or fake namespace.
        if (term.indexOf('\\') > -1) {
            tokens = tokens.concat(term.split('\\'));
        } else if (term.indexOf('_') > 0) {
            tokens = tokens.concat(term.split('_'));
        }

        // Merge in splitting the string by case and return
        tokens = tokens.concat(term.match(/(([A-Z]?[^A-Z]*)|([a-z]?[^a-z]*))/g).slice(0,-1));

        return tokens;
    };

    root.Sami = {
        /**
         * Cleans the provided term. If no term is provided, then one is
         * grabbed from the query string "search" parameter.
         */
        cleanSearchTerm: function(term) {
            // Grab from the query string
            if (typeof term === 'undefined') {
                var name = 'search';
                var regex = new RegExp("[\\?&]" + name + "=([^&#]*)");
                var results = regex.exec(location.search);
                if (results === null) {
                    return null;
                }
                term = decodeURIComponent(results[1].replace(/\+/g, " "));
            }

            return term.replace(/<(?:.|\n)*?>/gm, '');
        },

        /** Searches through the index for a given term */
        search: function(term) {
            // Create a new search index if needed
            if (!bhIndex) {
                bhIndex = new Bloodhound({
                    limit: 500,
                    local: searchIndex,
                    datumTokenizer: function (d) {
                        return tokenizer(d.name);
                    },
                    queryTokenizer: Bloodhound.tokenizers.whitespace
                });
                bhIndex.initialize();
            }

            results = [];
            bhIndex.get(term, function(matches) {
                results = matches;
            });

            if (!rootPath) {
                return results;
            }

            // Fix the element links based on the current page depth.
            return $.map(results, function(ele) {
                if (ele.link.indexOf('..') > -1) {
                    return ele;
                }
                ele.link = rootPath + ele.link;
                if (ele.fromLink) {
                    ele.fromLink = rootPath + ele.fromLink;
                }
                return ele;
            });
        },

        /** Get a search class for a specific type */
        getSearchClass: function(type) {
            return searchTypeClasses[type] || searchTypeClasses['_'];
        },

        /** Add the left-nav tree to the site */
        injectApiTree: function(ele) {
            ele.html(treeHtml);
        }
    };

    $(function() {
        // Modify the HTML to work correctly based on the current depth
        rootPath = $('body').attr('data-root-path');
        treeHtml = treeHtml.replace(/href="/g, 'href="' + rootPath);
        Sami.injectApiTree($('#api-tree'));
    });

    return root.Sami;
})(window);

$(function() {

    // Enable the version switcher
    $('#version-switcher').change(function() {
        window.location = $(this).val()
    });

    
        // Toggle left-nav divs on click
        $('#api-tree .hd span').click(function() {
            $(this).parent().parent().toggleClass('opened');
        });

        // Expand the parent namespaces of the current page.
        var expected = $('body').attr('data-name');

        if (expected) {
            // Open the currently selected node and its parents.
            var container = $('#api-tree');
            var node = $('#api-tree li[data-name="' + expected + '"]');
            // Node might not be found when simulating namespaces
            if (node.length > 0) {
                node.addClass('active').addClass('opened');
                node.parents('li').addClass('opened');
                var scrollPos = node.offset().top - container.offset().top + container.scrollTop();
                // Position the item nearer to the top of the screen.
                scrollPos -= 200;
                container.scrollTop(scrollPos);
            }
        }

    
    
        var form = $('#search-form .typeahead');
        form.typeahead({
            hint: true,
            highlight: true,
            minLength: 1
        }, {
            name: 'search',
            displayKey: 'name',
            source: function (q, cb) {
                cb(Sami.search(q));
            }
        });

        // The selection is direct-linked when the user selects a suggestion.
        form.on('typeahead:selected', function(e, suggestion) {
            window.location = suggestion.link;
        });

        // The form is submitted when the user hits enter.
        form.keypress(function (e) {
            if (e.which == 13) {
                $('#search-form').submit();
                return true;
            }
        });

    
});


