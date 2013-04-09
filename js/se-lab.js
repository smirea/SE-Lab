
$(function () {
  var $evaluation_form = $('#evaluation-form');

  var progress_classes = [
    'progress-danger',
    'progress-warning',
    'progress-success',
    'progress-info'
  ];

  $evaluation_form.
    on('submit', function (event) {
      $.get($(this).attr('action'), $(this).serialize(), function (response) {
        if (!response.result) {
          display_message('error', 'Failed to submit evaluation: ' + response.error);
          return;
        }
        display_message('success', 'Your evaluation has been recorded');
      });
      event.preventDefault();
    }).
    on('change.update-score', 'select', function () {
      var column = $(this).attr('column');
      var value = $evaluation_form.find('select[column="'+column+'"]').
                    map(function (object) { return Number($(this).val()); }).
                    get().
                    reduce(function (a, b) { return a + b; });
      value = value / 20 * 100;
      $evaluation_form.
        find('.progress[column="'+column+'"]').
        find('.bar').
        css('width', value + '%').
        find('output').
          val(Math.floor(value) + '%').
          end().
        parent().
        removeClass(progress_classes.join(' ')).
        addClass(progress_classes[Math.floor(value / 30)]);
    });

    $evaluation_form.find('tr').eq(1).find('select').trigger('change.update-score');
});

function jq_element (type) {
  return $(document.createElement(type));
}