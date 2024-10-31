<?php
/*
  Plugin Name: SADESIGN Password Generator
  Plugin URI: http://sadesign.pro/services/ecode/
  Description: Password Generator shortcode
  Version: 0.1.0.2
  Author: Sadesign Studio
  Author URI: http://sadesign.pro
 */
define("PLUGIN_DIR", plugin_dir_path(__FILE__));
define("PLUGIN_URL", plugin_dir_url(__FILE__));

register_activation_hook(__FILE__, "pgen_activation_callback");

function pgen_activation_callback() {
  register_uninstall_hook(__FILE__, "pgen_uninstall_callback");
}

function pgen_uninstall_callback() {
  remove_shortcode("pgen");
}

function pgen_shortcode_register($atts, $content = null) {
  registerPluginScripts();
  pgenScript();
  $result = file_get_contents(PLUGIN_DIR . "/release.html");
  $result = str_replace("<?php echo PLUGIN_URL; ?>", PLUGIN_URL, $result);
  return $result;
}

function registerPluginScripts() {
  wp_enqueue_script("core", PLUGIN_URL . "/js/core.js");
  wp_enqueue_script("webtoolkit.base64", PLUGIN_URL . "/js/webtoolkit.base64.js");
  wp_enqueue_script("webtoolkit.md5", PLUGIN_URL . "/js/webtoolkit.md5.js");
  wp_enqueue_style("pgen-style", PLUGIN_URL . "/css/release.css", false, "1.0.0", "all");
}

// add_action( 'wp_enqueue_scripts', 'registerPluginScripts' );
add_action('wp_ajax_passcounter', 'passcounter_callback');
add_action('wp_ajax_nopriv_passcounter', 'passcounter_callback');

function passcounter_callback() {
  $countOld = get_option('passcounter');
  $countNew = $countOld + $_POST['count'];
  update_option('passcounter', $countNew);

  echo $countNew;
  die(); // this is required to return a proper result
}

add_shortcode("pgen", "pgen_shortcode_register");

// add_action( 'wp_head', 'pgenScript');
function pgenScript() {
  ?>
  <script type="text/javascript" id="loadpage">
    jQuery(function ($) {
      $(function () {
        var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
        var data = {
          action: 'passcounter',
          count: 0
        };
        $.post(ajaxurl, data, function (response) {
          $("#passwordsCounter").html(response);
          console.log(response);
        });
      });
    });
  </script>
  <script type="text/javascript" id="numericbox">
    jQuery(function ($) {
      $(function () {
        $(".numericbox").each(function (idx, elem) {
          var value = parseInt($(elem).attr("value"));
          var inputValue = $(elem).children("input.value");
          inputValue.val(value);
        });
        $(".numericbox[enabled=false]").each(function (idx, elem) {
          $(elem).children("input.value").attr("disabled", "disabled");
          $(elem).children("button.arrow").attr("disabled", "disabled");
        });
        $(".numericbox button.arrow").mousedown(function (e) {
          var numericBox = $(this).parent(".numericbox");
          var inputValue = $(this).siblings("input.value");
          var value = parseInt(numericBox.attr("value"));
          var minimum = parseInt(numericBox.attr("minimum"));
          var maximum = parseInt(numericBox.attr("maximum"));
          var step = parseInt(numericBox.attr("step"));
          if (value != inputValue.val())
            value = inputValue.val();
          if ($(this).hasClass("down"))
            step = -step;
          this.interval = setInterval(function () {
            value += step;
            if (value >= minimum && value <= maximum) {
              numericBox.attr("value", value);
              inputValue.val(value);
              numericBox.trigger("changevalue", [value]);
            }
          },
                  200);
        });

        $(".numericbox button.arrow").mouseup(function (e) {
          clearInterval(this.interval);
        });

        $(".numericbox button.arrow").click(function (e) {
          var numericBox = $(this).parent(".numericbox");
          var inputValue = $(this).siblings("input.value");
          var value = parseInt(numericBox.attr("value"));
          var minimum = parseInt(numericBox.attr("minimum"));
          var maximum = parseInt(numericBox.attr("maximum"));
          var step = parseInt(numericBox.attr("step"));
          if ($(this).hasClass("down"))
            step = -step;
          value += step;
          if (value >= minimum && value <= maximum) {
            numericBox.attr("value", value);
            inputValue.val(value);
            numericBox.trigger("changevalue", [value]);
          }
        });

        $(".numericbox input.value").change(function (e) {
          var numericBox = $(this).parent(".numericbox");
          var minimum = parseInt(numericBox.attr("minimum"));
          var maximum = parseInt(numericBox.attr("maximum"));
          var value = parseInt($(this).val());
          if (!value || value < minimum)
            value = minimum;
          if (value > maximum)
            value = maximum;
          numericBox.attr("value", value);
          $(this).val(value);
          numericBox.trigger("changevalue", [value]);
        });

        $(".numericbox input.value").keydown(function (e) {
          var key = e.charCode || e.keyCode || 0;
          return (
                  key == 8 ||
                  key == 9 ||
                  key == 46 ||
                  (key >= 37 && key <= 40) ||
                  (key >= 48 && key <= 57) ||
                  (key >= 96 && key <= 105) ||
                  key == 173 ||
                  key == 109
                  );
        });
      });
    });
  </script>
  <script type="text/javascript" id="checkbox">
    jQuery(function ($) {
      $(function () {
        $(".checkbox[enabled=true] .check,.checkbox[enabled=true] .text").click(function () {
          var state = $(this).parent().attr("state");
          switch (state) {
            case "unchecked":
              state = "checked";
              break;
            case "checked":
              state = "unchecked";
              break;
          }
          $(this).parent().attr("state", state);
          $(this).trigger("changestate", [state]);
        });
      });
    });
  </script>
  <script type="text/javascript" id="base-events">
    jQuery(function ($) {
      $(function () {
        $(".shuffle-rounds").bind("changevalue", function (e, value) {
          var addText = $(this).children(".add-text");
          if ((value % 100) > 10 && (value % 100) < 20) {
            addText.html("раз");
            return false;
          }
          switch (value % 10) {
            case 2:
            case 3:
            case 4:
              addText.html("раза");
              break;
            default:
              addText.html("раз");
              break;
          }
        });

        $(".useshuffle").bind("changestate", function (e, state) {
          var shuffleopt = $(".shuffle-options");
          if (state == "checked") {
            shuffleopt.slideDown(500);
          } else {
            shuffleopt.slideUp(500);
          }
        });

        $(".usesigns").bind("changestate", function (e, state) {
          var sbox = $(".signs-percent");
          var lbox = $(".letters-percent");
          var dbox = $(".digits-percent");
          var letters = parseInt(lbox.attr("value"));
          var signs = parseInt(sbox.attr("value"));
          var digits = parseInt(dbox.attr("value"));
          var result = 0;
          var diff = 0;
          if (state == "checked") {
            sbox.removeAttr("enabled");
            sbox.children("input.value").removeAttr("disabled");
            sbox.children("button.arrow").removeAttr("disabled");
            if (letters < signs) {
              result = digits - signs;
              dbox.attr("value", result);
              dbox.children("input.value").attr("value", result);
            } else {
              result = letters - signs;
              lbox.attr("value", result);
              lbox.children("input.value").attr("value", result);
            }
          } else {
            sbox.attr("enabled", "false");
            sbox.children("input.value").attr("disabled", "disabled");
            sbox.children("button.arrow").attr("disabled", "disabled");
            if (letters < signs) {
              result = digits + signs;
              dbox.attr("value", result);
              dbox.children("input.value").attr("value", result);
            } else {
              result = letters + signs;
              lbox.attr("value", result);
              lbox.children("input.value").attr("value", result);
            }
          }
        });

        $("h3.advanced-options").click(function () {
          $(this).toggleClass("selected");
          $("div.advanced-options").slideToggle(500);
        });

        $(".letters-percent").bind("changevalue", function (e, value) {
          var usesigns = ($(".usesigns[state=checked]").length == 1);
          var dbox = $(".digits-percent");
          var sbox = $(".signs-percent");
          var lbox = $(this);
          var L = parseInt(value);
          var D = parseInt(dbox.attr("value"));
          var S = parseInt(sbox.attr("value")) * (usesigns ? 1 : 0);
          if (usesigns) {
            if (D > 0) {
              D = 100 - (L + S);
              dbox.attr("value", D);
              dbox.children("input.value").val(D);
            } else {
              S = 100 - (L + D);
              sbox.attr("value", S);
              sbox.children("input.value").val(S);
            }
          } else {
            D = 100 - (L + S);
            dbox.attr("value", D);
            dbox.children("input.value").val(D);
          }
        });

        $(".digits-percent").bind("changevalue", function (e, value) {
          var dbox = $(this);
          var sbox = $(".signs-percent");
          var lbox = $(".letters-percent");
          var usesigns = ($(".usesigns[state=checked]").length == 1);
          var L = parseInt(lbox.attr("value"));
          var D = parseInt(value);
          var S = parseInt(sbox.attr("value")) * (usesigns ? 1 : 0);
          if (usesigns) {
            if (S > 0) {
              S = 100 - (L + D);
              sbox.attr("value", S);
              sbox.children("input.value").val(S);
            } else {
              L = 100 - (D + S);
              lbox.attr("value", L);
              lbox.children("input.value").val(L);
            }

          } else {
            L = 100 - (D + S);
            lbox.attr("value", L);
            lbox.children("input.value").val(L);
          }
        });

        $(".signs-percent").bind("changevalue", function (e, value) {
          var dbox = $(".digits-percent");
          var sbox = $(this);
          var lbox = $(".letters-percent");
          var L = parseInt(lbox.attr("value"));
          var D = parseInt(dbox.attr("value"));
          var S = parseInt(value);
          if (D > 0) {
            D = 100 - (L + S);
            dbox.attr("value", D);
            dbox.children("input.value").val(D);
          } else {
            L = 100 - (D + S);
            lbox.attr("value", L);
            lbox.children("input.value").val(L);
          }
          lbox.attr("value", result);
          lbox.children("input.value").val(result);
        });
      });
    });
  </script>
  <script type="text/javascript" id="generator-helper">
    function FormatNumber(nStr) {
      nStr += '';
      x = nStr.split(' ');
      x1 = x[0];
      x2 = x.length > 1 ? '.' + x[1] : '';
      var rgx = /(\d+)(\d{3})/;
      while (rgx.test(x1)) {
        x1 = x1.replace(rgx, '$1' + ' ' + '$2');
      }
      return x1 + x2;
    }

    function FormatTime(value, type) {
      var strings = [["год", "года", "лет"], ["месяц", "месяца", "месяцов"], ["день", "дня", "дней"], ["час", "часа", "часов"], ["минута", "минуты", "минут"], ["сек", "сек", "сек"]];
      var result = "";
      if (value > 0) {
        if ((value % 100) > 10 && (value % 100) < 20) {
          return value + " " + strings[type][2];
        }
        switch (value % 10) {
          case 1:
            result = value + " " + strings[type][0];
            break;
          case 2:
          case 3:
          case 4:
            result = value + " " + strings[type][1];
            break;
          default:
            result = value + " " + strings[type][2];
            break;
        }
      }
      return result;
    }

    function FormatThousandTime(value) {
      var result = "";
      if (value > 1000 && value < 1000000) {
        result = "~ " + Math.round(value / 1000) + " тыс. лет";
      }
      if (value >= 1000000 && value < 1000000000) {
        result = "~ " + Math.round(value / 1000000) + " млн лет";
      }
      if (value >= 1000000000 && value < 1000000000000) {
        result = "~ " + Math.round(value / 1000000000) + " млрд лет";
      }
      if (value >= 1000000000000) {
        result = "Более квадриллиона лет";
      }
      return result;
    }

    function TimeConvert(seconds) {
      var years = 0;
      var months = 0;
      var days = 0;
      var hours = 0;
      var minutes = 0;
      var secs = 0;
      var remainder = 0;
      var result = "";
      if (seconds < 1) {
        return "И вы считаете это паролем?";
      }
      days = Math.floor(seconds / 86400);
      remainder = seconds % 86400;
      hours = Math.floor(remainder / 3600);
      remainder = seconds % 3600;
      minutes = Math.floor(remainder / 60);
      remainder = seconds % 60;
      secs = Math.round(remainder);
      years = Math.floor(days / 365);
      remainder = days % 365;
      months = Math.floor(remainder / 30);
      remainder = days % 30;
      days = Math.round(remainder);
      if (seconds <= 59 && minutes == 0)
        result = "~ " + FormatTime(secs, 5);
      if (seconds < 3600 && minutes <= 59)
        result = "~ " + FormatTime(minutes, 4) + " " + FormatTime(secs, 5);
      if (seconds < 86400 && seconds >= 3600 && hours <= 23)
        result = "~ " + FormatTime(hours, 3) + " " + FormatTime(minutes, 4);
      if (seconds < (86400 * 30) && days >= 1)
        result = "~ " + FormatTime(days, 2) + " " + FormatTime(hours, 3);
      if (seconds >= (86400 * 30)) {
        if (years == 0 && months >= 1)
          result = "~ " + FormatTime(months, 1) + " " + FormatTime(days, 2);
        if (years <= 10 && years >= 1)
          result = "~ " + FormatTime(years, 0) + " " + FormatTime(months, 1);
        if (years > 10 && years <= 1000)
          result = "~ " + FormatTime(years, 0);
        if (years > 1000)
          result = FormatThousandTime(years);
      }
      return result;
    }

    function SelectText(obj) {
      var e = obj;
      if (window.getSelection) {
        var s = window.getSelection();
        if (s.setBaseAndExtent) {
          s.setBaseAndExtent(e, 0, e, e.innerText.length - 1);
        } else {
          var r = document.createRange();
          r.selectNodeContents(e);
          s.removeAllRanges();
          s.addRange(r);
        }
      } else if (document.getSelection) {
        var s = document.getSelection();
        var r = document.createRange();
        r.selectNodeContents(e);
        s.removeAllRanges();
        s.addRange(r);
      } else if (document.selection) {
        var r = document.body.createTextRange();
        r.moveToElementText(e);
        r.select();
      }
    }
  </script>
  <script type="text/javascript" id="generator">
    jQuery(function ($) {
      var doRequest = true;
      var passwords = 0;
      var counterWrapper = $("#passwordsCounter");
      $(".do").click(function () {
        try {
          var gena = new Builder();
          var entropy = new Entropy();
          var resultList = $(".result-list");
          var resultItem = $(".result-item.example");
          var counter = parseInt(counterWrapper.html());
          var tempItem = null;
          var result = "";
          var result_entropy = 0;
          gena.length = parseInt($(".password-length").attr("value"));
          gena.useCyrillic = ($(".usecyr[state=checked]").length == 1);
          gena.useSigns = ($(".usesigns[state=checked]").length == 1);
          gena.Enhanced = ($("h3.advanced-options.selected").length == 1);
          gena.useShuffle = ($(".useshuffle[state=checked]").length == 1);
          gena.shuffleRounds = parseInt($(".shuffle-rounds").attr("value"));
          gena.noRepeat = ($(".norepeat[state=checked]").length == 1);
          gena.digitPercent = parseInt($(".digits-percent").attr("value")) / 100;
          gena.signPercent = (parseInt($(".signs-percent").attr("value")) / 100) * $(".usesigns[state=checked]").length;
          $(".result-item").remove(":not(.example)");
          for (var i = 0; i < 5; i++) {
            result = gena.Result();
            entropy.Password = result;
            result_entropy = Math.round(entropy.Current());
            tempItem = resultItem.clone();
            tempItem.children(".entropy").html(result_entropy);
            if (result_entropy <= 20) {
              tempItem.children(".entropy").addClass("bg-gray");
            }
            if (result_entropy > 20 && result_entropy <= 47) {
              tempItem.children(".entropy").addClass("bg-red");
            }
            if (result_entropy > 47 && result_entropy <= 80) {
              tempItem.children(".entropy").addClass("bg-orange");
            }
            if (result_entropy > 80) {
              tempItem.children(".entropy").addClass("bg-green");
            }
            tempItem.children(".password").children(".value").html(result);
            tempItem.children(".password").children(".time").html(TimeConvert(entropy.BruteForceTime));
            tempItem.children(".password").children(".hash.base64").html(Base64.encode(result));
            tempItem.children(".password").children(".hash.md5").html(MD5(result));
            tempItem.children(".password").children(".value, .hash").click(function () {
              SelectText(this);
            });
            tempItem.removeClass("example");
            tempItem.appendTo(resultList);
            tempItem = null;
          }
          counter += 5;
          counterWrapper.html(counter);
          counterWrapper.trigger("changecount", counter);
          passwords += 5;
        } catch (exc) {
          alert(exc.message);
        }
      });

      var changeCounter = 0;
      requestTimer = setInterval(function () {
        var preChangeCounter = parseInt(counterWrapper.html());
        var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
        if (changeCounter != preChangeCounter) {
          changeCounter = preChangeCounter;
          var data = {
            action: 'passcounter',
            count: passwords,
            timeout: 5000,
          };
          $.post(ajaxurl, data, function (response) {
            counterWrapper.html(response);
          });
          passwords = 0;
        } else {
          var data = {
            action: 'passcounter',
            count: 0,
            timeout: 5000,
          };
          $.post(ajaxurl, data, function (response) {
            counterWrapper.html(response);
          });
        }
      }, 15000);
    })
  </script>
  <?php
}