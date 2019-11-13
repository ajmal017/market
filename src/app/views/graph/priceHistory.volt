{% extends 'layout.volt' %}

{% block title %}Price history{% endblock %}
{% block heading %}Price history{% endblock %}

{% block head %}
<script>
	window.onload = function() {

	var chart = new CanvasJS.Chart("chartContainer", {
		title: {
			text: "{{ instrument.symbol }}"
		},
		subtitles: [{
			text: "{{ instrument.contract_volume }}"
		}],
		axisX: {
			valueFormatString: "DD MMM YYYY"
		},
		axisY: {
			includeZero: false,
			suffix: " USD"
		},
		data: [{
			type: "candlestick",
			xValueType: "dateTime",
			yValueFormatString: "#,##0.0 USD",
			xValueFormatString: "DD MMM YYYY",
			dataPoints: [{% for tradeDay in tradeDays %}
				{
					'x': {{ tradeDay.getTime() * 1000 }},
					'y': [
						{{ tradeDay.open }},
						{{ tradeDay.high }},
						{{ tradeDay.low }},
						{{ tradeDay.settle }}
					]
				}{% if !loop.last %},{% endif %}
			{% endfor %}]
		}]
	});
	chart.render();

	}
</script>
{% endblock %}

{% block content %}
{{ dump(contract.id) }}
<div id="chartContainer" style="height: 370px; width: 100%;"></div>
<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
{% endblock %}
