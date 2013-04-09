var jpeople_url = 'http://majestix.gislab.jacobs-university.de/jPeople/ajax.php';
var ajax_url = 'ajax.php';

var people;

$(function () {

  var $new_groups_form = $('#generate-new-groups');

  $(document).on('click', '.action[action]', function (event) {
    var action = $(this).attr('action');
    if (!actions[action]) {
      console.warn('Unknown action', action);
      return;
    }
    actions[action].apply(this, $.makeArray(arguments));
    event.preventDefault();
  });

  $new_groups_form.on('submit.generate_new_groups', function (event) {
    event.preventDefault();
    var group_size = Number($(this).find('[name="group-size"]').val());
    var phase_name = $(this).find('[name="phase"]').val();
    var exclude = $(this).find('[name="exclude"]').val().split(',');
    var excluded_eids = {}; // will be defined after the first ajax

    get_advanced_people(exclude, function (new_people) {
      people = new_people;
      var groups = randomly_allocate_in_groups(group_size, new_people);
      if (!test_groups(new_people, groups)) {
        return;
      }
      $('#new-groups').empty().show();
      for (var i=0; i<groups.length; ++i) {
        $('#new-groups').append(
          group_element(phase_name, i+1, groups[i].map(function (val) { return new_people[val]; }))
        );
      }
      $('#new-groups .group').disableSelection().sortable({
        connectWith: '.group',
        items: '.face',
        placeholder: 'ui-state-highlight'
      });
      set_new_groups_form_state(false);
    });
  });

  $new_groups_form.on('click.clear-new-groups', '#clear-new-groups:not(:disabled)', function (event) {
    $('#new-groups').empty().hide();
    set_new_groups_form_state(true);
  });

  $new_groups_form.on('click.set-new-groups', '#set-new-groups:not(:disabled)', function (event) {
    event.preventDefault();
    var groups = {};
    $('#new-groups .group').each(function () {
      groups[$(this).attr('group-number')] = $(this).find('.face[eid]').map(function(){ return $(this).attr('eid'); }).get();
    });
    $.get(ajax_url, {
      action: 'set_new_groups',
      phase: $('#new-groups .group').eq(0).attr('group-phase'),
      groups: groups
    }, function (response) {
      if (!response) {
        console.warn(response);
        display_message('error', response.error);
        return;
      }
      display_message('success', 'Groups successfully set, refresh page to interact with them bellow or continue changing here and then set them again :)');
    });
  });

  $('#groups, #new-groups').on('sortstart.highlight-previous-teammates', '.group', function (event, ui) {
    var eid = ui.item.attr('eid');
    if (!people[eid]) {
      console.warn('No teammates loaded to highlight previous teammates;');
      return;
    }
    var eids = people[eid].teammates.map(function(v){ return '.face[eid="'+v+'"]'; }).join(', ');
    $(event.delegateTarget).find(eids).addClass('teammates');
  })

  $('#groups, #new-groups').on('sortstop.highlight-previous-teammates', '.group', function (event, ui) {
    $(event.delegateTarget).find('.teammates').removeClass('teammates');
  });

  $('#groups').on('sortreceive.update-database', '.group', function (event, ui) {
    $.get(ajax_url, {
      action: 'add_to_group',
      eid: ui.item.attr('eid'),
      phase: ui.item.attr('group-phase'),
      number: $(this).attr('group-number')
    }, function (response) {
      if (!response) {
        console.warn(response);
        display_message('error', response.error);
        ui.sender.prepend(ui.item);
        return;
      }
    });
  });

  $.get(ajax_url, {
    action: 'get_people_from_phase',
    phase: phase
  }, function (response) {
    if (!response.result) {
      console.warn(response);
      display_message('error', response.error);
      return;
    }
    var groups = {};
    var max = 0;
    var in_groups = response.in_groups;
    for (var i=0; i<in_groups.length; ++i) {
      in_groups[i].group_number = Number(in_groups[i].group_number);
      max = Math.max(max, in_groups[i].group_number);
      groups[in_groups[i].group_number] = groups[in_groups[i].group_number] || [];
      groups[in_groups[i].group_number].push(in_groups[i]);
    }
    ++max;
    groups[max] = [];
    groups['NONE'] = response.without_groups;
    for (var group_number in groups) {
      $('#groups').prepend(group_element(phase, group_number, groups[group_number]));
    }
    $('#groups .group').disableSelection().sortable({
      connectWith: '.group',
      items: '.face',
      placeholder: 'ui-state-highlight'
    });
  });

  $('#add-user').on('keydown.add-user', function on_add_user (event) {
    if (event.keyCode === 13) {
      event.preventDefault();
      $.get(jpeople_url, {
        action: 'fullAutoComplete',
        str: $(this).val()
      }, function (result) {
        $('#users').
          empty().
          append(
            result.records.map(
              function (record) { return face_element(record, 'add'); }
            )
          );
      });
    }
  });
});

function set_new_groups_form_state (value) {
  $('#clear-new-groups,#set-new-groups').attr('disabled', value);
  $('#generate-new-groups').find('input[type="submit"], [name="group-size"], [name="phase"], [name="exclude"]').attr('disabled', !value);
}


var actions = {
  remove: function remove_from_course () {

  },
  group: function add_to_group () {

  },
  add: function add () {
    var $target = $(this).data('target');
    $.get(ajax_url, {
      action: 'add',
      data: $target.data('data')
    }, function (response) {
      if (!response.result) {
        display_message('error', response.error);
        console.warn(response);
        return;
      }
      $target.fadeOut(800, function delete_person () {
        $target.remove();
      });
    });
  }
};

function action_element (action_name) {
  return jq_element('a').
    attr('action', action_name).
    attr('href', 'javascript:void(0)').
    addClass('action').
    html(action_name);
}

function group_element (phase, number, people) {
  people = people || [];
  return jq_element('div').
          addClass('group').
          attr('group-number', number).
          attr('group-phase', phase).
          append(jq_element('h3').html('Group #' + number)).
          append(
            $.map(people, function (value, key) {
              return face_element(value).attr('group-phase', phase);
            })
          );

}

function face_element (data, actions) {
  if (typeof actions == 'string') {
    actions = actions.split(',').map(action_element).reduce(function ($a, $b) { return $a.add($b); }, $());
  } else {
    actions = actions || $();
  }
  var $wrapper = jq_element('li');
  $wrapper.
    data('data', data).
    addClass('face').
    attr('eid', data.eid).
    append(
      jq_element('table').append(
        jq_element('tr').append(
          '<td class="photo"><img src="'+data.photo_url+'" alt="photo" /></td>' +
          '<td class="data">' +
            '<h3>' + data.fname+' '+data.lname + '</h3>' +
            '<h4>' + data.description.replace(/[(,].*$/g, '') + '</h4>' +
            '<div class="country">' + '<img src="'+data.flag_small_url+'" alt="flag" />' + data.country + '</div>' +
            '<div class="email"><a href="mailto:' + data.email + '">' + data.email + '</a>' +
          '</td>'
          ,
          jq_element('td').
            addClass('actions').
            append(actions)
        )
      )
    );
  actions.data('target', $wrapper);
  if (actions.length === 0) {
    $wrapper.find('.actions').hide();
  }
  return $wrapper;
}