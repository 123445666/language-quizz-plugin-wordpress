(function ($) {
  'use strict';
  $(function () {
    $('#upload_image').click(open_custom_media_window);

    function open_custom_media_window() {
      if (this.window === undefined) {
        this.window = wp.media({
          title: 'Insert Image',
          library: {
            type: 'image'
          },
          multiple: false,
          button: {
            text: 'Insert Image'
          }
        });
        var self = this;
        this.window.on('select', function () {
          var response = self.window.state().get('selection').first().toJSON();

          $('.wp_attachment_id').val(response.id);
          $('.image').attr('src', response.sizes.thumbnail.url);
          $('.image').show();
        });
      }
      this.window.open();
      return false;
    }
  });
})(jQuery);
