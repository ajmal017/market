<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Margeen: {% block title %}Home{% endblock %}</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    {% block head %}
    {% endblock %}
</head>
<body>

<h1>{% block heading %}Home{% endblock %}</h1>
{% block content %}
{% endblock %}

</body>
</html>
