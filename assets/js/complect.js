$(function() {
	$(".count").change(function (){
		var id = this.id.substr(6);
		countChange(id);
	});
	$("#complect_count").change(function (){
		sum();
	});
	$(".undo").click(function (){
		var id = this.id.substr(5);
		$("#row_"+id+" .undo").hide();
		$("#count_"+id).val($("#base_count_"+id).val());
		countChange(id);
	});
});

function countChange(id){
	if($("#count_"+id).val() != $("#base_count_"+id).val()){
		$("#row_"+id+" .undo").show();
		$("#row_"+id).addClass("changed");
	}else{
		$("#row_"+id+" .undo").hide();
		$("#row_"+id).removeClass("changed");
	}
	sum();
	$("#row_"+id+" .row-sum span").html(format_rub(parseInt($("#price_"+id).val())*parseInt($("#count_"+id).val())));
}

function sum(){
	var sum = 0;
	$(".row").each(function (){
		var id = this.id.substr(4);
		sum += parseInt($("#count_"+id).val())*parseInt($("#price_"+id).val());
	});
	$("#complect_sum span").html(format_rub(sum));
	$("#sum span").html(format_rub(sum*parseInt($("#complect_count").val())));
}

function format_rub(val){
	return number_format(val, 0, ',', '&nbsp;');
}

function number_format (number, decimals, dec_point, thousands_sep) {
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function (n, prec) {
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