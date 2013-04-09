
var ajax_url = 'ajax.php';

var $message_container;

$(function init_message_container () {
  $message_container = jq_element('div').addClass('message-container').appendTo('body');
});

function test_groups (people, groups) {
  if (groups === null) {
    display_message('error', 'failed to find a way to allocate people in groups');
    return false;
  }
  var test_groups = Array.prototype.concat.apply(Array.prototype, groups);
  if (test_groups.length !== Object.keys(people).length) {
    display_message('error', 'Invalid group allocation. Failed!');
    console.warn(test_groups.length, Object.keys(people).length);
  }
  for (var eid in people) {
    if (test_groups.indexOf(eid) === -1) {
      display_message('error', 'Failed to find a way to allocate people in groups');
      console.log('Missing:', eid, 'groups:', groups, 'people:', people);
      return false;
    }
  }
  for (var i=0; i<test_groups.length; ++i) {
    var eid = test_groups[i];
    var group_no = Math.floor(i/groups[0].length);
    if (!people[eid].teammates) {
      continue;
    }
    for (var j=0; j<groups[group_no].length; ++j) {
      if (groups[group_no] != eid && people[eid].teammates.indexOf(groups[group_no]) !== -1) {
        display_message('error', 'Error while creating groups');
        return false;
      }
    }
  }
  return true;
}

function randomly_allocate_in_groups (group_size, people, infinite_recursion) {
  infinite_recursion = infinite_recursion || 0;
  var num_people = Object.keys(people).length;
  var num_groups = Math.ceil(num_people / group_size);
  var remaining = Object.keys(people).sort(function () { return 0.5 - Math.random(); });
  var groups = [];
  var check_teammates = function check_teammates (eid, group) {
    for (var i=0; i<group.length; ++i) {
      if (people[eid].teammates.indexOf(group[i]) > -1) {
        return false;
      }
    }
    return true;
  }
  for (var i=0; i<num_groups; ++i) {
    groups[i] = [];
    for (var j=0; j<group_size && i*group_size+j<num_people; ++j) {
      var eid = null;
      for (var k=0; k<remaining.length; ++k) {
        if (check_teammates(remaining[k], groups[i])) {
          eid = remaining[k];
          remaining.splice(k, 1)
          break;
        }
      }
      if (eid === null) {
        if (infinite_recursion > 20) {
          console.warn('Unable to allocate people in groups');
          return null;
        }
        return randomly_allocate_in_groups(group_size, people, infinite_recursion + 1);
      }
      groups[i].push(eid);
    }
  }
  return groups;
}


function get_advanced_people (exclude, callback) {
  exclude = exclude || [];
  callback = callback || function _no_callback () {};
  var people;     // maps eid -> person information
  var teammates;  // maps eid -> all his previous teammates
  var groups;     // maps eid -> all the groups he was in
  var excluded_eids = {}; // will be defined after the first ajax
  $.get(ajax_url, {
    action: 'get_all',
  }, function (response) {
    if (!response.result) {
      display_message('error', response.error);
      console.warn(response);
      return;
    }
    people = {};
    teammates = {};
    groups = {};
    for (var i=0; i<response.records.length; ++i) {
      if (exclude.indexOf(response.records[i].account) > -1) {
        excluded_eids[response.records[i].eid] = true
        continue;
      }
      people[response.records[i].eid] = response.records[i];
      teammates[response.records[i].eid] = [];
      groups[response.records[i].eid] = {};
    }
    $.get(ajax_url, {
      action: 'get_all_groups'
    }, function (response) {
      if (!response.result) {
        display_message('error', response.error);
        console.warn(response);
        return;
      }
      var together = {};
      for (var i=0; i<response.records.length; ++i) {
        var row = response.records[i];
        if (excluded_eids[row.eid]) {
          continue;
        }
        var index_name = row.phase + '_' + row.number;
        together[index_name] = together[index_name] || [];
        together[index_name].push(row.eid);
        groups[response.records[i].eid][row.phase] = row.number;
      }
      for (var key in together) {
        for (var i=0; i<together[key].length; ++i) {
          teammates[together[key][i]] = teammates[together[key][i]].concat(together[key]);
        }
      }
      for (var key in people) {
        people[key].teammates = teammates[people[key].eid];
        people[key].groups = groups[people[key].eid];
      }
      callback(people);
    });
  });
}

function randomly_allocate_codes (groups, code_allocations, people, infinite_recursion) {
  infinite_recursion = infinite_recursion || 0;
  if (infinite_recursion > 20) {
    console.warn('Unable to allocate people in groups');
    return null;
  }
  var original_groups = groups.concat('');
  groups = groups.map(function (g) { return g.map(function (eid) {return people[eid];}); });
  var remaining = groups.sort(function () { return 0.5 - Math.random(); });
  var check_groups = function check_groups (a, b) {
    for (var i=0; i<a.length; ++i) {
      if (!a[i].groups || a[i].groups.length === 0) {
        continue;
      }
      for (var j=0; j<b.length; ++j) {
        if (!b[j].groups || b[j].groups.length === 0) {
          continue;
        }
        for (var phase in b[j].groups) {
          if (a[i].groups[phase] == b[j].groups[phase]) {
            return false;
          }
        }
      }
    }
    return true;
  };
  var allocations = {};
  for (var i=0; i<remaining.length; ++i) {
    for (var j=0; j<groups.length; ++j) {
      if (allocations[j] !== undefined) {
        continue;
      }
      console.log(remaining[i]);
      if (check_groups(remaining[i], groups[j])) {
        break;
      }
    }
    if (j === groups.length) {
      return randomly_allocate_codes(original_groups, code_allocations, people, infinite_recursion + 1);
    }
    allocations[j] = i;
  }
  return allocations;
}

function get_code_groups (phases, callback) {
  phases = phases || ['xp1', 'xp2', 'xp3'];
  callback = callback || function  _no_callback () {};
  $.get(ajax_url, {
    action: 'get_all_groups',
  }, function (response) {
    if (!response.result) {
      display_message('error', response.error);
      console.warn(response);
      return;
    }
    var groups = {};
    for (var i=0; i<response.records.length; ++i) {
      var person = response.records[i];
      if (phases.indexOf(person.phase) === -1) {
        continue;
      }
      groups[person.number] = groups[person.number] || [];
      groups[person.number].push(person.eid);
    }
    callback(groups);
  });
}

function display_message (type, content) {
  var $message = get_message(type, content);
  $message.hide().fadeIn(400);
  setTimeout(function () {
    $message.fadeOut(400, function () {
      $message.remove();
    });
  }, 5000 + (content.length / 100) * 3000);
  $message_container.append($message);
  $message_container[0].scrollTop = $message_container[0].scrollHeight;
  return $message;
}

function get_message (type, content) {
  return jq_element('div').
            addClass('alert alert-'+type).
            html(content).
            append('<button type="button" class="close" data-dismiss="alert">x</button>');
}

function jq_element (type) {
  return $(document.createElement(type));
}