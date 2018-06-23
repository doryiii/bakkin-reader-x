// AngularJS glue
(function(){
    var app = angular.module("bakkinreader", ["ngCookies"]);
    app.controller('mainCtrl', function($scope, $window, $cookies) {

        // TODO: This is a very strange-looking hack...
        $scope.getNextNextImg = function() {
            if ($scope.next_page.chapter) {
                var next_page = get_next_page($scope.current_series,
                                            $scope.next_page.volume,
                                            $scope.next_page.chapter,
                                            $scope.next_page.page);
                if (next_page.page)
                    return next_page.chapter.pages[next_page.page];
            }
            return null;
        };

        // cookie stuffs
        $scope.set_progress = function() {
            if ($scope.current_volume && $scope.current_chapter) {
                var now = new $window.Date();
                var exp = new $window.Date(now.getFullYear() + 2,
                                            now.getMonth(), now.getDate());
                $cookies.put("bakkin_prog" + $scope.current_series.dir,
                                location.hash, {expires: exp});
            }
        };
        $scope.get_progress = function(seriesdir) {
            return $cookies.get("bakkin_prog" + seriesdir);
        };
    });
})();

// Helpers

function get_prev_chapter(series, volume, chapter) {
    var vol_i = series.volmap[volume.dir];
    var chap_i = volume.chapmap[chapter.dir];
    var vol = null;
    var chap = null;
    var page = null;

    if (chap_i > 0) {
        vol = volume;
        chap = vol.chapters[chap_i - 1];
        page = chap.pages.length - 1;
    } else if (vol_i > 0) {
        vol = series.volumes[vol_i - 1];
        chap = vol.chapters[vol.chapters.length - 1];
        page = chap.pages.length - 1;
    }
    return {"volume": vol, "chapter": chap, "page": page};
}

function get_next_chapter(series, volume, chapter) {
    var vol_i = series.volmap[volume.dir];
    var chap_i = volume.chapmap[chapter.dir];
    var vol = null;
    var chap = null;

    if (chap_i < volume.chapters.length - 1) {
        vol = volume;
        chap = vol.chapters[chap_i + 1];
    } else if (vol_i < series.volumes.length - 1) {
        vol = series.volumes[vol_i + 1];
        chap = vol.chapters[0];
    }
    return {"volume": vol, "chapter": chap, "page": 0};
}

function get_prev_page(series, volume, chapter, page) {
    if (page > 0) {
        return {"volume": volume,
                "chapter": chapter,
                "page": page - 1};
    } else {
        return get_prev_chapter(series, volume, chapter);
    }
}

function get_next_page(series, volume, chapter, page) {
    if (page < chapter.pages.length - 1) {
        return {"volume": volume,
                "chapter": chapter,
                "page": page + 1};
    } else {
        return get_next_chapter(series, volume, chapter);
    }
}

function imgDone() {
    var preloader = $("#preloader img");
    var viewer = $("#pageview img");

    // If the preloader is loading the main image, then move the img to
    // the main viewer, then setup viewport size for next page previewer
    if (preloader.attr("data-type") == "current") {
        viewer.attr("src", preloader.attr("src"));
        viewer.attr("onload", '$("#pageview #img-container")' +
                                '.width($("#pageview img").width())');
        $(window).resize(function() {
            $("#pageview #img-container").width($("#pageview img").width());
        });
        viewer.removeClass("preview");
        preloader.attr("data-type", "preload");
    }

    // Set up preloading of the next page
    if (preloader.attr("data-next") != "") {
        var url = preloader.attr("data-next");
        preloader.attr("data-next", preloader.attr("data-next-next"));
        preloader.attr("data-next-next", "");
        preloader.attr("src", url);
    }
}

$(document).ready(function() {
    // More AngularJS glue
    var element = angular.element($("body"));
    var controller = element.controller();
    var scope = element.scope();
    scope.current_series = null;
    scope.current_volume = null;
    scope.current_chapter = null;
    scope.current_page = 0;

    // Initial data querying
    // Trigger hashchange in case people went here with direct hashes
    $.get("main.php")
    .done(function(response) { scope.$apply(function() {
        scope.series = response;

        // build the map from volume/chapter dir -> index
        // the whole purpose of this is readable and persistent URLs
        // ...#m=OMRK&v=vol02&c=ch20, instead of
        // ...#m=1&v=2&c=22
        for (let manga in scope.series) {
            scope.series[manga]["volmap"] = [];
            scope.series[manga].volumes.forEach(function (vol, i) {
                scope.series[manga].volmap[vol.dir] = i;

                vol["chapmap"] = [];
                vol.chapters.forEach(function (chap, j) {
                    vol.chapmap[chap.dir] = j;
                });
            });
        }

        // go to the hash people passed in URL, if any
        setTimeout(() => $(window).trigger('hashchange'), 0);
    })})
    .fail(function(response) { scope.$apply(function() {
        alert("Something went wonky with the reader! Refresh later.");
    })});

    // Handle arrow keys (prev/next img)
    $(document).keydown(function(e){
        var scope = angular.element($("body")).scope();

        // don't wanna handle any event with modifier keys
        if (e.shiftKey || e.ctrlKey || e.altKey || e.metaKey)
            return true;

        // left
        if (e.keyCode == 37) {
            if (scope.prev_page.chapter) {
                location.hash = "#m=" + scope.current_series.dir +
                                "&v=" + scope.prev_page.volume.dir +
                                "&c=" + scope.prev_page.chapter.dir +
                                "&p=" + scope.prev_page.page;
            } else {
                location.hash = "#m=" + scope.current_series.dir;
            }
            return false;
        // right
        } else if (e.keyCode == 39) {
            if (scope.next_page.chapter) {
                location.hash = "#m=" + scope.current_series.dir +
                                "&v=" + scope.next_page.volume.dir +
                                "&c=" + scope.next_page.chapter.dir +
                                "&p=" + scope.next_page.page;
            } else {
                location.hash = "#m=" + scope.current_series.dir;
            }
            return false;
        }

        // don't handle any other key
        return true;
    });

    // The main event listener, handles window hash changes
    $(window).on('hashchange', function() {
        state = $.deparam.fragment(true);

        // series
        scope.current_series = ("m" in state) ?
            scope.series[state.m] : null;
        // volume
        if ("m" in state && "v" in state) {
            scope.current_volume_i = scope.current_series.volmap[state.v];
            scope.current_volume = scope.current_series.volumes
                [scope.current_volume_i];
        } else {
            scope.current_volume = null;
            scope.current_volume_i = null;
        }

        // chapter
        if ("m" in state && "v" in state && "c" in state) {
            scope.current_chapter_i = scope.current_volume.chapmap[state.c];
            scope.current_chapter = scope.current_volume.chapters
                [scope.current_chapter_i];

            // page
            scope.current_page = ("p" in state) ? state.p : 0;
            scope.set_progress();

            // next/previous page/chapter
            scope.next_chapter = get_next_chapter(scope.current_series,
                                                    scope.current_volume,
                                                    scope.current_chapter);
            scope.prev_chapter = get_prev_chapter(scope.current_series,
                                                    scope.current_volume,
                                                    scope.current_chapter);
            scope.next_page = get_next_page(scope.current_series,
                                            scope.current_volume,
                                            scope.current_chapter,
                                            scope.current_page);
            scope.prev_page = get_prev_page(scope.current_series,
                                            scope.current_volume,
                                            scope.current_chapter,
                                            scope.current_page);

        } else {
            scope.current_chapter = null;
            scope.current_chapter_i = null;
        }

        scope.$apply();

        if ("m" in state && "v" in state && "c" in state) {
            // Prepare the preloader. Display the thumb img while loading
            // Putting here since it needs Angular to populate the DOM
            $("#preloader img").attr("data-type", "current");
            $("#pageview img").attr("onload", "");
            $("#pageview img").attr("src",
                                    scope.current_chapter.thumbs[scope.current_page]);
            $("#pageview img").addClass("preview");

            // Scroll to the top of the page, or the image, depending on
            // whether we're on the first page of the chapter or not.
            var state = $.deparam.fragment(true);
            if ("p" in state && state.p != "0" && state.p != 0)
                $('html, body').animate(
                    {scrollTop: $('#scrollmark').offset().top}, 90);
            else
                $('html, body').animate(
                    {scrollTop: $('#chapter-view').offset().top}, 90);
        }
    });

    scope.$apply();
});
