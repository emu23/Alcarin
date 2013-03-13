namespace 'Alcarin.JQueryPlugins', (exports, Alcarin) ->

    $.fn.disable = ->
        @each ->
            $(@).attr 'disabled', 'disabled'

    $.fn.enable = (value)->
        @each ->
            if value == false
                $(@).attr 'disabled', 'disabled'
            else
                $(@).removeAttr 'disabled'

    $.fn.enabled = ->
        @first().attr('disabled') == undefined

    $.fn.disabled = ->
        @first().attr('disabled') != undefined

    $.fn.reset = ->
        @each -> @reset()

    $.fn.center = ->
        _ = $ @
        { left: _.width() / 2, top: _.height() / 2}

    $.fn.disableSelection = ->
        return this
                 .attr('unselectable', 'on')
                 .css('user-select', 'none')
                 .on('selectstart', false)

    # adding relative position set feature
    _old_position = $.fn.position
    $.fn.position = (arg)->
        if $.isPlainObject arg
            if arg.top?
                @.css 'top', "#{arg.top}px"
            if arg.left?
                @.css 'left', "#{arg.left}px"
            return @
        _old_position.call(@)

    def_spin = {
        lines: 10,
        length: 5,
        width: 2,
        radius: 2,
        corners: 1,
        rotate: 0,
        color: '#000'
        speed: 1,
        trail: 60,
        shadow: false,
        hwaccel: false,
        className: 'spinner'
        zIndex: 2e9,
        top: 'auto'
        left: 'auto'
    }

    # jquery plugin wrapper for "spinner" library
    $.fn.spin = (opts)->
        @each ->
            $el = $(@)

            spinner  = $el.data 'spinner'

            if spinner?
                spinner.stop()
                $el.removeData 'spinner'
            else
                options =  jQuery.extend def_spin, {color: $el.css('color')}
                if opts is not false
                    options = jQuery.extend options, opts
                spinner = new Spinner options
                spinner.spin @
                $el.data 'spinner', spinner
        @

    # just simple shortcuts
    $.bbq.push = $.bbq.pushState
    $.bbq.get = $.bbq.getState