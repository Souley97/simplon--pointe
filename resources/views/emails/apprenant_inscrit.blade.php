<!DOCTYPE html>
<html>
<head>
    <title>Bienvenue dans la promotion</title>
</head>
<body>
    <h1>Bonjour {{ $prenom }} {{ $nom }}</h1>
    <p>Vous avez été inscrit dans notre promotion avec succès !</p>
    <p>Voici vos informations de connexion :</p>
    <ul>
        <li>Email : {{ $email }}</li>
    </ul>
    <p>Veuillez changer votre mot de passe après votre première connexion.</p>
    <p>Cordialement,</p>
    <p>L'équipe de formation</p>
</body>
</html>
