(function($) {
    var SubColumnsBootstrapBundle = {
        onReady: function() {
            this.initColFull();

            $(document).ajaxComplete($.proxy(this.ajaxComplete, this));
        },
        ajaxComplete: function() {
        },
        initColFull: function() {
            this.doInitColFull('both');
            this.doInitColFull('left');
            this.doInitColFull('right');
        },
        doInitColFull: function(mode) {
            switch (mode) {
                case 'left':
                    colClass = '.col-full-left';
                    break;
                case 'right':
                    colClass = '.col-full-right';
                    break;
                default:
                    colClass = '.col-full';
                    break;
            }

            var $cols = $(colClass);

            if ($cols.length < 1)
                return;

            $(window).resize(function() {
                makeFullScreen();
            });

            function makeFullScreen() {
                var width = $(window).width(),
                    containerWidth = $('.container:first').width();

                switch (mode) {
                    case 'left':
                        $cols.css({
                            'margin-left': (width - containerWidth) / -2
                        });

                        break;
                    case 'right':
                        $cols.css({
                            'margin-right': (width - containerWidth) / -2
                        });
                        break;
                    case 'both':
                        $cols.css({
                            'margin-left': (width - containerWidth) / -2,
                            'margin-right': (width - containerWidth) / -2
                        });
                        break;
                }
            }

            makeFullScreen();
        }
    };

    $(document).ready(function() {
        SubColumnsBootstrapBundle.onReady();
    });
})(jQuery);
