/*
 * Handles snippet operations on the Pages main page
 */
+function ($) { "use strict";
    if ($.oc.pages === undefined)
        $.oc.pages = {}

    var SnippetManager = function ($masterTabs) {
        this.$masterTabs = $masterTabs

        var self = this

        $(document).on('hidden.oc.inspector', '.field-richeditor [data-snippet]', function() {
            self.syncEditorCode(this)
        })

        $(document).on('init.oc.richeditor', '.field-richeditor textarea', function(ev, richeditor) {
            self.initSnippets(richeditor.getElement())
        })

        $(document).on('setContent.oc.richeditor', '.field-richeditor textarea', function(ev, richeditor) {
            if (!richeditor.isCodeViewActive()) {
                self.initSnippets(richeditor.getElement())
            }
        })

        $(document).on('syncContent.oc.richeditor', '.field-richeditor textarea', function(ev, richeditor, container) {
            self.syncPageMarkup(ev, container)
        })

        $(document).on('figureKeydown.oc.richeditor', '.field-richeditor textarea', function(ev, originalEv, richeditor) {
            self.editorKeyDown(ev, originalEv, richeditor)
        })

        $(document).on('click', '[data-snippet]', function() {
            if ($(this).hasClass('inspector-open')) {
                return
            }

            $.oc.inspector.manager.createInspector(this)
            return false
        })
    }

    SnippetManager.prototype.onSidebarSnippetClick = function($sidebarItem) {
        var $pageForm = $('div.tab-content > .tab-pane.active form[data-object-type=page]', this.$masterTabs)

        if (!$pageForm.length) {
            $.oc.alert('Snippets can only be added to Pages. Please open or create a Page first.')
            return
        }

        var $activeEditorTab = $('.control-tabs.secondary-tabs .tab-pane.active', $pageForm),
            $textarea = $activeEditorTab.find('[data-control="richeditor"] textarea'),
            $richeditorNode = $textarea.closest('[data-control="richeditor"]'),
            $snippetNode = $('<figure contenteditable="false" data-inspector-css-class="hero">&nbsp;</figure>'),
            componentClass = $sidebarItem.attr('data-component-class'),
            snippetCode = $sidebarItem.data('snippet')

        if (!$textarea.length) {
            $.oc.alert('Snippets can only be added to page Content or HTML placeholders.')
            return
        }

        if (componentClass) {
            $snippetNode.attr({
                'data-component': componentClass,
                'data-inspector-class': componentClass
            })

            // If a component-based snippet was added, make sure that
            // its code is unique, as it will be used as a component
            // alias.

            snippetCode = this.generateUniqueComponentSnippetCode(componentClass, snippetCode, $pageForm)
        }

        $snippetNode.attr({
            'data-snippet': snippetCode,
            'data-name': $sidebarItem.data('snippet-name'),
            'tabindex': '0',
            'draggable': 'true',
            'data-ui-block': 'true'
        })

        $snippetNode.addClass('fr-draggable')

        $richeditorNode.richEditor('insertUiBlock', $snippetNode)
    }

    SnippetManager.prototype.generateUniqueComponentSnippetCode = function(componentClass, originalCode, $pageForm) {
        var updatedCode = originalCode,
            counter = 1,
            snippetFound = false

        do {
            snippetFound = false

            $('[data-control="richeditor"] textarea', $pageForm).each(function(){
                var $textarea = $(this),
                    $codeDom = $('<div>' + $textarea.val() + '</div>')

                if ($codeDom.find('[data-snippet="'+updatedCode+'"][data-component]').length > 0) {
                    snippetFound = true
                    updatedCode = originalCode + counter
                    counter++

                    return false
                }
            })

        } while (snippetFound)

        return updatedCode
    }

    SnippetManager.prototype.syncEditorCode = function(inspectable) {
        // Race condition
        setTimeout(function() {
            var $richeditor = $(inspectable).closest('[data-control=richeditor]')
            $richeditor.richEditor('syncContent')
            inspectable.focus()
        }, 0)
    }

    SnippetManager.prototype.initSnippets = function($editor) {
        var snippetCodes = []

        $('.fr-view [data-snippet]', $editor).each(function(){
            var $snippet = $(this),
                snippetCode = $snippet.attr('data-snippet'),
                componentClass = $snippet.attr('data-component')

            if (componentClass)
                snippetCode += '|' + componentClass

            snippetCodes.push(snippetCode)

            $snippet
                .addClass('loading')
                .addClass('fr-draggable')
                .attr({
                    'data-inspector-css-class': 'hero',
                    'data-name': 'Loading...',
                    'data-ui-block': 'true',
                    'draggable': 'true',
                    'tabindex': '0'
                })
                .html('&nbsp;')

            if (componentClass) {
                $snippet.attr('data-inspector-class', componentClass)
            }

            this.contentEditable = false
        })

        if (snippetCodes.length > 0) {
            var request = $editor.request('onGetSnippetNames', {
                data: {
                    codes: snippetCodes
                }
            }).done(function(data) {
                if (data.names !== undefined) {
                    $.each(data.names, function(code){
                        $('[data-snippet="'+code+'"]', $editor)
                            .attr('data-name', this)
                            .removeClass('loading')
                    })
                }
            })
        }
    }

    SnippetManager.prototype.syncPageMarkup = function(ev, container) {
        var $domTree = $('<div>'+container.html+'</div>')

        $('[data-snippet]', $domTree).each(function(){
            var $snippet = $(this)

            $snippet.removeAttr('contenteditable data-name tabindex data-inspector-css-class data-inspector-class data-property-inspectorclassname data-property-inspectorproperty data-ui-block draggable')
            $snippet.removeClass('fr-draggable fr-dragging')

            if (!$snippet.attr('class')) {
                $snippet.removeAttr('class')
            }
        })

        container.html = $domTree.html()
    }

    SnippetManager.prototype.editorKeyDown = function(ev, originalEv, richeditor) {
        if (richeditor.getTextarea() === undefined)
            return

        if (originalEv.target && $(originalEv.target).attr('data-snippet') !== undefined) {
            this.snippetKeyDown(originalEv, originalEv.target)
        }
    }

    SnippetManager.prototype.snippetKeyDown = function(ev, snippet) {
        if (ev.which == 32) {
            switch (ev.which) {
                case 32:
                    // Space key
                    $.oc.inspector.manager.createInspector(snippet)
                break
            }
        }
    }

    $.oc.pages.snippetManager = SnippetManager
}(window.jQuery);
