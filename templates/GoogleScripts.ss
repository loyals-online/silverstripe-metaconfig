<% if $Analytics4ID %>
    <script async src="https://www.googletagmanager.com/gtag/js?id=$Analytics4ID"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '$Analytics4ID');
    </script>
<% end_if %>
<% if $TagManagerID %>
    <noscript>
        <iframe src="//www.googletagmanager.com/ns.html?id=$TagManagerID" height="0" width="0" style="display:none;visibility:hidden"></iframe>
    </noscript>
    <script type="text/javascript">
        (function (w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({'gtm.start': new Date().getTime(), event: 'gtm.js'});
            var f = d.getElementsByTagName(s)[0], j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src = '//www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', '$TagManagerID');
    </script>
<% else_if $AnalyticsID && not $Analytics4ID %>
    <script type="text/javascript">
        (function (i, s, o, g, r, a, m) {
            i['GoogleAnalyticsObject'] = r;
            i[r] = i[r] || function () {(i[r].q = i[r].q || []).push(arguments)}, i[r].l = 1 * new Date();
            a = s.createElement(o), m = s.getElementsByTagName(o)[0];
            a.async = 1;
            a.src = g;
            m.parentNode.insertBefore(a, m)
        })(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');

        ga('create', '$AnalyticsID', 'auto');
        ga('send', 'pageview');
    </script>
<% end_if %>