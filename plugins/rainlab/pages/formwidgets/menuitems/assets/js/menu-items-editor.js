/*
 * The menu item editor. Provides tools for managing the 
 * menu items.
 */
+function ($) { "use strict";
    var MenuItemsEditor = function (el, options) {
        this.$el = $(el)
        this.options = options

        this.init()
    }

    MenuItemsEditor.prototype.init = function() {
        var self = this

        this.alias = this.$el.data('alias')
        this.$treeView = this.$el.find('div[data-control="treeview"]')

        this.typeInfo = {}

        // Menu items is clicked
        this.$el.on('open.oc.treeview', function(e) {
            return self.onItemClick(e.relatedTarget)
        })

        // Submenu item is clicked in the master tabs
        this.$el.on('submenu.oc.treeview', $.proxy(this.onSubmenuItemClick, this))

        this.$el.on('click', 'a[data-control="add-item"]', function(e) {
            self.onCreateItem(e.target)
            return false
        })
    }

    /*
     * Triggered when a submenu item is clicked in the menu editor.
     */
    MenuItemsEditor.prototype.onSubmenuItemClick = function(e) {
        if ($(e.relatedTarget).data('control') == 'delete-menu-item')
            this.onDeleteMenuItem(e.relatedTarget)

        if ($(e.relatedTarget).data('control') == 'create-item')
            this.onCreateItem(e.relatedTarget)

        return false
    }

    /*
     * Removes a menu item
     */
    MenuItemsEditor.prototype.onDeleteMenuItem = function(link) {
        if (!confirm('Do you really want to delete the menu item? This will also delete the subitems, if any.'))
            return

        $(link).trigger('change')
        $(link).closest('li[data-menu-item]').remove()

        $(window).trigger('oc.updateUi')

        this.$treeView.treeView('update')
        this.$treeView.treeView('fixSubItems')
    }

    /*
     * Opens the menu item editor
     */
    MenuItemsEditor.prototype.onItemClick = function(item, newItemMode) {
        var $item = $(item),
            $container = $('> div', $item),
            self = this

        $container.one('show.oc.popup', function(e){
            $(document).trigger('render')

            self.$popupContainer = $(e.relatedTarget);
            self.$itemDataContainer = $container.closest('li')

            $('input[type=checkbox]', self.$popupContainer).removeAttr('checked')

            self.loadProperties(self.$popupContainer, self.$itemDataContainer.data('menu-item'))
            self.$popupForm = self.$popupContainer.find('form')
            self.itemSaved = false

            $('input[name=title]', self.$popupContainer).focus().select()
            $('select[name=type]', self.$popupContainer).change(function(){
                self.loadTypeInfo(false, true)
            })

            self.$popupContainer.on('keydown', function(e) {
                if (e.which == 13)
                    self.applyMenuItem()
            })

            $('button[data-control="apply-btn"]', self.$popupContainer).click($.proxy(self.applyMenuItem, self))

            var $updateTypeOptionsBtn = $('<a class="sidebar-control" href="#"><i class="icon-refresh"></i></a>')
            $('div[data-field-name=reference]').addClass('input-sidebar-control').append($updateTypeOptionsBtn)

            $updateTypeOptionsBtn.click(function(){
                self.loadTypeInfo(true)

                return false
            })

            $updateTypeOptionsBtn.keydown(function(ev){
                if (ev.which == 13 || ev.which == 32) {
                    self.loadTypeInfo(true)
                    return false
                }
            })

            var $updateCmsPagesBtn = $updateTypeOptionsBtn.clone(true)
            $('div[data-field-name=cmsPage]').addClass('input-sidebar-control').append($updateCmsPagesBtn)

            self.loadTypeInfo()
        })

        $container.one('hide.oc.popup', function(e) {
            if (!self.itemSaved && newItemMode)
                $item.remove()

            self.$treeView.treeView('update')
            self.$treeView.treeView('fixSubItems')

            $container.removeClass('popover-highlight')
        })

        $container.popup({
            content: $('script[data-editor-template]', this.$el).html()
        })

        /*
         * Highlight modal target
         */
        $container.addClass('popover-highlight')
        $container.blur()

        return false
    }

    MenuItemsEditor.prototype.loadProperties = function($popupContainer, properties) {
        this.properties = properties

        var setPropertyOnElement = function($input, val) {
            if ($input.prop('type') == 'checkbox') {
                var checked = !(val == '0' || val == 'false' || val == 0 || val == undefined || val == null)
                checked ? $input.prop('checked', 'checked') : $input.removeAttr('checked')
            }
            else if ($input.prop('type') == 'radio') {
                $input.filter('[value="'+val+'"]').prop('checked', true)
            }
            else {
                $input.val(val)
                $input.change()
            }
        }

        $.each(properties, function(property, val) {
            if (property == 'viewBag') {
                $.each(val, function(vbProperty, vbVal) {
                    var $input = $('[name="viewBag['+vbProperty+']"]', $popupContainer).not('[type=hidden]')
                    setPropertyOnElement($input, vbVal)
                })
            }
            else {
                var $input = $('[name="'+property+'"]', $popupContainer).not('[type=hidden]')
                setPropertyOnElement($input, val)
            }
        })
    }

    MenuItemsEditor.prototype.loadTypeInfo = function(force, focusList) {
        var type = $('select[name=type]', this.$popupContainer).val()

        var self = this

        if (!force && this.typeInfo[type] !== undefined) {
            self.applyTypeInfo(this.typeInfo[type], type, focusList)
            return
        }

        $.oc.stripeLoadIndicator.show()
        this.$popupForm.request('onGetMenuItemTypeInfo')
            .always(function(){
                $.oc.stripeLoadIndicator.hide()
            })
            .done(function(data){
                self.typeInfo[type] = data.menuItemTypeInfo
                self.applyTypeInfo(data.menuItemTypeInfo, type, focusList)
            })
    }

    MenuItemsEditor.prototype.applyTypeInfo = function(typeInfo, type, focusList) {
        var $referenceFormGroup = $('div[data-field-name="reference"]', this.$popupContainer),
            $optionSelector = $('select', $referenceFormGroup),
            $nestingFormGroup = $('div[data-field-name="nesting"]', this.$popupContainer),
            $urlFormGroup = $('div[data-field-name="url"]', this.$popupContainer),
            $replaceFormGroup = $('div[data-field-name="replace"]', this.$popupContainer),
            $cmsPageFormGroup = $('div[data-field-name="cmsPage"]', this.$popupContainer),
            $cmsPageSelector = $('select', $cmsPageFormGroup),
            prevSelectedReference = $optionSelector.val(),
            prevSelectedPage = $cmsPageSelector.val()

        if (typeInfo.references) {
            $optionSelector.find('option').remove()
            $referenceFormGroup.show()

            var iterator = function(options, level, path) {
                $.each(options, function(code) {
                    var $option = $('<option></option>').attr('value', code),
                        offset = Array(level*4).join('&nbsp;'),
                        isObject = $.type(this) == 'object'

                    $option.text(isObject ? this.title : this)

                    var optionPath = path.length > 0
                        ? (path + ' / ' + $option.text())
                        : $option.text()

                    $option.data('path', optionPath)

                    $option.html(offset + $option.html())

                    $optionSelector.append($option)

                    if (isObject)
                        iterator(this.items, level+1, optionPath)
                })
            }

            iterator(typeInfo.references, 0, '')

            $optionSelector
                .val(prevSelectedReference ? prevSelectedReference : this.properties.reference)
                .triggerHandler('change')
        }
        else {
            $referenceFormGroup.hide()
        }

        if (typeInfo.cmsPages) {
            $cmsPageSelector.find('option').remove()
            $cmsPageFormGroup.show()

            $.each(typeInfo.cmsPages, function(code) {
                var $option = $('<option></option>').attr('value', code)

                $option.text(this).val(code)
                $cmsPageSelector.append($option)
            })

            $cmsPageSelector
                .val(prevSelectedPage ? prevSelectedPage : this.properties.cmsPage)
                .triggerHandler('change')
        }
        else {
            $cmsPageFormGroup.hide()
        }

        $nestingFormGroup.toggle(typeInfo.nesting !== undefined && typeInfo.nesting)
        $urlFormGroup.toggle(type == 'url')
        $replaceFormGroup.toggle(typeInfo.dynamicItems !== undefined && typeInfo.dynamicItems)

        $(document).trigger('render')

        if (focusList) {
            var focusElements = [
                $referenceFormGroup,
                $cmsPageFormGroup,
                $('div.custom-checkbox', $nestingFormGroup),
                $('div.custom-checkbox', $replaceFormGroup),
                $('input', $urlFormGroup)
            ]

            $.each(focusElements, function(){
                if (this.is(':visible')) {
                    var $self = this

                    window.setTimeout(function() {
                        if ($self.hasClass('dropdown-field'))
                            $('select', $self).select2('focus', 100)
                        else $self.focus()
                    })

                    return false;
                }
            })
        }
    }

    MenuItemsEditor.prototype.applyMenuItem = function() {
        var self = this,
            data = {},
            propertyNames = this.$el.data('item-properties'),
            basicProperties = {
                'title': 1,
                'type': 1,
                'code': 1
            },
            typeInfoPropertyMap = {
                reference: 'references',
                replace: 'dynamicItems',
                cmsPage: 'cmsPages'
            },
            typeInfo = {},
            validationErrorFound = false

        $.each(propertyNames, function() {
            var propertyName = this,
                $input = $('[name="'+propertyName+'"]', self.$popupContainer).not('[type=hidden]')

            if ($input.prop('type') !== 'checkbox') {
                data[propertyName] = $.trim($input.val())

                if (propertyName == 'type')
                    typeInfo = self.typeInfo[data.type]

                if (data[propertyName].length == 0) {
                    var typeInfoProperty = typeInfoPropertyMap[propertyName] !== undefined ? typeInfoPropertyMap[propertyName] : propertyName

                    if (typeInfo[typeInfoProperty] !== undefined) {

                        $.oc.flashMsg({
                            class: 'error',
                            text: self.$popupForm.attr('data-message-'+propertyName+'-required')
                        })

                        if ($input.prop("tagName") == 'SELECT')
                            $input.select2('focus')
                        else
                            $input.focus()

                        validationErrorFound = true

                        return false
                    }
                }
            }
            else {
                data[propertyName] = $input.prop('checked') ? 1 : 0
            }
        })

        if (validationErrorFound)
            return

        if (data.type !== 'url') {
            delete data['url']

            $.each(data, function(property) {
                if (property == 'type')
                    return

                var typeInfoProperty = typeInfoPropertyMap[property] !== undefined ? typeInfoPropertyMap[property] : property
                if ((typeInfo[typeInfoProperty] === undefined || typeInfo[typeInfoProperty] === false) 
                    && basicProperties[property] === undefined)
                    delete data[property]
            })
        }
        else {
            $.each(propertyNames, function(){
                if (this != 'url' && basicProperties[this] === undefined)
                    delete data[this]
            })
        }

        if ($.trim(data.title).length == 0) {
            $.oc.flashMsg({
                class: 'error',
                text: self.$popupForm.data('messageTitleRequired')
            })

            $('[name=title]', self.$popupContainer).focus()

            return
        }

        if (data.type == 'url' && $.trim(data.url).length == 0) {
            $.oc.flashMsg({
                class: 'error',
                text: self.$popupForm.data('messageUrlRequired')
            })

            $('[name=url]', self.$popupContainer).focus()

            return
        }

        $('> div span.title', self.$itemDataContainer).text(data.title)

        var referenceDescription = $.trim($('select[name=type] option:selected', self.$popupContainer).text())

        if (data.type == 'url') {
            referenceDescription += ': ' + $('input[name=url]', self.$popupContainer).val()
        }
        else if (typeInfo.references) {
            referenceDescription += ': ' + $.trim($('select[name=reference] option:selected', self.$popupContainer).data('path'))
        }

        $('> div span.comment', self.$itemDataContainer).text(referenceDescription)

        this.attachViewBagData(data)

        this.$itemDataContainer.data('menu-item', data)
        this.itemSaved = true
        this.$popupContainer.trigger('close.oc.popup')
        this.$el.trigger('change')
    }

    MenuItemsEditor.prototype.attachViewBagData = function(data) {
        var fields = this.$popupForm.serializeArray(),
            fieldName,
            fieldValue

        $.each(fields, function(index, field) {
            fieldName = field.name
            fieldValue = field.value

            if (fieldName.indexOf('viewBag[') != 0) {
                return true // Continue
            }

            /*
             * Break field name in to elements
             */
            var elements = [],
                searchResult,
                expression = /([^\]\[]+)/g

            while ((searchResult = expression.exec(fieldName))) {
                elements.push(searchResult[0])
            }

            /*
             * Attach elements to data with value
             */
            var currentData = data,
                elementsNum = elements.length,
                lastIndex = elementsNum - 1,
                currentProperty

            for (var i = 0; i < elementsNum; ++i) {
                currentProperty = elements[i]

                if (i === lastIndex) {
                    currentData[currentProperty] = fieldValue
                }
                else if (currentData[currentProperty] === undefined) {
                    currentData[currentProperty] = {}
                }

                currentData = currentData[currentProperty]
            }
        })
    }

    MenuItemsEditor.prototype.onCreateItem = function(target) {
        var parentList = $(target).closest('li[data-menu-item]').find(' > ol'),
            item = $($('script[data-item-template]', this.$el).html())

        if (!parentList.length)
            parentList = $(target).closest('div[data-control=treeview]').find(' > ol')

        parentList.append(item)
        this.$treeView.treeView('update')
        $(window).trigger('oc.updateUi')

        this.onItemClick(item, true)
    }

    MenuItemsEditor.DEFAULTS = {
    }

    // MENUITEMSEDITOR PLUGIN DEFINITION
    // ============================

    var old = $.fn.menuItemsEditor

    $.fn.menuItemsEditor = function (option) {
        var args = Array.prototype.slice.call(arguments, 1)
        return this.each(function () {
            var $this   = $(this)
            var data    = $this.data('oc.menuitemseditor')
            var options = $.extend({}, MenuItemsEditor.DEFAULTS, $this.data(), typeof option == 'object' && option)
            if (!data) $this.data('oc.menuitemseditor', (data = new MenuItemsEditor(this, options)))
            else if (typeof option == 'string') data[option].apply(data, args)
        })
    }

    $.fn.menuItemsEditor.Constructor = MenuItemsEditor

    // MENUITEMSEDITOR NO CONFLICT
    // =================

    $.fn.menuItemsEditor.noConflict = function () {
        $.fn.menuItemsEditor = old
        return this
    }

    // MENUITEMSEDITOR DATA-API
    // ===============

    $(document).on('render', function() {
        $('[data-control="menu-item-editor"]').menuItemsEditor()
    });
}(window.jQuery);