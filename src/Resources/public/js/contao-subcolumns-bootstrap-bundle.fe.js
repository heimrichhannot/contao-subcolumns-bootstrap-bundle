var SubColumnsBootstrapBundle = {
    onReady: function () {
        this.initColFull();
    },
    initColFull: function () {
        this.doInitColFull('both');
        this.doInitColFull('left');
        this.doInitColFull('right');
    },
    doInitColFull: function (mode) {
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

        var cols = document.querySelectorAll(colClass);

        if (cols.length < 1)
            return;

        window.addEventListener('resize', function () {
            makeFullScreen();
        }, true);

        function makeFullScreen() {
            var w = window,
                d = document,
                e = d.documentElement,
                g = d.getElementsByTagName('body')[0],
                width = w.innerWidth || e.clientWidth || g.clientWidth,
                firstContainer = document.querySelectorAll('.container')[0],
                containerWidth = firstContainer.clientWidth;

            containerWidth -= parseFloat(window.getComputedStyle(firstContainer).getPropertyValue('padding-right'));
            containerWidth -= parseFloat(window.getComputedStyle(firstContainer).getPropertyValue('padding-left'));

            switch (mode) {
                case 'left':
                    for (var i = 0, max = cols.length; i < max; i++) {
                        cols[i].style.marginLeft = ((width - containerWidth) / -2) + 'px';
                    }

                    break;
                case 'right':
                    for (var i = 0, max = cols.length; i < max; i++) {
                        cols[i].style.marginRight = ((width - containerWidth) / -2) + 'px';
                    }
                    break;
                case 'both':
                    for (var i = 0, max = cols.length; i < max; i++) {
                        cols[i].style.marginLeft = ((width - containerWidth) / -2) + 'px';
                        cols[i].style.marginRight = ((width - containerWidth) / -2) + 'px';
                    }
                    break;
            }
        }

        makeFullScreen();
    }
};

document.addEventListener("DOMContentLoaded", function (event) {
    SubColumnsBootstrapBundle.onReady();
});