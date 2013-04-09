var faces = {};
var roulette;
var all_people;

var $people;

$(function () {
  $people = $('#people');
  get_advanced_people(admins, function (people_map) {
    all_people = $.extend({}, people_map);
    faces = {};
    var images_left = Object.keys(people_map).length;
    var test = 0;
    for (var eid in people_map) {
      (function (person) {
        faces[person.eid] = new Face(person);
        faces[person.eid].
          get_element().
          attr('id', 'face-'+person.eid).
          attr('eid', person.eid);
        faces[person.eid].onload(function photo_loaded (face) {
            $people.append(face.get_element());
            person.photo_element = face.get_element().find('img');
            if (--images_left <= 0) {
              $(document).one('keydown', function () {
                people_loaded(function allocated_new_groups (new_groups) {
                  console.log(new_groups);
                });
              });
            }
          });
        })(people_map[eid]);
    };
  });
});

var in_group = {};
function people_loaded (callback, new_groups) {
  new_groups = new_groups || [];
  var groups = randomly_allocate_in_groups(2, all_people);
  if (!test_groups(all_people, groups)) {
    console.warn('ERROR!');
    return;
  }
  for (var i=0; i<groups.length; ++i) {
    in_group[groups[i][0]] = groups[i][1];
    in_group[groups[i][1]] = groups[i][0];
  }
  delete in_group['undefined'];
  var left = $people.find('.face').length;
  var recursive_callback = function (a, b) {
    console.log(a, '+', b);
    new_groups.push([a,b]);
    left -= 2;
    if (left >= 2) {
      make_group(recursive_callback, new_groups);
    } else {
      callback(new_groups);
    }
  }
  make_group(recursive_callback);
}

function make_group (callback) {
  callback = callback || function _no_callback () {};
  var people = $('#people .face').map(function () { return $(this).data('person'); }).get();
  select_person(function () {
    var wrapper = jq_element('div');
    var chosen = $('#people .chosen');
    var teammate = jq_element('img').addClass('wrapper-face').attr('src', unknown_face_url);
    var person = chosen.data('person');
    var unique_people = people.filter(function (v) {
      return person.teammates.indexOf(v.eid) === -1
    });
    roulette = new Roulette();
    roulette.set_people(unique_people);
    var pointer = jq_element('img');
    wrapper.
      addClass('roulette-wrapper').
      append(
        pointer,
        roulette.get_element(),
        jq_element('div').addClass('outer-center').append(
          jq_element('table').
            addClass('info-panel inner-center').
            append(
              jq_element('tr').append(
                jq_element('td').append(new Face(person).get_element().addClass('wrapper-face').attr('src', chosen.attr('src'))),
                jq_element('td').append(jq_element('span').addClass('separator').html('+')),
                jq_element('td').append(teammate)
              )
            )
        )
      );

    $people.children().hide();
    $people.append(wrapper);
    pointer.
      on('load', function () {
        $(this).css({
          position: 'absolute',
          zIndex: 1000,
          top: -10,
          left: (wrapper.width() - this.width) / 2 - 10,
          maxHeight: 30
        });
      }).
      attr('src', arrow_url);
    wrapper.hide().fadeIn();

    var teammates_faces = $();
    person.teammates.forEach(function (eid) {
      teammates_faces = teammates_faces.add($('#face-'+eid));
    });
    teammates_faces.fadeIn();
    // var angle = 1440 + Math.floor(Math.random() * 1440);
    var angle;
    var ratio = 360 / unique_people.length;
    for (var i=0; i<unique_people.length; ++i) {
      angle = 1 + i * ratio + Math.floor(Math.random() * (ratio-2));
      var index = roulette_get_index_at_angle(angle,unique_people.length);
      if (unique_people[index].eid === in_group[person.eid]) {
        break;
      }
    }
    angle += 1440 + (Math.floor(Math.random() * 5) * 360);
    var old_index = -1;
    roulette.spin(angle, null, function step_callback (now, fx) {
      var index = roulette_get_index_at_angle(now, unique_people.length);
      if (index == old_index) {
        return;
      }
      old_index = index;
      var new_teammate = new Face(unique_people[index]).get_element().addClass('wrapper-face');
      teammate.replaceWith(new_teammate);
      teammate = new_teammate;
    }, function choose_teammate () {
      var index = roulette_get_index_at_angle(angle, unique_people.length);
      $people.children().show();
      $people.
        find('#face-'+person.eid+', #face-'+unique_people[index].eid).
        slideUp(result_time - 100, function () {
          $(this).remove();
        });
      setTimeout(function () {
        roulette.get_element().parent().remove();
        delete roulette;
        callback(person.account, unique_people[index].account);
      }, result_time);
    });
  });
}

function roulette_get_index_at_angle (angle, num_slices) {
  var ratio = 360 / num_slices;
  return Math.floor((360 - angle % 360) / ratio) % num_slices;
}

function select_person (callback) {
  callback = callback || function _no_callback () {};
  var num_people = $('#people .face').length;
  $people.animate({
    some_random_property: num_people + Math.random() * num_people * 8
  }, {
    duration: select_time,
    easing: 'easeOutCirc',
    step: function person_pick_step (now, fx) {
      $('#people .chosen').removeClass('chosen');
      $('#people .face').eq(Math.floor(now) % num_people).addClass('chosen');
    },
    complete: function () {
      var chosen = $('#people .chosen');
      var remove = function remove_class (time) {
        chosen.removeClass('chosen');
      }
      var add = function add_class (time, callback) {
        remove();
        setTimeout(function () { chosen.addClass('chosen'); }, time);
      }
      var sum = 0;
      for (var i=0; i<7; ++i) {
        var time = 200;
        (function (time, sum) {
          setTimeout(function() { add(time); }, sum);
        })(time, sum);
        sum += (time * 2);
      }
      setTimeout(callback, sum);
    }
  });
}

var Face = function Face (person) {
  var $element = jq_element('span');
  $element.
    data('person', person).
    addClass('face').
    append(
      jq_element('img').attr('src', DIR_PHOTOS + '/' + person.eid + '.jpg'),
      jq_element('div').addClass('name').html(person.account)
    );
  return {
    person: person,
    element: $element,
    get_element: function get_element () {
      return this.element;
    },
    onload: function onload (callback) {
      var that = this;
      that.element.find('img').on('load', function () {
        callback(that, event);
      });
    }
  };
}

var Roulette = function Roulette () {
  var Line = function Line (x, y, angle) {
    this.x = x;
    this.y = y;
    this.angle = angle;
    this.point_at = function point_at (length) {
      return {
        x: Math.cos(this.angle) * length + this.x,
        y: Math.sin(this.angle) * length + this.y
      }
    }
  }
  return {
    canvas: null,
    context: null,
    people: null,
    element: null,
    radius: null,
    set_people: function set_people (people) {
      this.people = people;
      this.generate();
      return this;
    },
    get_element: function get_element () {
      return this.element;
    },
    spin: function spin (angle, duration, step_callback, callback) {
      angle = angle || 2300;
      duration = duration || spin_time;
      callback = callback || function _no_callback () {};
      step_callback = step_callback || function _no_callback () {};
      var that = this;
      var $elem = $(this.element);
      $elem.animate({
        rotation: angle
      }, {
        duration: duration,
        easing: 'easeOutCirc',
        step: function wheel_rotation_step (now, fx) {
          $elem.css({
            transform: 'rotateZ('+now+'deg)'
          });
          step_callback(now, fx);
        },
        complete: callback
      });
      return that;
    },
    generate: function generate (diameter, rotation) {
      diameter = diameter || 800;
      rotation = rotation || 0;
      rotation += 270;

      var that = this;
      that.canvas = document.createElement('canvas');
      that.canvas.width = diameter;
      that.canvas.height = diameter;
      that.context = that.canvas.getContext('2d');
      that.element = null;
      that.radius = diameter / 2;
      var shift = 10;
      diameter -= shift;
      var origin = {x:diameter / 2 + shift/2, y:diameter / 2 + shift/2};
      var circumference = 2 * Math.PI * that.radius;
      var slices = that.people.length;
      var slice_width = circumference / slices;
      var get_slice_angle = function get_slice_angle (number) {
        return rotation + 360 * (number / slices);
      }

      that.context.clearRect(0, 0, that.canvas.width, that.canvas.height);

      // draw border
      that.context.beginPath();
      that.context.arc(origin.x, origin.y, that.radius, 0, 2 * Math.PI);
      that.context.stroke();

      for (var i=0; i<slices; ++i) {
        that.context.beginPath();
        that.context.fillStyle = i == 0 ? '#fda42c' : (i % 2 == 0 ? '#fff' : '#88B6E9');
        that.context.moveTo(origin.x, origin.y);
        that.context.arc(
            origin.x,
            origin.y,
            that.radius,
            to_radians(get_slice_angle(i)),
            to_radians(get_slice_angle(i+1))
          );
        that.context.fill();
        (function (i) {
          var angle = get_slice_angle(i);
          var r_angle = to_radians(angle);
          var line_start = new Line(origin.x, origin.y, to_radians(get_slice_angle(i+1)));
          var slice_start = line_start.point_at(that.radius);
          // draw line
          that.context.beginPath();
          that.context.moveTo(origin.x, origin.y);
          that.context.lineTo(slice_start.x, slice_start.y);
          that.context.stroke();
          // insert image
          var img = that.people[i].photo_element;
          var old_css = {
            'max-width': img.css('max-width'),
            'max-height': img.css('max-height')
          };
          img.css({
            'max-width': Math.min(Math.floor(slice_width), 80) - 20,
            'max-height': Math.min(Math.floor(slice_width), 120) - 10
          });
          var mid_angle = (get_slice_angle(i)+get_slice_angle(i+1)) / 2;
          var line_middle = new Line(origin.x, origin.y, to_radians(mid_angle));
          var img_pos = line_middle.point_at(that.radius - 10);
          that.context.save();
          that.context.translate(img_pos.x, img_pos.y);
          that.context.rotate(to_radians(mid_angle + 90));
          that.context.drawImage(img[0], -img[0].width / 2, 0, img[0].width, img[0].height);
          that.context.restore();
          img.css(old_css);

          var text_pos = line_middle.point_at(that.radius - 120);
          that.context.save();
          that.context.translate(text_pos.x, text_pos.y);
          that.context.rotate(to_radians(angle + 92));
          that.context.fillStyle = "#000";
          that.context.font="20px Georgia";
          that.context.fillText(i, -that.context.measureText(i).width / 2, 0);
          that.context.restore();
        })(i);
      }
      that.element = jq_element('img').attr({
        src: that.canvas.toDataURL(),
        'class': 'roulette'
      });
      return that;
    }
  }
}

function to_degrees (angle) {
  return angle * (180 / Math.PI);
}

function to_radians (angle) {
  return angle * (Math.PI / 180);
} 
