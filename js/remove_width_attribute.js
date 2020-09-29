(function ($, wp, _) {
  if (!wp.media.events) {
    return;
  }
  wp.media.events.on('editor:image-update', function (options) {
    var editor = options.editor,
    dom = editor.dom,
    image = options.image;
    dom.setAttribs(image, {
      'width': null,
      'height': null
    });
  });
})(jQuery, wp, _);
