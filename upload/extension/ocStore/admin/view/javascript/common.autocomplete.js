// Autocomplete
+function ($) {
    $.fn.autocomplete = function (option) {
        return this.each(function () {
            var $this = $(this);
            var $dropdown = $('#' + $this.attr('list'));

            this.timer = null;
            this.items = [];

            $.extend(this, option);

			// Transitionrun - start script
			if ('ontransitionrun' in window) {
				$this.one('transitionrun', function () {
					$this[0].blur();
					$this.unbind('transitionrun');
					$this.unbind('focus');
					this.request();
				});
			}

            // Focus
			$this.on('focus', function () {
				$this[0].blur();
				$this.unbind('focus');
				if (!('ontransitionrun' in window)) {
					this.request();
				}
			});

            // Keydown
            $this.on('input', function (e) {
                this.request();

                var value = $this.val();

                if (value && this.items[value]) {
                    this.select(this.items[value]);
                }
            });

            // Request
            this.request = function () {
                clearTimeout(this.timer);

                this.timer = setTimeout(function (object) {
                    object.source($(object).val(), $.proxy(object.response, object));
                }, 1, this);
            };

            // Response
            this.response = function (json) {
                var html = '';
                var category = {};
                var name;
                var i = 0, j = 0;

                if (json.length) {
                    for (i = 0; i < json.length; i++) {
                        // update element items
                        this.items[json[i]['label']] = json[i];

                        if (!json[i]['category']) {
                            // ungrouped items
                            html += '<option>' + json[i]['label'] + '</option>';
                        } else {
                            // grouped items
                            name = json[i]['category'];

                            if (!category[name]) {
                                category[name] = [];
                            }

                            category[name].push(json[i]);
                        }
                    }

                    for (name in category) {
                        for (j = 0; j < category[name].length; j++) {
                            html += '<option value="' + category[name][j]['label'] + '">' + name + '</option>';
                        }
                    }
                }

                $dropdown.html(html);
            };
        });
    };
}(jQuery);