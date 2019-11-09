{% set title = 'Instruments' %}

<table>
{% for instrument in instruments %}
	<tr>
		<td><a href="/graph/history-price/{{ instrument.symbol }}">{{ instrument.symbol }}</a></td>
		<td><a href="/graph/history-price/{{ instrument.symbol }}">{{ instrument.name }}</a></td>
		<td>{{ instrument.contract_volume }}</td>
	</tr>
{% endfor %}
</table>
