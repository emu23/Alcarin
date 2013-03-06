namespace 'Alcarin', (exports, Alcarin) ->

    #base class for active views - grouped data
    #that will be automatically synhronized with
    #corresponding part of the html template.
    class exports.ActiveView

        @TYPE_CONTENT = 0
        @TYPE_ATTR    = 1

        #common regex, used by all, static for better performance
        @regex       = /{item\.(.*?)}/g
        #static list of all created active views
        @global_list = []
        @auto_init   = false

        #assoc array of this object properties. for each property name
        #we have list of jquery objects that need be updated when this
        #property will change
        bindings            : {}
        #before full initialization bindings won't be called and html template
        #won't be updated
        initialized         : false

        #simple properties container
        properties_container  : {}

        constructor: ->
            $.merge exports.ActiveView.global_list, [@]

            defaults = @.constructor.default_properties or {}

            @properties_container  = jQuery.extend({}, defaults)

            @active_list_container = {}
            @initialized           = false
            @bindings              = {}
            @rel = $()

        clone: ->
            copy = new @.constructor
            copy.properties_container = $.extend {}, @properties_container
            copy

        copy: (source)->
            if $.isPlainObject source
                for key, val of source
                    if $.isFunction @[key]
                        @[key] val
                    else
                        @properties_container[key] = val
                        if @initalized
                            @propertyChanged key
            else
                if @.constructor is source.constructor
                    $.extend(@properties_container, source.properties_container)
                    #reinit all properties
                    @init()

        # static function, should ba called once, after all page loading,
        # to prepare all views and update needed values. later all active-objects
        # will be auto initialize when bind.
        @initializeAll : ->
            for view in exports.ActiveView.global_list
                view.update()
            @auto_update = true;

        #it return function, should be used to preparing view properties.
        #check sample view below
        @dependencyProperty: (name, default_value, onChange)->
            if default_value?
                #save static, for full type
                @.default_properties = {} if not @.default_properties?
                @.default_properties[name] = default_value
            #this method will be called in specific object instance context
            (new_value) ->
                if not new_value? then return @properties_container[name]

                old_value = @properties_container[name]
                return false if new_value == old_value

                @properties_container[name] = new_value

                if @initialized
                    @propertyChanged name
                onChange?.call @, old_value, new_value
                @


        # it return function, should be used to updating activelist's
        # inside object
        @dependencyList: (query)->
            #this method will be called in specific object context
            () ->
                if not @active_list_container[query]?
                    activelist = @active_list_container[query] = new Alcarin.ActiveList
                    if @rel?
                        $bind_target = @rel.find query
                        activelist.bind $bind_target if $bind_target.length > 0

                        #console.log $bind_target
                return @active_list_container[query]

        #called automaticaly when class is full initialized and
        #property value will change.
        propertyChanged : (prop_name) ->

            if not @bindings[prop_name]?
                return
            bindings = @bindings[prop_name]

            for data in bindings
                $el = data.element
                new_val = org = data.original
                while result = ActiveView.regex.exec org
                    val = @properties_container[result[1]]
                    if val?
                        new_val = new_val.replace result[0], val
                switch data.type
                    when exports.ActiveView.TYPE_CONTENT
                        $el.html new_val
                    when exports.ActiveView.TYPE_ATTR
                        $el.prop data.attr, new_val
                        $el.attr data.attr, new_val
                    else
                        throw new Error('"#{data.type}" type not supported.')


        ###private function, storing entries in @bindings table for specific property
        names used in "content". it store object with 'type' (TYPE_ATTR/TYPE_CONTENT)
        'element' (jquery ref), 'original' ("content" value) and attribute ###
        prepare_bind : ($root, $child, content, attribute) ->
            #store in data attribute for later use
            checked = {}
            while result = ActiveView.regex.exec content
                property_name = result[1]

                if property_name?
                    if checked[property_name]? then continue
                    checked[property_name] = true
                    @bindings[property_name] = @bindings[property_name] ? []
                    @bindings[property_name].push {
                        'type'    : if attribute? then exports.ActiveView.TYPE_ATTR else exports.ActiveView.TYPE_CONTENT,
                        'element' : $child,
                        'original': content,
                        'attr'    : attribute,
                        'root'    : $root,
                    }
            true

        # check specific jquery element for properties that have been used inside
        # and store them in @bindings assoc array, to update html template when
        # correspondive property will change
        bind: (e) ->
            $e   = $ e
            return false if $e.is @rel

            @rel = @rel.add $e

            $e.each (index, val) =>

                $el = $ val

                # if this element was binded with another view we need unbind it to make
                # things work with this one.
                old_view = $el.data 'active-view'
                old_view?.unbind $el

                $el.data 'active-view', @

                all_children = $el.find('*')

                #checking all children attributes
                for child in all_children.toArray().concat [$el.get 0]
                    $child = $ child
                    for attr in child.attributes
                        @prepare_bind $el, $child, attr.value, attr.name

                children = all_children.filter (i, val)->
                    not $(val).children().length

                list = children.toArray()
                if not $el.children().length
                    list = list.concat [$el.get 0]

                for child in list
                    $child = $(child)
                    @prepare_bind $el, $child, $child.html()

                true

            # for query, activelist of @active_list_container
            #     activelist.bind $e.find query

            if ActiveView.auto_update
                @update()

            @onbind($e)
            true

        # this method can be override, will be automatically called when new target has been
        # binded to current active-view
        onbind: ($target) ->
            true

        # this method can be override, will be automatically called when target element has been
        # unbinded from current active-view
        onunbind: ($target) ->
            true

        #unbind not needed view relation
        unbind: (el)->
            $el = $ el

            for key, list of @bindings
                new_list = []
                for obj, index in list
                    if obj.root.is $el
                        $target = obj.element
                        switch obj.type
                            when exports.ActiveView.TYPE_CONTENT
                                $target.html obj.original
                            when exports.ActiveView.TYPE_ATTR
                                $target.prop obj.attr, obj.original
                                $target.attr obj.attr, obj.original
                    else
                        new_list.push obj
                @bindings[key] = new_list

            $el.removeData 'active-view'
            @rel = @rel.not $el
            @onunbind $el

        #shouldn't be called directly, rather by initializeAll static method.
        update : ->
            @initialized = true
            for property of @properties_container
                @propertyChanged property
            true

    ###
    Usage sample.

    class exports.TestView extends exports.ActiveView
        me    : @dependencyProperty('me')
        teraz : @dependencyProperty('teraz', 12)
        active_list: @dependencyList('.items') #jquery style child query

    av = new Alcarin.TestView()
    av.me 'psychowico321'
    #av.teraz 0
    #console.log av.teraz()
    av.bind '#active-item'

    When you prepare class like this you can use in html:

    <li id="active-item">
        <span class="pepe" data-tutaj="jajajaja">tell {item.me} raz</span>
        <span>a {item.teraz} dwa powiedz</span>
        <input type="text" value="{item.teraz}">
    </li>

    And later, for sample in click method:
    $('#active-item').click ->
        #set "teraz" value to 13
        av.teraz 13
        #get "teraz" value, only for sample
        av.teraz()

    And value on template will be automatically updated.
    ###


    $ ->
        #to add this at end of queue
        $ ->
            Alcarin.ActiveView.initializeAll()