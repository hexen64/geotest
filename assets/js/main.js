$(function() {

    /* complects ============================================================= */

    $(".btn_order").click(function(e) {
        e.preventDefault();

        var label = $(this).val();
        $(this).attr('disabled', true).val('Добавляется к заказу');

        var form_index = $(this).attr('data-index');
        var $inputs = $('#form_order_' + form_index + ' input');
        var data = {};
        $inputs.each(function() {
            var $this = $(this);

            var name = $this.attr('name');

            if ($this.attr('type') !== 'radio') {
                data[name] = $this.val();
            } else if ($this.is(':checked')) {
                data[name] = $this.val();
            }
        });

        data['json'] = 1;

        $.ajax({
            url: "/complect/order",
            data: data,
            dataType: "json",
            type: "post",
            success: function(data, textStatus, jqXHR) {
                $(this).attr('disabled', false).val(label);
                if (data.error) {
                    if (data.result.errors.global) {
                        $("#error_global")
                            .html(data.result.errors.global)
                            .show();
                    }
                    if (data.result.errors.form) {
                        for (var key in data.result.errors.form) {
                            var k = key.replace('variant_', 'variant_'+form_index+'_');
                            var $el = $('#' + k + '_error');
                            if (!$el.get(0)) {
                                $('<ul><li id="' + k + '_error" class="error_list">' + data.result.errors.form[key] + '</li></ul>').appendTo($('#' + k).parent());
                            } else {
                                $el.html(data.result.errors.form[key]).show();
                            }
                        }
                    }
                } else {
                    $('.error_list').hide();
                    $.cookie('idorder', data.result.idorder, {expires: 2592000, path: '/'});
                    window.location.href = '/order/' + data.result.idorder;
                }
            }
        });
    });

    $(".row-count input.count").bind('change keyup', function(event) {
        var value = parseInt($(this).val());
        var match = this.name.match(/\[([^\[]+)\]\[count\]/);

        if (match) {
            var id = match[1];

            if (value === $("#row_" + id + " .base_count").val()) {
                $("#undo_" + id).hide();
            } else {
                $("#undo_" + id).show();
            }

            $("#row_" + id + " .row-sum span.text-nowrap").html(format_rub(value * $("#row_" + id + " input.price").val()));
            calc_complect_price();
            order_total();
        }
    });

    $(".btn-undo").click(function() {
        var match = this.id.match(/undo_(.+)/);

        if (match) {
            var id = match[1];
            $(this).hide();
            var value = $("#row_" + id + " input.base_count").val();
            $("#row_" + id + " input.count").val(value);
            $("#row_" + id + " .row-sum span.text-nowrap").html(format_rub(value * $("#row_" + id + " input.price").val()));
            calc_complect_price();
        }
    });

    $('form').each(function(index, form) {
        $('#variant_' + index + '_count').bind('change keyup', function() {
            calc_complect_price();
        });
    });

    /* ======================================================================= */

    /* rows ================================================================== */

    $("#btn_order_row").click(function(e) {
        e.preventDefault();

        var label = $(this).html();
        $(this).attr('disabled', true).html('Добавляется к заказу');

        var $inputs = $('#form_order_row :input');
        var data = {};

        $inputs.each(function() {
            var $this = $(this);

            if ($this.attr('type') === 'radio') {
                if ($this.is(':checked')) {
                    data[this.name] = $this.val();
                }
            } else {
                data[this.name] = $this.val();
            }
        });

        data['json'] = 1;

        $.ajax({
            url: "/row/order",
            data: data,
            dataType: "json",
            type: "post",
            success: function(data, textStatus, jqXHR) {
                $(this).attr('disabled', false).html(label);

                if (data.error) {
                    if (data.result.errors.global) {
                        $("#error_global")
                            .html(data.result.errors.global)
                            .show();
                    }
                    if (data.result.errors.form) {
                        for (var key in data.result.errors.form) {
                            if (!$("#" + key + "_error").get(0)) {
                                $('<ul></ul>')
                                    .html('<li>' + data.result.errors.form[key] + '</li>')
                                    .attr('id', key + "_error")
                                    .addClass('error_list')
                                    .appendTo($("#" + key).parent());
                            } else {
                                $("#" + key + "_error")
                                    .html(data.result.errors.form[key])
                                    .show();
                            }
                        }
                    }
                } else {
                    $(".error_list").hide();
                    $.cookie("idorder", data.result.idorder, {expires: 2592000, path: '/'});
                    window.location.href = '/order/' + data.result.idorder;
                }
            }
        });
    });

    $("#row_count").bind('change keyup', function() {
        $("#row_total_price span.text-nowrap").html(format_rub(parseInt($("#row_price").val()) * parseInt($("#row_count").val())));
    });

    /* ======================================================================= */

    $('.expand-title').click(function() {
        $(this).parent().toggleClass('open');
    });

    if (document.location.hash) {
        $('.variant').each(function(index, el) {
            var $el = $(el),
                id = '#' + $el.attr('data-id');
            if (id === document.location.hash) {
                $('.variant').removeClass('open');
                $el.addClass('open');
            }
        });
    }

    $('.variant-header').click(function() {
        var el = $(this).parent();
        if (!el.hasClass('open')) {
            document.location.hash = el.attr('data-id');
            $('.variant').removeClass('open');
            el.addClass('open');
            $("html, body").animate({scrollTop: el.offset().top});
        }
    });
});

function calc_complect_price() {
    $('form').each(function(index, form) {
        var total = 0;
        var $form = $(form);
        $('.row', $form).each(function(index_tr, tr) {
            total += parseInt($('.price', $(tr)).val()) * parseInt($('.count', $(tr)).val());
        });
        $('#variant_price_' + index + ' span.text-nowrap').html(format_rub(total));
        $('#total_price_' + index + ' span.text-nowrap').html(format_rub(total * parseInt($('#variant_' + index + '_count').val())));
    });
}

function order_total() {
    var total = 0;

    $(".order-complect").each(function() {
        var id = $(this).attr("id").match(/(\d+)/)[1];
        total += parseInt($("#row_" + id + " .price").val()) * parseInt($("#row_" + id + " .count").val());
    });

    $(".order-row").each(function() {
        var id = $(this).attr("id").match(/(\d+)/)[1];
        total += parseInt($("#row_" + id + " .price").val()) * parseInt($("#row_" + id + " .count").val());
    });

    $("#order_total2 span.text-nowrap").html(format_rub(total));
}

function format_rub(val) {
    return number_format(val, 0, ',', '&nbsp;');
}

function number_format(number, decimals, dec_point, thousands_sep) {
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function(n, prec) {
            var k = Math.pow(10, prec);
            return '' + Math.round(n * k) / k;
        };
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
}

function is_int(input) {
    return typeof (input) == 'number' && parseInt(input) == input;
}