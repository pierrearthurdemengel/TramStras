{% extends 'base.html.twig' %}

{% block title %}Réinitialiser votre Mot de passe{% endblock %}

{% block body %}
    {% for flash_error in app.flashes('reset_password_error') %}
        <div class="alert alert-danger" role="alert">{{ flash_error }}</div>
    {% endfor %}
    <h1>Réinitialiser votre Mot de passe</h1>
<div class="card">
    {{ form_start(requestForm) }}
        {{ form_row(requestForm.<?= $email_field ?>) }}
        <div>
            <small>
                Entrez votre adresse email, et nous vous envoirons un email avec un liens pour réinitialiser votre mot de passe.
            </small>
        </div>
</div>
        <button class="btn btn-primary">Envoyer le mail de récupération de mot de passe</button>
    {{ form_end(requestForm) }}
{% endblock %}
