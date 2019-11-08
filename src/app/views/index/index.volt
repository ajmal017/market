Instruments:
{% for instrument in instruments %}
	{{ instrument.symbol }} {{ instrument.name }} {{ instrument.contract_volume }}
{% endfor %}
