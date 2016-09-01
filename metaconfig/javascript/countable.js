(function ($) {
    $.entwine('ss', function ($) {
        var onkey = function (e) {
            $this = $(this);
            $($this.data('counter')).text($this.val().length);
        };

        $('textarea.countable').entwine({
            onmatch: function (e) {
                var $this = $(this);
                $this.after('<div class="countable-counter" id="Counter_' + $this.attr('id') + '">' + $this.val().length + '</div>');
                $this.data('counter', '#Counter_' + $this.attr('id'));
            },
            onkeyup: onkey,
            onkeydown: onkey,
            onchange: onkey
        });

        $('input.text.countable').entwine({
            onmatch: function (e) {
                var $this = $(this);
                $this.after('<div class="countable-counter input" id="Counter_' + $this.attr('id') + '">' + $this.val().length + '</div>');
                $this.data('counter', '#Counter_' + $this.attr('id'));
            },
            onkeyup: onkey,
            onkeydown: onkey,
            onchange: onkey
        });
    });
})(jQuery);